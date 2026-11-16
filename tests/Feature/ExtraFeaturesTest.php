<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtraFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }
    private function customer(): User { return User::factory()->create(['role' => 'user']); }
    private function book(array $a = []): Book
    {
        return Book::create(array_merge(['title' => 'Laravel', 'author' => 'Taylor', 'price' => 10, 'stock' => 5], $a));
    }

    /** កម្មង់ ហើយ Admin អនុម័ត */
    private function approvedOrder(User $c, Book $b, int $qty = 1): Order
    {
        $this->actingAs($c)->post("/cart/{$b->id}", ['quantity' => $qty]);
        $this->actingAs($c)->post('/orders');
        $order = Order::latest('id')->firstOrFail();
        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/approve");

        return $order->fresh();
    }

    // ---------- Payment (KHQR) ----------

    public function test_qr_image_exists_and_is_shown_on_approved_order(): void
    {
        $this->assertFileExists(public_path(config('bookstore.khqr.image')));

        $c = $this->customer();
        $order = $this->approvedOrder($c, $this->book());

        $this->actingAs($c)->get("/orders/{$order->id}")->assertOk()->assertSee('images/khqr.jpg')->assertSee('KHQR');
    }

    public function test_customer_submits_proof_and_admin_confirms_payment(): void
    {
        Storage::fake('public');
        Notification::fake();
        $c = $this->customer();
        $order = $this->approvedOrder($c, $this->book(), 2);

        $this->actingAs($c)->post("/orders/{$order->id}/payment", [
            'payment_ref' => 'TXN12345',
            'payment_proof' => UploadedFile::fake()->image('proof.png'),
        ])->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('submitted', $order->payment_status);
        $this->assertSame('TXN12345', $order->payment_ref);
        Storage::disk('public')->assertExists($order->payment_proof);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/paid")->assertSessionHas('success');
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
        Notification::assertSentTo($c, OrderStatusChanged::class, fn ($n) => $n->event === 'paid');
    }

    public function test_payment_needs_no_reference_or_proof(): void
    {
        $c = $this->customer();
        $order = $this->approvedOrder($c, $this->book());

        $this->actingAs($c)->post("/orders/{$order->id}/payment", [])->assertSessionHasNoErrors();
        $this->assertSame('submitted', $order->fresh()->payment_status);
    }

    public function test_cannot_pay_someone_elses_order(): void
    {
        $owner = $this->customer();
        $approved = $this->approvedOrder($this->customer(), $this->book(['title' => 'B2']));

        $this->actingAs($owner)->post("/orders/{$approved->id}/payment", ['payment_ref' => 'X'])->assertForbidden();
        $this->assertSame('unpaid', $approved->fresh()->payment_status);
    }

    public function test_admin_can_reject_payment_proof_and_cash_payment_works(): void
    {
        $c = $this->customer();
        $order = $this->approvedOrder($c, $this->book());
        $order->update(['payment_status' => 'submitted', 'payment_ref' => 'BAD']);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/unpaid");
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->payment_ref);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/paid"); // សាច់ប្រាក់
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_customer_cannot_mark_paid(): void
    {
        $c = $this->customer();
        $order = $this->approvedOrder($c, $this->book());
        $this->actingAs($c)->post("/admin/orders/{$order->id}/paid")->assertRedirect(route('books.index'));
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    // ---------- Notifications ----------

    public function test_emails_are_sent_on_new_order_approve_and_reject(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $c = $this->customer();
        $b = $this->book();

        $this->actingAs($c)->post("/cart/{$b->id}");
        $this->actingAs($c)->post('/orders');
        Notification::assertSentTo($admin, NewOrderPlaced::class);

        $order = Order::firstOrFail();
        $this->actingAs($admin)->post("/admin/orders/{$order->id}/approve");
        Notification::assertSentTo($c, OrderStatusChanged::class, fn ($n) => $n->event === 'approved');

        $this->actingAs($c)->post("/cart/{$b->id}");
        $this->actingAs($c)->post('/orders');
        $second = Order::latest('id')->first();
        $this->actingAs($admin)->post("/admin/orders/{$second->id}/reject", ['reject_reason' => 'x']);
        Notification::assertSentTo($c, OrderStatusChanged::class, fn ($n) => $n->event === 'rejected');
    }

    public function test_low_stock_command_notifies_admins_only_when_needed(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $this->book(['stock' => 50]);

        $this->artisan('books:low-stock')->assertSuccessful();
        Notification::assertNothingSent();

        $this->book(['title' => 'Low', 'stock' => 2]);
        $this->artisan('books:low-stock')->assertSuccessful();
        Notification::assertSentTo($admin, LowStockAlert::class);
    }

    // ---------- Stock movements ----------

    public function test_every_stock_change_is_recorded(): void
    {
        $admin = $this->admin();

        // បង្កើតសៀវភៅ → initial
        $this->actingAs($admin)->post('/books', ['title' => 'T', 'author' => 'A', 'price' => 5, 'stock' => 10])->assertRedirect('/books');
        $book = Book::firstOrFail();
        $this->assertSame(10, $book->stock);
        $this->assertSame('initial', $book->movements()->first()->type);

        // លក់ → sale (-3)
        $this->actingAs($admin)->post('/sales', ['items' => [['book_id' => $book->id, 'quantity' => 3]]]);
        $m = $book->movements()->first();
        $this->assertSame(['sale', -3, 7], [$m->type, $m->change, $m->balance]);

        // កែដោយដៃ → adjust (+3 → 10)
        $this->actingAs($admin)->put("/books/{$book->id}", ['title' => 'T', 'author' => 'A', 'price' => 5, 'stock' => 10]);
        $m = $book->movements()->first();
        $this->assertSame(['adjust', 3, 10], [$m->type, $m->change, $m->balance]);

        // កែដោយមិនប្តូរស្តុក → គ្មាន movement ថ្មី
        $count = StockMovement::count();
        $this->actingAs($admin)->put("/books/{$book->id}", ['title' => 'T2', 'author' => 'A', 'price' => 5, 'stock' => 10]);
        $this->assertSame($count, StockMovement::count());

        // បោះបង់ការលក់ → void
        $this->actingAs($admin)->delete('/sales/'.\App\Models\Sale::first()->id);
        $this->assertSame('void', $book->movements()->first()->type);
        $this->assertSame(13, $book->fresh()->stock);
    }

    public function test_approved_order_records_sale_movement(): void
    {
        $b = $this->book(['stock' => 5]);
        $this->approvedOrder($this->customer(), $b, 2);
        $m = $b->movements()->first();
        $this->assertSame(['sale', -2, 3], [$m->type, $m->change, $m->balance]);
    }

    // ---------- Suppliers & purchases ----------

    public function test_purchase_restocks_book_and_logs_movement(): void
    {
        $admin = $this->admin();
        $sup = Supplier::create(['name' => 'ABC Books']);
        $b = $this->book(['stock' => 1]);

        $this->actingAs($admin)->post('/purchases', ['book_id' => $b->id, 'supplier_id' => $sup->id, 'quantity' => 20, 'unit_cost' => 4.5])
            ->assertRedirect('/purchases');

        $this->assertSame(21, $b->fresh()->stock);
        $this->assertSame(1, Purchase::count());
        $this->assertEquals(90, Purchase::first()->total_cost);
        $this->assertSame('restock', $b->movements()->first()->type);
    }

    public function test_purchase_validation_and_access(): void
    {
        $b = $this->book();
        $this->actingAs($this->admin())->post('/purchases', ['book_id' => $b->id, 'quantity' => 0, 'unit_cost' => -1])
            ->assertSessionHasErrors(['quantity', 'unit_cost']);

        $this->actingAs($this->customer())->get('/purchases')->assertRedirect(route('books.index'));
        $this->actingAs($this->customer())->post('/purchases', ['book_id' => $b->id, 'quantity' => 5, 'unit_cost' => 1])->assertRedirect(route('books.index'));
        $this->assertSame(5, $b->fresh()->stock);
    }

    public function test_supplier_crud(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/suppliers', ['name' => 'S1', 'phone' => '012'])->assertSessionHas('success');
        $s = Supplier::firstOrFail();
        $this->actingAs($admin)->put("/suppliers/{$s->id}", ['name' => 'S2'])->assertSessionHas('success');
        $this->assertSame('S2', $s->fresh()->name);
        $this->actingAs($admin)->delete("/suppliers/{$s->id}");
        $this->assertSame(0, Supplier::count());
        $this->actingAs($admin)->post('/suppliers', ['name' => ''])->assertSessionHasErrors('name');
    }

    // ---------- Reviews ----------

    public function test_only_buyers_can_review_and_one_review_per_user(): void
    {
        $b = $this->book();
        $stranger = $this->customer();
        $this->actingAs($stranger)->post("/books/{$b->id}/reviews", ['rating' => 5])->assertSessionHas('error');
        $this->assertSame(0, Review::count());

        $buyer = $this->customer();
        $this->approvedOrder($buyer, $b);
        $this->actingAs($buyer)->post("/books/{$b->id}/reviews", ['rating' => 5, 'comment' => 'ល្អ'])->assertSessionHas('success');
        $this->actingAs($buyer)->post("/books/{$b->id}/reviews", ['rating' => 3])->assertSessionHas('success');

        $this->assertSame(1, Review::count());
        $this->assertSame(3, Review::first()->rating);
        $this->actingAs($buyer)->get("/books/{$b->id}")->assertOk()->assertSee('អ្នកបានទិញ');
    }

    public function test_review_validation_and_deletion_rules(): void
    {
        $b = $this->book();
        $buyer = $this->customer();
        $this->approvedOrder($buyer, $b);
        $this->actingAs($buyer)->post("/books/{$b->id}/reviews", ['rating' => 9])->assertSessionHasErrors('rating');

        $this->actingAs($buyer)->post("/books/{$b->id}/reviews", ['rating' => 4]);
        $review = Review::firstOrFail();

        $this->actingAs($this->customer())->delete("/reviews/{$review->id}")->assertForbidden();
        $this->actingAs($this->admin())->delete("/reviews/{$review->id}")->assertSessionHas('success');
        $this->assertSame(0, Review::count());
    }

    // ---------- ISBN & import ----------

    public function test_isbn_is_unique_and_searchable(): void
    {
        $admin = $this->admin();
        $this->book(['isbn' => '9781234567890', 'title' => 'ByIsbn']);

        $this->actingAs($admin)->post('/books', ['title' => 'Dup', 'author' => 'A', 'price' => 1, 'stock' => 1, 'isbn' => '9781234567890'])
            ->assertSessionHasErrors('isbn');

        $res = $this->actingAs($admin)->get('/books?search=9781234567890');
        $this->assertCount(1, $res->viewData('books'));
    }

    public function test_csv_import_creates_updates_and_reports_errors(): void
    {
        $admin = $this->admin();
        $existing = $this->book(['title' => 'Old', 'author' => 'Ann', 'isbn' => '111', 'stock' => 2, 'price' => 5]);

        $csv = "\xEF\xBB\xBF".implode("\n", [
            'title,author,isbn,category,price,stock,description',
            'សៀវភៅថ្មី,អ្នកនិពន្ធ,222,អប់រំ,12.5,8,ពិពណ៌នា',
            'Old Renamed,Ann,111,Tech,9,6,',        // ISBN ដូចគ្នា → កែប្រែ
            ',NoTitle,333,,5,1,',                    // កំហុស: គ្មានចំណងជើង
            'BadPrice,Bob,444,,abc,1,',              // កំហុស: តម្លៃមិនមែនលេខ
        ]);
        $file = UploadedFile::fake()->createWithContent('books.csv', $csv);

        $this->actingAs($admin)->post('/books/import', ['file' => $file])->assertRedirect('/books/import');

        $result = session('import_result');
        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['updated']);
        $this->assertCount(2, $result['errors']);

        $this->assertSame('សៀវភៅថ្មី', Book::where('isbn', '222')->value('title'));
        $this->assertSame(8, Book::where('isbn', '222')->value('stock'));
        $existing->refresh();
        $this->assertSame(['Old Renamed', 6, 9.0], [$existing->title, $existing->stock, (float) $existing->price]);
        $this->assertSame('adjust', $existing->movements()->first()->type);
        $this->assertSame(1, \App\Models\Category::where('name', 'អប់រំ')->count());
    }

    public function test_import_rejects_missing_columns_and_customer_access(): void
    {
        $file = UploadedFile::fake()->createWithContent('bad.csv', "name,cost\nx,1\n");
        $this->actingAs($this->admin())->post('/books/import', ['file' => $file])->assertSessionHasErrors('file');

        $this->actingAs($this->customer())->get('/books/import')->assertRedirect(route('books.index'));
        $this->actingAs($this->admin())->get('/books/import/template')->assertOk();
    }

    // ---------- Profile ----------

    public function test_profile_update_name_email_and_password(): void
    {
        $u = $this->customer();

        $this->actingAs($u)->put('/profile', ['name' => 'New Name', 'email' => 'new@example.com'])->assertSessionHas('success');
        $this->assertSame('New Name', $u->fresh()->name);

        $this->actingAs($u)->put('/profile', ['name' => 'N', 'email' => 'new@example.com', 'current_password' => 'wrong', 'password' => 'secret12', 'password_confirmation' => 'secret12'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($u)->put('/profile', ['name' => 'N', 'email' => 'new@example.com', 'current_password' => 'password', 'password' => 'secret12', 'password_confirmation' => 'secret12'])
            ->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret12', $u->fresh()->password));
    }

    public function test_profile_email_must_be_unique(): void
    {
        $other = $this->customer();
        $u = $this->customer();
        $this->actingAs($u)->put('/profile', ['name' => 'N', 'email' => $other->email])->assertSessionHasErrors('email');
    }

    // ---------- UI smoke ----------

    public function test_new_pages_render_for_admin(): void
    {
        $admin = $this->admin();
        $b = $this->book(['stock' => 1]); // ជិតអស់ → បង្ហាញ panel
        foreach (['/suppliers', '/purchases', '/books/import', '/profile', "/books/{$b->id}", '/books'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->get('/books')->assertSee('សៀវភៅត្រូវការបំពេញស្តុក');
    }
}

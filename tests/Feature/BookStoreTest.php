<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function book(array $attrs = []): Book
    {
        return Book::create(array_merge(['title' => 'Laravel', 'author' => 'Taylor', 'price' => 10, 'stock' => 5], $attrs));
    }

    // ---------- Auth ----------

    public function test_user_can_register_but_never_becomes_admin(): void
    {
        $this->post('/register', [
            'name' => 'New', 'email' => 'new@example.com',
            'password' => 'secret1', 'password_confirmation' => 'secret1', 'role' => 'admin',
        ])->assertRedirect('/login');

        $this->assertSame('user', User::where('email', 'new@example.com')->value('role'));
    }

    public function test_register_requires_matching_password_confirmation(): void
    {
        $this->post('/register', ['name' => 'N', 'email' => 'n@example.com', 'password' => 'secret1', 'password_confirmation' => 'x'])
            ->assertSessionHasErrors('password');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $u = $this->customer();
        $this->post('/login', ['email' => $u->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ---------- Role-based access ----------

    public function test_customer_cannot_access_admin_pages(): void
    {
        $c = $this->customer();
        foreach (['/books/create', '/categories', '/sales', '/reports', '/users', '/books/export'] as $url) {
            $this->actingAs($c)->get($url)->assertRedirect(route('books.index'));
        }
        $this->actingAs($c)->post('/books', ['title' => 'x', 'author' => 'y', 'price' => 1, 'stock' => 1])->assertRedirect(route('books.index'));
        $this->assertSame(0, Book::count());
    }

    public function test_customer_can_browse_and_view_books(): void
    {
        $book = $this->book();
        $this->actingAs($this->customer())->get('/books')->assertOk()->assertSee('Laravel');
        $this->actingAs($this->customer())->get("/books/{$book->id}")->assertOk();
    }

    public function test_customer_does_not_see_internal_stats_but_admin_does(): void
    {
        $this->book();
        $this->actingAs($this->customer())->get('/books')->assertOk()->assertDontSee('តម្លៃស្តុកសរុប');
        $this->actingAs($this->admin())->get('/books')->assertOk()->assertSee('តម្លៃស្តុកសរុប');
    }

    // ---------- Books ----------

    public function test_admin_can_create_update_and_delete_book(): void
    {
        $admin = $this->admin();
        $cat = Category::create(['name' => 'Tech']);

        $this->actingAs($admin)->post('/books', [
            'title' => 'PHP', 'author' => 'Rasmus', 'price' => 12.5, 'stock' => 3, 'category_id' => $cat->id,
        ])->assertRedirect('/books');

        $book = Book::firstOrFail();
        $this->assertSame($cat->id, $book->category_id);

        $this->actingAs($admin)->put("/books/{$book->id}", ['title' => 'PHP 8', 'author' => 'Rasmus', 'price' => 15, 'stock' => 0])->assertRedirect('/books');
        $this->assertSame('PHP 8', $book->fresh()->title);

        $this->actingAs($admin)->delete("/books/{$book->id}")->assertRedirect('/books');
        $this->assertSame(0, Book::count());
    }

    public function test_book_validation_rejects_bad_input(): void
    {
        $this->actingAs($this->admin())->post('/books', ['title' => '', 'author' => 'x', 'price' => 'abc', 'stock' => -1])
            ->assertSessionHasErrors(['title', 'price', 'stock']);
    }

    public function test_search_does_not_leak_past_category_filter(): void
    {
        $a = Category::create(['name' => 'A']);
        $b = Category::create(['name' => 'B']);
        $this->book(['title' => 'Alpha', 'author' => 'Zed', 'category_id' => $a->id]);
        $this->book(['title' => 'Beta', 'author' => 'Alpha Author', 'category_id' => $b->id]);

        $res = $this->actingAs($this->customer())->get('/books?search=Alpha&category='.$a->id);
        $res->assertOk();
        $this->assertCount(1, $res->viewData('books'));
    }

    public function test_stock_filter_and_badges(): void
    {
        $this->book(['title' => 'Out', 'stock' => 0]);
        $this->book(['title' => 'Low', 'stock' => 2]);
        $this->book(['title' => 'Plenty', 'stock' => 50]);

        $this->assertSame(['Out'], Book::stockStatus('out')->pluck('title')->all());
        $this->assertSame(['Low'], Book::stockStatus('low')->pluck('title')->all());
        $this->assertSame(['Plenty'], Book::stockStatus('in')->pluck('title')->all());
    }

    public function test_csv_export_contains_books(): void
    {
        $this->book(['title' => 'ExportMe']);
        $res = $this->actingAs($this->admin())->get('/books/export');
        $res->assertOk();
        $this->assertStringContainsString('ExportMe', $res->streamedContent());
    }

    // ---------- Sales ----------

    public function test_sale_deducts_stock_and_snapshots_price(): void
    {
        $book = $this->book(['price' => 10, 'stock' => 5]);

        $this->actingAs($this->admin())->post('/sales', [
            'customer_name' => 'Dara',
            'items' => [['book_id' => $book->id, 'quantity' => 2]],
        ])->assertRedirect();

        $sale = Sale::with('items')->firstOrFail();
        $this->assertSame(3, $book->fresh()->stock);
        $this->assertEquals(20, $sale->total);
        $this->assertSame('Laravel', $sale->items->first()->title);

        // ប្តូរតម្លៃក្រោយលក់ មិនប៉ះពាល់វិក្កយបត្រចាស់
        $book->update(['price' => 99]);
        $this->assertEquals(10, $sale->items()->first()->price);
    }

    public function test_sale_rejects_quantity_above_stock_and_changes_nothing(): void
    {
        $book = $this->book(['stock' => 2]);

        $this->actingAs($this->admin())->post('/sales', ['items' => [['book_id' => $book->id, 'quantity' => 3]]])
            ->assertSessionHasErrors('items');

        $this->assertSame(2, $book->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_duplicate_lines_are_merged_before_checking_stock(): void
    {
        $book = $this->book(['stock' => 3]);

        $this->actingAs($this->admin())->post('/sales', ['items' => [
            ['book_id' => $book->id, 'quantity' => 2], ['book_id' => $book->id, 'quantity' => 2],
        ]])->assertSessionHasErrors('items');

        $this->assertSame(3, $book->fresh()->stock);
    }

    public function test_voiding_sale_restores_stock(): void
    {
        $book = $this->book(['stock' => 5]);
        $admin = $this->admin();
        $this->actingAs($admin)->post('/sales', ['items' => [['book_id' => $book->id, 'quantity' => 4]]]);
        $this->assertSame(1, $book->fresh()->stock);

        $this->actingAs($admin)->delete('/sales/'.Sale::first()->id)->assertRedirect('/sales');
        $this->assertSame(5, $book->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_invoice_survives_book_deletion(): void
    {
        $book = $this->book();
        $admin = $this->admin();
        $this->actingAs($admin)->post('/sales', ['items' => [['book_id' => $book->id, 'quantity' => 1]]]);
        $book->delete();

        $this->actingAs($admin)->get('/sales/'.Sale::first()->id)->assertOk()->assertSee('Laravel');
    }

    public function test_reports_page_renders(): void
    {
        $book = $this->book();
        $admin = $this->admin();
        $this->actingAs($admin)->post('/sales', ['items' => [['book_id' => $book->id, 'quantity' => 1]]]);
        $this->actingAs($admin)->get('/reports?days=7')->assertOk()->assertSee('Laravel');
    }

    // ---------- User management ----------

    public function test_admin_can_change_role_but_not_own_role_or_delete_self(): void
    {
        $admin = $this->admin();
        $other = $this->customer();

        $this->actingAs($admin)->patch("/users/{$other->id}/role", ['role' => 'admin'])->assertRedirect();
        $this->assertSame('admin', $other->fresh()->role);

        $this->actingAs($admin)->patch("/users/{$admin->id}/role", ['role' => 'user']);
        $this->assertSame('admin', $admin->fresh()->role);

        $this->actingAs($admin)->delete("/users/{$admin->id}");
        $this->assertNotNull($admin->fresh());

        $this->actingAs($admin)->delete("/users/{$other->id}");
        $this->assertNull($other->fresh());
    }

    public function test_role_must_be_valid(): void
    {
        $admin = $this->admin();
        $other = $this->customer();
        $this->actingAs($admin)->patch("/users/{$other->id}/role", ['role' => 'superuser'])->assertSessionHasErrors('role');
    }

    // ---------- API ----------

    public function test_api_requires_token_and_only_admin_can_create(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();

        $token = $this->postJson('/api/register', ['name' => 'Api', 'email' => 'api@example.com', 'password' => 'secret1'])
            ->assertCreated()->json('access_token');

        $this->withToken($token)->getJson('/api/books')->assertOk();
        $this->withToken($token)->postJson('/api/books', ['title' => 'x', 'author' => 'y', 'price' => 1])->assertForbidden();
    }
}

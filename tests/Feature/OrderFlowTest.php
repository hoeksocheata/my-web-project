<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }
    private function customer(): User { return User::factory()->create(['role' => 'user']); }
    private function book(array $a = []): Book
    {
        return Book::create(array_merge(['title' => 'Laravel', 'author' => 'Taylor', 'price' => 10, 'stock' => 5], $a));
    }

    /** អតិថិជនដាក់កន្ត្រក ហើយកម្មង់ */
    private function placeOrder(User $c, Book $b, int $qty): Order
    {
        $this->actingAs($c)->post("/cart/{$b->id}", ['quantity' => $qty]);
        $this->actingAs($c)->post('/orders', ['note' => 'សូមរហ័ស'])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    // ---------- Wishlist ----------

    public function test_customer_can_toggle_wishlist(): void
    {
        $c = $this->customer();
        $b = $this->book();

        $this->actingAs($c)->post("/wishlist/{$b->id}")->assertRedirect();
        $this->assertTrue($c->wishlist()->where('books.id', $b->id)->exists());
        $this->actingAs($c)->get('/wishlist')->assertOk()->assertSee('Laravel');

        $this->actingAs($c)->post("/wishlist/{$b->id}");
        $this->assertFalse($c->wishlist()->where('books.id', $b->id)->exists());
    }

    // ---------- Cart ----------

    public function test_cart_caps_quantity_at_stock_and_rejects_out_of_stock(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 3]);
        $out = $this->book(['title' => 'Out', 'stock' => 0]);

        $this->actingAs($c)->post("/cart/{$b->id}", ['quantity' => 99]);
        $this->assertSame(3, session('cart')[$b->id]);

        $this->actingAs($c)->post("/cart/{$out->id}")->assertSessionHas('error');
        $this->assertArrayNotHasKey($out->id, session('cart'));
    }

    public function test_admin_cannot_use_cart_or_orders_area(): void
    {
        $a = $this->admin();
        $b = $this->book();
        $this->actingAs($a)->post("/cart/{$b->id}")->assertRedirect(route('books.index'));
        $this->actingAs($a)->get('/cart')->assertRedirect(route('books.index'));
        $this->actingAs($a)->get('/wishlist')->assertRedirect(route('books.index'));
    }

    // ---------- Checkout ----------

    public function test_checkout_creates_pending_order_without_touching_stock(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5, 'price' => 12]);

        $order = $this->placeOrder($c, $b, 2);

        $this->assertSame('pending', $order->status);
        $this->assertEquals(24, $order->total);
        $this->assertSame(5, $b->fresh()->stock);        // ស្តុកមិនទាន់កាត់
        $this->assertNull(session('cart'));              // កន្ត្រកត្រូវសម្អាត
        $this->assertSame(0, Sale::count());
    }

    public function test_empty_cart_cannot_checkout(): void
    {
        $this->actingAs($this->customer())->post('/orders')->assertRedirect(route('cart.index'));
        $this->assertSame(0, Order::count());
    }

    // ---------- Admin approval ----------

    public function test_approving_order_deducts_stock_and_creates_invoice_for_customer(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5]);
        $order = $this->placeOrder($c, $b, 2);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/approve")->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame(3, $b->fresh()->stock);
        $this->assertNotNull($order->sale_id);
        $this->assertSame($c->id, $order->sale->customer_id);
        $this->assertEquals(20, $order->sale->total);
    }

    public function test_cannot_approve_twice(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5]);
        $order = $this->placeOrder($c, $b, 2);
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/orders/{$order->id}/approve");
        $this->actingAs($admin)->post("/admin/orders/{$order->id}/approve")->assertSessionHas('error');

        $this->assertSame(3, $b->fresh()->stock);
        $this->assertSame(1, Sale::count());
    }

    public function test_approval_fails_when_stock_dropped_meanwhile(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5]);
        $order = $this->placeOrder($c, $b, 4);

        $b->update(['stock' => 1]); // លក់ផ្សេងបានកាត់ស្តុកទៅហើយ

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/approve")->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(1, $b->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_admin_can_reject_with_reason(): void
    {
        $c = $this->customer();
        $order = $this->placeOrder($c, $this->book(), 1);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/reject", ['reject_reason' => 'អស់ស្តុក'])->assertSessionHas('success');

        $this->assertSame('rejected', $order->fresh()->status);
        $this->actingAs($c)->get("/orders/{$order->id}")->assertOk()->assertSee('អស់ស្តុក');
    }

    // ---------- Customer order history & privacy ----------

    public function test_customer_can_cancel_only_pending_own_order(): void
    {
        $c = $this->customer();
        $order = $this->placeOrder($c, $this->book(), 1);

        $this->actingAs($c)->post("/orders/{$order->id}/cancel")->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);

        $this->actingAs($c)->post("/orders/{$order->id}/cancel")->assertSessionHas('error');
    }

    public function test_customer_cannot_view_or_cancel_someone_elses_order(): void
    {
        $owner = $this->customer();
        $other = $this->customer();
        $order = $this->placeOrder($owner, $this->book(), 1);

        $this->actingAs($other)->get("/orders/{$order->id}")->assertForbidden();
        $this->actingAs($other)->post("/orders/{$order->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_cannot_reach_admin_order_routes(): void
    {
        $c = $this->customer();
        $order = $this->placeOrder($c, $this->book(), 1);

        $this->actingAs($c)->get('/admin/orders')->assertRedirect(route('books.index'));
        $this->actingAs($c)->post("/admin/orders/{$order->id}/approve")->assertRedirect(route('books.index'));
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_my_orders_lists_only_own_orders(): void
    {
        $a = $this->customer();
        $b = $this->customer();
        $this->placeOrder($a, $this->book(['title' => 'AAA-book']), 1);
        $this->placeOrder($b, $this->book(['title' => 'BBB-book']), 1);

        $res = $this->actingAs($a)->get('/orders');
        $res->assertOk();
        $this->assertCount(1, $res->viewData('orders'));
    }

    public function test_admin_order_pages_render(): void
    {
        $order = $this->placeOrder($this->customer(), $this->book(), 1);
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee($order->order_no);
        $this->actingAs($admin)->get("/admin/orders/{$order->id}")->assertOk();
    }

    public function test_customer_book_page_hides_inventory_value(): void
    {
        $b = $this->book(['price' => 10, 'stock' => 5]);
        $this->actingAs($this->customer())->get("/books/{$b->id}")->assertOk()->assertDontSee('តម្លៃស្តុកសរុប');
        $this->actingAs($this->admin())->get("/books/{$b->id}")->assertOk()->assertSee('តម្លៃស្តុកសរុប');
    }
}

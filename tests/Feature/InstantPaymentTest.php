<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** បង់ប្រាក់ភ្លាមបន្ទាប់ពីកម្មង់ + ជូនដំណឹងម្ចាស់ហាង */
class InstantPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->create(['role' => 'admin']); }
    private function customer(): User { return User::factory()->create(['role' => 'user']); }
    private function book(array $a = []): Book
    {
        return Book::create(array_merge(['title' => 'Laravel', 'author' => 'Taylor', 'price' => 10, 'stock' => 5], $a));
    }

    private function place(User $c, Book $b, int $qty = 1): Order
    {
        $this->actingAs($c)->post("/cart/{$b->id}", ['quantity' => $qty]);
        $this->actingAs($c)->post('/orders')->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_customer_sees_qr_immediately_without_waiting_for_approval(): void
    {
        $c = $this->customer();
        $order = $this->place($c, $this->book());

        $this->assertSame('pending', $order->status);
        $res = $this->actingAs($c)->get("/orders/{$order->id}")->assertOk();
        $res->assertSee('khqr', false);                       // រូប QR បង្ហាញ
        $res->assertDontSee('កំពុងរង់ចាំហាងអនុម័ត');
    }

    public function test_customer_can_pay_pending_order_and_admin_gets_notified(): void
    {
        $admin = $this->admin();
        $c = $this->customer();
        $order = $this->place($c, $this->book());

        $this->assertSame(1, $admin->notifications()->count());                  // ការកម្មង់ថ្មី
        $this->assertSame('bi-bag-plus', $admin->notifications()->get()->first()->data['icon']);

        $this->actingAs($c)->post("/orders/{$order->id}/payment", ['payment_ref' => 'TXN-1'])->assertSessionHas('success');

        $this->assertSame('submitted', $order->fresh()->payment_status);
        $this->assertSame(2, $admin->notifications()->count());                  // + មានអ្នកបង់លុយ
        $titles = $admin->notifications()->get()->pluck('data.title')->implode('|');
        $this->assertStringContainsString('មានអ្នកបង់លុយ', $titles);
    }

    public function test_confirming_payment_on_pending_order_approves_it_in_one_click(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5, 'price' => 10]);
        $order = $this->place($c, $b, 2);
        $this->actingAs($c)->post("/orders/{$order->id}/payment", ['payment_ref' => 'TXN-1']);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/paid")->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(3, $b->fresh()->stock);
        $this->assertNotNull($order->sale_id);
        $this->assertSame(1, Sale::count());
    }

    public function test_confirm_payment_fails_cleanly_when_stock_ran_out(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 5]);
        $order = $this->place($c, $b, 4);
        $this->actingAs($c)->post("/orders/{$order->id}/payment", ['payment_ref' => 'TXN-1']);
        $b->update(['stock' => 1]);

        $this->actingAs($this->admin())->post("/admin/orders/{$order->id}/paid")->assertSessionHas('error');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('submitted', $order->payment_status);   // មិនត្រូវសម្គាល់ថាបានបង់ទេ
        $this->assertSame(1, $b->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_cannot_cancel_after_paying_but_can_before(): void
    {
        $c = $this->customer();
        $paid = $this->place($c, $this->book(['title' => 'A']));
        $this->actingAs($c)->post("/orders/{$paid->id}/payment", ['payment_ref' => 'TXN-1']);
        $this->actingAs($c)->post("/orders/{$paid->id}/cancel")->assertSessionHas('error');
        $this->assertSame('pending', $paid->fresh()->status);

        $unpaid = $this->place($c, $this->book(['title' => 'B']));
        $this->actingAs($c)->post("/orders/{$unpaid->id}/cancel")->assertSessionHas('success');
        $this->assertSame('cancelled', $unpaid->fresh()->status);
    }

    public function test_bell_poll_returns_unread_and_open_marks_read(): void
    {
        $admin = $this->admin();
        $this->place($this->customer(), $this->book());

        $json = $this->actingAs($admin)->getJson('/admin/notifications/poll')->assertOk()->json();
        $this->assertSame(1, $json['unread']);
        $this->assertSame(1, $json['pending']);
        $this->assertFalse($json['latest'][0]['read']);

        $id = $json['latest'][0]['id'];
        $this->actingAs($admin)->get("/admin/notifications/{$id}/open")->assertRedirect();
        $this->assertSame(0, $admin->unreadNotifications()->count());

        $this->actingAs($admin)->get('/admin/notifications')->assertOk();
    }

    public function test_notifications_are_admin_only_and_private(): void
    {
        $admin = $this->admin();
        $c = $this->customer();
        $this->place($c, $this->book());

        $this->actingAs($c)->get('/admin/notifications/poll')->assertRedirect(route('books.index'));

        $other = $this->admin();
        $id = $admin->notifications()->first()->id;
        $this->actingAs($other)->get("/admin/notifications/{$id}/open")->assertNotFound(); // មើលរបស់ Admin ផ្សេងមិនបាន
    }

    public function test_telegram_message_sent_when_configured(): void
    {
        config(['bookstore.telegram.token' => 'TKN', 'bookstore.telegram.chat_id' => '999']);
        Http::fake();
        $this->admin();

        $order = $this->place($this->customer(), $this->book());

        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.telegram.org/botTKN/sendMessage')
            && $r['chat_id'] === '999' && str_contains($r['text'], $order->order_no));
    }

    public function test_no_telegram_call_when_not_configured_and_failure_never_breaks_checkout(): void
    {
        Http::fake();
        $this->admin();
        $this->place($this->customer(), $this->book());
        Http::assertNothingSent();

        config(['bookstore.telegram.token' => 'TKN', 'bookstore.telegram.chat_id' => '999']);
        Http::fake(fn () => throw new \Exception('network down'));
        $order = $this->place($this->customer(), $this->book(['title' => 'B']));
        $this->assertSame('pending', $order->status);   // ការកម្មង់នៅតែជោគជ័យ
    }

    public function test_buy_now_creates_order_and_keeps_existing_cart(): void
    {
        $this->admin();
        $c = $this->customer();
        $keep = $this->book(['title' => 'Keep']);
        $now = $this->book(['title' => 'Now', 'price' => 7]);
        $this->actingAs($c)->post("/cart/{$keep->id}", ['quantity' => 2]);

        $res = $this->actingAs($c)->post("/cart/{$now->id}/buy-now");

        $order = Order::latest('id')->firstOrFail();
        $res->assertRedirect("/orders/{$order->id}");
        $this->assertCount(1, $order->items);
        $this->assertSame('7.00', number_format($order->total, 2, '.', ''));
        $this->assertSame([$keep->id => 2], session('cart'));
    }

    public function test_buy_now_out_of_stock_is_refused(): void
    {
        $c = $this->customer();
        $b = $this->book(['stock' => 0]);
        $this->actingAs($c)->post("/cart/{$b->id}/buy-now")->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_demo_login_buttons_only_show_in_demo_mode(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('ចូលសាកល្បងលឿន');

        config(['bookstore.demo' => true]);
        $this->get('/login')->assertOk()->assertSee('ចូលសាកល្បងលឿន')->assertSee('admin@example.com');
    }

    public function test_api_login_list_and_admin_only_create(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password')]);
        $user = User::factory()->create(['role' => 'user', 'password' => bcrypt('password')]);

        $this->postJson('/api/login', ['email' => $admin->email, 'password' => 'wrong'])->assertStatus(401);
        $this->getJson('/api/books')->assertStatus(401);

        $ut = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])->assertOk()->json('access_token');
        $this->withToken($ut)->getJson('/api/books')->assertOk();
        $this->withToken($ut)->postJson('/api/books', ['title' => 'X', 'author' => 'Y', 'price' => 1])->assertForbidden();

        $this->app['auth']->forgetGuards();   // test ប្រើ app instance តែមួយ — លុប user ដែល cache ពីសំណើមុន
        $at = $this->postJson('/api/login', ['email' => $admin->email, 'password' => 'password'])->json('access_token');
        $this->withToken($at)->postJson('/api/books', ['title' => 'X', 'author' => 'Y', 'price' => 1])->assertCreated();
    }
}

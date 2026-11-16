<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/** ការជូនដំណឹងរបស់ Admin (កណ្ដឹង) */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    /** JSON សម្រាប់ JavaScript ពិនិត្យរាល់ ១៥ វិនាទី ដើម្បីបង្ហាញការជូនដំណឹងថ្មីភ្លាមៗ */
    public function poll(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread'  => $user->unreadNotifications()->count(),
            'pending' => Order::where('status', Order::PENDING)->count(),
            'latest'  => $user->notifications()->limit(6)->get()->map(fn ($n) => [
                'id'    => $n->id,
                'icon'  => $n->data['icon'] ?? 'bi-bell',
                'title' => $n->data['title'] ?? '',
                'body'  => $n->data['body'] ?? '',
                'url'   => route('admin.notifications.open', $n->id),
                'time'  => $n->created_at->diffForHumans(),
                'read'  => $n->read_at !== null,
            ])->values(),
        ]);
    }

    public function open(Request $request, string $id)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return redirect($n->data['url'] ?? route('admin.orders.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}

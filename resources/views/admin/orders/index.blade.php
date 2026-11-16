@extends('layouts.app')
@section('title', 'ការកម្មង់អនឡាញ')
@section('content')
<div class="page-head">
    <div><h1>🛎️ ការកម្មង់អនឡាញ</h1><p>អនុម័ត ឬបដិសេធការកម្មង់ពីអតិថិជន</p></div>
</div>

<ul class="nav nav-pills mb-3 gap-1">
    @foreach([null => 'ទាំងអស់', 'pending' => 'រង់ចាំ', 'approved' => 'អនុម័ត', 'rejected' => 'បដិសេធ', 'cancelled' => 'បោះបង់'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.orders.index', $key ? ['status' => $key] : []) }}">
                {{ $label }} @if($key && ($counts[$key] ?? 0))<span class="badge rounded-pill {{ $key === 'pending' ? 'bg-danger' : 'bg-secondary' }} ms-1">{{ $counts[$key] }}</span>@endif
            </a>
        </li>
    @endforeach
</ul>

<div class="bk-card">
@if($orders->isEmpty())
    <div class="empty"><i class="bi bi-bag"></i><h5 class="mt-3 text-body">មិនមានការកម្មង់ទេ</h5></div>
@else
    <div class="table-responsive">
        <table class="table bk-table">
            <thead><tr><th>លេខកម្មង់</th><th>អតិថិជន</th><th>កាលបរិច្ឆេទ</th><th>ចំនួន</th><th>សរុប</th><th>ស្ថានភាព</th><th></th></tr></thead>
            <tbody>
            @foreach($orders as $o)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $o) }}" class="book-title">{{ $o->order_no }}</a></td>
                    <td>{{ $o->user->name }}<div class="small text-muted">{{ $o->user->email }}</div></td>
                    <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $o->qty }} ក្បាល</td>
                    <td class="fw-bold">${{ number_format($o->total, 2) }}</td>
                    <td><span class="badge-soft {{ $o->status_badge['class'] }}">{{ $o->status_badge['label'] }}</span>@if(in_array($o->status, ['pending', 'approved']))<div class="mt-1"><span class="badge-soft {{ $o->payment_badge['class'] }}">{{ $o->payment_badge['label'] }}</span></div>@endif</td>
                    <td class="text-end"><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-soft btn-sm">{{ $o->isPending() ? 'ពិនិត្យ' : 'មើល' }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $orders->links() }}</div></div>@endif
@endif
</div>
@endsection

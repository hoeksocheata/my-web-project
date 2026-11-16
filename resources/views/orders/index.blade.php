@extends('layouts.app')
@section('title', 'ការកម្មង់របស់ខ្ញុំ')
@section('content')
<div class="page-head">
    <div><h1>🛍️ ការកម្មង់របស់ខ្ញុំ</h1><p>ប្រវត្តិ និងស្ថានភាពនៃការកម្មង់</p></div>
    <a href="{{ route('books.index') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> កម្មង់បន្ថែម</a>
</div>
<div class="bk-card">
@if($orders->isEmpty())
    <div class="empty"><i class="bi bi-bag"></i><h5 class="mt-3 text-body">អ្នកមិនទាន់មានការកម្មង់ទេ</h5></div>
@else
    <div class="table-responsive">
        <table class="table bk-table">
            <thead><tr><th>លេខកម្មង់</th><th>កាលបរិច្ឆេទ</th><th>ចំនួន</th><th>សរុប</th><th>ស្ថានភាព</th><th></th></tr></thead>
            <tbody>
            @foreach($orders as $o)
                <tr>
                    <td><a href="{{ route('orders.show', $o) }}" class="book-title">{{ $o->order_no }}</a></td>
                    <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $o->qty }} ក្បាល</td>
                    <td class="fw-bold">${{ number_format($o->total, 2) }}</td>
                    <td><span class="badge-soft {{ $o->status_badge['class'] }}">{{ $o->status_badge['label'] }}</span>@if($o->status === 'approved')<div class="mt-1"><span class="badge-soft {{ $o->payment_badge['class'] }}">{{ $o->payment_badge['label'] }}</span></div>@endif</td>
                    <td class="text-end"><a href="{{ route('orders.show', $o) }}" class="btn btn-soft btn-icon"><i class="bi bi-eye"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $orders->links() }}</div></div>@endif
@endif
</div>
@endsection

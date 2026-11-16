@extends('layouts.app')
@section('title', $order->order_no)
@section('content')
@php $short = $order->items->contains(fn ($i) => ! $i->book || $i->book->stock < $i->quantity); @endphp
<div class="page-head">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
    @if($order->sale)<a href="{{ route('sales.show', $order->sale) }}" class="btn btn-primary"><i class="bi bi-receipt me-1"></i> មើលវិក្កយបត្រ {{ $order->sale->invoice_no }}</a>@endif
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bk-card">
            <div class="bk-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">{{ $order->order_no }} · {{ $order->created_at->format('d/m/Y H:i') }}</h6>
                <span class="badge-soft {{ $order->status_badge['class'] }}">{{ $order->status_badge['label'] }}</span>
            </div>
            <div class="table-responsive">
                <table class="table bk-table">
                    <thead><tr><th>សៀវភៅ</th><th class="text-end">តម្លៃ</th><th class="text-end">ត្រូវការ</th><th class="text-end">ស្តុកបច្ចុប្បន្ន</th><th class="text-end">សរុប</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        @php $stock = $item->book?->stock; $bad = $order->isPending() && ($stock === null || $stock < $item->quantity); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $item->title }}</td>
                            <td class="text-end">${{ number_format($item->price, 2) }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">@if($order->isPending())<span class="badge-soft {{ $bad ? 'danger' : 'success' }}">{{ $stock ?? 'លុបហើយ' }}</span>@else — @endif</td>
                            <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important">
                <div class="text-end"><div class="text-muted">សរុប</div><div class="invoice-total text-primary">${{ number_format($order->total, 2) }}</div></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bk-card mb-4"><div class="bk-card-body">
            <div class="text-muted small">អតិថិជន</div><div class="fw-semibold">{{ $order->user->name }}</div><div class="small text-muted mb-3">{{ $order->user->email }}</div>
            <div class="text-muted small">កំណត់ចំណាំ</div><div>{{ $order->note ?: '—' }}</div>
            @if($order->reject_reason)<hr><div class="text-muted small">មូលហេតុបដិសេធ</div><div>{{ $order->reject_reason }}</div>@endif
        </div></div>

        @if(in_array($order->status, ['pending', 'approved']))
            @php $ps = $order->payment_status; @endphp
            <div class="bk-card"><div class="bk-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">ត្រូវធ្វើអ្វីបន្ត?</h6>
                    <span class="badge-soft {{ $order->payment_badge['class'] }}">{{ $order->payment_badge['label'] }}</span>
                </div>

                @if($order->payment_ref)<div class="small text-muted">លេខប្រតិបត្តិការ</div><div class="fw-semibold mb-2">{{ $order->payment_ref }}</div>@endif
                @if($order->payment_proof_url)<a href="{{ $order->payment_proof_url }}" target="_blank"><img src="{{ $order->payment_proof_url }}" class="proof-img mb-3" alt="ភស្តុតាង"></a>@endif

                @if($ps === 'paid')
                    <div class="alert alert-success mb-0 small"><i class="bi bi-patch-check-fill me-1"></i> បានទទួលលុយ {{ $order->paid_at?->format('d/m/Y H:i') }} — ការកម្មង់បានបញ្ចប់។</div>

                @else
                    @if($short && $order->isPending())
                        <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle-fill me-1"></i> ស្តុកមិនគ្រប់គ្រាន់សម្រាប់សៀវភៅខ្លះ — សូមបន្ថែមស្តុកជាមុន។</div>
                    @endif

                    @if($ps === 'submitted')
                        <div class="alert alert-info small"><i class="bi bi-bell-fill me-1"></i> អតិថិជនប្រាប់ថាបានបង់ហើយ។ សូមពិនិត្យក្នុងកម្មវិធីធនាគារថាបានទទួលលុយ <b>${{ number_format($order->total, 2) }}</b> មែនឬអត់។</div>
                    @else
                        <div class="alert alert-secondary small"><i class="bi bi-hourglass-split me-1"></i> កំពុងរង់ចាំអតិថិជនបង់លុយ <b>${{ number_format($order->total, 2) }}</b>។</div>
                    @endif

                    <form action="{{ route('admin.orders.paid', $order) }}" method="POST" class="mb-2" data-once>@csrf
                        <button class="btn btn-success w-100 py-2 fw-semibold"><i class="bi bi-cash-coin me-1"></i> ទទួលលុយរួច{{ $order->isPending() ? ' + បញ្ចប់ការកម្មង់' : '' }}</button></form>
                    @if($order->isPending())<div class="small text-muted mb-3">ប្រព័ន្ធនឹងកាត់ស្តុក និងបង្កើតវិក្កយបត្រឱ្យដោយស្វ័យប្រវត្តិ។</div>@endif

                    @if($ps === 'submitted')
                        <form action="{{ route('admin.orders.unpaid', $order) }}" method="POST" class="mb-2">@csrf
                            <button class="btn btn-outline-secondary btn-sm w-100">មិនឃើញលុយចូល / ភស្តុតាងមិនត្រឹមត្រូវ</button></form>
                    @endif
                @endif

                @if($order->isPending())
                    <hr style="border-color: var(--bk-border)">
                    @if($ps === 'unpaid')
                        <form action="{{ route('admin.orders.approve', $order) }}" method="POST" class="mb-2" data-once>@csrf
                            <button class="btn btn-outline-primary btn-sm w-100" @disabled($short)>អនុម័តដោយមិនទាន់បង់ (បង់ពេលទទួល)</button></form>
                    @endif
                    <details>
                        <summary class="small text-danger" style="cursor:pointer">បដិសេធការកម្មង់នេះ</summary>
                        @if($ps !== 'unpaid')<div class="alert alert-warning small mt-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> អតិថិជនបានបង់ហើយ — បើបដិសេធ ត្រូវសងលុយវិញដោយខ្លួនឯង។</div>@endif
                        <form action="{{ route('admin.orders.reject', $order) }}" method="POST" class="mt-2">@csrf
                            <input type="text" name="reject_reason" maxlength="300" class="form-control form-control-sm mb-2" placeholder="មូលហេតុ ឧ. អស់ស្តុក">
                            <button class="btn btn-danger btn-sm w-100"><i class="bi bi-x-circle me-1"></i> បញ្ជាក់បដិសេធ</button></form>
                    </details>
                @endif
            </div></div>
        @endif
    </div>
</div>
@endsection

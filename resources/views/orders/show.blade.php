@extends('layouts.app')
@section('title', $order->order_no)
@section('content')
<div class="page-head no-print">
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
    <div class="d-flex gap-2">
        @if($order->status === 'approved')<button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> បោះពុម្ព / PDF</button>@endif
        @if($order->isPending() && $order->payment_status === 'unpaid')
            <form action="{{ route('orders.cancel', $order) }}" method="POST" class="m-0" onsubmit="return confirm('បោះបង់ការកម្មង់នេះមែនទេ?')">@csrf
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i> បោះបង់ការកម្មង់</button></form>
        @endif
    </div>
</div>

<div class="bk-card invoice">
    <div class="bk-card-body p-4 p-md-5">
        <div class="d-flex justify-content-between flex-wrap gap-3 mb-4">
            <div><div class="bk-brand mb-2"><span class="logo"><i class="bi bi-book-half"></i></span> ហាងសៀវភៅរបស់ខ្ញុំ</div>
                <span class="badge-soft {{ $order->status_badge['class'] }}">{{ $order->status_badge['label'] }}</span></div>
            <div class="text-md-end">
                <h3 class="fw-bold mb-1">ការកម្មង់</h3>
                <div class="fw-semibold text-primary">{{ $order->order_no }}</div>
                <div class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                @if($order->sale)<div class="small mt-1">វិក្កយបត្រ៖ <b>{{ $order->sale->invoice_no }}</b></div>@endif
            </div>
        </div>

        @if($order->status === 'rejected')
            <div class="alert alert-danger"><b>ហាងបានបដិសេធ៖</b> {{ $order->reject_reason ?: 'គ្មានមូលហេតុបញ្ជាក់' }}</div>
        @elseif($order->status === 'approved')
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i> ការកម្មង់របស់អ្នកត្រូវបានអនុម័ត។ សូមអរគុណ!</div>
        @elseif($order->isPending())
            @if($order->payment_status === 'submitted')
                <div class="alert alert-warning"><i class="bi bi-hourglass-split me-1"></i> អរគុណ! ហាងកំពុងផ្ទៀងផ្ទាត់ការបង់ប្រាក់ ហើយនឹងរៀបចំការកម្មង់ជូនក្នុងពេលឆាប់ៗ។</div>
            @else
                <div class="alert alert-info"><i class="bi bi-qr-code-scan me-1"></i> ការកម្មង់របស់អ្នកបានដល់ហាងហើយ! សូមស្កេន QR ខាងក្រោមដើម្បីទូទាត់ភ្លាម ហាងនឹងរៀបចំជូនអ្នកភ្លាមៗបន្ទាប់ពីទទួលលុយ។</div>
            @endif
        @endif

        <div class="table-responsive">
            <table class="table bk-table">
                <thead><tr><th>#</th><th>សៀវភៅ</th><th class="text-end">តម្លៃ</th><th class="text-end">ចំនួន</th><th class="text-end">សរុប</th></tr></thead>
                <tbody>
                @foreach($order->items as $i => $item)
                    <tr><td>{{ $i + 1 }}</td><td class="fw-semibold">{{ $item->title }}</td>
                        <td class="text-end">${{ number_format($item->price, 2) }}</td><td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->subtotal, 2) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3"><div class="text-end"><div class="text-muted">សរុប</div><div class="invoice-total text-primary">${{ number_format($order->total, 2) }}</div></div></div>
        @if(in_array($order->status, ['pending', 'approved']))
            <hr style="border-color: var(--bk-border)">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="fw-bold mb-0">ការទូទាត់</h6>
                <span class="badge-soft {{ $order->payment_badge['class'] }}">{{ $order->payment_badge['label'] }}</span>
            </div>

            @if($order->payment_status === 'paid')
                <div class="alert alert-success mb-0"><i class="bi bi-patch-check-fill me-1"></i> បានទទួលការទូទាត់នៅ {{ $order->paid_at?->format('d/m/Y H:i') }}។ សូមអរគុណ!</div>
            @elseif($order->payment_status === 'submitted')
                <div class="alert alert-warning"><i class="bi bi-hourglass-split me-1"></i> អ្នកបានប្រាប់ហាងថាបានបង់ហើយ ហាងកំពុងផ្ទៀងផ្ទាត់។</div>
                @if($order->payment_ref)<div class="small text-muted">លេខប្រតិបត្តិការ៖ <b>{{ $order->payment_ref }}</b></div>@endif
                @if($order->payment_proof_url)<img src="{{ $order->payment_proof_url }}" class="proof-img mt-2" alt="ភស្តុតាង">@endif
            @else
                <div class="row g-4 no-print">
                    <div class="col-md-5">@include('partials.khqr', ['amount' => $order->total])</div>
                    <div class="col-md-7">
                        <ol class="ps-3 mb-3">
                            <li class="mb-1">បើកកម្មវិធីធនាគារ ហើយ <b>ស្កេន QR</b></li>
                            <li class="mb-1">បញ្ចូលចំនួន <b class="text-primary">${{ number_format($order->total, 2) }}</b> រួចបង់</li>
                            <li>ចុចប៊ូតុង <b>«ខ្ញុំបានបង់ហើយ»</b> ខាងក្រោម</li>
                        </ol>
                        <form action="{{ route('orders.pay', $order) }}" method="POST" enctype="multipart/form-data" data-once>
                            @csrf
                            <button class="btn btn-success btn-lg w-100"><i class="bi bi-check-circle me-1"></i> ខ្ញុំបានបង់ហើយ</button>
                            <details class="mt-3 small text-muted">
                                <summary style="cursor:pointer">បន្ថែមរូបថតអេក្រង់ (មិនចាំបាច់)</summary>
                                <input type="file" name="payment_proof" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm mt-2 @error('payment_proof') is-invalid @enderror">
                                @error('payment_proof')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </details>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        @if($order->note)<hr style="border-color: var(--bk-border)"><div class="small text-muted">កំណត់ចំណាំរបស់អ្នក</div><div>{{ $order->note }}</div>@endif
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'កន្ត្រក')
@section('content')
<div class="page-head">
    <div><h1>🛒 កន្ត្រករបស់ខ្ញុំ</h1><p>ពិនិត្យសៀវភៅ រួចបញ្ជូនការកម្មង់ទៅហាងដើម្បីអនុម័ត</p></div>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> បន្តរកមើលសៀវភៅ</a>
</div>

@if(empty($lines))
    <div class="bk-card"><div class="empty"><i class="bi bi-cart-x"></i><h5 class="mt-3 text-body">កន្ត្រករបស់អ្នកទទេ</h5>
        <a href="{{ route('books.index') }}" class="btn btn-primary mt-2">ទៅរកសៀវភៅ</a></div></div>
@else
<div class="row g-4">
    <div class="col-lg-8">
        <div class="bk-card">
            <div class="table-responsive">
                <table class="table bk-table">
                    <thead><tr><th>សៀវភៅ</th><th>តម្លៃ</th><th>ចំនួន</th><th class="text-end">សរុប</th><th></th></tr></thead>
                    <tbody>
                    @foreach($lines as $l)
                        @php $b = $l['book']; @endphp
                        <tr>
                            <td><div class="d-flex align-items-center gap-3">@include('books._cover', ['book' => $b, 'size' => 'sm'])
                                <div><a href="{{ route('books.show', $b) }}" class="book-title">{{ $b->title }}</a><div class="small text-muted">{{ $b->author }}</div></div></div></td>
                            <td>${{ number_format($b->price, 2) }}</td>
                            <td>
                                <form action="{{ route('cart.update', $b) }}" method="POST" class="d-flex gap-1 m-0">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $l['qty'] }}" min="0" max="{{ $b->stock }}" class="form-control form-control-sm" style="width:76px">
                                    <button class="btn btn-soft btn-sm" title="កែចំនួន"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <small class="text-muted">ស្តុក {{ $b->stock }}</small>
                            </td>
                            <td class="text-end fw-bold">${{ number_format($l['subtotal'], 2) }}</td>
                            <td class="text-end">
                                <form action="{{ route('cart.remove', $b) }}" method="POST" class="m-0">@csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-icon" title="ដកចេញ"><i class="bi bi-x-lg"></i></button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">@csrf @method('DELETE')
            <button class="btn btn-light btn-sm"><i class="bi bi-trash3 me-1"></i> សម្អាតកន្ត្រក</button></form>
    </div>

    <div class="col-lg-4">
        <div class="bk-card" style="position: sticky; top: 80px">
            <div class="bk-card-header"><h6 class="mb-0 fw-bold">សង្ខេបការកម្មង់</h6></div>
            <form action="{{ route('orders.store') }}" method="POST" class="bk-card-body" data-once>
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">សរុប</span><span class="fs-3 fw-bold text-primary">${{ number_format($total, 2) }}</span>
                </div>
                <label class="form-label" for="note">កំណត់ចំណាំ (ស្រេចចិត្ត)</label>
                <textarea id="note" name="note" rows="3" maxlength="500" class="form-control mb-3 @error('note') is-invalid @enderror" placeholder="ឧ. សូមទាក់ទងមុនថ្ងៃដឹកជញ្ជូន">{{ old('note') }}</textarea>
                @error('note')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"><i class="bi bi-send-check me-1"></i> បញ្ជូនការកម្មង់</button>
                <p class="small text-muted mt-3 mb-0">បន្ទាប់ពីចុចកម្មង់ អ្នកនឹងឃើញ QR ដើម្បីបង់លុយភ្លាម។</p>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

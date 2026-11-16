@extends('layouts.app')
@section('title', 'ទិញចូល / បំពេញស្តុក')
@section('content')
<div class="page-head"><div><h1>📦 ទិញចូល / បំពេញស្តុក</h1><p>កត់ត្រាសៀវភៅដែលទិញចូល ស្តុកនឹងកើនឡើងដោយស្វ័យប្រវត្តិ</p></div></div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="bk-card"><div class="bk-card-header"><h6 class="mb-0 fw-bold">កត់ត្រាការទិញចូល</h6></div>
        <form action="{{ route('purchases.store') }}" method="POST" class="bk-card-body" data-once>
            @csrf
            <label class="form-label" for="book_id">សៀវភៅ <span class="text-danger">*</span></label>
            <select id="book_id" name="book_id" class="form-select mb-1 @error('book_id') is-invalid @enderror" required>
                <option value="">— ជ្រើសរើស —</option>
                @foreach($books as $b)<option value="{{ $b->id }}" @selected((int) old('book_id', $selected) === $b->id)>{{ $b->title }} (ស្តុក {{ $b->stock }})</option>@endforeach
            </select>
            @error('book_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

            <label class="form-label mt-3" for="supplier_id">អ្នកផ្គត់ផ្គង់</label>
            <select id="supplier_id" name="supplier_id" class="form-select mb-1">
                <option value="">— មិនបញ្ជាក់ —</option>
                @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected((int) old('supplier_id') === $s->id)>{{ $s->name }}</option>@endforeach
            </select>
            @if($suppliers->isEmpty())<div class="form-text"><a href="{{ route('suppliers.index') }}">បន្ថែមអ្នកផ្គត់ផ្គង់</a></div>@endif

            <div class="row g-2 mt-2">
                <div class="col-6"><label class="form-label" for="quantity">ចំនួន <span class="text-danger">*</span></label>
                    <input type="number" min="1" id="quantity" name="quantity" value="{{ old('quantity', 10) }}" class="form-control @error('quantity') is-invalid @enderror" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-6"><label class="form-label" for="unit_cost">តម្លៃដើម ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" class="form-control @error('unit_cost') is-invalid @enderror" required>
                    @error('unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <button class="btn btn-primary w-100 mt-4"><i class="bi bi-check2-circle me-1"></i> បំពេញស្តុក</button>
        </form></div>
    </div>
    <div class="col-lg-8">
        <div class="bk-card">
            <div class="bk-card-header"><h6 class="mb-0 fw-bold">ប្រវត្តិការទិញចូល</h6></div>
            @if($purchases->isEmpty())
                <div class="empty"><i class="bi bi-box-seam"></i><p class="mt-3 mb-0">មិនទាន់មានការទិញចូលទេ</p></div>
            @else
                <div class="table-responsive"><table class="table bk-table">
                    <thead><tr><th>កាលបរិច្ឆេទ</th><th>សៀវភៅ</th><th>អ្នកផ្គត់ផ្គង់</th><th class="text-end">ចំនួន</th><th class="text-end">តម្លៃដើម</th><th class="text-end">សរុប</th></tr></thead>
                    <tbody>
                    @foreach($purchases as $p)
                        <tr>
                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            <td class="fw-semibold">{{ $p->book?->title }}</td>
                            <td>{{ $p->supplier?->name ?? '—' }}</td>
                            <td class="text-end">+{{ $p->quantity }}</td>
                            <td class="text-end">${{ number_format($p->unit_cost, 2) }}</td>
                            <td class="text-end fw-bold">${{ number_format($p->total_cost, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody></table></div>
                @if($purchases->hasPages())<div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $purchases->links() }}</div></div>@endif
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', $sale->invoice_no)
@section('content')
<div class="page-head no-print">
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> បោះពុម្ព / PDF</button>
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                data-action="{{ route('sales.destroy', $sale) }}" data-name="{{ $sale->invoice_no }} (ស្តុកនឹងត្រូវបូកត្រឡប់)"><i class="bi bi-x-circle me-1"></i> បោះបង់ការលក់</button>
    </div>
</div>

<div class="bk-card invoice">
    <div class="bk-card-body p-4 p-md-5">
        <div class="d-flex justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <div class="bk-brand mb-2"><span class="logo"><i class="bi bi-book-half"></i></span> ហាងសៀវភៅរបស់ខ្ញុំ</div>
                <div class="text-muted small">BookStore Management System</div>
            </div>
            <div class="text-md-end">
                <h3 class="fw-bold mb-1">វិក្កយបត្រ</h3>
                <div class="fw-semibold text-primary">{{ $sale->invoice_no }}</div>
                <div class="text-muted small">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6"><div class="text-muted small">អតិថិជន</div><div class="fw-semibold">{{ $sale->customer_name ?: 'អតិថិជនទូទៅ' }}</div></div>
            <div class="col-sm-6 text-sm-end"><div class="text-muted small">អ្នកលក់</div><div class="fw-semibold">{{ $sale->user?->name ?? '—' }}</div></div>
        </div>

        <div class="table-responsive">
            <table class="table bk-table">
                <thead><tr><th>#</th><th>សៀវភៅ</th><th class="text-end">តម្លៃ</th><th class="text-end">ចំនួន</th><th class="text-end">សរុប</th></tr></thead>
                <tbody>
                @foreach($sale->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $item->title }}</td>
                        <td class="text-end">${{ number_format($item->price, 2) }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <div class="text-end">
                <div class="text-muted">សរុបត្រូវបង់</div>
                <div class="invoice-total text-primary">${{ number_format($sale->total, 2) }}</div>
            </div>
        </div>

        <hr class="my-4" style="border-color: var(--bk-border)">
        <p class="text-center text-muted mb-0">សូមអរគុណដែលបានទិញសៀវភៅពីយើងខ្ញុំ 🙏</p>
    </div>
</div>
@endsection

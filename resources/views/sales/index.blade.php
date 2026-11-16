@extends('layouts.app')
@section('title', 'ការលក់')
@section('content')
<div class="page-head">
    <div>
        <h1>🧾 ការលក់</h1>
        <p>ប្រវត្តិការលក់ និងវិក្កយបត្រទាំងអស់</p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> លក់ថ្មី</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="bk-card stat"><span class="stat-icon tone-indigo"><i class="bi bi-receipt"></i></span>
            <div><p class="stat-label">ចំនួនវិក្កយបត្រ</p><p class="stat-value">{{ number_format($summary['count']) }}</p></div></div>
    </div>
    <div class="col-6">
        <div class="bk-card stat"><span class="stat-icon tone-green"><i class="bi bi-cash-coin"></i></span>
            <div><p class="stat-label">ចំណូលសរុប</p><p class="stat-value">${{ number_format($summary['revenue'], 2) }}</p></div></div>
    </div>
</div>

<div class="bk-card">
    <div class="bk-card-header toolbar">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="លេខវិក្កយបត្រ ឬឈ្មោះអតិថិជន...">
                </div>
            </div>
            <div class="col-6 col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control" title="ពីថ្ងៃ"></div>
            <div class="col-6 col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control" title="ដល់ថ្ងៃ"></div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit">ស្វែងរក</button>
                @if($search || $from || $to)<a href="{{ route('sales.index') }}" class="btn btn-light">សម្អាត</a>@endif
            </div>
        </form>
    </div>

    @if($sales->isEmpty())
        <div class="empty"><i class="bi bi-receipt"></i><h5 class="mt-3 text-body">មិនទាន់មានការលក់ទេ</h5>
            <a href="{{ route('sales.create') }}" class="btn btn-primary mt-2">បង្កើតការលក់ដំបូង</a></div>
    @else
        <div class="table-responsive">
            <table class="table bk-table">
                <thead><tr><th>វិក្កយបត្រ</th><th>កាលបរិច្ឆេទ</th><th>អតិថិជន</th><th>ចំនួន</th><th>សរុប</th><th class="text-end">សកម្មភាព</th></tr></thead>
                <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td><a href="{{ route('sales.show', $sale) }}" class="book-title">{{ $sale->invoice_no }}</a></td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer_name ?: 'អតិថិជនទូទៅ' }}</td>
                        <td>{{ $sale->qty }} ក្បាល</td>
                        <td class="fw-bold">${{ number_format($sale->total, 2) }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-soft btn-icon" title="មើលវិក្កយបត្រ"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-outline-danger btn-icon" title="បោះបង់" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                                    data-action="{{ route('sales.destroy', $sale) }}" data-name="{{ $sale->invoice_no }} (ស្តុកនឹងត្រូវបូកត្រឡប់)"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="bk-card-body border-top d-flex justify-content-end" style="border-color: var(--bk-border) !important"><div class="bk-pager">{{ $sales->links() }}</div></div>
        @endif
    @endif
</div>
@endsection

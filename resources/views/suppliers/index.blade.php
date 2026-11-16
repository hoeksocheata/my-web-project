@extends('layouts.app')
@section('title', 'អ្នកផ្គត់ផ្គង់')
@section('content')
<div class="page-head"><div><h1>🚚 អ្នកផ្គត់ផ្គង់</h1><p>ប្រភពដែលអ្នកទិញសៀវភៅចូលហាង</p></div></div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="bk-card"><div class="bk-card-header"><h6 class="mb-0 fw-bold">បន្ថែមអ្នកផ្គត់ផ្គង់</h6></div>
        <form action="{{ route('suppliers.store') }}" method="POST" class="bk-card-body" data-once novalidate>
            @csrf
            <label class="form-label" for="name">ឈ្មោះ</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control mb-1 @error('name') is-invalid @enderror" required>
            @error('name')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
            <label class="form-label mt-2" for="phone">ទូរស័ព្ទ</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" class="form-control mb-2">
            <label class="form-label" for="address">អាសយដ្ឋាន</label>
            <input type="text" id="address" name="address" value="{{ old('address') }}" maxlength="255" class="form-control mb-3">
            <button class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i> បន្ថែម</button>
        </form></div>
    </div>
    <div class="col-lg-8">
        <div class="bk-card">
            <div class="bk-card-header d-flex justify-content-between"><h6 class="mb-0 fw-bold">បញ្ជីអ្នកផ្គត់ផ្គង់</h6><span class="badge-soft muted">{{ $suppliers->count() }}</span></div>
            @if($suppliers->isEmpty())
                <div class="empty"><i class="bi bi-truck"></i><p class="mt-3 mb-0">មិនទាន់មានអ្នកផ្គត់ផ្គង់ទេ</p></div>
            @else
            <div class="table-responsive"><table class="table bk-table">
                <thead><tr><th>ឈ្មោះ</th><th>ទូរស័ព្ទ</th><th>អាសយដ្ឋាន</th><th>ទិញចូល</th><th class="text-end"></th></tr></thead>
                <tbody>
                @foreach($suppliers as $s)
                    <tr>
                        <td colspan="3">
                            <form action="{{ route('suppliers.update', $s) }}" method="POST" class="d-flex gap-2 m-0 flex-wrap">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $s->name }}" class="form-control form-control-sm" style="max-width:190px" required>
                                <input type="text" name="phone" value="{{ $s->phone }}" class="form-control form-control-sm" style="max-width:130px" placeholder="ទូរស័ព្ទ">
                                <input type="text" name="address" value="{{ $s->address }}" class="form-control form-control-sm" style="max-width:220px" placeholder="អាសយដ្ឋាន">
                                <button class="btn btn-soft btn-sm" title="រក្សាទុក"><i class="bi bi-check-lg"></i></button>
                            </form>
                        </td>
                        <td>{{ $s->purchases_count }} ដង</td>
                        <td class="text-end"><button type="button" class="btn btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                            data-action="{{ route('suppliers.destroy', $s) }}" data-name="{{ $s->name }}"><i class="bi bi-trash3"></i></button></td>
                    </tr>
                @endforeach
                </tbody></table></div>
            @endif
        </div>
    </div>
</div>
@endsection

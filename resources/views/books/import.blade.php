@extends('layouts.app')
@section('title', 'នាំចូលសៀវភៅ')
@section('content')
@php $result = session('import_result'); @endphp
<div class="page-head">
    <div><h1>📥 នាំចូលសៀវភៅពី CSV</h1><p>បន្ថែម ឬកែប្រែសៀវភៅច្រើនក្បាលក្នុងពេលតែមួយ</p></div>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
</div>

@if($result)
    <div class="bk-card mb-4"><div class="bk-card-body">
        <div class="d-flex gap-3 flex-wrap mb-2">
            <span class="badge-soft success">បន្ថែមថ្មី {{ $result['created'] }}</span>
            <span class="badge-soft warning">កែប្រែ {{ $result['updated'] }}</span>
            <span class="badge-soft {{ count($result['errors']) ? 'danger' : 'muted' }}">កំហុស {{ count($result['errors']) }}</span>
        </div>
        @if($result['errors'])
            <ul class="small text-danger mb-0 mt-2">@foreach($result['errors'] as $e)<li>{{ $e }}</li>@endforeach</ul>
        @endif
    </div></div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="bk-card"><div class="bk-card-body">
            <form action="{{ route('books.import.store') }}" method="POST" enctype="multipart/form-data" data-once>
                @csrf
                <label class="form-label" for="file">ជ្រើសរើសឯកសារ CSV</label>
                <input type="file" id="file" name="file" accept=".csv,text/csv" class="form-control mb-1 @error('file') is-invalid @enderror" required>
                @error('file')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                <div class="form-text mb-3">អតិបរមា 500 ជួរ · 2MB · សរសេរជា UTF-8 (Excel: Save as «CSV UTF-8»)</div>
                <button class="btn btn-primary"><i class="bi bi-upload me-1"></i> នាំចូល</button>
                <a href="{{ route('books.import.template') }}" class="btn btn-light ms-2"><i class="bi bi-download me-1"></i> ទាញយកឯកសារគំរូ</a>
            </form>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="bk-card"><div class="bk-card-body small">
            <div class="fw-bold mb-2">ជួរឈរដែលទទួលស្គាល់</div>
            <code>title</code>*, <code>author</code>*, <code>price</code>*, <code>isbn</code>, <code>category</code>, <code>stock</code>, <code>description</code>
            <hr style="border-color: var(--bk-border)">
            <ul class="mb-0 ps-3 text-muted">
                <li>ជួរឈរដែលមាន * ជាកាតព្វកិច្ច</li>
                <li>ISBN ដូចគ្នា (ឬចំណងជើង+អ្នកនិពន្ធដូចគ្នា) → កែប្រែសៀវភៅដែលមានស្រាប់</li>
                <li>ប្រភេទថ្មីនឹងត្រូវបង្កើតដោយស្វ័យប្រវត្តិ</li>
                <li>ការផ្លាស់ប្តូរស្តុកត្រូវបានកត់ត្រាក្នុងប្រវត្តិចលនា</li>
            </ul>
        </div></div>
    </div>
</div>
@endsection

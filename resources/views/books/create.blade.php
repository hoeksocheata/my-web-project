@extends('layouts.app')
@section('title', 'បន្ថែមសៀវភៅ')
@section('content')
<div class="page-head">
    <div>
        <h1>➕ បន្ថែមសៀវភៅថ្មី</h1>
        <p>បំពេញព័ត៌មានខាងក្រោម ដើម្បីបន្ថែមសៀវភៅចូលក្នុងស្តុក</p>
    </div>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
</div>

<div class="bk-card">
    <div class="bk-card-body">
        @include('books._form', ['action' => route('books.store'), 'method' => 'POST'])
    </div>
</div>
@endsection

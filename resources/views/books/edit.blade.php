@extends('layouts.app')
@section('title', 'កែប្រែសៀវភៅ')
@section('content')
<div class="page-head">
    <div>
        <h1>📝 កែប្រែព័ត៌មានសៀវភៅ</h1>
        <p>{{ $book->title }}</p>
    </div>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
</div>

<div class="bk-card">
    <div class="bk-card-body">
        @include('books._form', ['action' => route('books.update', $book), 'method' => 'PUT'])
    </div>
</div>
@endsection

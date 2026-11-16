@extends('layouts.app')
@section('title', 'បញ្ជីចូលចិត្ត')
@section('content')
@php $wishIds = $books->pluck('id')->all(); @endphp
<div class="page-head">
    <div><h1>❤️ បញ្ជីចូលចិត្ត</h1><p>សៀវភៅដែលអ្នករក្សាទុក</p></div>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅបញ្ជីសៀវភៅ</a>
</div>
@if($books->isEmpty())
    <div class="bk-card"><div class="empty"><i class="bi bi-heart"></i><h5 class="mt-3 text-body">មិនទាន់មានសៀវភៅចូលចិត្តទេ</h5>
        <p>ចុចរូបបេះដូងលើសៀវភៅណាមួយ ដើម្បីរក្សាទុកទីនេះ</p><a href="{{ route('books.index') }}" class="btn btn-primary">រកមើលសៀវភៅ</a></div></div>
@else
    <div class="row g-3">
        @foreach($books as $book)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="bk-card book-card">
                    <a href="{{ route('books.show', $book) }}">@include('books._cover', ['book' => $book, 'size' => 'lg'])</a>
                    <div class="body">
                        @if($book->category)<small class="text-muted">{{ $book->category->name }}</small>@endif
                        <a href="{{ route('books.show', $book) }}" class="book-title line-clamp-2">{{ $book->title }}</a>
                        <small class="text-muted">{{ $book->author }}</small>
                        <div class="d-flex align-items-center justify-content-between mt-auto pt-2">
                            <span class="price">${{ number_format($book->price, 2) }}</span>
                            <span class="badge-soft {{ $book->stock_badge['class'] }}">{{ $book->stock_badge['label'] }}</span>
                        </div>
                        <div class="d-flex gap-2 pt-2 justify-content-end">@include('books._customer_actions', ['book' => $book])</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @if($books->hasPages())<div class="mt-4 d-flex justify-content-center"><div class="bk-pager">{{ $books->links() }}</div></div>@endif
@endif
@endsection

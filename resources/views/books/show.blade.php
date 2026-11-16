@extends('layouts.app')
@section('title', $book->title)
@section('content')
@php $isAdmin = auth()->user()?->isAdmin(); @endphp

<div class="page-head">
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
    @if($isAdmin)
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('purchases.index', ['book' => $book->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-in-down me-1"></i> បំពេញស្តុក</a>
            <a href="{{ route('books.edit', $book) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> កែប្រែ</a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                    data-action="{{ route('books.destroy', $book) }}" data-name="{{ $book->title }}"><i class="bi bi-trash3 me-1"></i> លុប</button>
        </div>
    @endif
</div>

<div class="bk-card">
    <div class="bk-card-body">
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                @include('books._cover', ['book' => $book, 'size' => 'xl'])
            </div>
            <div class="col-md-8 col-lg-9">
                @if($book->category)<span class="badge-soft muted mb-2 d-inline-block">{{ $book->category->name }}</span>@endif
                <h2 class="fw-bold mb-1">{{ $book->title }}</h2>
                <p class="text-muted mb-2"><i class="bi bi-person me-1"></i>{{ $book->author }}@if($book->isbn) · ISBN {{ $book->isbn }}@endif</p>
                <div class="mb-3">@if($book->reviews->isNotEmpty())@include('partials.stars', ['value' => $book->reviews_avg_rating, 'count' => $book->reviews->count()])@else<small class="text-muted">មិនទាន់មានការវាយតម្លៃ</small>@endif</div>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <span class="fs-2 fw-bold text-primary">${{ number_format($book->price, 2) }}</span>
                    <span class="badge-soft {{ $book->stock_badge['class'] }} fs-6">{{ $book->stock_badge['label'] }}</span>
                </div>

                <h6 class="fw-bold">ការពិពណ៌នា</h6>
                <p class="mb-4">{!! $book->description ? nl2br(e($book->description)) : '<span class="text-muted">មិនមានការពិពណ៌នា</span>' !!}</p>

                @unless($isAdmin)
                    <div class="d-flex gap-2 mb-4">@include('books._customer_actions', ['book' => $book, 'labels' => true])</div>
                @endunless

                <div style="max-width: 480px">
                    <div class="detail-row"><span>ចំនួនក្នុងស្តុក</span><span class="fw-semibold">{{ $book->stock }} ក្បាល</span></div>
                    @if($isAdmin)<div class="detail-row"><span>តម្លៃស្តុកសរុប</span><span class="fw-semibold">${{ number_format($book->price * $book->stock, 2) }}</span></div>@endif
                    <div class="detail-row"><span>បានបន្ថែមនៅ</span><span class="fw-semibold">{{ $book->created_at?->format('d/m/Y') }}</span></div>
                    <div class="detail-row"><span>កែប្រែចុងក្រោយ</span><span class="fw-semibold">{{ $book->updated_at?->diffForHumans() }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ការវាយតម្លៃ --}}
<div class="bk-card mt-4">
    <div class="bk-card-header"><h6 class="mb-0 fw-bold">⭐ ការវាយតម្លៃពីអតិថិជន</h6></div>
    <div class="bk-card-body">
        @if($canReview)
            <form action="{{ route('reviews.store', $book) }}" method="POST" class="mb-4 pb-4 border-bottom" style="border-color: var(--bk-border) !important" data-once>
                @csrf
                <div class="fw-semibold mb-1">{{ $myReview ? 'កែការវាយតម្លៃរបស់អ្នក' : 'វាយតម្លៃសៀវភៅនេះ' }} <span class="badge-soft success ms-1">អ្នកបានទិញ</span></div>
                <div class="star-input mb-2">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" @checked((int) old('rating', $myReview?->rating) === $i)><label for="star{{ $i }}" title="{{ $i }} ផ្កាយ">★</label>
                    @endfor
                </div>
                @error('rating')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <textarea name="comment" rows="2" maxlength="500" class="form-control mb-2" placeholder="មតិយោបល់ (ស្រេចចិត្ត)">{{ old('comment', $myReview?->comment) }}</textarea>
                <button class="btn btn-primary btn-sm">រក្សាទុក</button>
            </form>
        @elseif(! $isAdmin)
            <div class="small text-muted mb-3"><i class="bi bi-info-circle me-1"></i> អ្នកអាចវាយតម្លៃបាន បន្ទាប់ពីការកម្មង់សៀវភៅនេះត្រូវបានអនុម័ត។</div>
        @endif

        @forelse($book->reviews as $r)
            <div class="d-flex gap-3 mb-3">
                <span class="avatar flex-none">{{ mb_strtoupper(mb_substr($r->user->name, 0, 1)) }}</span>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div><span class="fw-semibold">{{ $r->user->name }}</span> @include('partials.stars', ['value' => $r->rating])</div>
                        <small class="text-muted">{{ $r->created_at->diffForHumans() }}</small>
                    </div>
                    @if($r->comment)<div>{{ $r->comment }}</div>@endif
                    @if($isAdmin || $r->user_id === auth()->id())
                        <form action="{{ route('reviews.destroy', $r) }}" method="POST" class="mt-1" onsubmit="return confirm('លុបការវាយតម្លៃនេះ?')">@csrf @method('DELETE')
                            <button class="btn btn-link btn-sm text-danger p-0">លុប</button></form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-muted">មិនទាន់មានការវាយតម្លៃទេ។</div>
        @endforelse
    </div>
</div>

{{-- ប្រវត្តិចលនាស្តុក (Admin) --}}
@if($isAdmin)
<div class="bk-card mt-4">
    <div class="bk-card-header"><h6 class="mb-0 fw-bold">📦 ប្រវត្តិចលនាស្តុក</h6></div>
    @if($movements->isEmpty())
        <div class="empty py-4">មិនទាន់មានចលនាស្តុកទេ</div>
    @else
        <div class="table-responsive">
            <table class="table bk-table">
                <thead><tr><th>កាលបរិច្ឆេទ</th><th>ប្រភេទ</th><th class="text-end">ប្តូរ</th><th class="text-end">នៅសល់</th><th>ដោយ</th><th>កំណត់ចំណាំ</th></tr></thead>
                <tbody>
                @foreach($movements as $m)
                    <tr>
                        <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge-soft muted">{{ $m->type_label }}</span></td>
                        <td class="text-end fw-bold {{ $m->change >= 0 ? 'text-success' : 'text-danger' }}">{{ $m->change > 0 ? '+' : '' }}{{ $m->change }}</td>
                        <td class="text-end">{{ $m->balance }}</td>
                        <td>{{ $m->user?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $m->note }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
@endsection

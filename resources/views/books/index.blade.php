@extends('layouts.app')
@section('title', 'សៀវភៅ')
@section('content')
@php
    $isAdmin = auth()->user()?->isAdmin();
    $filtered = $search !== '' || $category || $stock || $sort !== 'latest';
    $viewUrl = fn ($v) => route('books.index', array_merge(request()->query(), ['view' => $v, 'page' => null]));
@endphp

<div class="page-head">
    <div>
        <h1>{{ $isAdmin ? 'ផ្ទាំងគ្រប់គ្រងសៀវភៅ' : 'បញ្ជីសៀវភៅ' }}</h1>
        <p>សួស្តី {{ auth()->user()->name }} 👋 {{ $isAdmin ? 'នេះជាទិដ្ឋភាពទូទៅនៃហាងរបស់អ្នក' : 'ស្វែងរក និងរកមើលសៀវភៅរបស់យើង' }}</p>
    </div>
    @if($isAdmin)
        <div class="d-flex gap-2">
            <a href="{{ route('books.import') }}" class="btn btn-outline-secondary"><i class="bi bi-upload me-1"></i> នាំចូល CSV</a>
            <a href="{{ route('books.export', request()->only('search', 'category', 'stock')) }}" class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i> នាំចេញ CSV
            </a>
            <a href="{{ route('books.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> បន្ថែមសៀវភៅ</a>
        </div>
    @endif
</div>

{{-- ស្ថិតិ (សម្រាប់ Admin ប៉ុណ្ណោះ) --}}
@if($isAdmin)
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="bk-card stat">
            <span class="stat-icon tone-indigo"><i class="bi bi-journal-bookmark"></i></span>
            <div><p class="stat-label">ចំណងជើងសរុប</p><p class="stat-value">{{ number_format($stats['books']) }} <small>ក្បាល</small></p></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bk-card stat">
            <span class="stat-icon tone-green"><i class="bi bi-cash-stack"></i></span>
            <div><p class="stat-label">តម្លៃស្តុកសរុប</p><p class="stat-value">${{ number_format($stats['value'], 2) }}</p></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bk-card stat">
            <span class="stat-icon tone-amber"><i class="bi bi-exclamation-triangle"></i></span>
            <div><p class="stat-label">ជិតអស់ស្តុក</p><p class="stat-value">{{ $stats['low'] }} <small>ក្បាល</small></p></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bk-card stat">
            <span class="stat-icon tone-red"><i class="bi bi-x-octagon"></i></span>
            <div><p class="stat-label">អស់ស្តុក</p><p class="stat-value">{{ $stats['out'] }} <small>ក្បាល</small></p></div>
        </div>
    </div>
</div>
@endif

@if($isAdmin && $lowBooks->isNotEmpty())
    <div class="bk-card mb-4" style="border-left: 4px solid #f59e0b">
        <div class="bk-card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> សៀវភៅត្រូវការបំពេញស្តុក</div>
                <a href="{{ route('purchases.index') }}" class="btn btn-soft btn-sm"><i class="bi bi-box-arrow-in-down me-1"></i> បំពេញស្តុក</a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($lowBooks as $lb)
                    <a href="{{ route('purchases.index', ['book' => $lb->id]) }}" class="badge-soft {{ $lb->stock_badge['class'] }} text-decoration-none">{{ $lb->title }} · {{ $lb->stock }}</a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="bk-card">
    {{-- Toolbar --}}
    <div class="bk-card-header toolbar">
        <form action="{{ route('books.index') }}" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="col-12 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ស្វែងរកចំណងជើង ឬអ្នកនិពន្ធ...">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <select name="category" class="form-select" data-autosubmit>
                    <option value="">គ្រប់ប្រភេទ</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected((string) $category === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="stock" class="form-select" data-autosubmit>
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="in" @selected($stock === 'in')>មានស្តុក</option>
                    <option value="low" @selected($stock === 'low')>ជិតអស់</option>
                    <option value="out" @selected($stock === 'out')>អស់ស្តុក</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="sort" class="form-select" data-autosubmit>
                    <option value="latest" @selected($sort === 'latest')>ថ្មីបំផុត</option>
                    <option value="oldest" @selected($sort === 'oldest')>ចាស់បំផុត</option>
                    <option value="title" @selected($sort === 'title')>ចំណងជើង (ក-អ)</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>តម្លៃ ទាប → ខ្ពស់</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>តម្លៃ ខ្ពស់ → ទាប</option>
                    <option value="stock_asc" @selected($sort === 'stock_asc')>ស្តុក តិច → ច្រើន</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary flex-grow-1 flex-lg-grow-0">ស្វែងរក</button>
                <div class="btn-group view-toggle" role="group">
                    <a href="{{ $viewUrl('table') }}" class="btn btn-outline-secondary {{ $view === 'table' ? 'active' : '' }}" title="តារាង"><i class="bi bi-list-ul"></i></a>
                    <a href="{{ $viewUrl('grid') }}" class="btn btn-outline-secondary {{ $view === 'grid' ? 'active' : '' }}" title="ក្រឡា"><i class="bi bi-grid-3x3-gap"></i></a>
                </div>
            </div>
        </form>

        <div class="d-flex align-items-center justify-content-between mt-3 small text-muted">
            <span>រកឃើញ <b class="text-body">{{ $books->total() }}</b> ក្បាល</span>
            @if($filtered)
                <a href="{{ route('books.index', ['view' => $view]) }}" class="text-decoration-none"><i class="bi bi-x-circle me-1"></i>សម្អាតតម្រង</a>
            @endif
        </div>
    </div>

    @if($books->isEmpty())
        <div class="empty">
            <i class="bi bi-journal-x"></i>
            <h5 class="mt-3 text-body">មិនមានទិន្នន័យសៀវភៅទេ</h5>
            <p class="mb-3">{{ $filtered ? 'សូមព្យាយាមប្តូរពាក្យស្វែងរក ឬតម្រង' : 'ចាប់ផ្តើមដោយបន្ថែមសៀវភៅដំបូងរបស់អ្នក' }}</p>
            @if($isAdmin && ! $filtered)
                <a href="{{ route('books.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> បន្ថែមសៀវភៅ</a>
            @endif
        </div>
    @elseif($view === 'grid')
        <div class="bk-card-body">
            <div class="row g-3">
                @foreach($books as $book)
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="bk-card book-card">
                            <a href="{{ route('books.show', $book) }}">@include('books._cover', ['book' => $book, 'size' => 'lg'])</a>
                            <div class="body">
                                @if($book->category)<small class="text-muted">{{ $book->category->name }}</small>@endif
                                <a href="{{ route('books.show', $book) }}" class="book-title line-clamp-2">{{ $book->title }}</a>
                                <small class="text-muted">{{ $book->author }}</small>
                                @if($book->reviews_count)<div>@include('partials.stars', ['value' => $book->reviews_avg_rating, 'count' => $book->reviews_count])</div>@endif
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2">
                                    <span class="price">${{ number_format($book->price, 2) }}</span>
                                    <span class="badge-soft {{ $book->stock_badge['class'] }}">{{ $book->stock_badge['label'] }}</span>
                                </div>
                                @unless($isAdmin)
                                    <div class="d-flex gap-2 pt-2 justify-content-end">@include('books._customer_actions', ['book' => $book])</div>
                                @endunless
                                @if($isAdmin)
                                    <div class="d-flex gap-2 pt-2">
                                        <a href="{{ route('books.edit', $book) }}" class="btn btn-soft btn-sm flex-fill"><i class="bi bi-pencil-square"></i> កែ</a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                                                data-action="{{ route('books.destroy', $book) }}" data-name="{{ $book->title }}"><i class="bi bi-trash3"></i></button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table bk-table">
                <thead>
                    <tr>
                        <th>សៀវភៅ</th>
                        <th>ប្រភេទ</th>
                        <th>តម្លៃ</th>
                        <th>ស្តុក</th>
                        <th class="text-end">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @include('books._cover', ['book' => $book, 'size' => 'sm'])
                                    <div>
                                        <a href="{{ route('books.show', $book) }}" class="book-title">{{ $book->title }}</a>
                                        <div class="small text-muted">{{ $book->author }}</div>
                                        @if($book->reviews_count)<div>@include('partials.stars', ['value' => $book->reviews_avg_rating, 'count' => $book->reviews_count])</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($book->category)
                                    <span class="badge-soft muted">{{ $book->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-bold">${{ number_format($book->price, 2) }}</td>
                            <td><span class="badge-soft {{ $book->stock_badge['class'] }}">{{ $book->stock_badge['label'] }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('books.show', $book) }}" class="btn btn-soft btn-icon" title="មើល"><i class="bi bi-eye"></i></a>
                                @unless($isAdmin) @include('books._customer_actions', ['book' => $book]) @endunless
                                @if($isAdmin)
                                    <a href="{{ route('books.edit', $book) }}" class="btn btn-soft btn-icon" title="កែប្រែ"><i class="bi bi-pencil-square"></i></a>
                                    <button type="button" class="btn btn-outline-danger btn-icon" title="លុប" data-bs-toggle="modal" data-bs-target="#confirmDelete"
                                            data-action="{{ route('books.destroy', $book) }}" data-name="{{ $book->title }}"><i class="bi bi-trash3"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($books->hasPages())
        <div class="bk-card-body border-top d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-color: var(--bk-border) !important">
            <small class="text-muted">បង្ហាញ {{ $books->firstItem() }}–{{ $books->lastItem() }} ក្នុងចំណោម {{ $books->total() }}</small>
            <div class="bk-pager">{{ $books->links() }}</div>
        </div>
    @endif
</div>
@endsection

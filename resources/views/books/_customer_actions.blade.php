{{-- ប៊ូតុងសម្រាប់អតិថិជន: ចូលចិត្ត + ដាក់ក្នុងកន្ត្រក. $book, $wishIds, $labels (ជម្រើស) --}}
@php $wished = in_array($book->id, $wishIds ?? []); $labels = $labels ?? false; @endphp
<form action="{{ route('wishlist.toggle', $book) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn {{ $labels ? 'btn-outline-danger' : 'btn-soft btn-icon' }}" title="{{ $wished ? 'ដកចេញពីបញ្ជីចូលចិត្ត' : 'ចូលចិត្ត' }}">
        <i class="bi {{ $wished ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>@if($labels) <span class="ms-1">{{ $wished ? 'បានចូលចិត្ត' : 'ចូលចិត្ត' }}</span>@endif
    </button>
</form>
@if($book->stock > 0)
    <form action="{{ route('cart.add', $book) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-primary {{ $labels ? '' : 'btn-icon' }}" title="ដាក់ក្នុងកន្ត្រក"><i class="bi bi-cart-plus"></i>@if($labels) <span class="ms-1">ដាក់ក្នុងកន្ត្រក</span>@endif</button>
    </form>
    <form action="{{ route('cart.buy-now', $book) }}" method="POST" class="d-inline" data-once>
        @csrf
        <button type="submit" class="btn btn-success {{ $labels ? '' : 'btn-icon' }}" title="ទិញភ្លាម"><i class="bi bi-lightning-charge-fill"></i>@if($labels) <span class="ms-1">ទិញភ្លាម</span>@endif</button>
    </form>
@else
    <button type="button" class="btn btn-light {{ $labels ? '' : 'btn-icon' }}" disabled title="អស់ស្តុក"><i class="bi bi-cart-x"></i>@if($labels) <span class="ms-1">អស់ស្តុក</span>@endif</button>
@endif

{{-- $book, $size = sm|lg|xl --}}
@php
    $size = $size ?? 'sm';
    $grad = 'g'.((($book->id ?? 0) % 5) + 1);
@endphp
<span class="cover {{ $size }} {{ $grad }}">
    @if($book->image_url)
        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" loading="lazy">
    @else
        <i class="bi bi-book"></i>
    @endif
</span>

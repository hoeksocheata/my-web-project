{{-- $value (0-5, អាចជាទសភាគ), $count (ជម្រើស) --}}
@php $v = (float) ($value ?? 0); $full = (int) round($v); @endphp
<span class="stars" title="{{ number_format($v, 1) }}/5">
    @for($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $full ? 'bi-star-fill' : 'bi-star off' }}"></i>@endfor
</span>@isset($count)<small class="text-muted ms-1">({{ $count }})</small>@endisset

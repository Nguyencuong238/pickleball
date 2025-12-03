{{-- OCR Badge Component --}}
@props(['badge', 'size' => 'md', 'showName' => true])

@php
$sizeClasses = [
    'sm' => 'width: 32px; height: 32px; font-size: 0.75rem;',
    'md' => 'width: 48px; height: 48px; font-size: 0.875rem;',
    'lg' => 'width: 64px; height: 64px; font-size: 1rem;',
];

$containerStyle = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => 'd-inline-flex flex-column align-items-center']) }}>
    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow"
         style="background: linear-gradient(135deg, #fbbf24, #f59e0b); {{ $containerStyle }}"
         title="{{ $badge->description ?? '' }}"
         data-bs-toggle="tooltip">
        {{ $badge->icon ?? '[BADGE]' }}
    </div>
    @if($showName && $size !== 'sm')
        <span class="mt-1 small text-muted">{{ $badge->name ?? 'Badge' }}</span>
    @endif
</div>

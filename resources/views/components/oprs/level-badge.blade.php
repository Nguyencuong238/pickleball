@props(['level', 'size' => 'md', 'showName' => true])

@php
$levels = [
    '1.0' => ['name' => 'Beginner', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-300'],
    '2.0' => ['name' => 'Novice', 'bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300'],
    '3.0' => ['name' => 'Intermediate', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300'],
    '3.5' => ['name' => 'Upper Intermediate', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-300'],
    '4.0' => ['name' => 'Advanced', 'bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300'],
    '4.5' => ['name' => 'Pro', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300'],
    '5.0+' => ['name' => 'Elite', 'bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-300'],
];
$info = $levels[$level] ?? $levels['1.0'];
$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-2 text-base',
];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-medium border {$info['bg']} {$info['text']} {$info['border']} {$sizeClasses[$size]}"]) }}>
    <span class="font-bold {{ $showName ? 'mr-1' : '' }}">{{ $level }}</span>
    @if($showName)
        <span>{{ $info['name'] }}</span>
    @endif
</span>

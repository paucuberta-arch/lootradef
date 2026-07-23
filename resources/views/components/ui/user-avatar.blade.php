@props([
    'user' => null,
    'size' => 'md',
    'shape' => 'circle',
    'alt' => null,
    'loading' => 'lazy',
])

@php
    $sizes = [
        'xs' => 'h-6 w-6',
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-16 w-16',
        'xl' => 'h-32 w-32',
        'custom' => '',
    ];
    $shapes = [
        'circle' => 'rounded-full',
        'soft' => 'rounded-2xl',
        'square' => 'rounded-lg',
        'custom' => '',
    ];
    $name = $user?->name ?? 'Usuario';
    $avatarIndex = $user?->id ? (($user->id - 1) % 5) + 1 : 1;
    $src = asset(sprintf('images/lootra_visual_pack/06_avatars/avatar_neon_%02d_512.png', $avatarIndex));
    $fallback = $user ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
@endphp

@if($user)
<img
    src="{{ $src }}"
    alt="{{ $alt ?? 'Foto de perfil de '.$name }}"
    loading="{{ $loading }}"
    decoding="async"
    {{ $attributes->class([
        $sizes[$size] ?? $sizes['md'],
        $shapes[$shape] ?? $shapes['circle'],
        'user-avatar shrink-0 object-cover ring-1 ring-white/15',
    ]) }}
>
@else
<span
    role="img"
    aria-label="Usuario sin foto de perfil"
    {{ $attributes->class([
        $sizes[$size] ?? $sizes['md'],
        $shapes[$shape] ?? $shapes['circle'],
        'user-avatar grid shrink-0 place-items-center bg-gradient-to-br from-brand-300 to-brand-500 text-xs font-black text-slate-950 ring-1 ring-white/15',
    ]) }}
>{{ $fallback }}</span>
@endif

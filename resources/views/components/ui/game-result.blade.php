@props(['type' => 'info', 'live' => true])
<x-ui.alert :type="$type" :live="$live" {{ $attributes }}>{{ $slot }}</x-ui.alert>

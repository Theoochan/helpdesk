@props(['color' => 'gray'])

@php
$class = match($color) {
    'blue'   => 'badge-info',
    'yellow' => 'badge-warning',
    'green'  => 'badge-success',
    'red'    => 'badge-error',
    'purple' => 'badge-secondary',
    default  => 'badge-ghost',
};
@endphp

<span {{ $attributes->merge(['class' => "badge badge-sm $class"]) }}>
    {{ $slot }}
</span>

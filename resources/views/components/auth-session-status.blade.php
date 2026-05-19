@props(['status'])

@if ($status)
    <div data-auto-dismiss {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-green-400']) }}>
        {{ $status }}
    </div>
@endif

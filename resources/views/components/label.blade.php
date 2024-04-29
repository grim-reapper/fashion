@props(['value'])

<label {{ $attributes->merge(['class' => 'block mb-1 text-black-light']) }}>
    {{ $value ?? $slot }}
</label>

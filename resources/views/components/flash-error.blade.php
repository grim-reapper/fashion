@props(['error'])

@if ($error)
    <div {{ $attributes->merge(['class' => 'max-w-xl bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative']) }}>
        {!! $error !!}
    </div>
@endif
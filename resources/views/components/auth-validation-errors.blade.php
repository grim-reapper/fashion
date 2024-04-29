@props(['errors'])

@if ($errors->any())
    <div {{ $attributes }} class="bg-red-100 border-red-500 px-4 py-2 rounded-md mb-2 max-w-md w-full">
        <ul class="text-red-500 mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@props(['tag' => 'button', 'href' => ''])

@if($tag == 'button')
    <button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-orange']) }}>
        {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['href' => $href, 'class' => 'btn']) }}>
        {{ $slot }}
    </a>
@endif


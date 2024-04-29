@props(['disabled' => false, 'value' => ''])

<textarea cols="48" rows="5"
          {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'field']) !!} onblur="clean_text(this)">{{$value}}</textarea>
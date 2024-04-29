@props(['disabled' => false, 'type' => 'text'])
@php
    $password_field = '';
    if($type == 'password'){
        $password_field = ' password-field';
    }
@endphp
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'field'. $password_field ]) !!} type="{{$type}}"
       onblur="clean_text(this)">
@if($type == 'password')
    @once
        @push('scripts')
            <script>
                window.addEventListener('DOMContentLoaded', function () {
                    const passwordFields = document.querySelectorAll('.password-field');
                    passwordFields.forEach(field => {
                        const container = document.createElement('div');
                        container.style.position = 'relative';

                        field.parentNode.insertBefore(container, field);
                        container.appendChild(field);

                        const toggleButton = document.createElement('span');
                        toggleButton.style.position = 'absolute';
                        toggleButton.style.top = '50%';
                        toggleButton.style.right = '10px';
                        toggleButton.style.transform = 'translateY(-50%)';
                        toggleButton.style.cursor = 'pointer';
                        toggleButton.style.display = 'none';
                        toggleButton.classList.add('text-pomegranate');
                        // toggleButton.textContent = '👁️';
                        field.addEventListener('keyup', (e) => {
                            if(e.target.type == 'text'){
                                toggleButton.textContent = 'Hide';
                            }else {
                                toggleButton.textContent = 'Show';
                            }
                            if (e.target.value !== '') {
                                toggleButton.style.display = 'block';

                            } else {
                                toggleButton.style.display = 'none';

                            }
                        });
                        toggleButton.addEventListener('click', () => {
                            if (field.type === 'password') {
                                field.type = 'text';
                                toggleButton.textContent = 'Hide'; // Eye icon
                            } else {
                                field.type = 'password';
                                toggleButton.textContent = 'Show'; // Eye closed icon
                            }
                        });

                        container.appendChild(toggleButton);
                    });
                });
            </script>
        @endpush
    @endonce
@endif
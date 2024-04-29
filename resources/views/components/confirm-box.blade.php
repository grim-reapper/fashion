@props(['title' => '', 'description' => ''])
<div class="modal-confirm-overlay" tabindex="0" x-data
     x-show="$store.confirmModal.open"
     @keydown.window.escape="$store.confirmModal.open = false"
     x-ref="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-black opacity-75 z400" x-on:click="$store.confirmModal.open = false"></div>
    <div class="modal-confirm text-black-light">
        <!--content-->
        <div class="">
            <!--body-->
            <div class="modal-confirm__body">
                {{ $icon ?? ''}}

                @if($title)
                    <h2 class="modal-confirm__body--h2">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="modal-confirm__body--p">{{ $description }}</p>
                @endif
                @if(isset($content))
                    {!! $content !!}
                @endif
            </div>
            <!--footer-->
            <div class="modal-confirm__footer">
                @if(isset($footer))
                    {!! $footer !!}
                @else
                    <button
                            class="cancel btn btn-outline-orange mr-3"
                            x-on:click="$store.confirmModal.onCancel()">Cancel
                    </button>
                    <button
                            class="ok btn btn-orange"
                            x-on:click="$store.confirmModal.onOk()" x-html="$store.confirmModal.okText">
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@push('styles')
    <style>
        .modal-confirm-overlay:focus {
            outline: none;
        }

        .z400 {
            z-index: 400;
        }

        .modal-confirm-overlay .modal-confirm {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            overflow: auto;
            max-height: calc(100vh - 50px);
            outline: none;

            box-sizing: border-box;
            width: 100%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            /*position: relative;*/
            padding: 1.5rem;
            max-width: 32rem;
            background: var(--modal-bg, #fff);
            border-radius: var(--modal-radius, 0.75rem);
            z-index: 500;
        }

        .modal-confirm-overlay .modal-confirm__body {
            text-align: center;
            padding: 1.2rem;
            flex: 1 1 auto;
            justify-content: center;
        }

        .modal-confirm-overlay .modal-confirm__body--icon {
            width: 4rem;
            height: 4rem;
            color: #ef4444;
            margin: auto;
            display: flex;
            align-items: center;
        }

        .modal-confirm-overlay .modal-confirm__body--h2 {
            margin: 0;
            text-align: center;
            padding-top: 1rem;
            padding-bottom: 1rem;
            font-size: 1.1rem;
            line-height: 1.75rem;
            font-weight: 700;
        }

        .modal-confirm-overlay .modal-confirm__body--p {
            margin: 0;
            text-align: center;
            color: #736c6c;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .modal-confirm-overlay .modal-confirm__footer {
            display: flex;
            justify-content: center;
            align-content: center;
            text-align: center;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('confirmModal', {
                open: false,
                okText: 'Confirm',
                onOk() {
                },
                onCancel() {
                },
            });
        });

        window.customConfirm = (props) => {
            return new Promise((resolve, reject) => {
                const confirmModal = Alpine.store('confirmModal');

                Object.assign(confirmModal, props);

                confirmModal.onOk = () => {
                    confirmModal.open = false;
                    resolve(true);
                };

                confirmModal.onCancel = () => {
                    confirmModal.open = false;
                    resolve(false);
                };

                confirmModal.open = true;
            });
        };
    </script>
@endpush
@props(['status' => ''])

@if($status === 'success' || session()->has('success'))
    <div class="bg-green text-white p-4 rounded-md mb-4 max-w-xl w-full flex gap-2 items-start">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M19.999 12a1 1 0 0 1 2 0c0 5.523-4.477 10-10 10a9.93 9.93 0 0 1-7-2.91 10 10 0 0 1-2.948-7.038C2.022 6.529 6.476 2.029 11.999 2a10.54 10.54 0 0 1 2.49.31 1 1 0 1 1-.59 1.91 8.789 8.789 0 0 0-1.9-.22 7.93 7.93 0 0 0-5.67 2.36A8 8 0 0 0 11.999 20a8 8 0 0 0 8-8zM8.29 11.29a1.004 1.004 0 0 1 1.42 0L12 13.54l6.22-7.2a1 1 0 0 1 1.5 1.32l-7 8A1 1 0 0 1 12 16a1 1 0 0 1-.71-.29l-3-3a1.004 1.004 0 0 1 0-1.42z" fill="#fff"/>
        </svg>
        <span>{!! session('success') !!}</span>
    </div>
@elseif($status === 'error' || session()->has('error'))
    <div class="bg-red-100 border-red-500 text-red-700 px-4 py-2 rounded-md mb-4 max-w-md w-full">
        {{ session('error') }}
    </div>
@elseif($status === 'info' || session()->has('info'))
    <div class="bg-blue-100 border-blue-500 text-blue-700 px-4 py-2 rounded-md mb-4 max-w-md w-full">
        {{ session('info') }}
    </div>
@endif
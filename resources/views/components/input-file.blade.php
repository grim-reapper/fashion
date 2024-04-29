@props(['multiple' => true, 'allowedFileTypes' => 'docx,doc,pdf' ])

<input id="filename" type="file" @if($multiple) multiple @endif  {!! $attributes->merge(['class' => '']) !!} onchange="validateSize(this)">
<p class="text-red-500" id="file_size_error"></p>

@push('scripts')
<script>
    function validateSize(input) {
        const allowedFileTypes = "{{ $allowedFileTypes }}".split(',');
        const maxAllowedSize = 2048; // size in kB = 2MB
        const files = input.files;
        let areValidFiles = true;
        let totalSize = 0;

        for (let i = 0; i < files.length; i++) {
            let file_name = files[i].name;
            totalSize += files[i].size / 1024; // size in bytes / 1024
            let file_parts = file_name.split('.');
            if (!allowedFileTypes.includes(file_parts[file_parts.length - 1])) {
                areValidFiles = false;
                break;
            }
        }
        if (!areValidFiles) {
            document.getElementById('file_size_error').innerText = 'Only Docx, Doc and PDF files are allowed';
            document.getElementById('filename').value = '';
        } else if (totalSize > maxAllowedSize) {
            document.getElementById('file_size_error').innerText = 'Files size should not exceed ' + (maxAllowedSize/1024) + 'MB';
            document.getElementById('filename').value = '';
        } else {
            document.getElementById('file_size_error').innerText = '';
        }
    }
</script>
@endpush
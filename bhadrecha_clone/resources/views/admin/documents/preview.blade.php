<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - {{ $document->name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/boxicons.css') }}" />
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-color: #2b2b2b;
            color: #fff;
        }
        .preview-header {
            background-color: #1e1e1e;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #383838;
        }
        .preview-container {
            height: calc(100vh - 65px);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: auto;
        }
        iframe, img {
            max-width: 100%;
            max-height: 100%;
            border: none;
        }
    </style>
</head>
<body>

    <div class="preview-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bx bx-file text-primary fs-3"></i>
            <div>
                <h6 class="mb-0 text-white fw-bold">{{ $document->name }}</h6>
                <small class="text-white-50">{{ $document->original_file_name }} &bull; {{ $document->formatted_file_size }} &bull; v{{ $document->version }}</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.documents.download', $document->id) }}" class="btn btn-sm btn-success">
                <i class="bx bx-download me-1"></i> Download
            </a>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                <i class="bx bx-x me-1"></i> Close
            </button>
        </div>
    </div>

    <div class="preview-container">
        @php
            $ext = strtolower($document->file_extension);
            $previewUrl = route('admin.documents.preview', $document->id);
        @endphp

        @if(in_array($ext, ['pdf']))
            <iframe src="{{ $previewUrl }}" width="100%" height="100%"></iframe>
        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']))
            <div class="p-4 text-center">
                <img src="{{ $previewUrl }}" alt="{{ $document->name }}" class="img-fluid rounded shadow">
            </div>
        @elseif(in_array($ext, ['txt', 'csv', 'json', 'log']))
            <iframe src="{{ $previewUrl }}" width="100%" height="100%" style="background:#fff;"></iframe>
        @else
            <div class="text-center p-5">
                <i class="bx bx-file text-secondary display-1 mb-3"></i>
                <h4 class="text-white fw-bold">Direct Browser Preview Not Supported</h4>
                <p class="text-white-50">Browser preview is supported for PDF, Images, and Text files. Click below to download original file.</p>
                <a href="{{ route('admin.documents.download', $document->id) }}" class="btn btn-primary btn-lg mt-3">
                    <i class="bx bx-download me-2"></i> Download File ({{ strtoupper($document->file_extension) }})
                </a>
            </div>
        @endif
    </div>

</body>
</html>

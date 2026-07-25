<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Http\Requests\Document\UploadVersionRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentVersionController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function store(UploadVersionRequest $request, Document $document)
    {
        $file = $request->file('document_file');
        $changelog = $request->input('changelog');

        $version = $this->documentService->uploadNewVersion($document, $file, auth()->id(), $changelog);

        return redirect()->route('admin.documents.show', $document->id)
            ->with('success', "New version (v{$version->version_number}) uploaded successfully.");
    }

    public function download(DocumentVersion $version)
    {
        if (!Storage::disk('local')->exists($version->storage_path)) {
            return back()->with('error', 'Version file not found on storage.');
        }

        $document = $version->document;
        $this->documentService->logDownload($document, auth()->id(), $version->id);

        return Storage::disk('local')->download($version->storage_path, $version->original_file_name);
    }
}

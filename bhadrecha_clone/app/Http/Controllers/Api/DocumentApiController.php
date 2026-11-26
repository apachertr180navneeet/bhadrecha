<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentFolder;
use App\Models\DocumentActivityLog;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentCategoryResource;
use App\Http\Resources\DocumentFolderResource;
use App\Http\Resources\DocumentActivityResource;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentApiController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $query = Document::forCompany($companyId)
            ->with(['category', 'folder', 'uploader']);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('document_number', 'like', $term)
                  ->orWhere('tags', 'like', $term);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        $documents = $query->latest()->paginate($request->get('per_page', 15));

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['company_id'] = $user->company_id;

        $file = $request->file('document_file');
        $document = $this->documentService->storeDocument($data, $file, $user->id);

        return new DocumentResource($document->load(['category', 'folder', 'uploader']));
    }

    public function show(Document $document)
    {
        $user = auth()->user();
        if ($document->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return new DocumentResource($document->load(['category', 'folder', 'uploader', 'versions']));
    }

    public function destroy(Document $document)
    {
        $user = auth()->user();
        if ($document->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $this->documentService->logActivity($document->id, $document->company_id, $user->id, 'delete', "Deleted document via API");
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    public function categories()
    {
        $categories = DocumentCategory::forCompany(auth()->user()->company_id)->active()->get();
        return DocumentCategoryResource::collection($categories);
    }

    public function folders()
    {
        $folders = DocumentFolder::forCompany(auth()->user()->company_id)->active()->get();
        return DocumentFolderResource::collection($folders);
    }

    public function activities()
    {
        $activities = DocumentActivityLog::where('company_id', auth()->user()->company_id)
            ->with(['document', 'user'])
            ->latest()
            ->paginate(20);

        return DocumentActivityResource::collection($activities);
    }
}

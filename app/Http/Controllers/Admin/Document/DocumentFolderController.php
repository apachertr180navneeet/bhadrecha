<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentFolder;
use App\Models\DocumentCategory;
use App\Models\Company;
use App\Models\Branch;
use App\Http\Requests\Document\StoreDocumentFolderRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentFolderController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $foldersQuery = DocumentFolder::query();
        if ($companyId) {
            $foldersQuery->forCompany($companyId);
        }

        $folders = $foldersQuery
            ->with(['parent', 'category', 'company', 'branch'])
            ->withCount('documents')
            ->orderBy('name', 'asc')
            ->get();

        $allFolders = DocumentFolder::when($companyId, fn($q) => $q->forCompany($companyId))->get();
        $categories = DocumentCategory::when($companyId, fn($q) => $q->forCompany($companyId))->get();
        $branches = $companyId ? Branch::where('company_id', $companyId)->get() : Branch::all();
        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.folders.index', compact('folders', 'allFolders', 'categories', 'branches', 'companies', 'companyId'));
    }

    public function store(StoreDocumentFolderRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        if (!$user->isSuperAdmin() || empty($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }
        $data['slug'] = Str::slug($data['name']);

        $folder = DocumentFolder::create($data);

        $this->documentService->logActivity(
            null,
            $folder->company_id,
            $user->id,
            'folder_create',
            "Created folder '{$folder->name}'"
        );

        return redirect()->route('admin.documents.folders.index')
            ->with('success', 'Document Folder created successfully.');
    }

    public function update(StoreDocumentFolderRequest $request, DocumentFolder $folder)
    {
        $user = auth()->user();
        $data = $request->validated();
        if (!$user->isSuperAdmin() || empty($data['company_id'])) {
            $data['company_id'] = $folder->company_id ?? $user->company_id;
        }
        $data['slug'] = Str::slug($data['name']);

        $folder->update($data);

        $this->documentService->logActivity(
            null,
            $folder->company_id,
            auth()->id(),
            'folder_edit',
            "Updated folder '{$folder->name}'"
        );

        return redirect()->route('admin.documents.folders.index')
            ->with('success', 'Document Folder updated successfully.');
    }

    public function destroy(DocumentFolder $folder)
    {
        $this->documentService->logActivity(
            null,
            $folder->company_id,
            auth()->id(),
            'folder_delete',
            "Deleted folder '{$folder->name}'"
        );

        $folder->delete();

        return redirect()->route('admin.documents.folders.index')
            ->with('success', 'Document Folder deleted successfully.');
    }
}

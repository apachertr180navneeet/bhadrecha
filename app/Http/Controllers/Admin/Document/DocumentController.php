<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentFolder;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
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

        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();
        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        if ($request->ajax()) {
            return $this->getDataTable($request, $companyId);
        }

        $categories = DocumentCategory::forCompany($companyId)->active()->get();
        $folders = DocumentFolder::forCompany($companyId)->active()->get();
        $branches = $companyId ? Branch::where('company_id', $companyId)->get() : Branch::all();
        $users = User::byCompany($companyId)->get();

        return view('admin.documents.index', compact('categories', 'folders', 'branches', 'companies', 'users', 'companyId'));
    }

    protected function getDataTable(Request $request, $companyId)
    {
        $query = Document::forCompany($companyId)
            ->with(['category', 'folder', 'uploader', 'company', 'branch']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('file_type')) {
            $query->where('file_extension', strtolower($request->file_type));
        }
        if ($request->filled('user_id')) {
            $query->where('uploaded_by', $request->user_id);
        }
        if ($request->filled('search_term')) {
            $term = '%' . $request->search_term . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('document_number', 'like', $term)
                  ->orWhere('original_file_name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('tags', 'like', $term);
            });
        }
        if ($request->filled('expiry_filter')) {
            switch ($request->expiry_filter) {
                case 'today':
                    $query->whereDate('expiry_date', today());
                    break;
                case '7_days':
                    $query->whereBetween('expiry_date', [today(), today()->addDays(7)]);
                    break;
                case '15_days':
                    $query->whereBetween('expiry_date', [today(), today()->addDays(15)]);
                    break;
                case '30_days':
                    $query->whereBetween('expiry_date', [today(), today()->addDays(30)]);
                    break;
                case 'expired':
                    $query->where('expiry_date', '<', today());
                    break;
            }
        }

        $totalRecords = $query->count();
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = ['id', 'document_number', 'name', 'category_id', 'version', 'file_size', 'expiry_date', 'status', 'created_at'];
        $orderBy = $columns[$orderColumnIndex] ?? 'created_at';

        $documents = $query->orderBy($orderBy, $orderDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($documents as $doc) {
            $data[] = [
                'id' => $doc->id,
                'uuid' => $doc->uuid,
                'document_number' => $doc->document_number,
                'name' => $doc->name,
                'category' => $doc->category?->name ?? 'N/A',
                'folder' => $doc->folder?->name ?? 'Root',
                'version' => 'v' . $doc->version,
                'file_extension' => strtoupper($doc->file_extension),
                'file_size' => $doc->formatted_file_size,
                'uploader' => $doc->uploader?->full_name ?? 'N/A',
                'expiry_date' => $doc->expiry_date ? $doc->expiry_date->format('d M Y') : '-',
                'is_expired' => $doc->is_expired,
                'is_expiring_soon' => $doc->is_expiring_soon,
                'status' => $doc->status,
                'downloads' => $doc->downloads_count,
                'created_at' => $doc->created_at->format('d M Y, h:i A'),
                'actions' => view('admin.documents.partials.actions', compact('doc'))->render(),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();
        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        $categories = DocumentCategory::forCompany($companyId)->active()->get();
        $folders = DocumentFolder::forCompany($companyId)->active()->get();
        $branches = $companyId ? Branch::where('company_id', $companyId)->get() : Branch::all();

        return view('admin.documents.create', compact('categories', 'folders', 'branches', 'companies', 'companyId'));
    }

    public function store(StoreDocumentRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $file = $request->file('document_file');
        $document = $this->documentService->storeDocument($data, $file, $user->id);

        return redirect()->route('admin.documents.show', $document->id)
            ->with('success', "Document '{$document->name}' uploaded successfully with Document Number: {$document->document_number}.");
    }

    public function show(Document $document)
    {
        $document->load(['category', 'folder', 'uploader', 'company', 'branch', 'versions.uploader', 'downloads.user', 'activities.user']);
        return view('admin.documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $companyId = $document->company_id;
        $categories = DocumentCategory::forCompany($companyId)->active()->get();
        $folders = DocumentFolder::forCompany($companyId)->active()->get();
        $branches = Branch::where('company_id', $companyId)->get();
        $companies = auth()->user()->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.edit', compact('document', 'categories', 'folders', 'branches', 'companies'));
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $data = $request->validated();

        if (!empty($data['tags'])) {
            if (!is_array($data['tags'])) {
                $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
            }
        }

        $document->update($data);

        $this->documentService->logActivity($document->id, $document->company_id, auth()->id(), 'edit', "Updated metadata for document '{$document->name}'");

        return redirect()->route('admin.documents.show', $document->id)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        $this->documentService->logActivity($document->id, $document->company_id, auth()->id(), 'delete', "Moved document '{$document->name}' to Trash");
        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document moved to trash successfully.');
    }

    public function download(Document $document)
    {
        if (!Storage::disk('local')->exists($document->storage_path)) {
            return back()->with('error', 'File not found on storage server.');
        }

        $this->documentService->logDownload($document, auth()->id());

        return Storage::disk('local')->download($document->storage_path, $document->original_file_name);
    }

    public function preview(Document $document)
    {
        if (!Storage::disk('local')->exists($document->storage_path)) {
            return back()->with('error', 'File not found on storage server.');
        }

        $this->documentService->logActivity($document->id, $document->company_id, auth()->id(), 'preview', "Previewed document '{$document->name}'");

        $fullPath = Storage::disk('local')->path($document->storage_path);
        $mimeType = $document->mime_type ?? mime_content_type($fullPath);

        // Serve inline file stream
        return Response::make(file_get_contents($fullPath), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->original_file_name . '"'
        ]);
    }

    public function trash(Request $request)
    {
        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $trashedDocuments = Document::onlyTrashed()
            ->forCompany($companyId)
            ->with(['category', 'uploader'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.documents.trash', compact('trashedDocuments', 'companyId'));
    }

    public function restore($id)
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $document->restore();

        $this->documentService->logActivity($document->id, $document->company_id, auth()->id(), 'restore', "Restored document '{$document->name}' from Trash");

        return back()->with('success', "Document '{$document->name}' restored successfully.");
    }

    public function forceDelete($id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can permanently delete documents.');
        }

        $document = Document::onlyTrashed()->findOrFail($id);
        if (Storage::disk('local')->exists($document->storage_path)) {
            Storage::disk('local')->delete($document->storage_path);
        }

        $document->forceDelete();

        return back()->with('success', 'Document permanently deleted.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $documentIds = $request->input('document_ids', []);

        if (empty($documentIds)) {
            return back()->with('error', 'No documents selected.');
        }

        $companyId = auth()->user()->company_id;

        switch ($action) {
            case 'delete':
                Document::forCompany($companyId)->whereIn('id', $documentIds)->delete();
                return back()->with('success', 'Selected documents moved to trash.');

            case 'restore':
                Document::onlyTrashed()->forCompany($companyId)->whereIn('id', $documentIds)->restore();
                return back()->with('success', 'Selected documents restored.');

            case 'download_zip':
                $zipPath = $this->documentService->createBulkZip($documentIds, $companyId);
                if ($zipPath && file_exists($zipPath)) {
                    return response()->download($zipPath)->deleteFileAfterSend(true);
                }
                return back()->with('error', 'Could not generate ZIP package for selected files.');

            case 'change_category':
                if ($request->filled('target_category_id')) {
                    Document::forCompany($companyId)->whereIn('id', $documentIds)->update(['category_id' => $request->target_category_id]);
                    return back()->with('success', 'Category updated for selected documents.');
                }
                break;

            case 'change_folder':
                if ($request->filled('target_folder_id')) {
                    Document::forCompany($companyId)->whereIn('id', $documentIds)->update(['folder_id' => $request->target_folder_id]);
                    return back()->with('success', 'Folder updated for selected documents.');
                }
                break;

            case 'change_status':
                if ($request->filled('target_status')) {
                    Document::forCompany($companyId)->whereIn('id', $documentIds)->update(['status' => $request->target_status]);
                    return back()->with('success', 'Status updated for selected documents.');
                }
                break;
        }

        return back()->with('error', 'Invalid action or missing target parameter.');
    }
}

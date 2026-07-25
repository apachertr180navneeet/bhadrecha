<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\Company;
use App\Http\Requests\Document\StoreDocumentCategoryRequest;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
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

        $categories = DocumentCategory::forCompany($companyId)
            ->with(['parent', 'company'])
            ->withCount('documents')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $parentCategories = DocumentCategory::forCompany($companyId)->whereNull('parent_id')->get();
        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.categories.index', compact('categories', 'parentCategories', 'companies', 'companyId'));
    }

    public function store(StoreDocumentCategoryRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        if (!$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }
        $data['slug'] = Str::slug($data['name']);

        $category = DocumentCategory::create($data);

        $this->documentService->logActivity(
            null,
            $category->company_id,
            $user->id,
            'category_create',
            "Created category '{$category->name}'"
        );

        return redirect()->route('admin.documents.categories.index')
            ->with('success', 'Document Category created successfully.');
    }

    public function update(StoreDocumentCategoryRequest $request, DocumentCategory $category)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        $this->documentService->logActivity(
            null,
            $category->company_id,
            auth()->id(),
            'category_edit',
            "Updated category '{$category->name}'"
        );

        return redirect()->route('admin.documents.categories.index')
            ->with('success', 'Document Category updated successfully.');
    }

    public function destroy(DocumentCategory $category)
    {
        $this->documentService->logActivity(
            null,
            $category->company_id,
            auth()->id(),
            'category_delete',
            "Deleted category '{$category->name}'"
        );

        $category->delete();

        return redirect()->route('admin.documents.categories.index')
            ->with('success', 'Document Category deleted successfully.');
    }
}

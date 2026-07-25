<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentDownload;
use App\Models\DocumentActivityLog;
use App\Models\DocumentCategory;
use App\Models\DocumentFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class DocumentService
{
    /**
     * Store a new document and initial version.
     */
    public function storeDocument(array $data, UploadedFile $file, $userId): Document
    {
        $companyId = $data['company_id'];
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFileName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $fileName = time() . '_' . Str::random(10) . '.' . $extension;
        $directory = "documents/company_{$companyId}/" . date('Y/m');
        $storagePath = $file->storeAs($directory, $fileName, 'local');

        // Tags parsing
        $tagsArray = null;
        if (!empty($data['tags'])) {
            if (is_array($data['tags'])) {
                $tagsArray = $data['tags'];
            } else {
                $tagsArray = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
            }
        }

        $document = Document::create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'folder_id' => $data['folder_id'] ?? null,
            'description' => $data['description'] ?? null,
            'tags' => $tagsArray,
            'version' => '1.0',
            'file_name' => $fileName,
            'original_file_name' => $originalFileName,
            'file_extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'storage_path' => $storagePath,
            'uploaded_by' => $userId,
            'department' => $data['department'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'status' => $data['status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null,
        ]);

        // Create initial version record
        DocumentVersion::create([
            'document_id' => $document->id,
            'company_id' => $companyId,
            'version_number' => '1.0',
            'file_name' => $fileName,
            'original_file_name' => $originalFileName,
            'file_extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'storage_path' => $storagePath,
            'changelog' => 'Initial upload',
            'uploaded_by' => $userId,
        ]);

        // Log Activity
        $this->logActivity($document->id, $companyId, $userId, 'upload', "Uploaded new document '{$document->name}' (v1.0)");

        return $document;
    }

    /**
     * Upload a new version for an existing document.
     */
    public function uploadNewVersion(Document $document, UploadedFile $file, $userId, ?string $changelog = null): DocumentVersion
    {
        $companyId = $document->company_id;
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFileName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Increment version string (e.g. 1.0 -> 2.0 or 1.1)
        $currentVer = (float) ($document->version ?? 1.0);
        $newVersionNumber = number_format($currentVer + 1.0, 1);

        $fileName = time() . '_v' . Str::slug($newVersionNumber) . '_' . Str::random(8) . '.' . $extension;
        $directory = "documents/company_{$companyId}/" . date('Y/m');
        $storagePath = $file->storeAs($directory, $fileName, 'local');

        // Create new version entry
        $version = DocumentVersion::create([
            'document_id' => $document->id,
            'company_id' => $companyId,
            'version_number' => $newVersionNumber,
            'file_name' => $fileName,
            'original_file_name' => $originalFileName,
            'file_extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'storage_path' => $storagePath,
            'changelog' => $changelog ?? "Uploaded version {$newVersionNumber}",
            'uploaded_by' => $userId,
        ]);

        // Update main document record to point to latest version
        $document->update([
            'version' => $newVersionNumber,
            'file_name' => $fileName,
            'original_file_name' => $originalFileName,
            'file_extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'storage_path' => $storagePath,
        ]);

        $this->logActivity($document->id, $companyId, $userId, 'version_upload', "Uploaded version {$newVersionNumber} for document '{$document->name}'");

        return $version;
    }

    /**
     * Log document activity.
     */
    public function logActivity(?int $documentId, ?int $companyId, ?int $userId, string $action, string $description): DocumentActivityLog
    {
        return DocumentActivityLog::create([
            'document_id' => $documentId,
            'company_id' => $companyId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }

    /**
     * Record document download.
     */
    public function logDownload(Document $document, $userId, ?int $versionId = null): DocumentDownload
    {
        $document->increment('downloads_count');

        $download = DocumentDownload::create([
            'document_id' => $document->id,
            'company_id' => $document->company_id,
            'version_id' => $versionId,
            'user_id' => $userId,
            'downloaded_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        $this->logActivity($document->id, $document->company_id, $userId, 'download', "Downloaded document '{$document->name}' (v" . ($versionId ? 'specific' : $document->version) . ")");

        return $download;
    }

    /**
     * Create ZIP archive for bulk download.
     */
    public function createBulkZip(array $documentIds, $companyId): ?string
    {
        $documents = Document::forCompany($companyId)->whereIn('id', $documentIds)->get();
        if ($documents->isEmpty()) {
            return null;
        }

        $zipFileName = 'documents_export_' . date('Ymd_His') . '.zip';
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/' . $zipFileName;
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($documents as $doc) {
                if (Storage::disk('local')->exists($doc->storage_path)) {
                    $absoluteFilePath = Storage::disk('local')->path($doc->storage_path);
                    $entryName = $doc->document_number . '_' . Str::slug($doc->name) . '.' . $doc->file_extension;
                    $zip->addFile($absoluteFilePath, $entryName);
                }
            }
            $zip->close();
            return $zipPath;
        }

        return null;
    }

    /**
     * Compute Storage & Metric statistics for a company.
     */
    public function getStorageMetrics(?int $companyId = null): array
    {
        $query = Document::withTrashed()->forCompany($companyId);

        $totalDocuments = (clone $query)->whereNull('deleted_at')->count();
        $totalBytes = (clone $query)->whereNull('deleted_at')->sum('file_size');

        $trashedBytes = (clone $query)->onlyTrashed()->sum('file_size');
        $trashedCount = (clone $query)->onlyTrashed()->count();

        $uploadedToday = (clone $query)->whereNull('deleted_at')->whereDate('created_at', today())->count();

        // Expiring calculations
        $expiringToday = (clone $query)->whereNull('deleted_at')->whereDate('expiry_date', today())->count();
        $expiringNext7 = (clone $query)->whereNull('deleted_at')->whereBetween('expiry_date', [today(), today()->addDays(7)])->count();
        $expiringNext15 = (clone $query)->whereNull('deleted_at')->whereBetween('expiry_date', [today(), today()->addDays(15)])->count();
        $expiringNext30 = (clone $query)->whereNull('deleted_at')->whereBetween('expiry_date', [today(), today()->addDays(30)])->count();

        // Largest 10 files
        $largestFiles = (clone $query)->whereNull('deleted_at')->orderBy('file_size', 'desc')->limit(10)->get();

        return [
            'total_documents' => $totalDocuments,
            'total_bytes' => $totalBytes,
            'formatted_total_size' => $this->formatBytes($totalBytes),
            'trashed_bytes' => $trashedBytes,
            'formatted_trashed_size' => $this->formatBytes($trashedBytes),
            'trashed_count' => $trashedCount,
            'uploaded_today' => $uploadedToday,
            'expiring_today' => $expiringToday,
            'expiring_7_days' => $expiringNext7,
            'expiring_15_days' => $expiringNext15,
            'expiring_30_days' => $expiringNext30,
            'largest_files' => $largestFiles,
        ];
    }

    public function formatBytes($bytes, $precision = 2)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, $precision) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, $precision) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, $precision) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}

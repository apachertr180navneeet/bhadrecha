<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'document_number',
        'company_id',
        'branch_id',
        'name',
        'category_id',
        'folder_id',
        'description',
        'tags',
        'version',
        'file_name',
        'original_file_name',
        'file_extension',
        'mime_type',
        'file_size',
        'storage_path',
        'uploaded_by',
        'department',
        'issue_date',
        'effective_date',
        'expiry_date',
        'status',
        'remarks',
        'downloads_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'issue_date' => 'date',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'downloads_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($doc) {
            if (empty($doc->uuid)) {
                $doc->uuid = (string) Str::uuid();
            }
            if (empty($doc->document_number)) {
                $doc->document_number = 'DOC-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id')->orderBy('created_at', 'desc');
    }

    public function downloads()
    {
        return $this->hasMany(DocumentDownload::class, 'document_id')->orderBy('downloaded_at', 'desc');
    }

    public function activities()
    {
        return $this->hasMany(DocumentActivityLog::class, 'document_id')->orderBy('created_at', 'desc');
    }

    public function scopeForCompany($query, $companyId = null)
    {
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiry_date || !($this->expiry_date instanceof \DateTimeInterface)) {
            return false;
        }
        return $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date || !($this->expiry_date instanceof \DateTimeInterface)) {
            return false;
        }
        return $this->expiry_date->isPast();
    }
}

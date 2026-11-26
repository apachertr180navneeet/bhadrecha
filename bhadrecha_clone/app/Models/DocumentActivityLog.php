<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'company_id',
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

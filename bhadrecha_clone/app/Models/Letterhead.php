<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letterhead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'letter_no',
        'letter_date',
        'recipient_name',
        'recipient_designation',
        'recipient_company',
        'recipient_address',
        'recipient_email',
        'subject',
        'content',
        'signatory_name',
        'signatory_designation',
        'created_by',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class UserMailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'is_active',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $hidden = [
        'mail_password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
        'mail_port' => 'integer',
    ];

    protected $appends = [
        'has_password',
        'is_configured',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setMailPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['mail_password'] = Crypt::encryptString($value);
        }
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        if (empty($this->attributes['mail_password'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['mail_password']);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getHasPasswordAttribute(): bool
    {
        return !empty($this->attributes['mail_password']);
    }

    public function getIsConfiguredAttribute(): bool
    {
        return !empty($this->mail_host)
            && !empty($this->mail_username)
            && !empty($this->attributes['mail_password'])
            && $this->is_active;
    }
}

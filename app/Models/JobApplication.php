<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class JobApplication extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_INTERVIEW = 'interview';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ON_HOLD = 'on_hold';

    // Sumber Constants
    public const SOURCE_INSTAGRAM = 'instagram';
    public const SOURCE_FACEBOOK = 'facebook';
    public const SOURCE_LINKEDIN = 'linkedin';
    public const SOURCE_TWITTER = 'twitter';
    public const SOURCE_JOBSTREET = 'jobstreet';
    public const SOURCE_INDEED = 'indeed';
    public const SOURCE_REFERRAL = 'referral';
    public const SOURCE_WEBSITE = 'website';
    public const SOURCE_WALK_IN = 'walk_in';
    public const SOURCE_OTHER = 'other';

    // Daftar Melalui Constants
    public const METHOD_MANUAL = 'manual';
    public const METHOD_EMAIL = 'email';
    public const METHOD_WEBSITE = 'website';
    public const METHOD_WHATSAPP = 'whatsapp';
    public const METHOD_SOCIAL_MEDIA = 'social_media';
    public const METHOD_REFERRAL = 'referral';
    public const METHOD_OTHER = 'other';

    protected $fillable = [
        'nama',
        'alamat',
        'nomor_telepon',
        'email',
        'status_penerimaan',
        'posisi',
        'department_id',
        'skor_kandidat',
        'sumber',
        'daftar_melalui',
        'file_terkait',
        'catatan',
        'tanggal_apply'
    ];

    protected $casts = [
        'file_terkait' => 'array',
        'skor_kandidat' => 'decimal:1',
        'tanggal_apply' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Status Check Methods
    public function isPending(): bool
    {
        return $this->status_penerimaan === self::STATUS_PENDING;
    }

    public function isInterview(): bool
    {
        return $this->status_penerimaan === self::STATUS_INTERVIEW;
    }

    public function isAccepted(): bool
    {
        return $this->status_penerimaan === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status_penerimaan === self::STATUS_REJECTED;
    }

    public function isOnHold(): bool
    {
        return $this->status_penerimaan === self::STATUS_ON_HOLD;
    }

    public function canBeProcessed(): bool
    {
        return in_array($this->status_penerimaan, [
            self::STATUS_PENDING,
            self::STATUS_INTERVIEW,
            self::STATUS_ON_HOLD
        ]);
    }

    // Status Update Methods
    public function markAsInterview(): bool
    {
        return $this->update([
            'status_penerimaan' => self::STATUS_INTERVIEW
        ]);
    }

    public function markAsAccepted(): bool
    {
        return $this->update([
            'status_penerimaan' => self::STATUS_ACCEPTED
        ]);
    }

    public function markAsRejected(): bool
    {
        return $this->update([
            'status_penerimaan' => self::STATUS_REJECTED
        ]);
    }

    public function markAsOnHold(): bool
    {
        return $this->update([
            'status_penerimaan' => self::STATUS_ON_HOLD
        ]);
    }

    // Utility Methods
    public function getStatusBadgeColor(): string
    {
        return match($this->status_penerimaan) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_INTERVIEW => 'info',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_ON_HOLD => 'secondary',
            default => 'light'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status_penerimaan) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_INTERVIEW => 'Interview',
            self::STATUS_ACCEPTED => 'Diterima',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_ON_HOLD => 'Ditahan',
            default => 'Unknown'
        };
    }

    public function getSumberLabel(): string
    {
        return match($this->sumber) {
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_LINKEDIN => 'LinkedIn',
            self::SOURCE_TWITTER => 'Twitter',
            self::SOURCE_JOBSTREET => 'JobStreet',
            self::SOURCE_INDEED => 'Indeed',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_WALK_IN => 'Walk In',
            self::SOURCE_OTHER => 'Lainnya',
            default => 'Unknown'
        };
    }

    public function getDaftarMelaluiLabel(): string
    {
        return match($this->daftar_melalui) {
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_EMAIL => 'Email',
            self::METHOD_WEBSITE => 'Website',
            self::METHOD_WHATSAPP => 'WhatsApp',
            self::METHOD_SOCIAL_MEDIA => 'Social Media',
            self::METHOD_REFERRAL => 'Referral',
            self::METHOD_OTHER => 'Lainnya',
            default => 'Unknown'
        };
    }

    public function getScoreRating(): string
    {
        if (is_null($this->skor_kandidat)) {
            return 'Belum dinilai';
        }

        return match(true) {
            $this->skor_kandidat >= 9.0 => 'Excellent',
            $this->skor_kandidat >= 8.0 => 'Very Good',
            $this->skor_kandidat >= 7.0 => 'Good',
            $this->skor_kandidat >= 6.0 => 'Average',
            $this->skor_kandidat >= 5.0 => 'Below Average',
            default => 'Poor'
        };
    }

    public function getScoreColor(): string
    {
        if (is_null($this->skor_kandidat)) {
            return 'secondary';
        }

        return match(true) {
            $this->skor_kandidat >= 8.0 => 'success',
            $this->skor_kandidat >= 7.0 => 'info',
            $this->skor_kandidat >= 6.0 => 'warning',
            default => 'danger'
        };
    }

    public function hasFiles(): bool
    {
        return !empty($this->file_terkait) && is_array($this->file_terkait);
    }

    public function getFileCount(): int
    {
        return $this->hasFiles() ? count($this->file_terkait) : 0;
    }

    public function getDaysOld(): int
    {
        return Carbon::parse($this->tanggal_apply)->diffInDays(now());
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_penerimaan', self::STATUS_PENDING);
    }

    public function scopeInterview($query)
    {
        return $query->where('status_penerimaan', self::STATUS_INTERVIEW);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status_penerimaan', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status_penerimaan', self::STATUS_REJECTED);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status_penerimaan', self::STATUS_ON_HOLD);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('sumber', $source);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('daftar_melalui', $method);
    }

    public function scopeWithHighScore($query, $minScore = 7.0)
    {
        return $query->where('skor_kandidat', '>=', $minScore);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('tanggal_apply', '>=', now()->subDays($days));
    }

    // Static Methods for Options
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_INTERVIEW => 'Interview',
            self::STATUS_ACCEPTED => 'Diterima',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_ON_HOLD => 'Ditahan',
        ];
    }

    public static function getSumberOptions(): array
    {
        return [
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_LINKEDIN => 'LinkedIn',
            self::SOURCE_TWITTER => 'Twitter',
            self::SOURCE_JOBSTREET => 'JobStreet',
            self::SOURCE_INDEED => 'Indeed',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_WALK_IN => 'Walk In',
            self::SOURCE_OTHER => 'Lainnya',
        ];
    }

    public static function getMethodOptions(): array
    {
        return [
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_EMAIL => 'Email',
            self::METHOD_WEBSITE => 'Website',
            self::METHOD_WHATSAPP => 'WhatsApp',
            self::METHOD_SOCIAL_MEDIA => 'Social Media',
            self::METHOD_REFERRAL => 'Referral',
            self::METHOD_OTHER => 'Lainnya',
        ];
    }
}
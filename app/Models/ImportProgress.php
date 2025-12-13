<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportProgress extends Model
{
    protected $table = 'import_progress';

    protected $fillable = [
        'user_email',
        'file_name',
        'total_rows',
        'processed_rows',
        'success_count',
        'failed_count',
        'status',
        'error_message',
    ];

    /**
     * Get the progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }

    /**
     * Check if import is still in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Get latest import for a user
     */
    public static function getLatestForUser(string $email): ?self
    {
        return static::where('user_email', $email)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get all errors for this import
     */
    public function errors()
    {
        return $this->hasMany(\App\Models\ImportError::class);
    }
}

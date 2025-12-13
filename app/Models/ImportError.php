<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportError extends Model
{
    protected $fillable = [
        'import_progress_id',
        'row_number',
        'row_data',
        'error_message',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function importProgress(): BelongsTo
    {
        return $this->belongsTo(ImportProgress::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'extra_attributes' => 'array',
        'tanggal_terima_dokumen' => 'date',
        'tanggal_status' => 'date',
        'tanggal_po' => 'date',
        'tanggal_datang' => 'date',

        'buyer' => \App\Enums\BuyerEnum::class,
        'status' => \App\Enums\ProcurementStatusEnum::class,
    ];

    public function getNilaiAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        // If it's stored as a float/int, return it directly
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        // If it is a string
        if (is_string($value)) {
            // If it contains dots as thousand separators (e.g. "92.819.800" or "2.158,00")
            // We assume Indonesian format if it has dots and length > 3 or has commas
            if (str_contains($value, '.') || str_contains($value, ',')) {
                 // Common case: 1.000.000 -> 1000000
                 // Case: 1.000,00 -> 1000.00
                 $cleaned = str_replace('.', '', $value);
                 $cleaned = str_replace(',', '.', $cleaned);
                 
                 if (is_numeric($cleaned)) {
                     return (float) $cleaned;
                 }
            }
            
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return $value;
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}

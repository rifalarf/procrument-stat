<?php

namespace App\Imports;

use App\Models\ProcurementItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProcurementImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection)
    {
        // Assuming first row is header, but ToCollection usually gives data. 
        // With WithHeadingRow concern, it's easier.
        // Let's assume the user uses WithHeadingRow (I need to add the interface)
        
        foreach ($collection as $row) {
            // $row is specific to how Maatwebsite parses it.
            // If I implement WithHeadingRow, keys are headers.
            
            // Basic mapping
            \App\Models\ProcurementItem::create([
                'no_pr' => $row['no_pr'] ?? $row['id_procurement'] ?? $row['external_id'] ?? $row['sheet_id'] ?? null,
                'mat_code' => $row['mat_code'] ?? null,
                'nama_barang' => $row['nama_barang'] ?? null,
                'qty' => $row['qty'] ?? 0,
                'um' => $row['um'] ?? null,
                'pg' => $row['pg'] ?? null,
                'user_requester' => $row['user_requester'] ?? $row['user'] ?? null,
                'nilai' => $this->cleanNilai($row['nilai'] ?? 0),
                'bagian' => $row['bagian'] ?? null,
                'tanggal_terima_dokumen' => isset($row['tanggal_terima_dokumen']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_terima_dokumen']) : null,
                'proc_type' => $row['proc_type'] ?? null,
                'status' => $row['status'] ?? 'RFQ',
                'buyer' => $row['buyer'] ?? null,
                'emergency' => $row['emergency'] ?? null,
                'nama_vendor' => $row['nama_vendor'] ?? null,
                'no_po' => $row['no_po'] ?? null,
                'tanggal_po' => isset($row['tanggal_po']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_po']) : null,
                'tanggal_datang' => isset($row['tanggal_datang']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_datang']) : null,
                'keterangan' => $row['keterangan'] ?? null,
                'last_updated_by' => auth()->user()->email ?? 'System',
                'last_updated_at' => now(),
            ]);
        }
    }

    private function cleanNilai($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        if (is_string($value)) {
             // Remove dots, replace comma with dot
             $cleaned = str_replace('.', '', $value);
             $cleaned = str_replace(',', '.', $cleaned);
             return is_numeric($cleaned) ? $cleaned : 0;
        }
        return 0;
    }
}

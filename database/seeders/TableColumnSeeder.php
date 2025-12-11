<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableColumnSeeder extends Seeder
{
    public function run()
    {
        $columns = [
            ['key' => 'id_procurement', 'label' => 'ID Procurement', 'order' => 1],
            ['key' => 'mat_code', 'label' => 'Mat Code', 'order' => 2],
            ['key' => 'nama_barang', 'label' => 'Nama Barang', 'order' => 3],
            ['key' => 'qty', 'label' => 'Qty', 'order' => 4],
            ['key' => 'um', 'label' => 'UoM', 'order' => 5],
            ['key' => 'nilai', 'label' => 'Nilai', 'order' => 6],
            ['key' => 'pg', 'label' => 'PG', 'order' => 7],
            ['key' => 'user_requester', 'label' => 'User', 'order' => 8],
            ['key' => 'bagian', 'label' => 'Bagian', 'order' => 9],
            ['key' => 'proc_type', 'label' => 'ProcX/Manual', 'order' => 10],
            ['key' => 'buyer', 'label' => 'Buyer', 'order' => 11],
            ['key' => 'status', 'label' => 'Status', 'order' => 12],
            ['key' => 'tanggal_status', 'label' => 'Tgl Status', 'order' => 13],
            ['key' => 'emergency', 'label' => 'Emergency', 'order' => 14],
            ['key' => 'no_po', 'label' => 'No PO', 'order' => 15],
            ['key' => 'nama_vendor', 'label' => 'Vendor', 'order' => 16],
            ['key' => 'tanggal_po', 'label' => 'Tgl PO', 'order' => 17],
            ['key' => 'tanggal_datang', 'label' => 'Tgl Datang', 'order' => 18],
            ['key' => 'keterangan', 'label' => 'Keterangan', 'order' => 19],
        ];

        foreach ($columns as $column) {
            \App\Models\TableColumn::updateOrCreate(
                ['key' => $column['key']],
                $column
            );
        }
    }
}

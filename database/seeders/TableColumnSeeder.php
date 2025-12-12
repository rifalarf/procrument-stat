<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableColumnSeeder extends Seeder
{
    public function run()
    {
        $columns = [
            ['key' => 'id_procurement', 'label' => 'ID Procurement', 'order' => 1, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'mat_code', 'label' => 'Mat Code', 'order' => 2, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'nama_barang', 'label' => 'Nama Barang', 'order' => 3, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'qty', 'label' => 'Qty', 'order' => 4, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'um', 'label' => 'UoM', 'order' => 5, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'nilai', 'label' => 'Nilai', 'order' => 6, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'pg', 'label' => 'PG', 'order' => 7, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'user_requester', 'label' => 'User', 'order' => 8, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'bagian', 'label' => 'Bagian', 'order' => 9, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'tanggal_terima_dokumen', 'label' => 'Tgl Terima Dok', 'order' => 10, 'is_visible' => true, 'is_dynamic' => false, 'type' => 'date'], // Added explicit type
            ['key' => 'proc_type', 'label' => 'ProcX/Manual', 'order' => 11, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'buyer', 'label' => 'Buyer', 'order' => 12, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'status', 'label' => 'Status', 'order' => 13, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'tanggal_status', 'label' => 'Tgl Status', 'order' => 14, 'is_visible' => true, 'is_dynamic' => false, 'type' => 'date'],
            ['key' => 'emergency', 'label' => 'Emergency', 'order' => 15, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'no_po', 'label' => 'No PO', 'order' => 16, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'nama_vendor', 'label' => 'Vendor', 'order' => 17, 'is_visible' => true, 'is_dynamic' => false],
            ['key' => 'tanggal_po', 'label' => 'Tgl PO', 'order' => 18, 'is_visible' => true, 'is_dynamic' => false, 'type' => 'date'],
            ['key' => 'tanggal_datang', 'label' => 'Tgl Datang', 'order' => 19, 'is_visible' => true, 'is_dynamic' => false, 'type' => 'date'],
            ['key' => 'keterangan', 'label' => 'Keterangan', 'order' => 20, 'is_visible' => true, 'is_dynamic' => false],
        ];

        foreach ($columns as $column) {
            \App\Models\TableColumn::updateOrCreate(
                ['key' => $column['key']],
                $column
            );
        }
    }
}

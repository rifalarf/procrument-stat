<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FixController extends Controller
{
    public function fixColumns()
    {
        try {
            // Force run the seeder
            Artisan::call('db:seed', [
                '--class' => 'TableColumnSeeder',
                '--force' => true
            ]);
            
            $output = Artisan::output();
            
            return "<h1>Berhasil Memperbaiki Kolom!</h1><p>Silakan kembali ke Dashboard.</p><pre>$output</pre><br><a href='/dashboard'>Kembali ke Dashboard</a>";
        } catch (\Exception $e) {
            return "<h1>Gagal!</h1><p>" . $e->getMessage() . "</p>";
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableColumn;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class AdminImportController extends Controller
{
    public function show()
    {
        return view('admin.import.index');
    }

    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        // storeAs returns "temp/filename.xlsx" (relative to disk root)
        $path = $file->storeAs('temp', 'import_' . now()->timestamp . '.' . $file->getClientOriginalExtension());

        // Use Storage facade to get absolute path.
        // If file system is local, this works.
        $absolutePath = \Illuminate\Support\Facades\Storage::path($path);

        // Read Headers
        $headings = (new HeadingRowImport)->toArray($absolutePath);
        $fileHeaders = $headings[0][0] ?? [];

        // Get DB Columns
        $dbColumns = TableColumn::ordered()->get();

        return view('admin.import.mapping', [
            'file_path' => $path, // Pass relative path to view
            'file_headers' => $fileHeaders,
            'db_columns' => $dbColumns,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'file_path' => 'required',
            'mapping' => 'required|array',
            'strategy' => 'required|in:skip,update',
        ]);

        $mapping = $request->input('mapping');
        $strategy = $request->input('strategy');
        $relativePath = $request->input('file_path');
        
        // Dispatch to queue for background processing
        // This prevents timeout on large files
        \App\Jobs\ProcessLargeImport::dispatch(
            $relativePath,
            $mapping,
            $strategy,
            auth()->user()->email
        );

        return redirect()->route('dashboard')->with('success', 
            'Import sedang diproses di background. Data akan muncul dalam beberapa menit.'
        );
    }
}

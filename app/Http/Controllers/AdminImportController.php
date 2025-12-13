<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableColumn;
use App\Models\ImportProgress;
use App\Models\ProcurementItem;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('temp', 'import_' . now()->timestamp . '.' . $file->getClientOriginalExtension());

        $absolutePath = Storage::path($path);

        // Read Headers
        $headings = (new HeadingRowImport)->toArray($absolutePath);
        $fileHeaders = $headings[0][0] ?? [];

        // Count total rows (excluding header)
        $spreadsheet = IOFactory::load($absolutePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $totalRows = $worksheet->getHighestRow() - 1; // Minus header row

        // Get DB Columns
        $dbColumns = TableColumn::ordered()->get();

        return view('admin.import.mapping', [
            'file_path' => $path,
            'file_name' => $originalName,
            'file_headers' => $fileHeaders,
            'db_columns' => $dbColumns,
            'total_rows' => $totalRows,
        ]);
    }

    /**
     * Start the import process - creates progress record and returns progress page
     */
    public function process(Request $request)
    {
        $request->validate([
            'file_path' => 'required',
            'mapping' => 'required|array',
            'strategy' => 'required|in:skip,update',
            'total_rows' => 'required|integer',
        ]);

        $relativePath = $request->input('file_path');
        $totalRows = $request->input('total_rows');
        $fileName = $request->input('file_name', 'Unknown');

        // Create progress record
        $progress = ImportProgress::create([
            'user_email' => auth()->user()->email,
            'file_name' => $fileName,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'pending',
        ]);

        // Store mapping and strategy in session for the chunked processing
        session([
            'import_' . $progress->id => [
                'file_path' => $relativePath,
                'mapping' => $request->input('mapping'),
                'strategy' => $request->input('strategy'),
            ]
        ]);

        // Redirect to progress page (which will handle chunked processing via JS)
        return redirect()->route('admin.import.progress', $progress->id);
    }

    /**
     * Show import progress page
     */
    public function progress($id)
    {
        $progress = ImportProgress::findOrFail($id);
        
        // Only allow owner to view progress
        if ($progress->user_email !== auth()->user()->email && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.import.progress', [
            'progress' => $progress,
        ]);
    }

    /**
     * API endpoint to get progress status
     */
    public function progressStatus($id)
    {
        $progress = ImportProgress::findOrFail($id);
        
        if ($progress->user_email !== auth()->user()->email && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return response()->json([
            'id' => $progress->id,
            'status' => $progress->status,
            'total_rows' => $progress->total_rows,
            'processed_rows' => $progress->processed_rows,
            'success_count' => $progress->success_count,
            'failed_count' => $progress->failed_count,
            'progress_percentage' => $progress->progress_percentage,
            'error_message' => $progress->error_message,
        ]);
    }

    /**
     * Process a single chunk of data (called via AJAX)
     */
    public function processChunk(Request $request, $id)
    {
        $progress = ImportProgress::findOrFail($id);
        
        if ($progress->user_email !== auth()->user()->email && auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Get import data from session
        $importData = session('import_' . $id);
        if (!$importData) {
            return response()->json(['error' => 'Import session expired'], 400);
        }

        $filePath = $importData['file_path'];
        $mapping = $importData['mapping'];
        $strategy = $importData['strategy'];

        $chunkSize = 100; // Process 100 rows per request
        $startRow = $progress->processed_rows + 2; // +2 because row 1 is header, and we're 1-indexed

        try {
            // Update status to processing
            if ($progress->status === 'pending') {
                $progress->update(['status' => 'processing']);
            }

            $absolutePath = Storage::path($filePath);
            $spreadsheet = IOFactory::load($absolutePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get headers from row 1
            $headers = [];
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $header = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                $headers[$col] = \Illuminate\Support\Str::slug($header, '_');
            }

            $successCount = 0;
            $failedCount = 0;
            $processedCount = 0;
            $endRow = min($startRow + $chunkSize - 1, $progress->total_rows + 1);

            for ($row = $startRow; $row <= $endRow; $row++) {
                $processedCount++;
                
                // Read row data
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $headerSlug = $headers[$col] ?? null;
                    if ($headerSlug) {
                        $rowData[$headerSlug] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                }

                // Map to database columns
                $data = $this->mapRowData($rowData, $mapping);
                
                if ($data === null) {
                    // Skip empty row
                    continue;
                }

                // Try to save
                try {
                    $result = $this->saveRow($data, $strategy);
                    if ($result) {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::warning('Import row failed: ' . $e->getMessage());
                }
            }

            // Update progress
            $progress->increment('processed_rows', $processedCount);
            $progress->increment('success_count', $successCount);
            $progress->increment('failed_count', $failedCount);

            // Check if completed
            $progress->refresh();
            if ($progress->processed_rows >= $progress->total_rows) {
                $progress->update(['status' => 'completed']);
                
                // Cleanup file and session
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
                session()->forget('import_' . $id);
            }

            return response()->json([
                'success' => true,
                'processed' => $processedCount,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'total_processed' => $progress->processed_rows,
                'is_complete' => $progress->status === 'completed',
            ]);

        } catch (\Exception $e) {
            $progress->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'is_complete' => true,
            ], 500);
        }
    }

    /**
     * Map row data to database columns
     */
    private function mapRowData(array $rowData, array $mapping): ?array
    {
        $data = [];

        foreach ($mapping as $dbKey => $excelHeader) {
            if ($excelHeader) {
                $value = $rowData[$excelHeader] ?? null;
                
                // Handle Dates
                if (str_starts_with($dbKey, 'tanggal_') && $value) {
                    try {
                        if (is_numeric($value)) {
                            $value = Date::excelToDateTimeObject($value);
                        } else {
                            $value = \Carbon\Carbon::parse($value);
                        }
                    } catch (\Exception $e) {
                        $value = null;
                    }
                }
                
                $data[$dbKey] = $value;
            }
        }

        // Clean Nilai
        if (isset($data['nilai'])) {
            $data['nilai'] = $this->cleanNilai($data['nilai']);
        }

        // Enum conversions
        if (isset($data['status'])) {
            $data['status'] = $this->convertStatusEnum($data['status']);
        }
        
        if (isset($data['buyer'])) {
            $data['buyer'] = $this->convertBuyerEnum($data['buyer']);
        }

        if (isset($data['bagian'])) {
            $data['bagian'] = $this->convertBagianEnum($data['bagian']);
        }

        // Check if row is empty
        if (empty($data['mat_code']) && empty($data['nama_barang']) && empty($data['no_pr'])) {
            return null;
        }

        return $data;
    }

    /**
     * Save a single row to database
     */
    private function saveRow(array $data, string $strategy): bool
    {
        $externalId = $data['no_pr'] ?? $data['id_procurement'] ?? null;
        
        if ($externalId) {
            $existing = ProcurementItem::where('no_pr', $externalId)->first();
            if ($existing) {
                if ($strategy === 'update') {
                    $existing->update(array_merge($data, [
                        'last_updated_by' => auth()->user()->email ?? 'Importer',
                        'last_updated_at' => now(),
                    ]));
                    return true;
                }
                return false; // Skip
            }
        }

        ProcurementItem::create(array_merge($data, [
            'last_updated_by' => auth()->user()->email ?? 'Importer',
            'last_updated_at' => now(),
        ]));
        
        return true;
    }

    private function cleanNilai($value)
    {
        if (is_null($value)) return 0;
        if (is_numeric($value)) return $value;

        if (is_string($value)) {
            $cleaned = str_replace('.', '', $value);
            $cleaned = str_replace(',', '.', $cleaned);
            if (is_numeric($cleaned)) return $cleaned;
        }
        return 0;
    }

    private function convertStatusEnum($input): ?string
    {
        if (!$input) return null;
        $input = trim($input);
        
        $enum = \App\Enums\ProcurementStatusEnum::tryFrom($input);
        if (!$enum) $enum = \App\Enums\ProcurementStatusEnum::tryFrom(strtoupper($input));
        if (!$enum) {
            $normalized = strtoupper(str_replace(' ', '_', $input));
            $enum = \App\Enums\ProcurementStatusEnum::tryFrom($normalized);
        }
        
        if ($enum) return $enum->value;
        
        foreach (\App\Enums\ProcurementStatusEnum::cases() as $case) {
            if (strtolower(trim($case->label())) === strtolower($input)) {
                return $case->value;
            }
        }
        
        return null;
    }

    private function convertBuyerEnum($input): ?string
    {
        if (!$input) return null;
        $input = trim($input);
        
        $enum = \App\Enums\BuyerEnum::tryFrom($input);
        if (!$enum) $enum = \App\Enums\BuyerEnum::tryFrom(strtoupper($input));
        if (!$enum) {
            $normalized = strtoupper(str_replace(' ', '_', $input));
            $enum = \App\Enums\BuyerEnum::tryFrom($normalized);
        }
        
        if ($enum) return $enum->value;
        
        foreach (\App\Enums\BuyerEnum::cases() as $case) {
            if (strtolower(trim($case->label())) === strtolower($input)) {
                return $case->value;
            }
        }
        
        return null;
    }

    private function convertBagianEnum($input): ?string
    {
        if (!$input) return null;
        $input = trim($input);
        
        $enum = \App\Enums\BagianEnum::tryFrom($input);
        if (!$enum) $enum = \App\Enums\BagianEnum::tryFrom(strtoupper($input));
        if (!$enum) {
            $normalized = strtoupper(str_replace(' ', '', $input));
            $enum = \App\Enums\BagianEnum::tryFrom($normalized);
        }
        
        if ($enum) return $enum->value;
        
        foreach (\App\Enums\BagianEnum::cases() as $case) {
            if (strtolower(trim($case->label())) === strtolower($input)) {
                return $case->value;
            }
        }
        
        return null;
    }
}

<?php

namespace App\Imports;

use App\Models\ProcurementItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AdvancedProcurementImport implements ToCollection, WithHeadingRow
{
    protected $mapping;
    protected $strategy;

    public function __construct(array $mapping, string $strategy)
    {
        $this->mapping = $mapping;
        $strategy = $strategy;
        $this->strategy = $strategy;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Map row data based on user mapping (DB Column Key => Excel Header Slug)
            $data = [];
            file_put_contents(storage_path('log.txt'), "Row Start: " . json_encode($row) . PHP_EOL, FILE_APPEND); // Log Raw Row to file

            foreach ($this->mapping as $dbKey => $excelHeader) {
                if ($excelHeader) {
                    $value = $row[$excelHeader] ?? null;
                    
                    // Handle Dates
                    if (str_starts_with($dbKey, 'tanggal_') && $value) {
                         try {
                            if (is_numeric($value)) {
                                $value = Date::excelToDateTimeObject($value);
                            } else {
                                $value = \Carbon\Carbon::parse($value);
                            }
                         } catch (\Exception $e) {
                            file_put_contents(storage_path('log.txt'), "Date Parse Failed for $dbKey value: " . json_encode($value) . " Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                            $value = null; 
                         }
                    }
                    
                    $data[$dbKey] = $value;
                }
            }

            file_put_contents(storage_path('log.txt'), "Mapped Data Before Enum: " . json_encode($data) . PHP_EOL, FILE_APPEND);

            // Enum Conversion for Status
            if (isset($data['status'])) {
                $statusInput = trim($data['status']); 
                
                // 1. Try exact value match
                $statusEnum = \App\Enums\ProcurementStatusEnum::tryFrom($statusInput);
                
                // 2. Try UPPERCASE value match (e.g. "po" -> "PO")
                if (!$statusEnum) {
                     $statusEnum = \App\Enums\ProcurementStatusEnum::tryFrom(strtoupper($statusInput));
                }

                // 3. Try Normalized (spaces -> underscores) e.g. "Proses PO" -> "PROSES_PO"
                // And explicit mapping for common legacy values
                if (!$statusEnum) {
                    $normalized = strtoupper(str_replace(' ', '_', $statusInput));
                    $statusEnum = \App\Enums\ProcurementStatusEnum::tryFrom($normalized);
                    
                    // Fallback map
                    if (!$statusEnum) {
                         if ($normalized === 'PROSES_PO') $statusEnum = \App\Enums\ProcurementStatusEnum::PO; // or APPROVAL_PO
                    }
                }

                if ($statusEnum) {
                    $data['status'] = $statusEnum->value;
                } else {
                    // 4. Try matching from label
                    $found = false;
                    foreach (\App\Enums\ProcurementStatusEnum::cases() as $case) {
                        if (strtolower(trim($case->label())) === strtolower($statusInput)) {
                            $data['status'] = $case->value;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) $data['status'] = null; // SAFETY: Set to null if invalid
                }
            }
            
            // Enum Conversion for Buyer (similar logic)
            // Enum Conversion for Buyer (similar logic)
            if (isset($data['buyer'])) {
                $buyerInput = trim($data['buyer']);
                
                $buyerEnum = \App\Enums\BuyerEnum::tryFrom($buyerInput);
                if (!$buyerEnum) {
                     $buyerEnum = \App\Enums\BuyerEnum::tryFrom(strtoupper($buyerInput));
                }
                
                if (!$buyerEnum) {
                     // Normalize Buyer: "John Doe" -> "JOHN_DOE"
                     $normalizedBuyer = strtoupper(str_replace(' ', '_', $buyerInput));
                     $buyerEnum = \App\Enums\BuyerEnum::tryFrom($normalizedBuyer);
                }

                if ($buyerEnum) {
                    $data['buyer'] = $buyerEnum->value;
                } else {
                    $found = false;
                    foreach (\App\Enums\BuyerEnum::cases() as $case) {
                        if (strtolower(trim($case->label())) === strtolower($buyerInput)) {
                            $data['buyer'] = $case->value;
                            $found = true;
                            break;
                        }
                    }
                     if (!$found) $data['buyer'] = null; // Safety
                }
            }

            // Enum Conversion for Bagian
            if (isset($data['bagian'])) {
                $bagianInput = trim($data['bagian']);
                
                $bagianEnum = \App\Enums\BagianEnum::tryFrom($bagianInput);
                 if (!$bagianEnum) {
                     $bagianEnum = \App\Enums\BagianEnum::tryFrom(strtoupper($bagianInput));
                }
                
                if (!$bagianEnum) {
                     // Normalize Bagian: "PBJ 1" -> "PBJ1" (Remove spaces)
                     $normalizedBagian = strtoupper(str_replace(' ', '', $bagianInput));
                     $bagianEnum = \App\Enums\BagianEnum::tryFrom($normalizedBagian);
                }

                if ($bagianEnum) {
                    $data['bagian'] = $bagianEnum->value;
                } else {
                    $found = false;
                    foreach (\App\Enums\BagianEnum::cases() as $case) {
                        if (strtolower(trim($case->label())) === strtolower($bagianInput)) {
                            $data['bagian'] = $case->value;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) $data['bagian'] = null; // Safety
                }
            }
            
            file_put_contents(storage_path('log.txt'), "Final Data for DB: " . json_encode($data) . PHP_EOL, FILE_APPEND);

            // Auto-fill logic for empty external_id if preferred? No, let conflict logic handle it.
            
            // Check for conflict (ID Dokumen/Procurement)
            $externalId = $data['id_procurement'] ?? null;
            
            if ($externalId) {
                // Remove update/create logic duplication
                $existing = ProcurementItem::where('id_procurement', $externalId)->first();
                if ($existing) {
                    if ($this->strategy === 'update') {
                         file_put_contents(storage_path('log.txt'), "Updating existing item: $externalId" . PHP_EOL, FILE_APPEND);
                         $existing->update(array_merge($data, [
                             'last_updated_by' => auth()->user()->email ?? 'Importer',
                             'last_updated_at' => now(),
                        ]));
                    } else {
                        file_put_contents(storage_path('log.txt'), "Skipping existing item: $externalId" . PHP_EOL, FILE_APPEND);
                    }
                    continue; 
                }
            }

            // Skipped empty row check
            if (empty($data['mat_code']) && empty($data['nama_barang']) && empty($data['id_procurement'])) {
                file_put_contents(storage_path('log.txt'), "Skipping empty row: " . json_encode($row) . PHP_EOL, FILE_APPEND);
                continue;
            }

            // Create new
            file_put_contents(storage_path('log.txt'), "Creating new item" . PHP_EOL, FILE_APPEND);
            try {
                ProcurementItem::create(array_merge($data, [
                    'last_updated_by' => auth()->user()->email ?? 'Importer',
                    'last_updated_at' => now(),
                ]));
            } catch (\Exception $e) {
                file_put_contents(storage_path('log.txt'), "Failed to create item: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        }
    }
}

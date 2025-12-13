<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AdvancedProcurementImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessLargeImport implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600; // 10 minutes

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    protected string $filePath;
    protected array $mapping;
    protected string $strategy;
    protected string $userEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, array $mapping, string $strategy, string $userEmail)
    {
        $this->filePath = $filePath;
        $this->mapping = $mapping;
        $this->strategy = $strategy;
        $this->userEmail = $userEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting large import job for file: {$this->filePath}");

        try {
            $absolutePath = Storage::path($this->filePath);
            
            // Set auth context for the import (so last_updated_by works)
            $user = \App\Models\User::where('email', $this->userEmail)->first();
            if ($user) {
                auth()->login($user);
            }

            Excel::import(
                new AdvancedProcurementImport($this->mapping, $this->strategy),
                $absolutePath
            );

            // Cleanup the temp file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

            Log::info("Large import job completed successfully for file: {$this->filePath}");

        } catch (\Exception $e) {
            Log::error("Large import job failed: " . $e->getMessage());
            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Import job permanently failed: " . $exception->getMessage());
        
        // Cleanup the temp file even on failure
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }
}

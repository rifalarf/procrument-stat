@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-indigo-500 to-purple-600">
            <h2 class="text-xl font-bold text-white">Import Progress</h2>
            <p class="mt-1 text-sm text-indigo-100">{{ $progress->file_name ?? 'Data Import' }}</p>
        </div>

        <!-- Progress Content -->
        <div class="p-6">
            <!-- Status Badge -->
            <div class="flex items-center justify-between mb-6">
                <span class="text-sm text-gray-500">Status</span>
                <span id="status-badge" class="px-3 py-1 text-sm font-medium rounded-full 
                    {{ $progress->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $progress->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $progress->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $progress->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                    <span id="status-text">{{ ucfirst($progress->status) }}</span>
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span id="progress-percentage">{{ $progress->progress_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div id="progress-bar" 
                         class="h-4 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-300 ease-out"
                         style="width: {{ $progress->progress_percentage }}%">
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900" id="processed-count">{{ $progress->processed_rows }}</div>
                    <div class="text-sm text-gray-500">Diproses</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900" id="total-count">{{ $progress->total_rows }}</div>
                    <div class="text-sm text-gray-500">Total Baris</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600" id="success-count">{{ $progress->success_count }}</div>
                    <div class="text-sm text-gray-500">Berhasil</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-600" id="failed-count">{{ $progress->failed_count }}</div>
                    <div class="text-sm text-gray-500">Gagal</div>
                    @if($progress->failed_count > 0)
                        <a href="{{ route('admin.import.progress.errors', $progress->id) }}" 
                           class="text-xs text-red-600 hover:text-red-800 underline mt-1 inline-block"
                           id="view-errors-link">
                            Lihat Detail →
                        </a>
                    @endif
                </div>
            </div>

            <!-- Error Message -->
            <div id="error-container" class="hidden mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700" id="error-message"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div id="success-container" class="hidden mb-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">Import selesai! Data telah berhasil dimasukkan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="flex items-center justify-center py-4 {{ in_array($progress->status, ['pending', 'processing']) ? '' : 'hidden' }}">
                <svg class="animate-spin h-6 w-6 text-indigo-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600" id="loading-text">Memproses data...</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.import.form') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Import Lagi
                </a>
                <a href="{{ route('dashboard') }}" id="go-dashboard-btn" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressId = {{ $progress->id }};
    const csrfToken = '{{ csrf_token() }}';
    let isProcessing = false;
    let retryCount = 0;
    const maxRetries = 3;
    
    function updateUI(data) {
        const percentage = data.total_rows > 0 
            ? Math.round((data.processed_rows / data.total_rows) * 100) 
            : 0;
        
        // Update progress bar
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-percentage').textContent = percentage + '%';
        
        // Update counts
        document.getElementById('processed-count').textContent = data.processed_rows;
        document.getElementById('total-count').textContent = data.total_rows;
        document.getElementById('success-count').textContent = data.success_count;
        document.getElementById('failed-count').textContent = data.failed_count;
        
        // Update loading text
        document.getElementById('loading-text').textContent = 
            `Memproses... ${data.processed_rows} / ${data.total_rows} baris`;
    }
    
    function updateStatus(status, errorMessage = null) {
        const statusBadge = document.getElementById('status-badge');
        const statusText = document.getElementById('status-text');
        statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        
        statusBadge.className = 'px-3 py-1 text-sm font-medium rounded-full ';
        
        if (status === 'completed') {
            statusBadge.className += 'bg-green-100 text-green-800';
            document.getElementById('success-container').classList.remove('hidden');
            document.getElementById('loading-indicator').classList.add('hidden');
        } else if (status === 'processing') {
            statusBadge.className += 'bg-blue-100 text-blue-800';
        } else if (status === 'pending') {
            statusBadge.className += 'bg-yellow-100 text-yellow-800';
        } else if (status === 'failed') {
            statusBadge.className += 'bg-red-100 text-red-800';
            document.getElementById('error-container').classList.remove('hidden');
            document.getElementById('error-message').textContent = errorMessage || 'Unknown error occurred';
            document.getElementById('loading-indicator').classList.add('hidden');
        }
    }
    
    async function processNextChunk() {
        if (isProcessing) return;
        isProcessing = true;
        
        try {
            const response = await fetch(`/admin/import/progress/${progressId}/chunk`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            retryCount = 0; // Reset retry count on success
            
            // Get updated progress
            const statusResponse = await fetch(`/admin/import/progress/${progressId}/status`);
            const statusData = await statusResponse.json();
            
            updateUI(statusData);
            updateStatus(statusData.status, statusData.error_message);
            
            if (!data.is_complete) {
                isProcessing = false;
                // Process next chunk immediately
                setTimeout(processNextChunk, 100);
            } else {
                isProcessing = false;
            }
            
        } catch (error) {
            console.error('Chunk processing error:', error);
            retryCount++;
            
            if (retryCount < maxRetries) {
                isProcessing = false;
                // Retry after a delay
                setTimeout(processNextChunk, 2000);
            } else {
                updateStatus('failed', error.message);
                isProcessing = false;
            }
        }
    }
    
    // Start processing if import is pending
    @if(in_array($progress->status, ['pending', 'processing']))
        processNextChunk();
    @else
        // If already completed/failed, show appropriate message
        @if($progress->status === 'completed')
            document.getElementById('success-container').classList.remove('hidden');
            document.getElementById('loading-indicator').classList.add('hidden');
        @elseif($progress->status === 'failed')
            document.getElementById('error-container').classList.remove('hidden');
            document.getElementById('error-message').textContent = '{{ $progress->error_message ?? "Unknown error" }}';
            document.getElementById('loading-indicator').classList.add('hidden');
        @endif
    @endif
});
</script>
@endsection

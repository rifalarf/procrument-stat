@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Error Import</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $progress->file_name }} - {{ $errors->total() }} error ditemukan</p>
            </div>
            <a href="{{ route('admin.import.progress', $progress->id) }}" class="btn btn-ghost btn-sm">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Error Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">Baris</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pesan Error</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Data Asli</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($errors as $error)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                #{{ $error->row_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-red-600 font-medium">{{ $error->error_message }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <details class="text-xs">
                                <summary class="cursor-pointer text-gray-500 hover:text-gray-700">Lihat Data</summary>
                                <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-40">{{ json_encode($error->row_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada error
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $errors->links() }}
    </div>
</div>
@endsection

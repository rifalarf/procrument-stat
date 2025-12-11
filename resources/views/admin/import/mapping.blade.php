@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Map Columns & Strategy
            </h2>
            <p class="mt-1 text-sm text-gray-500">Ensure your data lands in the right place.</p>
        </div>
    </div>
    
    <form action="{{ route('admin.import.process') }}" method="POST" id="import-form">
        @csrf
        <input type="hidden" name="file_path" value="{{ $file_path }}">

        <!-- Conflict Strategy Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8 border border-gray-100">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Conflict Resolution</h3>
                <div class="mt-2 text-sm text-gray-500">
                    <p>When an item with the same <strong>ID Dokumen</strong> is found:</p>
                </div>
                <div class="mt-5 space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                    <div class="flex items-center">
                        <input id="skip" name="strategy" type="radio" value="skip" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="skip" class="ml-3 block text-sm font-medium text-gray-700">
                            Skip (Keep existing data)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="update" name="strategy" type="radio" value="update" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="update" class="ml-3 block text-sm font-medium text-gray-700">
                            Update (Overwrite with new)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapping Table -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Column Mapping</h3>
                <p class="mt-1 text-sm text-gray-500">Match your Excel headers to the system columns.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Database Column
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Excel Header Match
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($db_columns as $col)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $col->label }}</div>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $col->key }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select name="mapping[{{ $col->key }}]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm border">
                                    <option value="">-- Ignore --</option>
                                    @foreach($file_headers as $header)
                                        @php
                                            $slug = \Illuminate\Support\Str::slug($header, '_');
                                            // Normalization attempt for matching: replace dashes with underscores for 'key' comparison
                                            $normalizedSlug = str_replace('-', '_', $slug);
                                            // Exact match on Label OR Slug-Key match
                                            $selected = ($normalizedSlug === $col->key || strtolower($header) === strtolower($col->label)) ? 'selected' : '';
                                        @endphp
                                        <option value="{{ $slug }}" {{ $selected }}>{{ $header }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <a href="{{ route('admin.import.form') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back
            </a>
            <button type="button" onclick="confirmModal('Confirm Import', 'Are you sure you want to proceed with the import? This strategy will be applied to conflicts.', 'import-form')" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Run Import System
            </button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Create New Column</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <form action="{{ route('admin.columns.store') }}" method="POST" class="space-y-6">
            @csrf
            


            <div>
                <label for="label" class="block text-sm font-medium text-gray-700">Display Label</label>
                <div class="mt-1">
                    <input type="text" name="label" id="label" required placeholder="e.g. Notes" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Data Type</label>
                <div class="mt-1">
                    <select name="type" id="type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <!-- Select type would require options JSON, skipping for simplicity in this MVP -->
                    </select>
                </div>
            </div>

            <div>
                <label for="order" class="block text-sm font-medium text-gray-700">Order Position</label>
                <div class="mt-1">
                    <input type="number" name="order" id="order" value="99" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="is_visible" name="is_visible" type="checkbox" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_visible" class="font-medium text-gray-700">Visible</label>
                    <p class="text-gray-500">Show this column in the main table.</p>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.columns.index') }}" class="underline mr-4">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">Create Column</button>
            </div>
        </form>
    </div>
</div>
@endsection

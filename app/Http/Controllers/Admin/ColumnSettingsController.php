<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableColumn;
use Illuminate\Http\Request;

class ColumnSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all columns ordered by 'order'
        $columns = TableColumn::orderBy('order')->get();
        return view('admin.columns.index', compact('columns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.columns.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,date,number,select',
            'order' => 'required|integer',
            // 'is_visible' is handled separately
        ]);

        // Auto-generate key: extra_slug_label
        $slug = \Illuminate\Support\Str::slug($validated['label'], '_');
        $key = 'extra_' . $slug . '_' . time(); // Unique key

        $validated['key'] = $key;
        $validated['is_dynamic'] = true; 
        $validated['is_visible'] = $request->has('is_visible');

        TableColumn::create($validated);

        return redirect()->route('admin.columns.index')->with('success', 'Column created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TableColumn $column)
    {
        return view('admin.columns.edit', compact('column'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TableColumn $column)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'order' => 'required|integer',
        ]);

        $column->update([
            'label' => $validated['label'],
            'order' => $validated['order'],
            'is_visible' => $request->has('is_visible'),
        ]);

        return redirect()->route('admin.columns.index')->with('success', 'Column updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TableColumn $column)
    {
        // Safety: Do not allow deletion of NON-dynamic columns
        if (!$column->is_dynamic) {
            return back()->with('error', 'Cannot delete core system columns.');
        }

        $column->delete();

        return redirect()->route('admin.columns.index')->with('success', 'Column deleted successfully.');
    }
}

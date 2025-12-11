<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            \App\Models\TableColumn::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }


}

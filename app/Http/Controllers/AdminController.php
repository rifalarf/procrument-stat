<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::all();
        $bagians = \App\Enums\BagianEnum::cases();
        return view('admin.users.index', compact('users', 'bagians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,user',
            'bagian_access' => 'nullable|array',
        ]);
        
        \App\Models\User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email ?: null,
            'password' => bcrypt($request->password),
            'role' => $request->role ?? 'user',
            'bagian_access' => $request->bagian_access,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $bagians = \App\Enums\BagianEnum::cases();
        return view('admin.users.edit', compact('user', 'bagians'));
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $request->validate([
            'role' => 'required|in:admin,user',
            'bagian_access' => 'nullable|array',
        ]);

        $user->update([
            'role' => $request->role,
            'bagian_access' => $request->bagian_access,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function showImportForm()
    {
        return view('admin.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProcurementImport, $request->file('file'));

        return back()->with('success', 'Data imported successfully.');
    }

    public function destroy($id)
    {
        \App\Models\User::findOrFail($id)->delete();
        return back()->with('success', 'User removed.');
    }
}

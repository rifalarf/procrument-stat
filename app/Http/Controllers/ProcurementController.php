<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\ProcurementItem::query();

        // Search logic
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                // Adjust schema column names if needed. 'nama_vendor' is correct per schema.
                $q->where('mat_code', 'like', "%{$search}%")
                  ->orWhere('no_pr', 'like', "%{$search}%") // Renamed ID Procurement to NO PR
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('nama_vendor', 'like', "%{$search}%") 
                  ->orWhere('no_po', 'like', "%{$search}%")
                  ->orWhere('user_requester', 'like', "%{$search}%");
            });
        }

        // Filter logic
        if ($buyer = $request->input('buyer')) {
            $query->where('buyer', $buyer);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        
        // Bagian filter logic with RBAC
        $currentUser = auth()->user();
        $allowedBagians = null; // null means all allowed

        if ($currentUser && !$currentUser->isAdmin()) {
            $access = $currentUser->bagian_access;
            // Debug logging
            // error_log('User: ' . $currentUser->email . ' Access: ' . json_encode($access));

            if (is_array($access) && count($access) > 0) {
                // Ensure values are strings to match Enum values/DB
                $allowedBagians = array_map('strval', $access); 
            } elseif (is_string($access) && !empty($access)) {
                 $allowedBagians = [$access]; 
            }
        }

        if ($allowedBagians !== null) {
            // RESTRICTED ACCESS
            if (count($allowedBagians) === 1) {
                 // CASE 1: Single Access -> Force Filter
                 $query->where('bagian', $allowedBagians[0]);
            } else {
                 // CASE 2: Multi Access
                 $requestedBagian = $request->input('bagian');
                 
                 if ($requestedBagian && in_array($requestedBagian, $allowedBagians)) {
                     // User requested a valid visible bagian
                     $query->where('bagian', $requestedBagian);
                 } else {
                     // Default view or invalid request -> Show ALL allowed
                     $query->whereIn('bagian', $allowedBagians);
                 }
            }
        } else {
            // UNRESTRICTED ACCESS (Admin or No Restrictions Set)
            if ($bagian = $request->input('bagian')) {
                $query->where('bagian', $bagian);
            }
        }
        
        if ($user = $request->input('user')) {
             $query->where('user_requester', $user);
        }

        $items = $query->latest('last_updated_at')->paginate(100);
        $columns = \App\Models\TableColumn::ordered()->visible()->get();
        $buyers = \App\Enums\BuyerEnum::cases();
        $statuses = \App\Enums\ProcurementStatusEnum::cases();
        $bagians = \App\Enums\BagianEnum::cases();
        
        // Pass allowed bagians to view to filter the dropdown or hide it
        // If allowedBagians is set and count == 1, we might want to hide the dropdown.
        // If allowedBagians is set and count > 1, we show dropdown but only with allowed options?
        // Let's pass $allowedBagians to view.
        // If null, show all `BagianEnum::cases()`.
        
        $visibleBagians = $bagians;
        if ($allowedBagians) {
            $visibleBagians = array_filter($bagians, fn($b) => in_array($b->value, $allowedBagians));
        }
        
        // Get unique users for filter
        $users = \App\Models\ProcurementItem::distinct()->whereNotNull('user_requester')->orderBy('user_requester')->pluck('user_requester');

        return view('procurement.index', compact('items', 'columns', 'buyers', 'statuses', 'bagians', 'users', 'allowedBagians', 'visibleBagians'));
    }

    public function quickUpdate(Request $request, $id)
    {
        $item = \App\Models\ProcurementItem::findOrFail($id);
        
        // Log::info('Quick Update Request', $request->all());

        $request->validate([
            'field' => 'required|in:status,bagian,pg',
            'value' => 'nullable',
        ]);

        $field = $request->input('field');
        $value = $request->input('value');
        $oldValue = $item->{$field};
        
        // Resolve enum value for logging if needed
        $oldValueLog = ($oldValue instanceof \UnitEnum) ? $oldValue->value : $oldValue;

        // Specific Validation
        if ($field === 'status') {
            if (!$value) return response()->json(['success' => false, 'message' => 'Status required'], 422);
            if (!\App\Enums\ProcurementStatusEnum::tryFrom($value)) {
                 return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
            }
        }
        if ($field === 'bagian') {
             if ($value && !\App\Enums\BagianEnum::tryFrom($value)) {
                 return response()->json(['success' => false, 'message' => 'Invalid bagian'], 422);
             }
        }

        if ($oldValueLog != $value) {
            $updateData = [
                $field => $value,
                'last_updated_by' => auth()->user()->email ?? 'System',
                'last_updated_at' => now(),
            ];
            
            if ($field === 'status') {
                $updateData['tanggal_status'] = now();
            }

            $item->update($updateData);

            // Log change
            \App\Models\Log::create([
                'procurement_item_id' => $item->id,
                'changed_by' => auth()->user()->email ?? 'System',
                'change_detail' => ucfirst($field) . " changed from '{$oldValueLog}' to '{$value}'",
                'changed_at' => now(), // Assuming changed_at exists or uses created_at
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, $id)
    {
        $item = \App\Models\ProcurementItem::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string',
        ]);

        $oldStatus = $item->status;
        $newStatus = $request->input('status');

        if ($oldStatus !== $newStatus) {
            $item->update([
                'status' => $newStatus,
                'tanggal_status' => now(),
                'last_updated_by' => auth()->user()->email ?? 'System',
                'last_updated_at' => now(),
            ]);

            // Log change
            \App\Models\Log::create([
                'procurement_item_id' => $item->id,
                'changed_by' => auth()->user()->email ?? 'System',
                'change_detail' => "Status changed from {$oldStatus} to {$newStatus}",
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Updated successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        // If ids is a string (from form hidden input), decode it
        if (is_string($ids)) {
             // It might be a JSON string from Alpine x-bind:value="JSON.stringify(selected)"
             $decoded = json_decode($ids, true);
             if (is_array($decoded)) {
                 $ids = $decoded;
             }
        }
        
        $request->merge(['ids' => $ids]);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:procurement_items,id',
        ]);
        
        \App\Models\ProcurementItem::whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected items deleted.');
    }

    public function truncate()
    {
        // Double check admin middleware handles auth, but good to be safe if moved.
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'Unauthorized');
        }

        \App\Models\ProcurementItem::truncate();

        return redirect()->route('dashboard')->with('success', 'All data has been deleted.');
    }

    public function show($id)
    {
        $item = \App\Models\ProcurementItem::with('logs')->findOrFail($id);
        return view('procurement.show', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = \App\Models\ProcurementItem::findOrFail($id);
        
        // Validation logic for updating item
        // Standard user can only edit PG, Bagian, Status, Keterangan
        $allowed = ['pg', 'bagian', 'status', 'keterangan'];
        
        if (!auth()->user()->isAdmin()) {
             $data = $request->only($allowed);
        } else {
             $data = $request->all();
        }
        
        $oldData = $item->getAttributes();
        
        $item->update(array_merge($data, [
            'last_updated_by' => auth()->user()->email,
            'last_updated_at' => now()
        ]));

        // Detect and Log changes
        $changes = [];
        $ignored = ['_token', '_method']; // Fields to ignore in logs

        foreach ($data as $key => $value) {
            if (in_array($key, $ignored)) {
                continue;
            }

            $old = $oldData[$key] ?? null;
            
            // Normalize for comparison and logging
            $oldVal = $old;
            $newVal = $value;
            
            // If old value is an Enum, get its value
            if ($old instanceof \UnitEnum) {
                $oldVal = $old->value;
            }
            
            // If new value is an Enum (unlikely in request data unless cast manually, but good safety)
            if ($newVal instanceof \UnitEnum) {
                $newVal = $newVal->value;
            }

            if ($oldVal != $newVal) { 
                 // Format: "Status changed from 'OLD' to 'NEW'"
                 $changes[] = ucfirst($key) . " changed from '{$oldVal}' to '{$newVal}'";
            }
        }
        
        if (!empty($changes)) {
            \App\Models\Log::create([
                'procurement_item_id' => $item->id,
                'changed_by' => auth()->user()->email,
                'change_detail' => implode(', ', $changes),
            ]);
        }

        return redirect()->route('procurement.show', $id)->with('success', 'Item updated.');
    }

    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProcurementExport($request), 'procurement_items.xlsx');
    }

    public function create()
    {
        return view('procurement.create');
    }

    public function store(Request $request)
    {
        // Allow Admins to create items.
        // If regular users should create, remove this check or adjust policy.
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create items manually.');
        }

        $validated = $request->validate([
            'no_pr' => 'nullable|string',
            'mat_code' => 'required|string|max:255',
            'nama_barang' => 'required|string',
            'qty' => 'required|numeric',
            'um' => 'required|string',
            'nilai' => 'nullable|numeric',
            'pg' => 'nullable|string',
            'user_requester' => 'nullable|string',
            'bagian' => 'nullable|string',
            'tanggal_terima_dokumen' => 'nullable|date',
            'proc_type' => 'nullable|string',
            'buyer' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\BuyerEnum::class)],
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\ProcurementStatusEnum::class)],
            'tanggal_status' => 'nullable|date',
            'emergency' => 'nullable|string',
            'no_po' => 'nullable|string',
            'nama_vendor' => 'nullable|string',
            'tanggal_po' => 'nullable|date',
            'tanggal_datang' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $item = \App\Models\ProcurementItem::create(array_merge($validated, [
            'last_updated_by' => auth()->user()->email,
            'last_updated_at' => now(),
            'tanggal_status' => now(), // Initial status date
        ]));

        // Log creation
        \App\Models\Log::create([
            'procurement_item_id' => $item->id,
            'changed_by' => auth()->user()->email,
            'change_detail' => 'Item created manually',
        ]);

        return redirect()->route('dashboard')->with('success', 'New item created successfully.');
    }
}

@extends('layouts.app')

@section('content')
<div x-data="{
    selected: [],
    allSelected: false,
    toggleAll() {
        if (this.selected.length === {{ $items->count() }}) {
            this.selected = [];
        } else {
            this.selected = [{{ $items->pluck('id')->implode(',') }}];
        }
    },

    deleteAll() {
        if(confirm('WARNING: This will delete ALL data in the database. Are you absolutely sure?')) {
            if(confirm('FINAL WARNING: This action cannot be undone. Are you absolutely really sure?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.procurement.delete-all') }}';
                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}" class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <h1 class="text-2xl font-bold text-base-content">Dashboard Pengadaan</h1>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isAdmin())
                <div x-show="selected.length > 0" x-cloak>
                    <form action="{{ route('admin.procurement.bulk-delete') }}" method="POST" id="bulk-delete-form" onsubmit="return confirm('Are you sure you want to delete selected items?')">
                        @csrf
                        <input type="hidden" name="ids" :value="JSON.stringify(selected)">
                        <button type="submit" class="btn btn-error btn-sm text-white">
                            Delete Selected (<span x-text="selected.length"></span>)
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.import.form') }}" class="btn btn-accent btn-sm text-white">Import Excel</a>
                <a href="{{ route('admin.columns.index') }}" class="btn btn-neutral btn-sm text-white">Columns</a>
                <a href="{{ route('procurement.create') }}" class="btn btn-primary btn-sm text-white">+ New Item</a>
            @endif
            <!-- Export Button -->
            <a href="{{ route('procurement.export') }}" class="btn btn-success btn-sm text-white">Export XLSX</a>
        </div>
    </div>

    <!-- Filters & Search -->
    <form method="GET" action="{{ route('dashboard') }}" class="bg-base-100 p-4 rounded-box shadow space-y-4">
        <!-- Row 1: Search -->
        <div class="form-control">
            <label class="label"><span class="label-text font-medium">Search</span></label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Mat Code, ID Procurement, Name, PO..." class="input input-bordered w-full">
        </div>

        <!-- Row 2: Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Buyer</span></label>
                <select name="buyer" class="select select-bordered w-full">
                    <option value="">All Buyers</option>
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->value }}" {{ request('buyer') == $buyer->value ? 'selected' : '' }}>{{ $buyer->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                 <label class="label"><span class="label-text font-medium">Status</span></label>
                <select name="status" class="select select-bordered w-full">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                 <label class="label"><span class="label-text font-medium">Bagian</span></label>
                <select name="bagian" class="select select-bordered w-full">
                    <option value="">All Bagian</option>
                    @foreach($bagians as $bagian)
                        <option value="{{ $bagian->value }}" {{ request('bagian') == $bagian->value ? 'selected' : '' }}>{{ $bagian->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control">
                 <label class="label"><span class="label-text font-medium">User</span></label>
                <select name="user" class="select select-bordered w-full">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user }}" {{ request('user') == $user ? 'selected' : '' }}>{{ $user }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">Filter</button>
            </div>
        </div>
    </form>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-base-100 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <!-- head -->
                <thead class="bg-base-200">
                    <tr>
                        @if(auth()->user()->isAdmin())
                            <th class="w-10">
                                <input type="checkbox" @click="toggleAll()" :checked="selected.length === {{ $items->count() }} && {{ $items->count() }} > 0" class="checkbox checkbox-primary checkbox-sm">
                            </th>
                        @endif
                        @foreach($columns as $col)
                            <th class="whitespace-nowrap">{{ $col->label }}</th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="hover">
                            @if(auth()->user()->isAdmin())
                                <td>
                                    <input type="checkbox" value="{{ $item->id }}" x-model="selected" class="checkbox checkbox-primary checkbox-sm">
                                </td>
                            @endif
                            @foreach($columns as $col)
                                <td class="{{ $col->key === 'nama_barang' ? 'min-w-[200px] whitespace-normal' : 'whitespace-nowrap' }}">
                                    @if($col->key == 'status')
                                        <div x-data="{ 
                                            current: '{{ $item->status instanceof \UnitEnum ? $item->status->value : $item->status }}',
                                            update(val) {
                                                const oldVal = this.current;
                                                this.current = val;
                                                
                                                fetch('/procurement/{{ $item->id }}/quick-update', {
                                                    method: 'POST',
                                                    headers: { 
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({ field: 'status', value: val })
                                                })
                                                .then(r => r.json())
                                                .then(data => {
                                                    if(!data.success) {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed: ' + (data.message || 'Unknown'), type: 'error' } }));
                                                        this.current = oldVal; 
                                                    } else {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Status updated', type: 'success' } }));
                                                    }
                                                });
                                            }
                                        }">
                                            <select x-model="current" @change="update($event.target.value)" 
                                                class="select select-bordered select-xs w-full max-w-[160px]"
                                            >
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($col->key == 'bagian')
                                        <div x-data="{ 
                                            current: '{{ $item->bagian }}',
                                            update(val) {
                                                const oldVal = this.current;
                                                this.current = val;
                                                
                                                fetch('/procurement/{{ $item->id }}/quick-update', {
                                                    method: 'POST',
                                                    headers: { 
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({ field: 'bagian', value: val })
                                                })
                                                .then(r => r.json())
                                                .then(data => {
                                                    if(!data.success) {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed: ' + (data.message || 'Unknown'), type: 'error' } }));
                                                        this.current = oldVal; // Revert
                                                    } else {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Bagian updated', type: 'success' } }));
                                                    }
                                                });
                                            }
                                         }">
                                            <select x-model="current" @change="update($event.target.value)" 
                                                class="select select-ghost select-xs w-full max-w-[100px]"
                                            >
                                                <option value="">-</option>
                                                @foreach($bagians as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                         </div>
                                    @elseif($col->key == 'pg')
                                         <div x-data="{ 
                                            val: '{{ $item->pg }}',
                                            update() {
                                                 fetch('/procurement/{{ $item->id }}/quick-update', {
                                                    method: 'POST',
                                                    headers: { 
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({ field: 'pg', value: this.val })
                                                }).then(r => r.json()).then(d => { 
                                                    if(!d.success) {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: d.message, type: 'error' } }));
                                                    } else {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'PG updated', type: 'success' } }));
                                                    }
                                                });
                                            }
                                         }">
                                            <input type="text" x-model="val" @blur="update()" @keydown.enter="update()" class="input input-ghost input-xs w-full max-w-[80px]">
                                         </div>
                                    @elseif($col->key == 'buyer')
                                        @php
                                            $buyerEnum = $item->buyer;
                                            $color = $buyerEnum?->color() ?? '#f3f4f6';
                                            $isDark = in_array($color, ['#3d3d3d', '#b10202', '#753800', '#473822', '#11734b', '#0a53a8', '#215a6c', '#5a3286']);
                                        @endphp
                                        <span class="badge font-semibold whitespace-nowrap" style="background-color: {{ $color }}; color: {{ $isDark ? 'white' : 'black' }}; border: none;">
                                            {{ $buyerEnum?->label() ?? '-' }}
                                        </span>
                                    @elseif((str_starts_with($col->key, 'tanggal_') || in_array($col->key, ['tanggal_po', 'tanggal_datang', 'tanggal_status'])) && $item->{$col->key})
                                         {{ \Carbon\Carbon::parse($item->{$col->key})->format('d M Y') }}
                                    @elseif(str_starts_with($col->key, 'extra_'))
                                        {{ $item->extra_attributes[$col->key] ?? '-' }}
                                    @elseif($col->key == 'nilai')
                                        {{ number_format($item->nilai, 0, ',', '.') }}
                                    @else
                                        {{ $item->{$col->key} }}
                                    @endif
                                </td>
                            @endforeach
                             <td>
                                <a href="{{ route('procurement.show', $item->id) }}" class="btn btn-ghost btn-xs text-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                             @if(auth()->user()->isAdmin())
                                <td></td>
                             @endif
                            <td colspan="{{ $columns->count() + 1 }}" class="text-center py-6 text-gray-500">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4 mt-4">
        @foreach($items as $item)
            <a href="{{ route('procurement.show', $item->id) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-all cursor-pointer">
                <div class="card-body p-4">
                    <!-- Title -->
                    <h3 class="card-title text-base font-bold line-clamp-2">
                        {{ $item->nama_barang ?? 'No Name' }}
                    </h3>
                    
                    <!-- ID Dokumen -->
                    <p class="text-sm opacity-70 mt-1">
                        ID: <span class="font-medium">{{ $item->external_id ?? '-' }}</span>
                    </p>

                    <!-- Status -->
                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase">Status:</span>
                        <span class="badge badge-outline">
                             {{ $item->status instanceof \UnitEnum ? $item->status->label() : $item->status }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $items->withQueryString()->links() }}
    </div>

</div>

@push('scripts')
<style>
/* Adjust pagination for DaisyUI if needed */
</style>
@endpush
@endsection

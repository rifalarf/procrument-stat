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
        confirmModal('DELETE ALL DATA', 'WARNING: This will delete ALL data in the database. Are you absolutely sure?', () => {
             // Second level confirmation - delay slightly to allow first modal to close smoothly or just reuse
             setTimeout(() => {
                 confirmModal('FINAL WARNING', 'This action cannot be undone. Are you absolutely really sure?', () => {
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
                 });
             }, 200);
        });
    }
}" class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <h1 class="text-2xl font-bold text-base-content">Dashboard Pengadaan</h1>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isAdmin())
                <div x-show="selected.length > 0" x-cloak class="flex gap-2">
                     <template x-if="selected.length === 1">
                        <a :href="'/procurement/' + selected[0]" class="btn btn-primary btn-sm text-white">
                            Edit Item
                        </a>
                    </template>
                    <form action="{{ route('admin.procurement.bulk-delete') }}" method="POST" id="bulk-delete-form">
                        @csrf
                        <input type="hidden" name="ids" :value="JSON.stringify(selected)">
                        <button type="button" @click="confirmModal('Delete Selected', 'Are you sure you want to delete these items?', 'bulk-delete-form')" class="btn btn-error btn-sm text-white">
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
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Mat Code, NO PR, Name, User, PO..." class="input input-bordered w-full">
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
            @if(!isset($allowedBagians) || count($allowedBagians) > 1)
            <div class="form-control">
                 <label class="label"><span class="label-text font-medium">Bagian</span></label>
                <select name="bagian" class="select select-bordered w-full">
                    <option value="">All Bagian</option>
                    @foreach($visibleBagians as $bagian)
                        <option value="{{ $bagian->value }}" {{ request('bagian') == $bagian->value ? 'selected' : '' }}>{{ $bagian->label() }}</option>
                    @endforeach
                </select>
            </div>
            @endif
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
                                <td class="{{ $col->key === 'nama_barang' ? 'min-w-[250px] whitespace-normal' : ($col->key === 'status' ? 'min-w-[150px]' : 'whitespace-nowrap') }}">
                                    @if($col->key == 'nama_barang')
                                        <a href="{{ route('procurement.show', $item->id) }}" class="link link-hover font-semibold text-primary">
                                            {{ $item->nama_barang }}
                                        </a>
                                    @elseif($col->key == 'status')
                                        <div x-data="{ 
                                            current: '{{ $item->status instanceof \UnitEnum ? $item->status->value : $item->status }}',
                                            options: {{ json_encode(\App\Enums\ProcurementStatusEnum::cases() ? collect(\App\Enums\ProcurementStatusEnum::cases())->mapWithKeys(function($s) {
                                                $c = $s->color();
                                                $isDark = in_array($c, ['#3d3d3d', '#b10202', '#753800', '#5a3286', '#0a53a8', '#473822', '#11734b', '#215a6c']); 
                                                return [$s->value => ['label' => $s->label(), 'color' => $c, 'text' => $isDark ? '#fff' : '#000']];
                                            }) : []) }},
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
                                        }" class="relative inline-block w-full max-w-full">
                                            <!-- Visual Badge -->
                                            <div class="badge h-auto py-2 px-3 w-full justify-start text-left font-semibold border-0 text-xs gap-2 shadow-sm"
                                                 :style="{ backgroundColor: options[current]?.color || '#f3f4f6', color: options[current]?.text || '#000' }">
                                                 <span x-text="options[current]?.label || current" class="truncate"></span>
                                                 <!-- Chevron Icon for visual cue -->
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-50 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>

                                            <!-- Hidden Select Overlay -->
                                            <select x-model="current" @change="update($event.target.value)" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer appearance-none z-10"
                                                title="Change Status"
                                            >
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($col->key == 'bagian')
                                        @php
                                            $bagianEnum = \App\Enums\BagianEnum::tryFrom($item->bagian);
                                        @endphp
                                        <span class="badge font-semibold whitespace-nowrap" style="background-color: {{ $bagianEnum?->color() ?? '#f3f4f6' }}; color: white; border: none;">
                                            {{ $bagianEnum?->label() ?? $item->bagian ?? '-' }}
                                        </span>
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
                        </tr>
                    @empty
                        <tr>
                             @if(auth()->user()->isAdmin())
                                <td></td>
                             @endif
                            <td colspan="{{ $columns->count() }}" class="text-center py-6 text-gray-500">No items found.</td>
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
                        NO PR: <span class="font-medium">{{ $item->no_pr ?? '-' }}</span>
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

    @if(auth()->user()->isAdmin())
    <!-- Danger Zone - Placed at bottom, far from other buttons -->
    <div class="mt-12 pt-8 border-t border-gray-200" x-data="{ showDanger: false, confirmText: '', canDelete: false }">
        <details class="collapse bg-red-50 border border-red-200 rounded-lg">
            <summary class="collapse-title text-sm font-medium text-red-800 cursor-pointer">
                ⚠️ Zona Berbahaya (Klik untuk Membuka)
            </summary>
            <div class="collapse-content">
                <div class="pt-4">
                    <p class="text-sm text-red-600 mb-4">
                        <strong>Peringatan:</strong> Tindakan di bawah ini tidak dapat dibatalkan dan akan menghapus SEMUA data procurement.
                    </p>
                    
                    <button type="button" 
                            @click="showDanger = true" 
                            class="btn btn-error btn-outline btn-sm">
                        🗑️ Hapus Semua Data
                    </button>
                </div>
            </div>
        </details>

        <!-- Confirmation Modal -->
        <div x-show="showDanger" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDanger = false"></div>

                <!-- Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('admin.procurement.delete-all') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Hapus Semua Data
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Tindakan ini akan menghapus <strong>SEMUA</strong> data procurement secara permanen. 
                                            Tidak dapat dibatalkan.
                                        </p>
                                        <p class="text-sm text-gray-700 mb-2">
                                            Ketik <strong class="text-red-600">hapus semua data</strong> untuk konfirmasi:
                                        </p>
                                        <input type="text" 
                                               name="confirmation"
                                               x-model="confirmText"
                                               @input="canDelete = (confirmText === 'hapus semua data')"
                                               placeholder="Ketik di sini..."
                                               class="input input-bordered w-full"
                                               autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit" 
                                    :disabled="!canDelete"
                                    :class="{ 'btn-disabled opacity-50': !canDelete }"
                                    class="btn btn-error text-white">
                                Hapus Permanen
                            </button>
                            <button type="button" @click="showDanger = false; confirmText = ''; canDelete = false" class="btn btn-ghost">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<style>
/* Adjust pagination for DaisyUI if needed */
</style>
@endpush
@endsection

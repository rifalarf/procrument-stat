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
        confirmModal('HAPUS SEMUA DATA', 'PERINGATAN: Ini akan menghapus SEMUA data di database. Apakah Anda benar-benar yakin?', () => {
             // Second level confirmation - delay slightly to allow first modal to close smoothly or just reuse
             setTimeout(() => {
                 const input = prompt('FINAL SAFETY CHECK: Ketik "hapus semua data" untuk konfirmasi.');
                 if (input === 'hapus semua data') {
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
             }, 200);
        });
    }
}" class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <h1 class="text-2xl font-bold text-base-content">Pelacak Pengadaan</h1>
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
                        <button type="button" @click="confirmModal('Hapus Terpilih', 'Apakah Anda yakin ingin menghapus item ini?', 'bulk-delete-form')" class="btn btn-error btn-sm text-white">
                            Hapus Terpilih (<span x-text="selected.length"></span>)
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.import.form') }}" class="btn btn-accent btn-sm text-white">Import Excel</a>
                <a href="{{ route('admin.columns.index') }}" class="btn btn-neutral btn-sm text-white">Kolom</a>
                <a href="{{ route('procurement.create') }}" class="btn btn-primary btn-sm text-white">+ Buat Baru</a>
            @endif
            <!-- Export Button -->
            <a href="{{ route('procurement.export') }}" class="btn btn-success btn-sm text-white">Export XLSX</a>
        </div>
    </div>

    <!-- Filters & Search -->
    <form method="GET" action="{{ route('dashboard') }}" class="bg-base-100 p-4 rounded-box shadow space-y-4">
        <!-- Row 1: Search -->
        <div class="form-control">
            <label class="label"><span class="label-text font-medium">Cari</span></label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Mat Code, ID Procurement, Nama, User, PO..." class="input input-bordered w-full">
        </div>

        <!-- Row 2: Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="form-control" x-data="searchableSelect({ 
                    options: {{ json_encode(collect($buyers)->map(fn($b) => ['value' => $b->value, 'label' => $b->label()])->values()) }},
                    value: '{{ request('buyer') }}',
                    placeholder: 'Pilih Buyer'
                })">
                <label class="label"><span class="label-text font-medium">Buyer</span></label>
                <div class="relative" @click.outside="isOpen = false">
                    <input type="hidden" name="buyer" :value="selectedVal">
                    
                    <div @click="toggle()" class="input input-bordered w-full flex items-center justify-between cursor-pointer">
                        <span x-text="selectedLabel || 'Semua Buyer'" :class="{'text-gray-400': !selectedLabel}"></span>
                        <div class="flex items-center gap-2">
                             <template x-if="selectedVal">
                                <button type="button" @click.stop="clear()" class="btn btn-ghost btn-xs btn-circle text-gray-400 hover:text-gray-600">✕</button>
                             </template>
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 transition-transform" :class="isOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="isOpen" x-cloak class="absolute top-full left-0 w-full z-50 bg-base-100 border border-base-300 rounded-box shadow-xl mt-1 max-h-60 overflow-y-auto">
                        <div class="p-2 sticky top-0 bg-base-100 z-10">
                            <input x-ref="searchInput" x-model="search" type="text" class="input input-sm input-bordered w-full" placeholder="Cari...">
                        </div>
                        <ul class="menu menu-compact p-2">
                            <template x-for="option in filteredOptions" :key="option.value">
                                <li>
                                    <a @click="select(option)" 
                                       :class="{'active': selectedVal == option.value}">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                             <li x-show="filteredOptions.length === 0" class="text-gray-500 p-2 text-center text-sm">Tidak ada hasil</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="form-control" x-data="searchableSelect({ 
                    options: {{ json_encode(collect($statuses)->map(fn($s) => ['value' => $s->value, 'label' => $s->label()])->values()) }},
                    value: '{{ request('status') }}',
                    placeholder: 'Pilih Status'
                })">
                 <label class="label"><span class="label-text font-medium">Status</span></label>
                 <div class="relative" @click.outside="isOpen = false">
                    <input type="hidden" name="status" :value="selectedVal">
                    
                    <div @click="toggle()" class="input input-bordered w-full flex items-center justify-between cursor-pointer">
                        <span x-text="selectedLabel || 'Semua Status'" :class="{'text-gray-400': !selectedLabel}"></span>
                         <div class="flex items-center gap-2">
                             <template x-if="selectedVal">
                                <button type="button" @click.stop="clear()" class="btn btn-ghost btn-xs btn-circle text-gray-400 hover:text-gray-600">✕</button>
                             </template>
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 transition-transform" :class="isOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="isOpen" x-cloak class="absolute top-full left-0 w-full z-50 bg-base-100 border border-base-300 rounded-box shadow-xl mt-1 max-h-60 overflow-y-auto">
                        <div class="p-2 sticky top-0 bg-base-100 z-10">
                            <input x-ref="searchInput" x-model="search" type="text" class="input input-sm input-bordered w-full" placeholder="Cari...">
                        </div>
                        <ul class="menu menu-compact p-2">
                            <template x-for="option in filteredOptions" :key="option.value">
                                <li>
                                    <a @click="select(option)" 
                                       :class="{'active': selectedVal == option.value}">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                            <li x-show="filteredOptions.length === 0" class="text-gray-500 p-2 text-center text-sm">Tidak ada hasil</li>
                        </ul>
                    </div>
                </div>
            </div>
            @if(!isset($allowedBagians) || count($allowedBagians) > 1)
            <div class="form-control" x-data="searchableSelect({ 
                    options: {{ json_encode(collect($visibleBagians)->map(fn($b) => ['value' => $b->value, 'label' => $b->label()])->values()) }},
                    value: '{{ request('bagian') }}',
                    placeholder: 'Pilih Bagian'
                })">
                 <label class="label"><span class="label-text font-medium">Bagian</span></label>
                 <div class="relative" @click.outside="isOpen = false">
                    <input type="hidden" name="bagian" :value="selectedVal">
                    
                    <div @click="toggle()" class="input input-bordered w-full flex items-center justify-between cursor-pointer">
                        <span x-text="selectedLabel || 'Semua Bagian'" :class="{'text-gray-400': !selectedLabel}"></span>
                        <div class="flex items-center gap-2">
                             <template x-if="selectedVal">
                                <button type="button" @click.stop="clear()" class="btn btn-ghost btn-xs btn-circle text-gray-400 hover:text-gray-600">✕</button>
                             </template>
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 transition-transform" :class="isOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="isOpen" x-cloak class="absolute top-full left-0 w-full z-50 bg-base-100 border border-base-300 rounded-box shadow-xl mt-1 max-h-60 overflow-y-auto">
                        <div class="p-2 sticky top-0 bg-base-100 z-10">
                            <input x-ref="searchInput" x-model="search" type="text" class="input input-sm input-bordered w-full" placeholder="Cari...">
                        </div>
                        <ul class="menu menu-compact p-2">
                            <template x-for="option in filteredOptions" :key="option.value">
                                <li>
                                    <a @click="select(option)" 
                                       :class="{'active': selectedVal == option.value}">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                            <li x-show="filteredOptions.length === 0" class="text-gray-500 p-2 text-center text-sm">Tidak ada hasil</li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <div class="form-control" x-data="searchableSelect({ 
                    options: {{ json_encode($users->map(fn($u) => ['value' => $u, 'label' => $u])->values()) }},
                    value: '{{ request('user') }}',
                    placeholder: 'Pilih User'
                })">
                 <label class="label"><span class="label-text font-medium">User</span></label>
                 <div class="relative" @click.outside="isOpen = false">
                    <input type="hidden" name="user" :value="selectedVal">
                    
                    <div @click="toggle()" class="input input-bordered w-full flex items-center justify-between cursor-pointer">
                        <span x-text="selectedLabel || 'Semua User'" :class="{'text-gray-400': !selectedLabel}"></span>
                        <div class="flex items-center gap-2">
                             <template x-if="selectedVal">
                                <button type="button" @click.stop="clear()" class="btn btn-ghost btn-xs btn-circle text-gray-400 hover:text-gray-600">✕</button>
                             </template>
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 transition-transform" :class="isOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="isOpen" x-cloak class="absolute top-full left-0 w-full z-50 bg-base-100 border border-base-300 rounded-box shadow-xl mt-1 max-h-60 overflow-y-auto">
                        <div class="p-2 sticky top-0 bg-base-100 z-10">
                            <input x-ref="searchInput" x-model="search" type="text" class="input input-sm input-bordered w-full" placeholder="Cari...">
                        </div>
                        <ul class="menu menu-compact p-2">
                            <template x-for="option in filteredOptions" :key="option.value">
                                <li>
                                    <a @click="select(option)" 
                                       :class="{'active': selectedVal == option.value}">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                            <li x-show="filteredOptions.length === 0" class="text-gray-500 p-2 text-center text-sm">Tidak ada hasil</li>
                        </ul>
                    </div>
                </div>
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
                                                title="Ubah Status"
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
                            <td colspan="{{ $columns->count() }}" class="text-center py-6 text-gray-500">Tidak ada data ditemukan.</td>
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
                        ID: <span class="font-medium">{{ $item->id_procurement ?? '-' }}</span>
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
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchableSelect', ({ options, value, placeholder }) => ({
            isOpen: false,
            search: '',
            selectedVal: value,
            selectedLabel: '',
            options: options, 

            init() {
                const initial = this.options.find(o => o.value == this.selectedVal);
                if(initial) {
                    this.selectedLabel = initial.label;
                }
            },

            get filteredOptions() {
                if (this.search === '') return this.options;
                return this.options.filter(option => 
                    option.label.toLowerCase().includes(this.search.toLowerCase())
                );
            },

            select(option) {
                this.selectedVal = option.value;
                this.selectedLabel = option.label;
                this.search = '';
                this.isOpen = false;
            },

            clear() {
                this.selectedVal = '';
                this.selectedLabel = '';
                this.search = '';
                this.isOpen = false;
            },
            
            toggle() {
                if (this.isOpen) {
                    this.isOpen = false;
                } else {
                    this.isOpen = true;
                    this.search = '';
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            }
        }));
    });
</script>
<style>
/* Adjust pagination for DaisyUI if needed */
</style>
@endpush
@endsection

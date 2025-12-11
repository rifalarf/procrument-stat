@extends('layouts.app')

@section('content')
<div class="card bg-base-100 shadow-xl overflow-hidden">
    <div class="card-body p-0">
        <div class="px-6 py-5 flex justify-between items-center bg-base-200">
            <div>
                <h3 class="text-xl font-bold">Procurement Detail</h3>
                <p class="text-sm opacity-70">ID: {{ $item->id }} | {{ $item->mat_code }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-error btn-sm">Back to Dashboard</a>
            </div>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('procurement.update', $item->id) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <!-- Static / Read-only Fields -->
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">ID Procurement</span></label>
                        <div class="font-semibold">{{ $item->id_procurement }}</div>
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Mat Code</span></label>
                        <div class="font-semibold">{{ $item->mat_code }}</div>
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Nama Barang</span></label>
                        <div class="font-semibold">{{ $item->nama_barang }}</div>
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Qty / UoM</span></label>
                        <div class="font-semibold">{{ $item->qty }} {{ $item->um }}</div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Nilai</span></label>
                        <div class="font-semibold">{{ number_format($item->nilai, 0, ',', '.') }}</div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Vendor</span></label>
                        <div class="font-semibold">{{ $item->nama_vendor }}</div>
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">User Requester</span></label>
                        <div class="font-semibold">{{ $item->user_requester }}</div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium opacity-70">Tgl Terima Dokumen</span></label>
                        <div class="font-semibold">{{ $item->tanggal_terima_dokumen }}</div>
                    </div>
                </div>

                <div class="divider my-6">Editable Fields</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-base-200 p-6 rounded-box">
                    <!-- Status -->
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">Status</span></label>
                        <select name="status" class="select select-bordered w-full">
                            @foreach(\App\Enums\ProcurementStatusEnum::cases() as $status)
                                <option value="{{ $status->value }}" {{ $item->status === $status || $item->status?->value === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-medium">PG</span></label>
                        <input type="text" name="pg" value="{{ $item->pg }}" class="input input-bordered w-full">
                    </div>

                    <div class="form-control w-full">
                         <label class="label"><span class="label-text font-medium">Bagian</span></label>
                        <select name="bagian" class="select select-bordered w-full">
                            <option value="">Select Bagian</option>
                            @foreach(\App\Enums\BagianEnum::cases() as $bagian)
                                <option value="{{ $bagian->value }}" {{ $item->bagian === $bagian->value ? 'selected' : '' }}>{{ $bagian->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control w-full md:col-span-2">
                        <label class="label"><span class="label-text font-medium">Keterangan</span></label>
                        <textarea name="keterangan" rows="3" class="textarea textarea-bordered w-full">{{ $item->keterangan }}</textarea>
                    </div>

                    <!-- Admin Only Fields (If Admin) -->
                    @if(auth()->user()->isAdmin())
                         <div class="col-span-full divider text-xs text-warning uppercase font-bold mt-4">Admin Override</div>
                         <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium text-warning">Admin: Buyer</span></label>
                            <select name="buyer" class="select select-bordered select-warning w-full">
                                <option value="">Select Buyer</option>
                                @foreach(\App\Enums\BuyerEnum::cases() as $buyer)
                                    <option value="{{ $buyer->value }}" {{ $item->buyer === $buyer || $item->buyer?->value === $buyer->value ? 'selected' : '' }}>{{ $buyer->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium text-warning">Admin: No PO</span></label>
                            <input type="text" name="no_po" value="{{ $item->no_po }}" class="input input-bordered input-warning w-full">
                        </div>
                    @endif
                </div>

                <div class="card-actions justify-end mt-6">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Logs Section -->
<div class="card bg-base-100 shadow-xl mt-8">
    <div class="card-body">
        <h3 class="card-title text-lg font-bold mb-4">History / Logs</h3>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Date</th>
                        <th class="whitespace-nowrap">User</th>
                        <th class="w-full">Change</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->logs->sortByDesc('changed_at') as $log)
                        <tr>
                            <td class="whitespace-nowrap font-mono text-xs">{{ $log->changed_at }}</td>
                            <td class="whitespace-nowrap font-medium">{{ $log->changed_by }}</td>
                            <td>{{ $log->change_detail }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

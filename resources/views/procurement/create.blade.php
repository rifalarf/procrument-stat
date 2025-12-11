@extends('layouts.app')

@section('content')
<div class="card bg-base-100 shadow-xl max-w-4xl mx-auto">
    <div class="card-body">
        <h1 class="card-title text-2xl font-bold mb-6">Create New Procurement Item</h1>

        <form action="{{ route('procurement.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Row 1: Identification -->
            <div class="divider text-lg font-semibold">Identification</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">ID Procurement</span></label>
                    <input type="text" name="id_procurement" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Material Code *</span></label>
                    <input type="text" name="mat_code" required class="input input-bordered w-full">
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-medium">Nama Barang *</span></label>
                    <input type="text" name="nama_barang" required class="input input-bordered w-full">
                </div>
            </div>

            <!-- Row 2: Specs & Value -->
            <div class="divider text-lg font-semibold mt-6">Specs & Value</div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Qty *</span></label>
                    <input type="number" step="any" name="qty" required class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">UoM *</span></label>
                    <input type="text" name="um" required class="input input-bordered w-full">
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-medium">Nilai (Budget/Value)</span></label>
                    <input type="number" step="0.01" name="nilai" class="input input-bordered w-full">
                </div>
            </div>

            <!-- Row 3: Request Info -->
            <div class="divider text-lg font-semibold mt-6">Requester Info</div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">PG</span></label>
                    <input type="text" name="pg" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Bagian</span></label>
                    <input type="text" name="bagian" class="input input-bordered w-full">
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text font-medium">User Requester</span></label>
                    <input type="text" name="user_requester" class="input input-bordered w-full">
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Tgl Terima Dok.</span></label>
                    <input type="date" name="tanggal_terima_dokumen" class="input input-bordered w-full">
                </div>
            </div>

            <!-- Row 4: Procurement Info -->
            <div class="divider text-lg font-semibold mt-6">Procurement Status</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Proc Type</span></label>
                    <input type="text" name="proc_type" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Buyer</span></label>
                     <select name="buyer" class="select select-bordered w-full">
                        <option value="">Select Buyer</option>
                        @foreach(\App\Enums\BuyerEnum::cases() as $buyer)
                            <option value="{{ $buyer->value }}">{{ $buyer->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                     <label class="label"><span class="label-text font-medium">Status *</span></label>
                     <select name="status" class="select select-bordered w-full">
                        @foreach(\App\Enums\ProcurementStatusEnum::cases() as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Tgl Status</span></label>
                    <input type="date" name="tanggal_status" class="input input-bordered w-full">
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-medium">Emergency Note</span></label>
                    <input type="text" name="emergency" class="input input-bordered w-full">
                </div>
            </div>

            <!-- Row 5: Vendor & PO -->
            <div class="divider text-lg font-semibold mt-6">Vendor & PO</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Vendor Name</span></label>
                    <input type="text" name="nama_vendor" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">No PO</span></label>
                    <input type="text" name="no_po" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Tgl PO</span></label>
                    <input type="date" name="tanggal_po" class="input input-bordered w-full">
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Tgl Datang</span></label>
                    <input type="date" name="tanggal_datang" class="input input-bordered w-full">
                </div>
            </div>

            <!-- Row 6: Remarks -->
            <div class="form-control mt-4">
                <label class="label"><span class="label-text font-medium">Keterangan</span></label>
                <textarea name="keterangan" rows="3" class="textarea textarea-bordered w-full"></textarea>
            </div>

            <div class="card-actions justify-end mt-6">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Item</button>
            </div>
        </form>
    </div>
</div>
@endsection

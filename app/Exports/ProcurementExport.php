<?php

namespace App\Exports;

use App\Models\ProcurementItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;

class ProcurementExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = ProcurementItem::query();
        $request = $this->request;

        // Search logic - replicated from ProcurementController
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('mat_code', 'like', "%{$search}%")
                  ->orWhere('no_pr', 'like', "%{$search}%")
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
        
        // RBAC Logic
        $currentUser = auth()->user();
        $allowedBagians = null;

        if ($currentUser && !$currentUser->isAdmin()) {
            $access = $currentUser->bagian_access;
            if (is_array($access) && count($access) > 0) {
                $allowedBagians = array_map('strval', $access); 
            } elseif (is_string($access) && !empty($access)) {
                 $allowedBagians = [$access]; 
            }
        }

        if ($allowedBagians !== null) {
            if (count($allowedBagians) === 1) {
                 $query->where('bagian', $allowedBagians[0]);
            } else {
                 $requestedBagian = $request->input('bagian');
                 
                 if ($requestedBagian && in_array($requestedBagian, $allowedBagians)) {
                     $query->where('bagian', $requestedBagian);
                 } else {
                     $query->whereIn('bagian', $allowedBagians);
                 }
            }
        } else {
            if ($bagian = $request->input('bagian')) {
                $query->where('bagian', $bagian);
            }
        }
        
        if ($user = $request->input('user')) {
             $query->where('user_requester', $user);
        }

        return $query->latest('last_updated_at');
    }

    public function headings(): array
    {
        return [
            'ID Procurement',
            'Mat Code',
            'Nama Barang',
            'Qty',
            'UM',
            'Nilai',
            'PG',
            'User',
            'Bagian',
            'Tgl Terima Dok',
            'Proc Type',
            'Buyer',
            'Status',
            'Tgl Status',
            'Emergency',
            'No PO',
            'Nama Vendor',
            'Tgl PO',
            'Tgl Datang',
            'Keterangan',
            'Last Updated By',
            'Last Updated At',
        ];
    }

    public function map($item): array
    {
        return [
            $item->no_pr,
            $item->mat_code,
            $item->nama_barang,
            $item->qty,
            $item->um,
            $item->nilai,
            $item->pg,
            $item->user_requester,
            $this->formatEnum($item->bagian),
            $this->formatDate($item->tanggal_terima_dokumen),
            $item->proc_type,
            $this->formatEnum($item->buyer),
            $this->formatEnum($item->status),
            $this->formatDate($item->tanggal_status),
            $item->emergency,
            $item->no_po,
            $item->nama_vendor,
            $this->formatDate($item->tanggal_po),
            $this->formatDate($item->tanggal_datang),
            $item->keterangan,
            $item->last_updated_by,
            $this->formatDate($item->last_updated_at, true),
        ];
    }

    private function formatEnum($value)
    {
        if ($value instanceof \UnitEnum) {
            return $value->value;
        }
        return $value;
    }

    private function formatDate($date, $withTime = false)
    {
        if (!$date) return null;
        try {
            return $withTime 
                ? \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s')
                : \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return $date;
        }
    }
}

@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-base-content">Riwayat Log</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-error">Kembali ke Dashboard</a>
    </div>

    <div class="bg-base-100 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th class="w-48">Tanggal</th>
                        <th class="w-48">User</th>
                        <th class="w-32">ID Procurement</th>
                        <th class="w-32">Bagian</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="hover">
                            <td class="whitespace-nowrap text-sm text-gray-500">
                                {{ $log->changed_at ? \Carbon\Carbon::parse($log->changed_at)->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="text-sm font-medium">
                                {{ $log->changed_by }}
                            </td>
                            <td>
                                @if($log->procurementItem)
                                    <a href="{{ route('procurement.show', $log->procurementItem->id) }}" class="link link-primary no-underline hover:underline">
                                        {{ $log->procurementItem->id_procurement ?? '#' . $log->procurementItem->id }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Deleted</span>
                                @endif
                            </td>
                            <td>
                                @if($log->procurementItem && $log->procurementItem->bagian)
                                    @php
                                        $bagianEnum = \App\Enums\BagianEnum::tryFrom($log->procurementItem->bagian);
                                    @endphp
                                    <span class="badge badge-sm border-0 font-semibold" style="background-color: {{ $bagianEnum?->color() ?? '#f3f4f6' }}; color: white;">
                                        {{ $bagianEnum?->label() ?? $log->procurementItem->bagian }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-sm">
                                {{ $log->change_detail }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">Tidak ada log ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-base-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

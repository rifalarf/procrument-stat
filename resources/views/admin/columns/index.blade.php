@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" x-data>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-base-content">Manage Table Columns</h1>
        <div class="flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-error btn-sm text-white">Back to Dashboard</a>
            <a href="{{ route('admin.columns.create') }}" class="btn btn-primary btn-sm">Add New Column</a>
        </div>
    </div>

    <div class="bg-base-100 shadow-xl rounded-box overflow-hidden">
        <table class="table table-zebra w-full">
            <thead class="bg-base-200">
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Label</th>
                    <th scope="col">Key/Field</th>
                    <th scope="col">Type</th>
                    <th scope="col">Visible</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-columns">
                @foreach($columns as $column)
                <tr data-id="{{ $column->id }}" class="hover">
                    <td class="cursor-move handle">
                        <div class="flex items-center space-x-2">
                             <svg class="w-4 h-4 text-base-content opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </div>
                    </td>
                    <td class="font-medium">{{ $column->label }}</td>
                    <td class="opacity-70">{{ $column->key }}</td>
                    <td class="opacity-70">{{ $column->type }}</td>
                    <td>
                        @if($column->is_visible)
                            <span class="badge badge-success badge-sm text-white">Yes</span>
                        @else
                            <span class="badge badge-error badge-sm text-white">No</span>
                        @endif
                    </td>
                    <td class="flex space-x-2 justify-end">
                        <a href="{{ route('admin.columns.edit', $column->id) }}" class="btn btn-ghost btn-xs text-info">Edit</a>
                        @if($column->is_dynamic)
                            <form action="{{ route('admin.columns.destroy', $column->id) }}" method="POST" id="delete-column-{{ $column->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmModal('Delete Column', 'Are you sure you want to delete this column?', 'delete-column-{{ $column->id }}')" class="btn btn-ghost btn-xs text-error">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('sortable-columns');
        var sortable = Sortable.create(el, {
            handle: '.handle',
            animation: 150,
            onEnd: function (evt) {
                var order = [];
                el.querySelectorAll('tr[data-id]').forEach(function(row) {
                    order.push(row.getAttribute('data-id'));
                });
                
                fetch('{{ route('admin.columns.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order }) 
                }).then(response => {
                    if(response.ok) {
                         window.location.reload();
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed to save order', type: 'error' } }));
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection

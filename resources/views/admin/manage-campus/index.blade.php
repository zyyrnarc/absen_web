@extends('layouts.app')

@section('content')

@if ($errors->any())
    <div class="flash-error mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
    <form method="GET" action="{{ route('manage-campus') }}">
        <div class="relative">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search campus"
                class="search-input pl-4 pr-8 py-2 w-64"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">&#128269;</span>
        </div>
    </form>

    <button type="button" onclick="toggleAddModal()" class="btn-add">
        + Add Campus >
    </button>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3 w-20">ID</th>
                <th class="px-5 py-3">Campus</th>
                <th class="px-5 py-3">Address</th>
                <th class="px-5 py-3 w-36">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campuses as $campus)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500 font-medium">{{ $campus->id }}</td>
                    <td class="px-5 py-3 text-gray-700 font-medium">{{ $campus->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $campus->address ?: '-' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                class="btn-action btn-action-edit"
                                title="Edit"
                                onclick="openEditCampus({{ $campus->id }}, @js($campus->name), @js($campus->address))"
                            >
                                E
                            </button>

                            <form
                                action="{{ route('manage-campus.destroy', $campus->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin hapus campus ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">X</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data campus</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="modalAddCampus" class="modal-corner hidden">
    <h2>Add Campus</h2>
    <form action="{{ route('manage-campus.store') }}" method="POST">
        @csrf
        <div class="flex flex-col gap-3">
            <input
                type="text"
                name="name"
                placeholder="Campus Name"
                class="form-input"
                value="{{ old('name') }}"
                required
            >
            <input
                type="text"
                name="address"
                placeholder="Campus Address"
                class="form-input"
                value="{{ old('address') }}"
            >
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="toggleAddModal()">Cancel</button>
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<div id="modalEditCampus" class="modal-corner hidden">
    <h2>Edit Campus</h2>
    <form id="formEditCampus" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-3">
            <input
                type="text"
                id="edit_campus_name"
                name="name"
                placeholder="Campus Name"
                class="form-input"
                required
            >
            <input
                type="text"
                id="edit_campus_address"
                name="address"
                placeholder="Campus Address"
                class="form-input"
            >
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="closeEditCampus()">Cancel</button>
            <button type="submit" class="btn-save">Update</button>
        </div>
    </form>
</div>

<script>
    function toggleAddModal() {
        const modal = document.getElementById('modalAddCampus');
        const edit = document.getElementById('modalEditCampus');

        edit.classList.add('hidden');
        modal.classList.toggle('hidden');
    }

    function openEditCampus(id, name, address) {
        document.getElementById('edit_campus_name').value = name ?? '';
        document.getElementById('edit_campus_address').value = address ?? '';
        document.getElementById('formEditCampus').action = '/admin/manage-campus/' + id;

        document.getElementById('modalAddCampus').classList.add('hidden');
        document.getElementById('modalEditCampus').classList.remove('hidden');
    }

    function closeEditCampus() {
        document.getElementById('modalEditCampus').classList.add('hidden');
    }

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalAddCampus').classList.remove('hidden');
        });
    @endif
</script>

@endsection

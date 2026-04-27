@extends('layouts.app')

@section('content')

@if ($errors->any())
    <div class="flash-error mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
    <form method="GET" action="{{ route('manage-mentor') }}">
        <div class="relative">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search mentor"
                class="search-input pl-4 pr-8 py-2 w-64"
            >
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">&#128269;</span>
        </div>
    </form>

    <button type="button" onclick="toggleAddModal()" class="btn-add">
        + Add Mentor >
    </button>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden" style="max-width: 760px;">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3 w-20">ID</th>
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Posisi</th>
                <th class="px-5 py-3 w-36">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mentors as $mentor)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500 font-medium">{{ $mentor->id }}</td>
                    <td class="px-5 py-3 text-gray-700 font-medium">{{ $mentor->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $mentor->position ?: '-' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                class="btn-action btn-action-edit"
                                title="Edit"
                                onclick="openEditMentor({{ $mentor->id }}, @js($mentor->name), @js($mentor->position))"
                            >
                                E
                            </button>

                            <form
                                action="{{ route('manage-mentor.destroy', $mentor->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin hapus mentor ini?')"
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
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data mentor</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="modalAddMentor" class="modal-corner hidden">
    <h2>Add Mentor</h2>
    <form action="{{ route('manage-mentor.store') }}" method="POST">
        @csrf
        <div class="flex flex-col gap-3">
            <input
                type="text"
                name="name"
                placeholder="Mentor Name"
                class="form-input"
                value="{{ old('name') }}"
                required
            >
            <input
                type="text"
                name="position"
                placeholder="Mentor Position"
                class="form-input"
                value="{{ old('position') }}"
            >
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="toggleAddModal()">Cancel</button>
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<div id="modalEditMentor" class="modal-corner hidden">
    <h2>Edit Mentor</h2>
    <form id="formEditMentor" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-3">
            <input
                type="text"
                id="edit_mentor_name"
                name="name"
                placeholder="Mentor Name"
                class="form-input"
                required
            >
            <input
                type="text"
                id="edit_mentor_position"
                name="position"
                placeholder="Mentor Position"
                class="form-input"
            >
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="closeEditMentor()">Cancel</button>
            <button type="submit" class="btn-save">Update</button>
        </div>
    </form>
</div>

<script>
    function toggleAddModal() {
        const modal = document.getElementById('modalAddMentor');
        const edit = document.getElementById('modalEditMentor');

        edit.classList.add('hidden');
        modal.classList.toggle('hidden');
    }

    function openEditMentor(id, name, position) {
        document.getElementById('edit_mentor_name').value = name ?? '';
        document.getElementById('edit_mentor_position').value = position ?? '';
        document.getElementById('formEditMentor').action = '/admin/manage-mentor/' + id;

        document.getElementById('modalAddMentor').classList.add('hidden');
        document.getElementById('modalEditMentor').classList.remove('hidden');
    }

    function closeEditMentor() {
        document.getElementById('modalEditMentor').classList.add('hidden');
    }

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalAddMentor').classList.remove('hidden');
        });
    @endif
</script>

@endsection

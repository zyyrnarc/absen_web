@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-800">Manage Major</h1>
    <p class="text-sm text-gray-500 mt-1">Managing Major interns on your company</p>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="flash-success mb-4">{{ session('success') }}</div>
@endif

{{-- SEARCH + ADD BUTTON --}}
<div class="flex items-center justify-between mb-5">
    <form method="GET" action="{{ route('manage-major') }}">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search"
                   class="search-input pl-4 pr-8 py-2 w-48">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        </div>
    </form>

    <button onclick="toggleAddModal()" class="btn-add">
        + Add Major >
    </button>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden" style="max-width: 520px;">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">Major</th>
                <th class="px-5 py-3">Study Program</th>
                <th class="px-5 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($majors as $major)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500 font-medium">{{ $major->id }}</td>
                    <td class="px-5 py-3 text-gray-700 font-medium">{{ $major->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $major->study_program ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            {{-- Edit --}}
                            <button onclick="openEditMajor({{ $major->id }}, '{{ $major->name }}', '{{ $major->study_program }}')"
                                    class="btn-action btn-action-edit" title="Edit">✏</button>

                            {{-- Delete --}}
                            <form action="{{ route('manage-major.destroy', $major->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus major ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data major</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ========== MODAL ADD MAJOR (pojok kanan) ========== --}}
<div id="modalAddMajor" class="modal-corner hidden">
    <h2>Add Major</h2>
    <form action="{{ route('manage-major.store') }}" method="POST">
        @csrf
        <div class="flex flex-col gap-3">
            <input type="text" name="name"          placeholder="Major Name"    class="form-input" required>
            <input type="text" name="study_program" placeholder="Study Program" class="form-input">
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="toggleAddModal()">Cancel</button>
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

{{-- ========== MODAL EDIT MAJOR (pojok kanan) ========== --}}
<div id="modalEditMajor" class="modal-corner hidden">
    <h2>Edit Major</h2>
    <form id="formEditMajor" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-3">
            <input type="text" id="edit_major_name"          name="name"          placeholder="Major Name"    class="form-input" required>
            <input type="text" id="edit_major_study_program" name="study_program" placeholder="Study Program" class="form-input">
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn-cancel" onclick="closeEditMajor()">Cancel</button>
            <button type="submit" class="btn-save">Update</button>
        </div>
    </form>
</div>

{{-- SCRIPT --}}
<script>
    function toggleAddModal() {
        const modal = document.getElementById('modalAddMajor');
        const edit  = document.getElementById('modalEditMajor');
        edit.classList.add('hidden');
        modal.classList.toggle('hidden');
    }

    function openEditMajor(id, name, studyProgram) {
        document.getElementById('edit_major_name').value          = name;
        document.getElementById('edit_major_study_program').value = studyProgram ?? '';
        document.getElementById('formEditMajor').action           = '/admin/manage-major/' + id;

        document.getElementById('modalAddMajor').classList.add('hidden');
        document.getElementById('modalEditMajor').classList.remove('hidden');
    }

    function closeEditMajor() {
        document.getElementById('modalEditMajor').classList.add('hidden');
    }
</script>

@endsection

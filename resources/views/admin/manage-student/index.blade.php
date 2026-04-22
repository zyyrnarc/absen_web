@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-800">Manage Student</h1>
    <p class="text-sm text-gray-500 mt-1">Managing Student as interns on your company</p>
</div>

{{-- Flash Message --}}
@if(session('success'))
    <div class="flash-success mb-4">{{ session('success') }}</div>
@endif

{{-- SEARCH + ADD BUTTON --}}
<div class="flex items-center justify-between mb-5">
    <form method="GET" action="{{ route('manage-student') }}">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search"
                   class="search-input pl-4 pr-8 py-2 w-48">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        </div>
    </form>

    <button onclick="document.getElementById('modalAddStudent').classList.remove('hidden')"
            class="btn-add">
        + Add Student >
    </button>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">NIM</th>
                <th class="px-5 py-3">Major</th>
                <th class="px-5 py-3">Campus</th>
                <th class="px-5 py-3">Mentor</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-700">{{ $student->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->nim ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->major ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->campus->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->mentor->name ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <span class="{{ $student->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ $student->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            {{-- View --}}
                            <button class="btn-action btn-action-view" title="View">↗</button>

                            {{-- Edit --}}
                            <button onclick="openEditModal({{ $student->id }}, '{{ $student->name }}', '{{ $student->nim }}', '{{ $student->major }}', '{{ $student->study_program }}', {{ $student->campus_id }}, {{ $student->mentor_id ?? 'null' }}, '{{ $student->email }}', '{{ $student->username }}', '{{ $student->status }}')"
                                    class="btn-action btn-action-edit" title="Edit">✏</button>

                            {{-- Delete --}}
                            <form action="{{ route('manage-student.destroy', $student->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus mahasiswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-gray-400">Belum ada data mahasiswa</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ========== MODAL ADD STUDENT ========== --}}
<div id="modalAddStudent" class="modal-overlay hidden">
    <div class="modal-box" style="width:480px">
        <h2>Add Intern Account</h2>
        <button class="modal-close" onclick="document.getElementById('modalAddStudent').classList.add('hidden')">✕</button>

        <form action="{{ route('manage-student.store') }}" method="POST">
            @csrf
            <div class="flex flex-col gap-3">
                <input type="text"   name="name"          placeholder="Name"          class="form-input" required>
                <input type="text"   name="nim"           placeholder="NIM"           class="form-input" required>
                <input type="text"   name="major"         placeholder="Major"         class="form-input" required>
                <input type="text"   name="study_program" placeholder="Study Program" class="form-input">

                <select name="campus_id" class="form-input" required>
                    <option value="" disabled selected>Campus</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                </select>

                <select name="mentor_id" class="form-input">
                    <option value="" disabled selected>Mentor</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                    @endforeach
                </select>

                <input type="email"  name="email"    placeholder="Email"    class="form-input" required>
                <input type="text"   name="username" placeholder="Username" class="form-input">
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="btn-cancel"
                        onclick="document.getElementById('modalAddStudent').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ========== MODAL EDIT STUDENT ========== --}}
<div id="modalEditStudent" class="modal-overlay hidden">
    <div class="modal-box" style="width:480px">
        <h2>Edit Intern Account</h2>
        <button class="modal-close" onclick="document.getElementById('modalEditStudent').classList.add('hidden')">✕</button>

        <form id="formEditStudent" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="flex flex-col gap-3">
                <input type="text"   id="edit_name"          name="name"          placeholder="Name"          class="form-input" required>
                <input type="text"   id="edit_nim"           name="nim"           placeholder="NIM"           class="form-input" required>
                <input type="text"   id="edit_major"         name="major"         placeholder="Major"         class="form-input" required>
                <input type="text"   id="edit_study_program" name="study_program" placeholder="Study Program" class="form-input">

                <select id="edit_campus_id" name="campus_id" class="form-input" required>
                    <option value="" disabled>Campus</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                </select>

                <select id="edit_mentor_id" name="mentor_id" class="form-input">
                    <option value="">Pilih Mentor (opsional)</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                    @endforeach
                </select>

                <input type="email" id="edit_email"    name="email"    placeholder="Email"    class="form-input" required>
                <input type="text"  id="edit_username" name="username" placeholder="Username" class="form-input">

                <select id="edit_status" name="status" class="form-input">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="btn-cancel"
                        onclick="document.getElementById('modalEditStudent').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="btn-save">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    // Tutup modal kalau klik di luar
    ['modalAddStudent','modalEditStudent','modalPermit'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });

    // Buka modal edit dan isi data
    function openEditModal(id, name, nim, major, studyProgram, campusId, mentorId, email, username, status) {
        document.getElementById('edit_name').value          = name;
        document.getElementById('edit_nim').value           = nim;
        document.getElementById('edit_major').value         = major;
        document.getElementById('edit_study_program').value = studyProgram;
        document.getElementById('edit_campus_id').value     = campusId;
        document.getElementById('edit_mentor_id').value     = mentorId ?? '';
        document.getElementById('edit_email').value         = email;
        document.getElementById('edit_username').value      = username;
        document.getElementById('edit_status').value        = status;
        document.getElementById('formEditStudent').action   = '/admin/manage-student/' + id;
        document.getElementById('modalEditStudent').classList.remove('hidden');
    }
</script>

@endsection

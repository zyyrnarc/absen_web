@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="flash-error mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
    <form method="GET" action="{{ route('manage-student') }}">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search student"
                   class="search-input pl-4 pr-8 py-2 w-64">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">S</span>
        </div>
    </form>

    <button type="button" onclick="toggleAddStudentModal()" class="btn-add">
        + Add Student
    </button>
</div>

<section class="bg-white rounded-2xl shadow-md overflow-hidden student-table-shell">
    <div class="student-table-head">
        <div>
            <p class="student-table-kicker">Data Mahasiswa</p>
            <h2 class="student-table-title">Daftar akun student</h2>
        </div>
        <p class="student-table-meta">{{ $students->count() }} student</p>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm student-table">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3 whitespace-nowrap">Image</th>
                <th class="px-5 py-3 whitespace-nowrap">Name</th>
                <th class="px-5 py-3 whitespace-nowrap">NIM</th>
                <th class="px-5 py-3 whitespace-nowrap">Major</th>
                <th class="px-5 py-3 whitespace-nowrap">Gender</th>
                <th class="px-5 py-3 whitespace-nowrap">Campus</th>
                <th class="px-5 py-3 whitespace-nowrap">Mentor</th>
                <th class="px-5 py-3 whitespace-nowrap">Password</th>
                <th class="px-5 py-3 whitespace-nowrap">Status</th>
                <th class="px-5 py-3 whitespace-nowrap">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        @if ($student->avatar_url)
                            <img
                                src="{{ $student->avatar_url }}"
                                alt="Foto {{ $student->name }}"
                                class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-sm">
                        @else
                            <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-medium text-gray-700">{{ $student->name }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->nim ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->major ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->gender ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->campus ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->mentor ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->password }}</td>
                    <td class="px-5 py-3 student-status-cell">
                        <span class="{{ $student->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ $student->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('manage-student.edit', $student->id) }}"
                               class="btn-action btn-action-edit" title="Edit">E</a>

                            <form action="{{ route('manage-student.destroy', $student->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus mahasiswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">D</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-5 py-8 text-center text-gray-400">Belum ada data mahasiswa</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</section>

<div id="modalAddStudent" class="modal-student-overlay hidden" onclick="closeAddStudentModal(event)">
    <div class="modal-corner modal-student">
        <h2>Add Student</h2>
        @include('admin.manage-student.partials.form', [
            'formAction' => route('manage-student.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Save',
            'showStatus' => false,
            'student' => null,
            'isModal' => true,
        ])
    </div>
</div>

<script>
    function toggleAddStudentModal() {
        document.getElementById('modalAddStudent').classList.toggle('hidden');
    }

    function closeAddStudentModal(event) {
        if (event.target.id === 'modalAddStudent') {
            document.getElementById('modalAddStudent').classList.add('hidden');
        }
    }

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalAddStudent').classList.remove('hidden');
        });
    @endif
</script>
@endsection

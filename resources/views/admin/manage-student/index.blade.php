@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="flash-error mb-4">
        {{ $errors->first() }}
    </div>
@endif

@if(session('success'))
    <div class="flash-success mb-4">{{ session('success') }}</div>
@endif

<div class="flex items-center justify-between mb-5">
    <form method="GET" action="{{ route('manage-student') }}">
        <div class="relative">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search"
                   class="search-input pl-4 pr-8 py-2 w-48">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">S</span>
        </div>
    </form>

    <button type="button" onclick="toggleAddStudentModal()" class="btn-add">
        + Add Student
    </button>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-purple-100 text-purple-800 font-bold text-left">
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">NIM</th>
                <th class="px-5 py-3">Major</th>
                <th class="px-5 py-3">Campus</th>
                <th class="px-5 py-3">Mentor</th>
                <th class="px-5 py-3">Password</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($student->avatar_url)
                                <img
                                    src="{{ $student->avatar_url }}"
                                    alt="Foto {{ $student->name }}"
                                    class="w-9 h-9 rounded-full object-cover border border-slate-200 flex-shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="font-medium text-gray-700">{{ $student->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->nim ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->major ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->campus ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->mentor ?? '-' }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $student->password }}</td>
                    <td class="px-5 py-3">
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
                    <td colspan="8" class="px-5 py-8 text-center text-gray-400">Belum ada data mahasiswa</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="modalAddStudent" class="modal-corner hidden" style="width: 420px; max-width: calc(100vw - 32px);">
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

<script>
    function toggleAddStudentModal() {
        document.getElementById('modalAddStudent').classList.toggle('hidden');
    }

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalAddStudent').classList.remove('hidden');
        });
    @endif
</script>
@endsection

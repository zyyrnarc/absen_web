@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="flash-error mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Edit Student</h1>
        <p class="text-sm text-gray-500">Perbarui data mahasiswa tanpa modal.</p>
    </div>

    <a href="{{ route('manage-student') }}" class="btn-cancel inline-flex items-center justify-center">
        Back
    </a>
</div>

@include('admin.manage-student.partials.form', [
    'formAction' => route('manage-student.update', $student->id),
    'formMethod' => 'PUT',
    'submitLabel' => 'Update',
    'showStatus' => true,
    'student' => $student,
])
@endsection

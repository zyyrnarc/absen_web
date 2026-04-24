@php
    $isModal = $isModal ?? false;
@endphp

<div class="{{ $isModal ? '' : 'bg-white rounded-2xl shadow-md p-6' }}">
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="student-form-grid">
            <input type="text" name="name" placeholder="Name" class="form-input"
                   value="{{ old('name', $student?->name ?? '') }}" required>
            <input type="text" name="nim" placeholder="NIM" class="form-input"
                   value="{{ old('nim', $student?->nim ?? '') }}" required>
            <input type="text" name="major" placeholder="Major" class="form-input"
                   value="{{ old('major', $student?->major ?? '') }}" required>
            <input type="text" name="study_program" placeholder="Study Program" class="form-input"
                   value="{{ old('study_program', $student?->study_program ?? '') }}">
            <input type="text" name="campus" placeholder="Campus" class="form-input"
                   value="{{ old('campus', $student?->campus ?? '') }}" required>
            <input type="text" name="mentor" placeholder="Mentor" class="form-input"
                   value="{{ old('mentor', $student?->mentor ?? '') }}">

            <input type="email" name="email" placeholder="Email" class="form-input"
                   value="{{ old('email', $student?->email ?? '') }}" required>
            <input type="text" name="username" placeholder="Username" class="form-input"
                   value="{{ old('username', $student?->username ?? '') }}">
            <input type="text" name="password" placeholder="{{ $showStatus ? 'Password baru (kosongkan jika tidak diubah)' : 'Password' }}" class="form-input"
                   value="{{ old('password', $showStatus ? '' : ($student?->password ?? '')) }}"
                   {{ $showStatus ? '' : 'required' }}>

            @if ($showStatus)
                <select name="status" class="form-input">
                    <option value="active" {{ old('status', $student?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $student?->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            @endif
        </div>

        <div class="flex justify-end gap-3 mt-6">
            @if ($isModal)
                <button type="button" class="btn-cancel inline-flex items-center justify-center" onclick="toggleAddStudentModal()">
                    Cancel
                </button>
            @else
                <a href="{{ route('manage-student') }}" class="btn-cancel inline-flex items-center justify-center">
                    Cancel
                </a>
            @endif
            <button type="submit" class="btn-save">{{ $submitLabel }}</button>
        </div>
    </form>
</div>

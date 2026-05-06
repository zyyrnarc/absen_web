@php
    $isModal = $isModal ?? false;
@endphp

<div class="{{ $isModal ? '' : 'bg-white rounded-2xl shadow-md p-6' }}">
    <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="student-form-grid">
            <div class="student-form-full">
                <label class="form-label mb-2 block">Foto Profil</label>
                <div class="student-photo-box">
                    @if (!empty($student?->avatar_url))
                        <img src="{{ $student->avatar_url }}" alt="Foto mahasiswa" class="w-16 h-16 rounded-full object-cover border border-slate-200">
                    @else
                        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-lg">
                            {{ strtoupper(substr($student?->name ?? 'M', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <input type="file" name="avatar" class="form-input" accept="image/*">
                        <p class="form-help mt-2">Opsional. Foto ini akan dipakai juga pada endpoint mobile sebagai `avatar_url`.</p>
                        @error('avatar')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="student-form-field">
                <label class="form-label">Name</label>
                <input type="text" name="name" placeholder="Masukkan nama mahasiswa" class="form-input"
                       value="{{ old('name', $student?->name ?? '') }}" required>
            </div>

            <div class="student-form-field">
                <label class="form-label">NIM</label>
                <input type="text" name="nim" placeholder="Masukkan NIM" class="form-input"
                       value="{{ old('nim', $student?->nim ?? '') }}" required>
            </div>

            <div class="student-form-field">
                <label class="form-label">Major</label>
                <input type="text" name="major" placeholder="Masukkan jurusan" class="form-input"
                       value="{{ old('major', $student?->major ?? '') }}" required>
            </div>

            <div class="student-form-field">
                <label class="form-label">Study Program</label>
                <input type="text" name="study_program" placeholder="Masukkan program studi" class="form-input"
                       value="{{ old('study_program', $student?->study_program ?? '') }}">
            </div>

            <div class="student-form-field">
                <label class="form-label">Campus</label>
                <input type="text" name="campus" placeholder="Masukkan nama kampus" class="form-input"
                       value="{{ old('campus', $student?->campus ?? '') }}" required>
            </div>

            <div class="student-form-field">
                <label class="form-label">Mentor</label>
                <input type="text" name="mentor" placeholder="Masukkan nama mentor" class="form-input"
                       value="{{ old('mentor', $student?->mentor ?? '') }}">
            </div>

            <div class="student-form-field">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-input" required>
                    <option value="">Pilih gender</option>
                    <option value="Male" {{ old('gender', $student?->gender ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', $student?->gender ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="student-form-field">
                <label class="form-label">Email</label>
                <input type="email" name="email" placeholder="Masukkan email" class="form-input"
                       value="{{ old('email', $student?->email ?? '') }}" required>
            </div>

            <div class="student-form-field">
                @php
                    $passwordInputId = $showStatus ? 'edit-student-password' : 'add-student-password';
                @endphp
                <label class="form-label">{{ $showStatus ? 'Password Baru' : 'Password' }}</label>
                <div class="password-field">
                    <input
                        id="{{ $passwordInputId }}"
                        type="password"
                        name="password"
                        placeholder="{{ $showStatus ? 'Kosongkan jika tidak diubah' : 'Masukkan password' }}"
                        class="form-input password-input"
                        value="{{ old('password', '') }}"
                        {{ $showStatus ? '' : 'required' }}>
                    <button
                        type="button"
                        class="password-toggle"
                        onclick="toggleStudentPassword('{{ $passwordInputId }}', this)">
                        Tampilkan
                    </button>
                </div>
                @if ($showStatus)
                    <p class="form-help">Password lama tidak bisa ditampilkan kembali karena disimpan aman. Isi kolom ini jika ingin mengganti password student.</p>
                @endif
            </div>

            @if ($showStatus)
                <div class="student-form-field student-form-full">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="active" {{ old('status', $student?->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $student?->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
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

<script>
    function toggleStudentPassword(inputId, button) {
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    }
</script>

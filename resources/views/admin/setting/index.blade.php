@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $logoUrl = $setting->company_logo_path ? Storage::url($setting->company_logo_path) : null;
    @endphp

    @if ($errors->any())
        <div class="flash-error mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="settings-grid">
        <section class="settings-panel">
            <span class="panel-eyebrow">Company Setting</span>
            <h2 class="panel-title">Atur company name, logo, dan address</h2>
            <p class="panel-subtitle">
                Perubahan di halaman ini akan dipakai sebagai identitas perusahaan pada panel admin.
            </p>

            <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data" class="settings-form settings-stack">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input
                        id="company_name"
                        type="text"
                        name="company_name"
                        value="{{ old('company_name', $setting->company_name) }}"
                        class="form-input"
                        placeholder="Masukkan nama perusahaan">
                    @error('company_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="company_logo" class="form-label">Company Logo</label>
                    <div class="logo-preview">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Company logo">
                        @else
                            <div class="logo-placeholder">Logo</div>
                        @endif

                        <div class="w-full">
                            <input id="company_logo" type="file" name="company_logo" class="form-input" accept="image/*">
                            <p class="form-help">Format gambar: JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                        </div>
                    </div>
                    @error('company_logo')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="company_address" class="form-label">Address</label>
                    <textarea
                        id="company_address"
                        name="company_address"
                        class="form-input form-textarea"
                        placeholder="Masukkan alamat perusahaan">{{ old('company_address', $setting->company_address) }}</textarea>
                    @error('company_address')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-save">Simpan company setting</button>
                </div>
            </form>
        </section>

        <aside class="settings-panel">
            <span class="panel-eyebrow">Security</span>
            <h2 class="panel-title">Ganti password admin</h2>
            <p class="panel-subtitle">
                Isi password lama lalu buat password baru untuk menjaga keamanan akun admin.
            </p>

            <form action="{{ route('setting.password.update') }}" method="POST" class="settings-form settings-stack">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        class="form-input"
                        placeholder="Masukkan password lama">
                    @error('current_password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="Masukkan password baru">
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="Ulangi password baru">
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-save">Simpan password</button>
                </div>
            </form>
        </aside>
    </div>
@endsection

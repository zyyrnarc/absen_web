@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $avatarLetter = strtoupper(substr($admin?->name ?? 'A', 0, 1));
        $avatarUrl = $admin?->avatar_path ? Storage::disk('public')->url($admin->avatar_path) : null;
    @endphp

    @if ($errors->any())
        <div class="flash-error mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="settings-grid">
        <section class="profile-panel">
            <span class="panel-eyebrow">Admin Profile</span>
            <h2 class="panel-title">Kelola foto dan identitas akun admin</h2>
            <p class="panel-subtitle">
                Perbarui foto profil, nama, email, dan nomor telepon dari halaman ini agar identitas admin tetap rapi.
            </p>

            <div class="profile-summary">
                <div class="profile-card">
                    @if ($avatarUrl)
                        <img
                            src="{{ $avatarUrl }}"
                            alt="Foto admin"
                            class="w-20 h-20 rounded-full object-cover border border-slate-200 shadow-sm">
                    @else
                        <div class="profile-avatar">{{ $avatarLetter }}</div>
                    @endif
                    <div>
                        <p class="profile-name">{{ $admin?->name ?? 'Admin' }}</p>
                        <p class="profile-role">{{ $setting?->company_name ?? 'Administrator' }}</p>
                    </div>
                </div>

                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Email</span>
                        <span class="profile-meta-value">{{ $admin?->email ?? '-' }}</span>
                    </div>

                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Phone</span>
                        <span class="profile-meta-value">{{ $admin?->phone ?: 'Nomor telepon belum diisi.' }}</span>
                    </div>

                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Photo URL</span>
                        <span class="profile-meta-value break-all">{{ $avatarUrl ?? 'Foto profil belum diupload.' }}</span>
                    </div>
                </div>

                <a href="{{ route('setting') }}" class="profile-link">Buka company setting</a>
            </div>
        </section>

        <aside class="profile-panel">
            <span class="panel-eyebrow">Update Profile</span>
            <h2 class="panel-title">Simpan perubahan akun admin</h2>
            <p class="panel-subtitle">
                Format gambar yang didukung: JPG, PNG, atau WEBP dengan ukuran maksimal 4MB.
            </p>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="settings-form settings-stack">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="avatar" class="form-label">Foto Profil</label>
                    <div class="logo-preview">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Preview foto admin">
                        @else
                            <div class="logo-placeholder">Foto</div>
                        @endif

                        <div class="w-full">
                            <input id="avatar" type="file" name="avatar" class="form-input" accept="image/*">
                            <p class="form-help">Foto ini akan tampil di panel admin dan bisa dipakai sebagai sumber URL foto profil.</p>
                        </div>
                    </div>
                    @error('avatar')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Nama</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $admin?->name) }}"
                        class="form-input"
                        placeholder="Masukkan nama admin">
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $admin?->email) }}"
                        class="form-input"
                        placeholder="Masukkan email admin">
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input
                        id="phone"
                        type="text"
                        name="phone"
                        value="{{ old('phone', $admin?->phone) }}"
                        class="form-input"
                        placeholder="Masukkan nomor telepon">
                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-save">Simpan profil admin</button>
                </div>
            </form>
        </aside>
    </div>
@endsection

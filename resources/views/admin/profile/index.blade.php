@extends('layouts.app')

@section('content')
    @php
        $avatarLetter = strtoupper(substr($admin?->name ?? 'A', 0, 1));
    @endphp

    <div class="settings-grid">
        <section class="profile-panel">
            <span class="panel-eyebrow">Admin Profile</span>
            <h2 class="panel-title">Profil admin terhubung ke halaman setting</h2>
            <p class="panel-subtitle">
                Saat kartu profil di bagian header diklik, admin akan langsung diarahkan ke halaman setting untuk mengatur
                data perusahaan dan password.
            </p>

            <div class="profile-summary">
                <div class="profile-card">
                    <div class="profile-avatar">{{ $avatarLetter }}</div>
                    <div>
                        <p class="profile-name">{{ $admin?->name ?? 'Admin' }}</p>
                        <p class="profile-role">{{ $setting?->company_name ?? 'EduAdmin Company' }}</p>
                    </div>
                </div>

                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Email</span>
                        <span class="profile-meta-value">{{ $admin?->email ?? '-' }}</span>
                    </div>

                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Company Name</span>
                        <span class="profile-meta-value">{{ $setting?->company_name ?? 'EduAdmin Company' }}</span>
                    </div>

                    <div class="profile-meta-item">
                        <span class="profile-meta-label">Address</span>
                        <span class="profile-meta-value">{{ $setting?->company_address ?: 'Alamat perusahaan belum diisi.' }}</span>
                    </div>
                </div>

                <a href="{{ route('setting') }}" class="profile-link">Buka halaman setting</a>
            </div>
        </section>

        <aside class="profile-panel">
            <span class="panel-eyebrow">Quick Info</span>
            <h2 class="panel-title">Yang bisa diatur di setting</h2>
            <p class="panel-subtitle">
                Halaman setting sekarang sudah disiapkan untuk data utama perusahaan dan keamanan akun admin.
            </p>

            <div class="profile-meta">
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Company Name</span>
                    <span class="profile-meta-value">Nama perusahaan bisa diubah kapan saja.</span>
                </div>

                <div class="profile-meta-item">
                    <span class="profile-meta-label">Company Logo</span>
                    <span class="profile-meta-value">Upload logo perusahaan agar tampil di header admin.</span>
                </div>

                <div class="profile-meta-item">
                    <span class="profile-meta-label">Address & Password</span>
                    <span class="profile-meta-value">Alamat perusahaan dan password admin disimpan dari satu halaman.</span>
                </div>
            </div>
        </aside>
    </div>
@endsection

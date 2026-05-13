<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Activity Export</title>
    @php
        use Illuminate\Support\Facades\Storage;

        $logoUrl = $setting?->company_logo_path ? Storage::disk('public')->url($setting->company_logo_path) : null;
    @endphp
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            background: #f3f4f6;
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px;
            background: #ffffff;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-back {
            color: #374151;
            background: #e5e7eb;
        }

        .btn-print {
            color: #ffffff;
            background: #2563eb;
            border: none;
            cursor: pointer;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .company {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 16px;
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        .logo-placeholder {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: #eef2ff;
            color: #4338ca;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .title {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .muted {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
        }

        .card-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: .06em;
        }

        .card-value {
            font-size: 24px;
            font-weight: 800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .empty {
            text-align: center;
            color: #6b7280;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                max-width: none;
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <a href="{{ route('weekly-activity', ['student_id' => $selectedStudent, 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-back">Kembali</a>
        </div>

        <div class="header">
            <div class="company">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Company Logo" class="logo">
                @else
                    <div class="logo-placeholder">LOGO</div>
                @endif
                <div>
                    <h1 class="title">Weekly Activity Report</h1>
                    <p class="muted">{{ $setting?->company_name ?? 'Perusahaan' }}</p>
                    <p class="muted">{{ $setting?->company_address ?: 'Alamat perusahaan belum diisi.' }}</p>
                </div>
            </div>

            <div>
                <p class="muted"><strong>Periode:</strong> {{ $months[$selectedMonth] }} {{ $selectedYear }}</p>
                <p class="muted"><strong>Mahasiswa:</strong> {{ $selectedStudentName ?? 'Semua mahasiswa' }}</p>
                <p class="muted"><strong>Dicetak:</strong> {{ $generatedAt->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="summary">
            <div class="card">
                <span class="card-label">Total Activities</span>
                <div class="card-value">{{ $activities->count() }}</div>
            </div>
            <div class="card">
                <span class="card-label">Student Filter</span>
                <div class="card-value">{{ $selectedStudentName ?? 'Semua' }}</div>
            </div>
            <div class="card">
                <span class="card-label">Month</span>
                <div class="card-value">{{ $months[$selectedMonth] }} {{ $selectedYear }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Aktivitas / Jobdesc</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>{{ $activity['name'] }}</td>
                        <td>{{ $activity['day'] }}</td>
                        <td>{{ $activity['times'] }}</td>
                        <td>{{ $activity['activity'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">Tidak ada aktivitas untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

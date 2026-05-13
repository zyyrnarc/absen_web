@extends('layouts.app')

@section('content')

{{-- STAT CARDS --}}
<div class="stat-grid mb-6">
    <div class="stat-card bg-card-purple text-white rounded-2xl px-6 py-4 shadow-md">
        <p class="text-xs font-semibold uppercase opacity-80 mb-1">Total Workdays</p>
        <p class="text-4xl font-extrabold">{{ $totalWorkdays }} Days</p>
        <p class="text-xs font-semibold opacity-70 mt-1">{{ $months[$selectedMonth] }} {{ $selectedYear }}</p>
    </div>
    <div class="stat-card bg-card-pink text-white rounded-2xl px-6 py-4 shadow-md">
        <p class="text-xs font-semibold uppercase opacity-80 mb-1">Present Today</p>
        <p class="text-4xl font-extrabold">{{ $presentToday }}</p>
    </div>
    <div class="stat-card bg-card-red text-white rounded-2xl px-6 py-4 shadow-md">
        <p class="text-xs font-semibold uppercase opacity-80 mb-1">Waiting Permit</p>
        <p class="text-4xl font-extrabold">{{ $waitingPermit }}</p>
    </div>
</div>

{{-- MAIN ROW --}}
<div class="page-split">

    {{-- KIRI: Tabel Attendance --}}
    <div class="content-panel rounded-2xl p-5">

        {{-- Title + Filter bulan --}}
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-gray-700">Daily Attendance Monitoring</h2>
            <span class="bg-purple-100 text-purple-700 text-sm font-bold px-4 py-1.5 rounded-lg">
                {{ $months[$selectedMonth] }} {{ $selectedYear }}
            </span>
        </div>

        {{-- Table --}}
        <div class="overflow-auto rounded-xl border border-gray-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-600 font-bold text-left">
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Check-In</th>
                        <th class="px-4 py-3">Check-out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($att->user->name ?? $att->student->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $att->user->name ?? $att->student->name ?? 'Pengguna' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ \Carbon\Carbon::parse($att->attendance_date)->format('d-M-Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $att->check_in_at?->format('H:i') ?? $att->time ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($att->check_out_at)
                                    <span class="text-gray-600">{{ $att->check_out_at->format('H:i') }}</span>
                                @else
                                    <span class="badge-pending">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada data absensi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Filter bawah --}}
        <div class="flex items-center gap-3 mt-4 flex-wrap">
            <form method="GET" action="{{ route('monthly-absence') }}" class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-500 font-semibold">Years</label>
                    <select name="year" class="filter-select" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-500 font-semibold">Month</label>
                    <select name="month" class="filter-select" onchange="this.form.submit()">
                        @foreach($months as $k => $m)
                            <option value="{{ $k }}" {{ $selectedMonth == $k ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Search</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search"
                           class="search-input" onchange="this.form.submit()">
                </div>
            </form>
        </div>

    </div>

    {{-- KANAN: Pending Permit --}}
    <div class="permit-panel rounded-2xl p-5 flex flex-col">
        <h2 class="text-base font-bold text-gray-700 mb-4">Pending Permit</h2>

        <div class="flex flex-col gap-3 flex-1">
            @forelse($pendingPermits as $permit)
                <div class="permit-card flex items-center gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr($permit['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-700 truncate">{{ $permit['name'] }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $permit['type'] }} - {{ $permit['date'] }}</p>
                    </div>
                    <form action="{{ route('monthly-absence.permit.approve', $permit['id']) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="approve-btn bg-approve-green text-white text-xs font-bold px-2.5 py-1.5 rounded-lg">
                            Approve
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-4">Tidak ada permit pending</p>
            @endforelse
        </div>

        <button onclick="document.getElementById('modalPermit').classList.remove('hidden')"
                class="mt-4 text-center text-sm text-purple-500 hover:text-purple-700 font-semibold hover:underline">
            View all permit ....
        </button>
    </div>

</div>

{{-- MODAL PENDING PERMIT --}}
<div id="modalPermit" class="modal-overlay hidden">
    <div class="modal-box">
        <h2>Pending Permit</h2>
        <button class="modal-close" onclick="document.getElementById('modalPermit').classList.add('hidden')">x</button>

        <div class="flex flex-col gap-3">
            @forelse($pendingPermits as $permit)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($permit['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-700">{{ $permit['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $permit['type'] }} - {{ $permit['date'] }}</p>
                    </div>
                    <form action="{{ route('monthly-absence.permit.approve', $permit['id']) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="approve-btn bg-approve-green text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                            Approve
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada permit pending</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.getElementById('modalPermit').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>

@endsection

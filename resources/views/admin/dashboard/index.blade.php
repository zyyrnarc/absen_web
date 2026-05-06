@extends('layouts.app')

@section('content')

{{-- STAT CARDS --}}
<div class="flex gap-4 mb-6 items-start">

    <div class="stat-card bg-card-purple text-white rounded-2xl px-6 py-4 flex-1 shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80 mb-1">Total Campus</p>
        <p class="text-4xl font-extrabold">{{ $totalCampus }}</p>
    </div>

    <div class="stat-card bg-card-pink text-white rounded-2xl px-6 py-4 flex-1 shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80 mb-1">Total Mahasiswa</p>
        <p class="text-4xl font-extrabold">{{ $totalMahasiswa }}</p>
    </div>

    <div class="stat-card bg-card-red text-white rounded-2xl px-6 py-4 flex-1 shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80 mb-1">Total Mentor</p>
        <p class="text-4xl font-extrabold">{{ $totalMentor }}</p>
    </div>

    <div class="notification-box flex-1 bg-white rounded-2xl p-3 shadow-md border border-purple-100">
        <div class="text-2xl mb-1">🔔</div>
        @foreach($notifications as $notif)
            <p class="text-xs text-gray-600 truncate py-0.5">{{ $notif }}</p>
        @endforeach
    </div>

</div>

{{-- MAIN ROW --}}
<div class="flex gap-5">

    {{-- KIRI: Chart + Table --}}
    <div class="flex-1 flex flex-col gap-5">

        {{-- Chart --}}
        <div class="bg-white rounded-2xl p-5 shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-700">Kehadiran Bulan</h2>
                <select name="bulan" class="month-select text-sm border rounded-lg px-3 py-1.5 focus:outline-none">
                    @foreach($months as $key => $month)
                        <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div class="chart-container flex items-end gap-4 h-36 px-4 pt-2 rounded-xl">
                @foreach($chartData as $day => $value)
                    <div class="chart-bar-wrap flex flex-col items-center flex-1 gap-1">
                        <div class="chart-bar rounded-t-full w-full"
                             style="height: {{ ($value / $chartMax) * 100 }}%; min-height: 8px;"></div>
                        <span class="text-xs text-gray-500">{{ $day }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Weekly Table --}}
        <div class="bg-white rounded-2xl p-5 shadow-md">
            <h2 class="text-sm font-bold inline-block bg-purple-100 text-purple-700 px-4 py-1.5 rounded-lg mb-3">
                Weekly Activity Today
            </h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-600 font-semibold text-left">
                        <th class="px-4 py-2.5">Day</th>
                        <th class="px-4 py-2.5">Mahasiswa</th>
                        <th class="px-4 py-2.5">Aktivty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($weeklyActivities as $activity)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2.5 capitalize text-gray-600">{{ $activity['day'] }}</td>
                            <td class="px-4 py-2.5 text-gray-700">{{ $activity['mahasiswa'] }}</td>
                            <td class="px-4 py-2.5 text-gray-700">{{ $activity['aktivty'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-gray-400">Tidak ada aktivitas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- KANAN: Pending Permits --}}
    <div class="w-72 bg-white rounded-2xl p-5 shadow-md">
        <h2 class="text-base font-bold text-gray-700 mb-4">Pending Permit</h2>
        <div class="flex flex-col gap-3">
            @forelse($pendingPermits as $permit)
                <div class="permit-card flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($permit['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-700 truncate">{{ $permit['name'] }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $permit['type'] }} - {{ $permit['date'] }}</p>
                    </div>
                    <form action="{{ route('permit.approve', $permit['id']) }}" method="POST">
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
        <a href="{{ route('monthly-absence') }}" class="mt-4 block text-center text-sm text-purple-500 hover:underline font-semibold">
            View all permit ....
        </a>
    </div>

</div>

@endsection

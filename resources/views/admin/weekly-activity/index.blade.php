@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-800">Weekly Activity</h1>
    <p class="text-sm text-gray-500 mt-1">For a week, this Interns are working hard!!</p>
</div>

{{-- FILTER ROW --}}
<div class="toolbar-row mb-4">

    {{-- Student selector --}}
    <form method="GET" action="{{ route('weekly-activity') }}" class="toolbar-form">
        <select name="student_id" class="student-select-btn" onchange="this.form.submit()">
            <option value="">👤 Semua Mahasiswa</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ $selectedStudent == $student->id ? 'selected' : '' }}>
                    {{ $student->name }}
                </option>
            @endforeach
        </select>

        {{-- Spacer --}}
        <div class="toolbar-spacer"></div>

        {{-- Hamburger icon --}}
        <div class="toolbar-icon">
            ☰
        </div>

        {{-- Month filter --}}
        <select name="month" class="filter-select" onchange="this.form.submit()">
            @foreach($months as $k => $m)
                <option value="{{ $k }}" {{ $selectedMonth == $k ? 'selected' : '' }}>{{ $m }} {{ $selectedYear }}</option>
            @endforeach
        </select>
    </form>

</div>

{{-- TABLE --}}
<div class="content-panel rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-gray-600 font-bold text-left bg-gray-50 border-b border-gray-100">
                <th class="px-6 py-4">Interns</th>
                <th class="px-6 py-4">Day</th>
                <th class="px-6 py-4">Times</th>
                <th class="px-6 py-4">Activity / Jobdesc</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($activity['name'], 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-700">{{ $activity['name'] }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 font-medium">{{ $activity['day'] }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $activity['times'] }}</td>
                    <td class="px-6 py-4">
                        <button class="btn-view">View Activity</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">Tidak ada aktivitas minggu ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

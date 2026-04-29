<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;

class ManageMajorController extends Controller
{
    public function index()
    {
        $search = request('search');
        $majors = Major::when($search, fn ($q) =>
                        $q->where('name', 'like', "%$search%")
                          ->orWhere('study_program', 'like', "%$search%"))
                    ->orderBy('id')
                    ->get();

        return view('admin.manage-major.index', compact('majors', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'study_program' => 'nullable|string|max:255',
        ]);

        Major::create($validated);

        return back()->with('success', 'Major berhasil ditambahkan!');
    }

    public function update(Request $request, Major $major)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'study_program' => 'nullable|string|max:255',
        ]);

        $major->update($validated);

        return back()->with('success', 'Major berhasil diupdate!');
    }

    public function destroy(Major $major)
    {
        $major->delete();
        return back()->with('success', 'Major berhasil dihapus!');
    }
}

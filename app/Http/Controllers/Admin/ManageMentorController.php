<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ManageMentorController extends Controller
{
    public function index()
    {
        $search = request('search');

        $mentors = Mentor::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            })
            ->orderBy('id')
            ->get();

        return view('admin.manage-mentor.index', compact('mentors', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        Mentor::create($validated);

        return back()->with('success', 'Mentor berhasil ditambahkan!');
    }

    public function update(Request $request, Mentor $mentor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $mentor->update($validated);

        return back()->with('success', 'Mentor berhasil diupdate!');
    }

    public function destroy(Mentor $mentor)
    {
        try {
            $mentor->delete();
        } catch (QueryException $exception) {
            return back()->withErrors([
                'mentor' => 'Mentor tidak bisa dihapus karena masih dipakai data lain.',
            ]);
        }

        return back()->with('success', 'Mentor berhasil dihapus!');
    }
}

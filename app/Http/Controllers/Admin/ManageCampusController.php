<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ManageCampusController extends Controller
{
    public function index()
    {
        $search = request('search');

        $campuses = Campus::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('id')
            ->get();

        return view('admin.manage-campus.index', compact('campuses', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Campus::create($validated);

        return back()->with('success', 'Campus berhasil ditambahkan!');
    }

    public function update(Request $request, Campus $campus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $campus->update($validated);

        return back()->with('success', 'Campus berhasil diupdate!');
    }

    public function destroy(Campus $campus)
    {
        try {
            $campus->delete();
        } catch (QueryException $exception) {
            return back()->withErrors([
                'campus' => 'Campus tidak bisa dihapus karena masih dipakai data lain.',
            ]);
        }

        return back()->with('success', 'Campus berhasil dihapus!');
    }
}

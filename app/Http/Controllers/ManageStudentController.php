<?php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class ManageStudentController extends Controller
{
    public function index()
    {
        $search   = request('search');
        $students = Student::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%")
                                         ->orWhere('nim', 'like', "%$search%"))
            ->orderBy('name')
            ->get();

        return view('admin.manage-student.index', compact('students', 'search'));
    }

    public function create()
    {
        return redirect()->route('manage-student');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim',
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus'        => 'required|string|max:255',
            'mentor'        => 'nullable|string|max:255',
            'email'         => 'required|email|unique:students,email',
            'username'      => 'nullable|string|unique:students,username',
            'password'      => 'required|string|max:255',
        ]);

        Student::create($request->all());

        return redirect()
            ->route('manage-student')
            ->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function edit(Student $student)
    {
        return view('admin.manage-student.edit.index', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim,' . $student->id,
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus'        => 'required|string|max:255',
            'mentor'        => 'nullable|string|max:255',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'username'      => 'nullable|string|unique:students,username,' . $student->id,
            'password'      => 'nullable|string|max:255',
            'status'        => 'in:active,inactive',
        ]);

        $payload = $request->all();

        if (! $request->filled('password')) {
            unset($payload['password']);
        }

        $student->update($payload);

        return redirect()
            ->route('manage-student')
            ->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return back()->with('success', 'Mahasiswa berhasil dihapus!');
    }
}

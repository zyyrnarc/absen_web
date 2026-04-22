<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Campus;
use App\Models\Mentor;
use Illuminate\Http\Request;

class ManageStudentController extends Controller
{
    public function index()
    {
        $search   = request('search');
        $students = Student::with(['campus', 'mentor'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%")
                                         ->orWhere('nim', 'like', "%$search%"))
            ->orderBy('name')
            ->get();

        $campuses = Campus::orderBy('name')->get();
        $mentors  = Mentor::orderBy('name')->get();

        return view('admin.manage-student.index', compact('students', 'campuses', 'mentors', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim',
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus_id'     => 'required|exists:campuses,id',
            'mentor_id'     => 'nullable|exists:mentors,id',
            'email'         => 'required|email|unique:students,email',
            'username'      => 'nullable|string|unique:students,username',
        ]);

        Student::create($request->all());

        return back()->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim,' . $student->id,
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus_id'     => 'required|exists:campuses,id',
            'mentor_id'     => 'nullable|exists:mentors,id',
            'email'         => 'required|email|unique:students,email,' . $student->id,
            'username'      => 'nullable|string|unique:students,username,' . $student->id,
            'status'        => 'in:active,inactive',
        ]);

        $student->update($request->all());

        return back()->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return back()->with('success', 'Mahasiswa berhasil dihapus!');
    }
}

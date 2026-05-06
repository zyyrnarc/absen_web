<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManageStudentController extends Controller
{
    public function index()
    {
        $search   = request('search');
        $students = User::query()
            ->with('profile')
            ->where('role', 'intern')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery
                                ->where('student_id', 'like', "%{$search}%")
                                ->orWhere('major', 'like', "%{$search}%")
                                ->orWhere('institution_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->transformStudent($user));

        return view('admin.manage-student.index', compact('students', 'search'));
    }

    public function create()
    {
        return redirect()->route('manage-student');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|max:255|unique:intern_profiles,student_id',
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus'        => 'required|string|max:255',
            'mentor'        => 'nullable|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'username'      => 'nullable|string|max:255|unique:users,username',
            'password'      => 'required|string|min:6|max:255',
            'avatar'        => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'] ?? null,
                'password' => $validated['password'],
                'role' => 'intern',
                'is_active' => true,
                'avatar_path' => $request->hasFile('avatar')
                    ? $request->file('avatar')->store('student-avatars', 'public')
                    : null,
            ]);

            $user->profile()->create([
                'student_id' => $validated['nim'],
                'institution_name' => $validated['campus'],
                'major' => $validated['major'],
                'study_program' => $validated['study_program'] ?? null,
                'supervisor_name' => $validated['mentor'] ?? null,
                'status' => 'active',
            ]);
        });

        return redirect()
            ->route('manage-student')
            ->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    public function edit(int $student)
    {
        $student = $this->transformStudent($this->findInternOrFail($student));

        return view('admin.manage-student.edit.index', compact('student'));
    }

    public function update(Request $request, int $student)
    {
        $user = $this->findInternOrFail($student);
        $profileId = $user->profile?->id;

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => ['required', 'string', 'max:255', Rule::unique('intern_profiles', 'student_id')->ignore($profileId)],
            'major'         => 'required|string',
            'study_program' => 'nullable|string',
            'campus'        => 'required|string|max:255',
            'mentor'        => 'nullable|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'username'      => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password'      => 'nullable|string|min:6|max:255',
            'status'        => 'in:active,inactive',
            'avatar'        => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, $user, $validated): void {
            $userPayload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'] ?? null,
                'is_active' => ($validated['status'] ?? 'active') === 'active',
            ];

            if (! empty($validated['password'])) {
                $userPayload['password'] = $validated['password'];
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $userPayload['avatar_path'] = $request->file('avatar')->store('student-avatars', 'public');
            }

            $user->update($userPayload);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => $validated['nim'],
                    'institution_name' => $validated['campus'],
                    'major' => $validated['major'],
                    'study_program' => $validated['study_program'] ?? null,
                    'supervisor_name' => $validated['mentor'] ?? null,
                    'status' => $validated['status'] ?? 'active',
                ]
            );
        });

        return redirect()
            ->route('manage-student')
            ->with('success', 'Data mahasiswa berhasil diupdate!');
    }

    public function destroy(int $student)
    {
        $user = $this->findInternOrFail($student);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return back()->with('success', 'Mahasiswa berhasil dihapus!');
    }

    private function findInternOrFail(int $id): User
    {
        return User::query()
            ->with('profile')
            ->where('role', 'intern')
            ->findOrFail($id);
    }

    private function transformStudent(User $user): object
    {
        return (object) [
            'id' => $user->id,
            'name' => $user->name,
            'nim' => $user->profile?->student_id,
            'major' => $user->profile?->major,
            'study_program' => $user->profile?->study_program,
            'campus' => $user->profile?->institution_name,
            'mentor' => $user->profile?->supervisor_name,
            'email' => $user->email,
            'username' => $user->username,
            'password' => 'Tersimpan aman',
            'status' => $user->is_active ? 'active' : 'inactive',
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
        ];
    }
}

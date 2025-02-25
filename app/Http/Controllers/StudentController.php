<?php

namespace App\Http\Controllers;

use App\Http\Resources\SectionResource;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Resources\ClassResource;
use App\Http\Resources\StudentResource;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $studentQuery = Student::search($request);
        $classes = ClassResource::collection(Classes::all());
        $sections = SectionResource::collection(Section::all());
        $message = session('success');
        return inertia('Student/Index', [
            'students' => StudentResource::collection(
                $studentQuery->paginate(request('perPage') ?? 5)
            ),
            'classes' => $classes,
            'sections' => $sections,
            'message' => $message,
            'totalPages' => $studentQuery->paginate(5)->total(),
            'currentPage' => $studentQuery->paginate(5)->currentPage(),
            'search' => request('search') ?? '',
            'perPage' => request('perPage') ?? '5',
            'sort' => [
                'column' => $request->input('sort'),
                'direction' => $request->input('direction')
            ]
        ]);
    }

    protected function applySearch(Builder $query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function create()
    {
        $classes = ClassResource::collection(Classes::all());

        return inertia('Student/Create', [
            'classes' => $classes
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        $validatedData = $request->validated();
        // Store the image if it exists
        if ($request->hasFile('image')) {
            $validatedData['image'] = Storage::url($request->file('image')->store('public/images'));
        }

        Student::create($validatedData);
        session()->flash('success', 'Student created successfully!');
        return redirect()->route('students.index');
    }

    public function edit(Student $student)
    {
        $classes = ClassResource::collection(Classes::all());

        return inertia('Student/Edit', [
            'student' => StudentResource::make($student),
            'classes' => $classes
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validatedData = $request->validated();
        if ($request->hasFile('image')) {
            $validatedData['image'] = Storage::url($request->file('image')->store('public/images'));
        }
        $student->update($validatedData);
        session()->flash('success', 'Student updated successfully!');
        return redirect()->route('students.index');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        session()->flash('success', 'Student deleted successfully!');
        return redirect()->route('students.index');
    }

    public function toggleStatus($id)
    {
        $student = Student::findOrFail($id);
        $student->status = !$student->status; // Toggle status
        $student->save();
        $status = $student->status ? 'Active' : 'Inactive';
        session()->flash('success', "Student status updated successfully.");

        return response()->json([
            'success' => true,
            'message' => "Student status updated to {$status} successfully.",
            'status' => $status,
            'redirect' => route('students.index')
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassResource;
use App\Http\Resources\SectionResource;
use App\Http\Resources\StudentResource;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function toggleStatus($id)
    {
        $student = Student::findOrFail($id);
        $student->status = !$student->status; // Toggle status
        $student->save();
        $status = $student->status ? 'Active' : 'Inactive';
        return response()->json([
            'success' => true,
            'message' => "Student status updated to {$status} successfully.",
            'status' => $status,
            'redirect' => route('students.index')
        ]);
    }

    public function index(Request $request)
    {
        $studentQuery = Student::query();
        return response()->json([
            'data' => StudentResource::collection(
                $studentQuery->paginate(5)
            ),
        ]);
    }
}

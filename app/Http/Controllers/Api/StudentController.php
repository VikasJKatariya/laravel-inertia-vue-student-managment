<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}

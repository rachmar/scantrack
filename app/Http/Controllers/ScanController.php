<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    /**
     * Display the specified student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // Validate the request to ensure 'code' is present
        $request->validate([
            'code' => 'required|string', // Adjust validation as needed
        ]);

        // Search for the student using the provided code
        $student = Student::where('card_id', $request->code)->first();

        if ($student) {
            return response()->json($student);  // Return student data if found
        } else {
            return response()->json([
                'message' => 'Student / Visitor ID invalid. <br/> Please check the ID and try again.'
            ], 404);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Visitor;
use Illuminate\Http\Request;

class ScanController extends Controller
{   
    /**
     * Display the specified student or visitor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view("public.scan");
    }

    /**
     * Display the specified student or visitor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // Validate the request to ensure 'code' is present
        $request->validate([
            'code' => 'required|string',
        ]);

        // Search for the student or visitor using the provided code
        $student = Student::where('card_id', $request->code)->first();

        if ($student) {
            return response()->json($student);
        }

        $visitor = Visitor::where('card_id', $request->code)->first();

        if ($visitor) {
            return response()->json($visitor);
        }

        // If neither is found, return an error message
        return response()->json([
            'message' => 'Scan ID not found, Please check the ID and try again.'
        ], 404);
    }

}

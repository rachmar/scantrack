<?php

namespace App\Http\Controllers;

use App\Models\AbsenceRecord;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;

class AbsenceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $semesters = Semester::get();

        $departments = Department::get();

        $departmentId = $request->input('department'); // Get department ID from request (optional)
        $courseId = $request->input('course'); // Get department ID from request (optional)

        $records = AbsenceRecord::with(['student.course.department', 'semester'])
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->whereHas('student.course.department', function ($q) use ($departmentId) {
                    $q->where('id', $departmentId);
                });
            })
            ->when($courseId, function ($query) use ($courseId) {
                $query->whereHas('student.course', function ($q) use ($courseId) {
                    $q->where('id', $courseId);
                });
            })
            ->orderBy('student_id')
            ->orderBy('semester_id')
            ->orderBy('date')
            ->get()
            ->groupBy(['student_id', 'semester_id']);

        return view("admin.absences.index", compact('records', 'semesters', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        AbsenceRecord::where('student_id', $id)
            ->where('clear', false)
            ->update([
                'clear' => true
            ]);

        return redirect()->route('absences.index');
    }
}

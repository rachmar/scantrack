<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'level',
        'active'
    ];

    protected $cast = [
        'active' => 'boolean',
    ];

    /**
     * Get semesters based on course slug
     */
    public static function getSemesterByCourse(Course $course)
    {
        $level = in_array($course->slug, ['CDC', 'JHS', 'SHS']) ? 'basic' : 'college';
        return self::where('level', $level)->get();
    }
    
    public function getActiveSemesterByStudent(Student $student)
    {
        $level = $student->isBasicEducation() ? 'basic' : 'college';
        return self::where('level', $level)->where('active')->get();
    }
    
}

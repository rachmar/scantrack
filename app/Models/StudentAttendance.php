<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
    ];

    /**
     * Get the student associated with this attendance record.
     */
    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');
    }
}

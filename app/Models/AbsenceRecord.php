<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceRecord extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'student_id',
        'semester_id',
        'date',
        'clear'
    ];

    protected $cast = [
        'clear' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

}

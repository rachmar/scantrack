<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'card_id',
        'course_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'image',
        'schedule'
    ];

    protected $casts = [
        'schedule' => 'array'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function fullName()
    {
        return $this->first_name.' '.$this->last_name;
    }
    public function showSchedule()
    {
        return implode(' ', $this->schedule);
    }
    public function isBasicEducation()
    {   
        return in_array($this->course->slug, ['CDC', 'JHS', 'SHS']);
    }

    public function currentSchedule()
    {
        return $this->schedule ?? [];
    }

    public function absenceRecord()
    {
        return $this->hasMany(AbsenceRecord::class)->where('clear', true)
            ->pluck('id', 'date')->toArray() ?? [];
    }

    /**
     * Get the masked email.
     *
     * @return string
     */
    public function getEmailAttribute($value)
    {
        $parts = explode('@', $value);
        $masked = substr($parts[0], 0, 2) . '****';
        return $masked . '@' . $parts[1];
    }

    /**
     * Get the masked phone number.
     *
     * @return string
     */
    public function getPhoneAttribute($value)
    {
        return substr($value, 0, 3) . '****' . substr($value, -3);
    }
}

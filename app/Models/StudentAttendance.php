<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'section_subject_id',
        'date',
        'status',
        'remarks',
        'teacher_key',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

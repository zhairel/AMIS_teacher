<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['user_id', 'enrollment_applicant_id', 'student_number', 'grade_level', 'school_year', 'section'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicant()
    {
        return $this->belongsTo(EnrollmentApplicant::class, 'enrollment_applicant_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function approvedGrades()
    {
        return $this->hasMany(Grade::class)->whereIn('status', ['approved', 'published']);
    }
}

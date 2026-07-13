<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradebookAssessment extends Model
{
    protected $fillable = ['section_subject_id', 'subject_id', 'teacher_key', 'title', 'grading_period', 'max_score', 'assessment_date'];

    protected $casts = ['assessment_date' => 'date'];

    public function scores()
    {
        return $this->hasMany(GradebookScore::class, 'assessment_id');
    }
}

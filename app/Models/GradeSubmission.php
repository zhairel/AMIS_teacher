<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSubmission extends Model
{
    protected $fillable = ['section_subject_id', 'grading_period', 'teacher_key', 'status', 'submitted_at', 'reviewed_at', 'reviewed_by', 'review_notes', 'locked_at'];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'locked_at' => 'datetime'];
}

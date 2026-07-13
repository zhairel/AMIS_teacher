<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradebookScore extends Model
{
    protected $fillable = ['assessment_id', 'student_id', 'score', 'status', 'teacher_key'];

    protected $casts = ['score' => 'decimal:2'];
}

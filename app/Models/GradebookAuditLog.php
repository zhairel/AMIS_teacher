<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradebookAuditLog extends Model
{
    protected $fillable = ['section_subject_id', 'teacher_key', 'action', 'record_type', 'record_id', 'metadata'];

    protected $casts = ['metadata' => 'array'];
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('student_attendances')) {
            Schema::create('student_attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->unsignedBigInteger('section_subject_id')->nullable()->index();
                $table->date('date')->index();
                $table->string('status', 30)->default('Present');
                $table->string('remarks')->nullable();
                $table->string('teacher_key', 160)->index();
                $table->timestamps();

                $table->unique(['student_id', 'date', 'section_subject_id'], 'student_attendance_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};

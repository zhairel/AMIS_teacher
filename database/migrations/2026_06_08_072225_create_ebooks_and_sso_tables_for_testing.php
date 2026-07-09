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
        if (!Schema::hasTable('ebooks')) {
            Schema::create('ebooks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('grade_level', 100)->nullable();
                $table->string('file_path');
                $table->string('cover_image_path')->nullable();
                $table->boolean('is_downloadable')->default(false);
                $table->string('status', 50)->default('published');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index(['status', 'grade_level']);
                $table->index('grade_level', 'ebooks_grade_level_index');
                $table->index('status', 'ebooks_status_index');
            });
        }

        if (!Schema::hasTable('ebook_access_logs')) {
            Schema::create('ebook_access_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ebook_id')->constrained('ebooks')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('action');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['ebook_id', 'user_id', 'action']);
            });
        }

        if (!Schema::hasTable('sso_tokens')) {
            Schema::create('sso_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token', 64)->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('source_portal', 50)->default('amis_admin');
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();

                $table->index(['token', 'expires_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_tokens');
        Schema::dropIfExists('ebook_access_logs');
        Schema::dropIfExists('ebooks');
    }
};

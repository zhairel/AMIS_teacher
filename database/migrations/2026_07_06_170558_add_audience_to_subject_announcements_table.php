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
        Schema::table('subject_announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('subject_announcements', 'audience')) {
                $table->string('audience', 60)->default('Subject')->index()->after('body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_announcements', function (Blueprint $table) {
            if (Schema::hasColumn('subject_announcements', 'audience')) {
                $table->dropColumn('audience');
            }
        });
    }
};

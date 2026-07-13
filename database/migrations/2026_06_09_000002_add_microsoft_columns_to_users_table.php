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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'microsoft_id')) {
                $column = $table->string('microsoft_id')->nullable();
                if (Schema::hasColumn('users', 'firebase_linked_at')) {
                    $column->after('firebase_linked_at');
                }
            }
            if (! Schema::hasColumn('users', 'microsoft_email')) {
                $table->string('microsoft_email')->nullable()->after('microsoft_id');
            }
            if (! Schema::hasColumn('users', 'microsoft_linked_at')) {
                $table->timestamp('microsoft_linked_at')->nullable()->after('microsoft_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['microsoft_id', 'microsoft_email', 'microsoft_linked_at']);
        });
    }
};

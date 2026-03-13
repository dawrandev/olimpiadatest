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
        Schema::table('groups', function (Blueprint $table) {
            // Drop the old unique constraint on name only
            $table->dropUnique(['name']);

            // Add new unique constraint on name + faculty_id
            $table->unique(['name', 'faculty_id'], 'groups_name_faculty_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique('groups_name_faculty_unique');
            $table->unique('name');
        });
    }
};

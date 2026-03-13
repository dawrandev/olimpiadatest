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
        Schema::table('questions', function (Blueprint $table) {
            $table->string('type', 50)->default('single_choice')->after('topic_id');
            $table->string('left_items_title', 255)->nullable()->after('type');
            $table->string('right_items_title', 255)->nullable()->after('left_items_title');
        });

        Schema::create('question_matching_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->enum('side', ['left', 'right']);
            $table->string('key', 10);
            $table->text('text');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index(['question_id', 'side']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_matching_pairs');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

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
        Schema::create('user_chapter_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source_type');
            $table->string('source_id');
            $table->string('chapter_id');
            $table->timestamps();

            $table->unique(['user_id', 'source_type', 'source_id', 'chapter_id'], 'user_chapter_read_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_chapter_reads');
    }
};

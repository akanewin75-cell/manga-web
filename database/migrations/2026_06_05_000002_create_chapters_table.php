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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->onDelete('cascade');
            $table->string('source_chapter_id')->nullable()->index();
            $table->string('chapter_num');
            $table->string('title')->nullable();
            $table->boolean('is_local')->default(false);
            $table->string('local_path')->nullable();
            $table->timestamps();
            
            $table->unique(['manga_id', 'chapter_num']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};

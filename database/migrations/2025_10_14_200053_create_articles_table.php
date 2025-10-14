<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url', 1000)->unique();
            $table->text('image_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

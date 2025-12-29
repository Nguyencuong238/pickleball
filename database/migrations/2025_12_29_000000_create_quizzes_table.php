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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tên câu hỏi
            $table->text('description')->nullable(); // Mô tả
            $table->text('question'); // Nội dung câu hỏi
            $table->json('options'); // Các lựa chọn dạng JSON: {"a": "...", "b": "...", "c": "...", "d": "..."}
            $table->string('correct_answer'); // Đáp án đúng (a, b, c, d)
            $table->text('explanation')->nullable(); // Giải thích đáp án
            $table->string('category')->nullable(); // Danh mục
            $table->integer('difficulty')->default(1); // Độ khó (1-3)
            $table->boolean('is_active')->default(true); // Kích hoạt/vô hiệu hóa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

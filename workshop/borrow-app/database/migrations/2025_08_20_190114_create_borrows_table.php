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
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('borrowed'); // borrowed|returned|overdue
            $table->string('note')->nullable();
            $table->timestamps();

            // ดัชนีที่ช่วยงานรายงาน/ค้นหา
            $table->index(['borrowed_at']);
            $table->index(['due_at']);
            $table->index(['returned_at']);
            $table->index(['user_id','equipment_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};

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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_category_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();     // รหัสอุปกรณ์
            $table->string('name');               // ชื่ออุปกรณ์
            $table->unsignedInteger('stock')->default(0);
            $table->string('photo_path')->nullable(); // เก็บพาธรูป (storage)
            $table->timestamps();

            $table->index('stock'); // ช่วย filter ในตาราง/รายงาน
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

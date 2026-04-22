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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('status_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('lokasi', 150);
            $table->string('gambar')->nullable();

            $table->timestamps();

            // Index untuk performa
            $table->index('judul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

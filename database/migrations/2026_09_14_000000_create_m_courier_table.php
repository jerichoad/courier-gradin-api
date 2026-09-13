<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_courier', function (Blueprint $table) {
            $table->id('courier_id');
            $table->string('courier_code', 20)->unique();
            $table->string('courier_name', 150);
            $table->string('courier_phone', 30)->nullable();
            $table->string('courier_email', 100)->nullable();
            $table->unsignedTinyInteger('courier_level');
            $table->text('courier_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('courier_name');
            $table->index('courier_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_courier');
    }
};

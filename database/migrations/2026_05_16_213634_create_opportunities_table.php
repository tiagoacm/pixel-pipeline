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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('segment');
            $table->string('commercial_contact');
            $table->string('commercial_phone', 30);
            $table->string('technical_contact');
            $table->string('technical_phone', 30);
            $table->text('opportunity_description');
            $table->foreignId('responsible_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};

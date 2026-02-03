<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playbooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('angle');
            $table->text('dm_template');
            $table->text('email_template');
            $table->json('constraints')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playbooks');
    }
};

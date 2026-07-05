<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_folder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();
            $table->string('permission')->default('view');
            $table->timestamps();
            $table->unique(['group_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_folder');
    }
};

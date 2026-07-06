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
        Schema::create('google_drive_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('folder_id'); // ID da pasta do Google Drive extraído do link
            $table->string('folder_name'); // Nome da pasta
            $table->text('folder_link'); // Link completo da pasta
            $table->boolean('is_active')->default(true); // Ativar/Desativar
            $table->string('added_by')->nullable(); // Email de quem adicionou
            $table->timestamps();

            $table->unique(['workspace_id', 'folder_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_drive_folders');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('user_id')->nullable();
            $table->json('media_ids');
            $table->json('platforms');
            $table->json('days');
            $table->json('times');
            $table->string('timezone');
            $table->text('prompt');
            $table->string('status')->default('pending');
            $table->unsignedSmallInteger('total_posts')->default(0);
            $table->unsignedSmallInteger('created_posts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_schedules');
    }
};

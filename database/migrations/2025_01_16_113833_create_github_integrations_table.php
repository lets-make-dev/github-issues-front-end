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
        Schema::create('github_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_from');
            $table->unsignedBigInteger('account_to');
            $table->unsignedBigInteger('repo_from');
            $table->unsignedBigInteger('repo_to');
            $table->text('labels');
            $table->dateTime('last_sync_at');
            $table->unsignedBigInteger('project_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_integrations');
    }
};

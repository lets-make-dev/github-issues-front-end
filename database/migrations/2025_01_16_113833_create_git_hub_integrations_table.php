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
        Schema::create('git_hub_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_from');
            $table->foreignId('account_to');
            $table->string('repo_from');
            $table->string('repo_to');
            $table->text('labels');
            $table->dateTime('last_sync_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_hub_integrations');
    }
};

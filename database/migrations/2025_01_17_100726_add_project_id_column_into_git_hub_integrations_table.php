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
        Schema::table('git_hub_integrations', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('git_hub_integrations', function (Blueprint $table) {
            $table->dropColumn('project_id')->nullable();
        });
    }
};

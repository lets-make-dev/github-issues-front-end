<?php

use App\Enums\GithubIssueState;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_issue_id')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('repository_id');
            $table->unsignedBigInteger('project_id');
            $table->string('title');
            $table->text('body');
            $table->text('labels')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->string('creator')->nullable();
            $table->string('status')->default(GithubIssueState::Open->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};

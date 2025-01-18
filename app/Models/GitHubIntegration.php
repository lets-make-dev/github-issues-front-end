<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GitHubIntegration extends Model
{
    protected $fillable = [
        'account_from',
        'account_to',
        'repo_from',
        'repo_to',
        'labels',
        'last_sync_at',
        'project_id'
    ];
}

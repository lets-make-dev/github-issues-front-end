<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Repository;
use Illuminate\Database\Eloquent\Model;

class GitHubIntegration extends Model
{
    protected $table = 'github_integrations';

    protected $fillable = [
        'account_from',
        'account_to',
        'repo_from',
        'repo_to',
        'labels',
        'last_sync_at',
        'project_id'
    ];

    public function accountFrom()
    {
        return $this->belongsTo(Account::class, 'account_from');
    }

    public function accountTo()
    {
        return $this->belongsTo(Account::class, 'account_to');
    }

    public function repoFrom()
    {
        return $this->belongsTo(Repository::class, 'repo_from');
    }

    public function repoTo()
    {
        return $this->belongsTo(Repository::class, 'repo_to');
    }

}

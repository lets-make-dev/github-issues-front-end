<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'title',
        'body',
        'labels',
        'repository_name',
        'github_issue_id',
        'project_id',
        'account_id'
    ];
}

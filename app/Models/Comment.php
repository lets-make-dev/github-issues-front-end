<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['content', 'project_id', 'repository_id', 'issue_number', 'github_comment_id', 'issue_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    // public function scopeByIssueAndProject(Builder $query, $issueNumber, $projectId, $repositoryId)
    // {
    //     return $query->where('issue_number', $issueNumber)
    //         ->where('project_id', $projectId)
    //         ->where('repository_id', $repositoryId);
    // }
}

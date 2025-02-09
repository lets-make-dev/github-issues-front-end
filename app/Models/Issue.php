<?php

namespace App\Models;

use App\Models\RepoLabel;
use App\Models\IssueSynced;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Issue extends Model
{
    protected $fillable = [
        'title',
        'body',
        'labels',
        'repository_id',
        'project_id',
        'account_id',
        'creator',
        'is_synced',
        'status',
        'github_issue_id',
        'issue_number'

    ];

    public function syncedIssues()
    {
        return $this->belongsToMany(
            Issue::class,
            IssueSynced::class,
            'from_issue',
            'to_issue'
        );
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    protected function labels(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => RepoLabel::whereIn('id', json_decode($value))->get(['id', 'name', 'color'])->toArray(),
        );
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

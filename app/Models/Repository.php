<?php

namespace App\Models;

use App\Models\RepoLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'labels',
        'account_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function scopeCheckProject($query, $projectId)
    {
        return $query->whereHas('projects', function ($query) use ($projectId) {
            $query->where('projects.id', $projectId);
        });
    }

    public function labels()
    {
        return $this->hasMany(RepoLabel::class);
    }
}

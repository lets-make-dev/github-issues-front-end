<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'visibility'];

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function repositories()
    {
        return $this->belongsToMany(Repository::class);
    }

    public function gitHubIntegrations()
    {
        return $this->hasMany(GitHubIntegration::class, 'project_id', 'id');
    }
}

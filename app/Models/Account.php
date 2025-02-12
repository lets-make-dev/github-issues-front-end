<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'github_token',
        'project_id',
        'name',
    ];

    public function repositories()
    {
        return $this->hasMany(Repository::class);
    }

    public function syncedAccounts()
    {
        return $this->hasManyThrough(
            Account::class,
            GitHubIntegration::class,
            'account_from',
            'id',
            'id',
            'account_to'
        );
    }
}

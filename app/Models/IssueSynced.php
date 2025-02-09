<?php

namespace App\Models;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Model;

class IssueSynced extends Model
{
    protected $table = 'issues_synced';

    protected $fillable = [
        'from_issue',
        'to_issue',
    ];

}

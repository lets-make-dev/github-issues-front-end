<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepoLabel extends Model
{
    protected $table = 'repository_labels';
    protected $fillable = [ 'repository_id', 'label_id', 'name', 'color', 'description' ];
}

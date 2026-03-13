<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}

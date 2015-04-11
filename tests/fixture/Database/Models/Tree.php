<?php

namespace FHTeam\LaravelValidator\Tests\Fixture\Database\Models;

use Eloquent;

class Tree extends Eloquent
{

    public $timestamps = false;

    // MASS ASSIGNMENT -------------------------------------------------------
    // define which attributes are mass assignable (for security)
    // we only want these 3 attributes able to be filled
    protected $fillable = ['type', 'age', 'bear_id'];

    // DEFINE RELATIONSHIPS --------------------------------------------------
    public function bear()
    {
        return $this->belongsTo(Bear::class);
    }
}
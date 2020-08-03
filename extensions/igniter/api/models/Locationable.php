<?php

namespace Igniter\Api\Models;

use Model;

/**
 * locationable Model
 */
class Locationable extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'locationables';

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    public $timestamps = TRUE;

    /**
     * @var array Relations
     */
    public $relation = [
        'hasOne' => [],
        'hasMany' => [],
        'belongsTo' => [],
        'belongsToMany' => [],
        'morphTo' => [],
        'morphOne' => [],
        'morphMany' => [],
    ];
}

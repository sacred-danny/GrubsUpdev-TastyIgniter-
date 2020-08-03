<?php

namespace Igniter\Api\Models;

use Model;

/**
 * CustomerPush Model
 */
class CustomerPush extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'customer_push';

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

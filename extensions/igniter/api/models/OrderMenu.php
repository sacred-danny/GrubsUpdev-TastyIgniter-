<?php

namespace Igniter\Api\Models;

use Model;

/**
 * OrderMenu Model
 */
class OrderMenu extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'order_menus';

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

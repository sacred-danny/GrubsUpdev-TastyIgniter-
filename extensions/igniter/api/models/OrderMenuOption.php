<?php

namespace Igniter\Api\Models;

use Model;

/**
 * OrderMenuOption Model
 */
class OrderMenuOption extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'order_menu_options';

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

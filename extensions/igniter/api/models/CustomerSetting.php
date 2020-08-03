<?php

namespace Igniter\Api\Models;

use Model;

/**
 * CustomerSetting Model
 */
class CustomerSetting extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'customer_settings';

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

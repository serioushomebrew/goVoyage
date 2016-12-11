<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AirPort extends Model
{
    /**
     * The database table name
     *
     * @var string
     */
    protected $table = 'airports';

    /**
     * The attributes are mass assignable
     */
    protected $fillable = [
        'code',
        'name',
        'city',
        'country_code',
        'country_name',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be mutated to dates
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * Indicates if the model should be timestamped
     */
    public $timestamps = false;

    /**
     * Boot the model
     */
    public static function boot()
    {
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}

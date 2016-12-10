<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CacheWeather extends Model
{
    /**
     * The database table name
     *
     * @var string
     */
    protected $table = 'cache_weathers';

    /**
     * The attributes are mass assignable
     */
    protected $fillable = [
        'origin_code',
        'temp',
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

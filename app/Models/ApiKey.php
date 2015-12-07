<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

class ApiKey extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'api_keys';

    /**
     * The model's default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'scope' => ['user'],
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'scope' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id',
        'scope',
    ];

    /**
     * Create a new API key.
     *
     * @param $attributes
     * @return ApiKey
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random API key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        if (empty($this->api_key)) {
            $this->api_key = str_random(40);
        }
    }

    /**
     * Mutator for 'app_id' field.
     */
    public function setAppIdAttribute($app_id)
    {
        $this->attributes['app_id'] = snake_case(str_replace(' ', '', $app_id));
    }

    /**
     * Getter for 'scope' field.
     */
    public function getScopeAttribute()
    {
        $scope = $this->attributes['scope'];
        if (is_string($scope)) {
            $scope = json_decode($scope);
        }

        return ! empty($scope) ? $scope : ['user'];
    }
}

<?php namespace Northstar\Models;

use Jenssegers\Mongodb\Model as Eloquent;
use Validator;

class Campaign extends Eloquent
{

    /**
     * Guarded attributes
     */
    protected $guarded = array('_id');

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'reportback_id' => 'integer',
        'signup_id' => 'integer',
        'signup_group' => 'integer',
    ];

    /**
     * The attributes which should be stored as MongoDate objects.
     * @see https://github.com/jenssegers/laravel-mongodb#dates
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Setting default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'drupal_id' => null,
        'reportback_id' => null,
        'signup_group' => null,
        'signup_id' => null,
        'signup_source' => null,
    ];

    /**
     * For all Campaign attributes not hidden, where keys are unset, set those
     * value to null.
     *
     * @param $campaign User campaign activity data
     */
    public static function populateAllAttributes(&$campaign)
    {
        $tmp = new Campaign();

        $attrs_not_hidden = array_diff($tmp->getAttributes(), $tmp->getHidden());

        foreach ($attrs_not_hidden as $key => $value) {
            if ($key == 'signup_group') {
                $default_value = $campaign->signup_id;
            } else {
                $default_value = null;
            }

            $campaign->$key = isset($campaign->$key) ? $campaign->$key : $default_value;
        }
    }

}

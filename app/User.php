<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Overtrue\LaravelFollow\Traits\CanBeFollowed;
use Overtrue\LaravelFollow\Traits\CanFollow;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
//use ScoutElastic\Searchable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    use CanFollow, CanBeFollowed;
//    use Searchable;

    protected $indexConfigurator = UserConfigurator::class;

    protected $searchRules = [
        //
    ];

    // Here you can specify a mapping for model fields
    protected $mapping = [
        'properties' => [
            'username' => [
                'type' => 'text',
                // Also you can configure multi-fields, more details you can find here https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ]
                ]
            ],
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'firstname' => 'John',
        'lastname' => 'Doe',
    ];

    /**
     * The model's default rules.
     *
     * @var array
     */
    public static $rules=[
        'username' => 'required|unique:users',
        'email'=>'required|email|unique:users',
        'password' => 'required|min:6',
        'firstname' => '',
        'lastname' => '',
        'mobile' => 'nullable|unique:users'
    ];

    /**
     * The model's default update rules.
     *
     * @return array
     * @var array
     */

    public function updateRules()
    {
        return [
            'username' => 'sometimes|required|min:5|unique:users,username,'.$this->id,
            'email'=>'sometimes|required|email|unique:users,email,'.$this->id,
            'password' => 'sometimes|required|min:6',
            'firstname' => '',
            'lastname' => '',
            'mobile' => 'nullable|unique:users,mobile,'.$this->id
        ];
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'users_index';
    }


    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        // Customize array...

        return $this->toArray();
    }

    public function image()
    {
        return $this->morphOne(Image::class,'imageable');
    }

    /**
     * A user can have many messages
     *
     * @return HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_name', 'state_description','country_id'
    ];

    /**
     * Relationship between country and state
     */
    public function country(){
        return $this->BelongsTo(Country::class);
    }

    /**
     * Relationship between country and state
     */
    public function users(){
        return $this->belongsToMany(User::class);
    }
}

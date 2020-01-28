<?php


namespace App\Repositries;


use App\Country;

class CountryRepository extends Repository
{
    /**
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        return Country::class;
    }
}

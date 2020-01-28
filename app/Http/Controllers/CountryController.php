<?php

namespace App\Http\Controllers;

use App\Country;
use App\Helpers\ResponseHelper;
use App\Http\Resources\CountryResource;
use App\Repositries\CountryRepository;

class CountryController extends Controller
{
    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * CountryController constructor.
     * @param CountryRepository $countryRepository
     */
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $countries = $this->countryRepository->all();
        return CountryResource::collection($countries)
            ->additional(ResponseHelper::additionalInfo());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return CountryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        $data = $this->validate(request(), [
            'country_name' => 'required|min:2|unique:countries',
            'country_alias' => 'required|min:2',
            'continent_id' => 'required|exists:continents,id'
        ]);
        $country = $this->countryRepository->store($data);
        return new CountryResource($country);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $country
     * @return CountryResource
     */
    public function show(Country $country)
    {
        return new CountryResource($country);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Country $country
     * @return CountryResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Country $country)
    {
        $data = $this->validate(request(), [
            'country_name' => 'sometimes|required|min:2|unique:countries,country_name,'. $country->id,
            'country_alias' => 'sometimes|required|min:2',
            'continent_id' => 'sometimes|required|exists:continents,id'
        ]);
        return new CountryResource($this->countryRepository->update($country, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $country
     * @return CountryResource
     */
    public function destroy(Country $country)
    {
        return new CountryResource($this->countryRepository->delete($country));
    }
}

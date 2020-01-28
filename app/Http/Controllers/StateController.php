<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\StateResource;
use App\Repositries\StateRepository;
use App\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StateController extends Controller
{
    /**
     * @var StateRepository
     */
    private $stateRepo;

    public function __construct(StateRepository $stateRepository)
    {
        $this->stateRepo = $stateRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $states = $this->stateRepo->all();
        return StateResource::collection($states)
            ->additional(ResponseHelper::additionalInfo());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return StateResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        $data = $this->validate(request(), [
            'state_name' => 'required|min:2|unique:states',
            'state_description' => 'required|min:2',
            'country_id' => 'required|exists:countries,id'
        ]);
        $state = $this->stateRepo->store($data);
        return new StateResource($state);
    }

    /**
     * Display the specified resource.
     *
     * @param State $state
     * @return StateResource
     */
    public function show(State $state)
    {
        return new StateResource($state);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param State $state
     * @return StateResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(State $state)
    {
        $data = $this->validate(request(), [
            'state_name' => 'sometimes|required|min:2|unique:states,state_name,'. $state->id,
            'state_description' => 'sometimes|required|min:2',
            'country_id' => 'sometimes|required|exists:countries,id'
        ]);
        return new StateResource($this->stateRepo->update($state, $data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param State $state
     * @return StateResource
     */
    public function destroy(State $state)
    {
        return new StateResource($this->stateRepo->delete($state));
    }
}

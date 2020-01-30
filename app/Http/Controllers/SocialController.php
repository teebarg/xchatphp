<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\CountryResource;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SocialController extends Controller
{
    /**
     * SocialController constructor.
     */
    public function __construct()
    {
        $this->middleware(['jwt.verify'], ['except' => ['search']]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return AnonymousResourceCollection|Response
     */
    public function index()
    {
        $user = auth()->user();
        $test = $user->followings()->get();
        return UserResource::collection($test)
            ->additional(ResponseHelper::additionalInfo());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     */
    public function manage(User $user)
    {
        $authenticated = auth()->user();
        $test = $authenticated->toggleFollow($user);
        return ResponseHelper::createSuccessResponse($test, 'Operation Successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function search()
    {
        $user = User::search('');
        dd($user);
        dd(request('keyword'));
        return ResponseHelper::createSuccessResponse([], 'Operation Successful');
    }
}

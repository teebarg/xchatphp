<?php


namespace App\Repositries;

use App\User;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepository extends Repository
{
    /**
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        return User::class;
    }

    public function autoLogin($user) {
        $token = auth()->login($user);

        JWTAuth::setToken($token);
    }

    public function upload($data) {
        $user = auth()->user();
        $decode = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));
        $image_name = auth()->user()->username . '.jpeg';
        Storage::disk('public')->put('upload/profile/' . $image_name, $decode);
        $user->image()->updateOrCreate(['image' => $image_name]);
//        if($user->image){
//            $user->image()->update(['image' => $image_name]);
//        } else {
//            $user->image()->create(['image' => $image_name]);
//        }
        return $user;
    }
}

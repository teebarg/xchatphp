<?php

namespace App\Http\Controllers;

use App\Events\PasswordRecoveryEvent;
use App\Helpers\Helper;
use App\Helpers\ResponseCodes;
use App\Helpers\ResponseHelper;
use App\Helpers\ResponseMessages;
use App\Http\Resources\UserResource;
use App\Repositries\UserRepository;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Create a new AuthController instance.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->middleware(['jwt.verify'], ['except' => ['register', 'login', 'socialite']]);
//        $this->middleware(['role:Admin|Super Admin']);
        $this->userRepository = $userRepository;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return Response
     * @throws ValidationException
     */
    public function login()
    {
        $this->validate(request(), [
            'email'=>'required|email',
            'password' => 'required'
        ]);
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return ResponseHelper::createErrorResponse(
                ResponseMessages::LOGIN_FAIL, ResponseCodes::LOGIN_FAIL, [], ResponseCodes::UNPROCESSABLE_ENTITY
            );
        }
        JWTAuth::setToken($token);
        return new UserResource(auth()->user());
    }

    /**
     *
     */
    public function register()
    {
        $data = $this->validate(request(), User::$rules);

        $result = array_merge($data, ['username' => request('username') ?? Str::random(8), 'password' => bcrypt(request('password'))]);
        $user = $this->userRepository->store($result);

        $this->userRepository->autoLogin($user);
        return new UserResource(auth()->user());
    }

    /**
     * Get the authenticated User.
     *
     * @return UserResource
     */
    public function me()
    {
        return new UserResource(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse|Response
     */
    public function logout()
    {
        auth()->logout();
        Return ResponseHelper::createSuccessResponse([], 'Successfully Logged Out');
    }

    /**
     * Refresh a token.
     *
     * @return UserResource|JsonResponse
     */
    public function refresh()
    {
        $token = auth()->refresh();
        JWTAuth::setToken($token);
        return new UserResource(auth()->user());
    }

    /**
     * Log in with social credentials.
     *
     * @param Request $request
     * @return UserResource|Response
     */
    public function socialite(Request $request)
    {
        $driver = $request->input('type');
        $digits = 10;
        try {
            $socialUser = Socialite::driver($driver)->userFromToken($request->input('tokenId'));
            $existUser = User::where('email', $socialUser->email)->first();

            if ($existUser) {
                $user = User::find($existUser->id);
                $token = auth()->login($user);
            } else {
                $names = explode(' ', $socialUser->name);
                $user = User::create([
                    'username' => Helper::random_username($socialUser->name),
                    'email' => $socialUser->email,
                    'firstname' => $names[0],
                    'lastname' => $names[1],
                    'password' => bcrypt(rand(1, 10000)),
                    'mobile' => rand(pow(10, $digits-1), pow(10, $digits)-1)
                ]);
                $token = auth()->login($user);
            }
            JWTAuth::setToken($token);
            return new UserResource(auth()->user());
        } catch (Exception $e) {
            return ResponseHelper::createErrorResponse([], ResponseMessages::LOGIN_FAIL);
        }
    }

    /**
     * Change Password.
     *
     * @param User $user
     * @return Response
     * @throws ValidationException
     */
    public function changePassword(User $user)
    {
        if (!password_verify(request('old_password'), $user->password)) {
            return $this->sendError("Incorrect Password", 401);
        }

        $this->validate(request(), [
            'new_password' => 'required|confirmed|min:6'
        ]);

        $user->update(["password" => bcrypt(request('password'))]);

        return $this->sendSuccess('Password Updated Successfully');
    }

    /**
     * Change Password.
     *
     * @return Response
     * @throws ValidationException
     */
    public function forgotPassword()
    {

        $this->validate(request(), [
            'email' => 'required|email'
        ]);

        $user = User::where('email', \request('email'))->first();
        if (!$user){
            return $this->sendError("We cannot find this Email", 401);
        }
        event(new PasswordRecoveryEvent($user));
        return $this->sendSuccess('Password Recovery Email Sent');
    }

    /**
     * Reset Password.
     *
     * @return Response
     * @throws ValidationException
     */
    public function resetPassword(User $user)
    {
        if (!password_verify(request('old_password'), $user->password)) {
            return $this->sendError("Incorrect Password", 401);
        }

        $this->validate(request(), [
            'new_password' => 'required|confirmed|min:6'
        ]);

        $user->update(["password" => bcrypt(request('password'))]);

        return $this->sendSuccess('Password Updated Successfully');
    }
}

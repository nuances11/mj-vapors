<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultApiResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthController extends BaseController
{
    /**
     * Login api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                $accessToken =  $user->createToken(config('constants.request_token'))->accessToken;

                $user->getRoleNames()->toArray();

                $responseData = [
                    'user' => $user,
                    'permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
                    'access_token' => $accessToken,
                    'roles' => $user->getRoleNames(),
                ];

                return $this->sendResponse($responseData, 'User login successfully.');
            }
            else{
                return $this->sendError('Email or password is incorrect.', ['error'=>'Email or password is incorrect']);
            }

        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            if (isset($ex->validator)) {
                $message = $ex->validator;
            }
            return response()->json(['error' => $message, 'e' => $ex], 403);
        }
    }

    public function logout(Request $request)
    {

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $success = $user->token()->revoke();
//            $tokens =  $user->tokens->pluck('id');
//            Token::whereIn('id', $tokens)
//                ->update(['revoked'=> true]);
//
//            $success = RefreshToken::whereIn('access_token_id', $tokens)->update(['revoked' => true]);

            if ($success) {
                DB::commit();
                return $this->sendResponse(['success' => $success], 'User logged out successfully.');
            } else {
                DB::rollBack();
                return $this->sendError('Error occured.', ['error'=>'Error occured.']);
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            $message = $ex->getMessage();
            if (isset($ex->validator)) {
                $message = $ex->validator;
            }
            return response()->json(['error' => $message, 'e' => $ex], 403);
        }

    }

    /**
     * Registration
     */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken(config('constants.request_token'))->accessToken;

        return response()->json(['token' => $token], 200);
    }
}

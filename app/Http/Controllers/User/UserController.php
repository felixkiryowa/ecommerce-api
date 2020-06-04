<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use JWTAuth;

class UserController extends Controller
{
    /**
     * Function to authenticate users
     */
    public function authenticate( Request $request ) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $credentials = $request->only( 'email', 'password' );

        $user = User::where( 'email', $request->get('email'))->first();
        if($user) {
            $customlaims = [
                'username' => $user->username, 
                'email' => $user->email,
                'user_id'=>$user->id
            ];
            try {

                if (!$token = JWTAuth::claims($customlaims)->attempt($credentials)) {
                    $validator->getMessageBag()->add('password', 'Wrong password');
                    return response()->json($validator->errors(), 400);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
            $generatedToken = [
                'success' => true,
                'user' =>[
                    'username' => $user->username, 
                    'email' => $user->email,
                    'user_id'=>$user->id
                ],
                'auth_token' => 'Bearer '. $token,
            ];

            return response()->json($generatedToken, 200);

        }else{
            try {

                if (!$token = JWTAuth::attempt($credentials)) {
                    $validator->getMessageBag()->add('email', 'Email does not exist');
                    return response()->json($validator->errors(), 400);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }

        }
    }

    /**
     * Function to register users
     * 
     */

    public function register( Request $request ) {

        $validator  = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 400 );
        }

        $user = User::create( [
            'username' => ucfirst($request->get( 'username' )) ,
            'email' => $request->get( 'email' ),
            'password' => Hash::make( $request->get( 'password' ) ),
        ] );


        $token = JWTAuth::fromUser($user);

        $response = [
            'success' => true,
            'user' => [
                'username' => $user->username, 
                'email' => $user->email,
                'user_id'=>$user->id
            ],
            'auth_token' => 'Bearer '. $token,            
            'message' => 'Sucessfully created an account please Log in'

            ];

        return response()->json($response,201);
    }

    /**
     * Function to get  authenticated user
     */
    public function getAuthenticatedUser() {
        try {

            if ( ! $user = JWTAuth::parseToken()->authenticate() ) {
                return response()->json( ['user_not_found'], 404 );
            }

        } catch ( Tymon\JWTAuth\Exceptions\TokenExpiredException $e ) {

            return response()->json( ['token_expired'], $e->getStatusCode() );

        } catch ( Tymon\JWTAuth\Exceptions\TokenInvalidException $e ) {

            return response()->json( ['token_invalid'], $e->getStatusCode() );

        } catch ( Tymon\JWTAuth\Exceptions\JWTException $e ) {

            return response()->json( ['token_absent'], $e->getStatusCode() );

        }

        return response()->json( compact( 'user' ) );
    }
}

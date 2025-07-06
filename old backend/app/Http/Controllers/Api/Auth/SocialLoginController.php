<?php





namespace App\Http\Controllers\Api\Auth;



use App\Models\User;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Resources\UserResource;

use Illuminate\Support\Facades\Validator;



class SocialLoginController extends Controller

{

    public function SocialLogin(Request $request){

        return $this->handleSocialLogin($request, 'receiver');

    }



    public function donorSocialLogin(Request $request){

        return $this->handleSocialLogin($request, 'donor');

    }



    private function handleSocialLogin(Request $request, $role){

        $valid = Validator::make($request->all(), [

            'name' => [

                'required',

                'string'

            ],

            'email' => [

                'required',

                'email',

            ],

            'social_id' => [

                'required',

                'string'

            ],

            'service' => [

                'required',

                'string',

            ],

            'photo' => [

                'nullable'

            ],

            'device_token' => [

                'nullable'

            ],

        ]);



        if ($valid->fails()) {

            $message = collect([

                $valid->errors()->first('name'),

                $valid->errors()->first('email'),

                $valid->errors()->first('social_id'),

                $valid->errors()->first('service'),

                $valid->errors()->first('device_token'),

            ])->filter(fn ($item, $key) => !empty($item))->values();



            return response()->json(['response' => false, 'message' => $message]);

        }



        // Check if the email is already registered with a different role

        $existingUser = User::where('email', $request->input('email'))->first();

        if ($existingUser && $existingUser->role !== $role) {

            return response()->json(['response' => false, 'message' => 'This email is already registered with a different role.']);

        }



        // Check if the user exists with the given social credentials

        $user = User::where('email', $request->input('email'))->orWhereHas('social', function ($query) use ($request) {

            $query->where('social_id', $request->input('social_id'))->where('service', $request->input('service'));

        })->first();



        if (!$user) {

            $user = User::create([

                'name' => $request->input('name'),

                'email' => $request->input('email'),

                'role' => $role,

                'photo' => $request->input('photo') ?? null

            ]);

        }



        if (!$user->social) {

            $user->social()->create([

                'social_id' => $request->input('social_id'),

                'service' => $request->input('service'),

            ]);

        }

        

        $token = $user->createToken('app-token')->plainTextToken;



        $user->update([

            'device_token' => $request->device_token

        ]);



        return (new UserResource($user))->additional([

            'response' => true,

            'message' => 'Logged-in Successfully',

            'meta' => [

                'access_token' => $token

            ]

        ]);

    }

}


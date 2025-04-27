<?php

namespace App\Http\Controllers\Api\Receiver;

use App\Models\Post;
use App\Models\User;
use App\Models\BankDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Notifications\PushNotification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Resources\Receiver\ReceiverProfileResource;
use App\Notifications\PostUpdatePushNotification;
use Illuminate\Support\Facades\Artisan;
class ReceiverProfileController extends Controller
{
    public $notifyImage = null;

    public function __construct()
    {
        $this->middleware(['user.role:receiver']);
    }

    public function index(Request $request)
    {
        return (new ReceiverProfileResource($request->user()))->additional([
            'response' => true,
            'message' => ['Receiver Profile Data']
        ]);
    }

    public function detailUpdate(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'gender' => 'nullable',
            'photo' => 'nullable|image|max:10240',
        ], [
            'photo.max' => 'Photo must be under 10MB'
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('name'),
                $valid->errors()->first('gender'),
                $valid->errors()->first('photo'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $user = User::updateOrCreate([
                'id' => $request->user()->id,
            ], [
                'name' => $request->name,
            ]);

            if (!is_null($request->file('photo'))) {
                if (!is_null($request->user()->photo)) {
                    if (!Str::contains($request->user()->photo, 'http')) {
                        unlink(storage_path('app/public/' . $request->user()->photo));
                    }
                }

                $path = $request->photo->store('profile/photo', 'public');

                $user->update(['photo' => $path, 'gender' => $request->gender]);
            } else {
                $user->update(['gender' => $request->gender]);
            }

            // send user data
            return (new ReceiverProfileResource($user))->additional([
                'response' => true,
                'message' => ['Profile Updated Successfully']
            ]);
        }
    }

    public function bankUpdate(Request $request)
    {
        Artisan::call('optimize:clear');
        $valid = Validator::make($request->all(), [
            // 'bank_name' => 'required',
            'account_name' => 'required',
            'account_id_type'=> 'required',
            'paypal_account_id' => 'required',
            'currency' => 'required'
        ]);

        if ($valid->fails()) {

            return response()->json($valid->getMessageBag());
        } else {

            BankDetail::updateOrCreate([
                'user_id' => $request->user()->id,
            ], [
                // 'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_id_type' => $request->account_id_type,
                'paypal_account_id' => $request->paypal_account_id,
                'currency' => $request->currency,
            ]);

            // send user data
            return (new ReceiverProfileResource($request->user()))->additional([
                'response' => true,
                'message' => ['Bank Details Updated Successfully']
            ]);
        }
    }

    public function postUpdate(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'amount' => 'required|max:10',
            'biography' => 'required|max:500',
            'image' => 'nullable|image|max:10240',
        ], [
            'image.max' => 'Image must be under 10MB'
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('amount'),
                $valid->errors()->first('biography'),
                $valid->errors()->first('image'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            // update donation
            $donation = Post::updateOrCreate([
                'user_id' => $request->user()->id,
            ], [
                'amount' => $request->amount,
                'biography' => $request->biography,
            ]);

            // sync tags
            if ($request->has('tags')) {
                $donation->tags()->sync(json_decode($request->tags), [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            // file upload
            if (!is_null($request->file('image'))) {
                if ($donation->image !== null) {
                    unlink(storage_path('app/public/' . $donation->image));
                }

                $path = $request->image->store('post/image', 'public');

                $donation->updateOrCreate([
                    'user_id' => $request->user()->id,
                ], [
                    'image' => $path,
                ]);
            }

            if (!is_null($request->file('image'))) {
                $this->notifyImage = $path;
            } else {
                $this->notifyImage = $donation->image;
            }

            // notification list
            $data = [
                'to' => 'donor',
                'title' => 'Post Updated',
                'image' => URL::to('/storage') . '/' . $this->notifyImage,
                'description' => $request->biography,
                'date' => Carbon::now(),
                'amount' => null
            ];

            $users = User::where('role', 'donor')->get();
            if ($users->count() > 0) {
                Notification::send($users, new UserNotification($data));
            }

            // push notification
            $title = 'Post Updated';
            $body = $donation->biography;
            $pageName = 'DonorReceiverDetailsScreen';
            $searchId = $donation->id;

            $tokens = User::where('role', 'donor')->whereNotNull('device_token')->pluck('device_token')->toArray();
            Notification::send(null, new PostUpdatePushNotification($title, $body, $pageName, $searchId, $tokens));

            // send user data
            return (new ReceiverProfileResource($request->user()))->additional([
                'response' => true,
                'message' => ['Post Updated Successfully']
            ]);
        }
    }
}

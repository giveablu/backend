<?php

namespace App\Http\Controllers\Api\Receiver;

use App\Models\Post;
use App\Models\User;
use App\Models\AppFaq;
use App\Models\BankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\AppFaqResource;
use App\Notifications\PushNotification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PostUpdatePushNotification;

class ReceiverHomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:receiver']);
    }

    public function index(Request $request)
    {
        $faq = AppFaq::where('type', 'receiver')->get();

        if ($faq->count() > 0) {
            $faqs = AppFaqResource::collection($faq);
            return response()->json(['data' => ['faqs' => $faqs], 'response' => true, 'message' => ['Receiver Home']]);
        } else {
            return response()->json(['response' => false, 'message' => ['No Data Found']]);
        }
    }

    public function donationStore(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'amount' => 'required|max:10',
            'biography' => 'required|max:500',
            'image' => 'required|image|max:10240',
            'tags' => 'required',
        ],[
            'image.max' => 'Image must be under 10MB'
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('amount'),
                $valid->errors()->first('biography'),
                $valid->errors()->first('image'),
                $valid->errors()->first('tags'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            // create or update donation
            $post = Post::updateOrCreate([
                'user_id' => $request->user()->id,
            ], [
                'amount' => $request->amount,
                'biography' => $request->biography,
            ]);

            // sync tags
            if ($request->has('tags')) {
                $post->tags()->sync(json_decode($request->tags), [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            // file upload
            if (!is_null($request->file('image'))) {
                if (!empty($post->image) && Storage::disk('public')->exists($post->image)) {
                    Storage::disk('public')->delete($post->image);
                }

                $path = $request->image->store('post/image', 'public');

                $post->updateOrCreate([
                    'user_id' => $request->user()->id,
                ], [
                    'image' => $path,
                ]);
            }

            // notification list
            $data = [
                'to' => 'donor',
                'title' => 'New Post Added',
                'image' => URL::to('/storage') . '/' . $path,
                'description' => $request->biography,
                'date' => Carbon::now(),
                'amount' => null
            ];

            $users = User::where('role', 'donor')->get();
            if ($users->count() > 0) {
                Notification::send($users, new UserNotification($data));
            }

            // push notification
            $title = 'New Post Added';
            $body = $post->biography;
            $pageName = 'DonorReceiverDetailsScreen';
            $searchId = $post->id;

            $tokens = User::where('role', 'donor')->whereNotNull('device_token')->pluck('device_token')->toArray();
            Notification::send(null, new PostUpdatePushNotification($title, $body, $pageName, $searchId, $tokens));

            // send user data
            return (new UserResource($request->user()))->additional([
                'response' => true,
                'message' => ['Post Details Updated']
            ]);
        }
    }

    public function BankStore(Request $request)
    {
        $valid = Validator::make($request->all(), [
            // 'bank_name' => 'required',
            'account_name' => 'required',
            "paypal_account_id_type"=> 'required',
            "paypal_account_id" => 'required',
            "currency" => 'required'

            // 'account_name' => 'required',
            // 'account_no' => 'required',
            // 'ifsc_code' => 'required',
        ]);

        if ($valid->fails()) {
            $message = collect([
                // $valid->errors()->first('bank_name'),
                $valid->errors()->first('account_name'),
                $valid->errors()->first('paypal_account_id_type'),
                $valid->errors()->first('paypal_account_id'),
                $valid->errors()->first('currency'),
                // $valid->errors()->first('account_name'),
                // $valid->errors()->first('account_no'),
                // $valid->errors()->first('ifsc_code'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            // create or update bank
            BankDetail::updateOrCreate([
                'user_id' => $request->user()->id,
            ], [
                // 'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_id_type' => $request->paypal_account_id_type,
                'paypal_account_id' => $request->paypal_account_id,
                'currency' => $request->currency,
                // 'account_no' => $request->account_no,
                // 'ifsc_code' => $request->ifsc_code,
            ]);

            // send user data
            return (new UserResource($request->user()))->additional([
                'response' => true,
                'message' => ['Bank Details Updated']
            ]);
        }
    }
}

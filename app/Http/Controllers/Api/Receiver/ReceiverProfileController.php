<?php

namespace App\Http\Controllers\Api\Receiver;

use App\Models\Post;
use App\Models\User;
use App\Models\Tag;
use App\Models\BankDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
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
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:10240',
            'profile_description' => 'nullable|string|max:1000',
            'city' => 'required|string|max:150',
            'region' => 'nullable|string|max:150',
            'country' => 'required|string|max:150',
        ], [
            'photo.max' => 'Photo must be under 10MB',
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('name'),
                $valid->errors()->first('gender'),
                $valid->errors()->first('photo'),
                $valid->errors()->first('profile_description'),
                $valid->errors()->first('city'),
                $valid->errors()->first('region'),
                $valid->errors()->first('country'),
            ])->filter(fn ($item) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        }

        $user = $request->user();

        $user->fill([
            'name' => trim($request->name),
            'gender' => $this->nullIfEmpty($request->gender),
            'profile_description' => $this->nullIfEmpty($request->profile_description),
            'city' => $this->nullIfEmpty($request->city),
            'region' => $this->nullIfEmpty($request->region),
            'country' => $this->nullIfEmpty($request->country),
        ]);

        if ($request->hasFile('photo')) {
            $existingPhoto = $user->photo;
            if (!empty($existingPhoto) && !Str::contains($existingPhoto, 'http') && Storage::disk('public')->exists($existingPhoto)) {
                Storage::disk('public')->delete($existingPhoto);
            }

            $path = $request->photo->store('profile/photo', 'public');
            $user->photo = $path;
        }

        $user->save();

        $user->refresh()->load(['post.tags', 'bankDetail']);

        return (new ReceiverProfileResource($user))->additional([
            'response' => true,
            'message' => ['Profile Updated Successfully']
        ]);
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
            'amount' => 'nullable|max:10',
            'biography' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:10240',
            'hardships' => 'nullable',
            'tags' => 'nullable',
            'clear_hardships' => 'nullable|boolean',
        ], [
            'image.max' => 'Image must be under 10MB'
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('amount'),
                $valid->errors()->first('biography'),
                $valid->errors()->first('image'),
                $valid->errors()->first('hardships'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        }

        $post = Post::firstOrNew([
            'user_id' => $request->user()->id,
        ]);

        if ($request->filled('amount')) {
            $post->amount = $request->amount;
        }

        if (! is_null($request->biography)) {
            $post->biography = $this->nullIfEmpty($request->biography);
        }

        $post->save();

        // sync hardships / tags
        $rawHardships = $request->input('hardships', $request->input('tags'));
        if (! is_null($rawHardships)) {
            $parsedHardships = is_string($rawHardships) ? json_decode($rawHardships, true) : $rawHardships;
            if (! is_array($parsedHardships)) {
                $parsedHardships = [];
            }

            $tagIds = $this->resolveHardshipTagIds($parsedHardships);
            $post->tags()->sync($tagIds);
        } elseif ($request->boolean('clear_hardships')) {
            $post->tags()->sync([]);
        }

        // file upload
        if ($request->hasFile('image')) {
            if (!empty($post->image) && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $path = $request->image->store('post/image', 'public');
            $post->image = $path;
            $post->save();
            $this->notifyImage = $path;
        } else {
            $this->notifyImage = $post->image;
        }

        // notification list
        $data = [
            'to' => 'donor',
            'title' => 'Post Updated',
            'image' => $this->notifyImage ? URL::to('/storage') . '/' . $this->notifyImage : null,
            'description' => $post->biography,
            'date' => Carbon::now(),
            'amount' => null
        ];

        $users = User::where('role', 'donor')->get();
        if ($users->count() > 0) {
            Notification::send($users, new UserNotification($data));
        }

        // push notification
        $title = 'Post Updated';
        $body = $post->biography;
        $pageName = 'DonorReceiverDetailsScreen';
        $searchId = $post->id;

        $tokens = User::where('role', 'donor')->whereNotNull('device_token')->pluck('device_token')->toArray();
        if (! empty($tokens)) {
            Notification::send(null, new PostUpdatePushNotification($title, $body, $pageName, $searchId, $tokens));
        }

        $request->user()->load(['post.tags', 'bankDetail']);

        return (new ReceiverProfileResource($request->user()))->additional([
            'response' => true,
            'message' => ['Post Updated Successfully']
        ]);
    }

    private function nullIfEmpty(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function resolveHardshipTagIds(array $entries): array
    {
        return collect($entries)
            ->map(function ($entry) {
                if (is_array($entry)) {
                    $id = $entry['id'] ?? null;
                    $name = $entry['name'] ?? null;

                    if ($id) {
                        return (int) $id;
                    }

                    return $this->findOrCreateHardshipId($name);
                }

                if (is_numeric($entry)) {
                    return (int) $entry;
                }

                if (is_string($entry)) {
                    return $this->findOrCreateHardshipId($entry);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function findOrCreateHardshipId(?string $name): ?int
    {
        $normalized = $this->normalizeHardshipName($name);

        if (! $normalized) {
            return null;
        }

        $tag = Tag::firstOrCreate(['name' => $normalized]);

        return $tag->id;
    }

    private function normalizeHardshipName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);

        if ($trimmed === '') {
            return null;
        }

        return Str::title(mb_strtolower($trimmed));
    }
}

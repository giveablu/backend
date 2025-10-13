<?php

namespace App\Http\Controllers\Api\Donor;

use App\Models\Post;
use App\Models\AppFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\AppFaqResource;
use App\Http\Resources\Donor\DonorHomeResource;

class DonorHomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:donor']);
    }

    public function index(Request $request)
    {
        $user = $request->user()->load('donorPreference');

        $excludedPostIds = $user->deleteds()->pluck('post_id');

        $baseQuery = Post::query()
            ->whereNotIn('id', $excludedPostIds)
            ->whereHas('user', fn ($builder) => $builder->where('role', 'receiver'))
            ->with(['user', 'tags']);

        $query = clone $baseQuery;

        $preference = $user->donorPreference;
        $hasActivePreference = false;

        if ($preference) {
            if ($preference->preferred_country) {
                $query->whereHas('user', fn ($builder) => $builder->where('country', $preference->preferred_country));
                $hasActivePreference = true;
            }

            if ($preference->preferred_region) {
                $query->whereHas('user', fn ($builder) => $builder->where('region', $preference->preferred_region));
                $hasActivePreference = true;
            }

            if ($preference->preferred_city) {
                $query->whereHas('user', fn ($builder) => $builder->where('city', $preference->preferred_city));
                $hasActivePreference = true;
            }

            if (!empty($preference->preferred_hardship_ids)) {
                $query->whereHas('tags', fn ($builder) => $builder->whereIn('tags.id', $preference->preferred_hardship_ids));
                $hasActivePreference = true;
            }
        }

        $posts = $query->inRandomOrder()->paginate(4);

        if ($posts->count() > 0) {
            return DonorHomeResource::collection($posts)->additional([
                'response' => true,
                'message' => ['Donor Home']
            ]);
        }

        if ($preference && $hasActivePreference) {
            $fallbackPosts = (clone $baseQuery)->inRandomOrder()->paginate(4);

            if ($fallbackPosts->count() > 0) {
                return DonorHomeResource::collection($fallbackPosts)->additional([
                    'response' => true,
                    'message' => ['We found recipients outside your saved preferences while we locate more matches.'],
                    'preference_status' => 'fallback',
                ]);
            }

            return response()->json([
                'response' => false,
                'message' => ['No recipients match your preferences yet. Try adjusting your criteria.'],
                'data' => [],
            ]);
        }

        return response()->json([
            'response' => false,
            'message' => ['No Donation Found'],
            'data' => [],
        ]);
    }

    public function softDelete(Request $request, $id)
    {
        $posts = Post::get();

        if ($posts->count() > 0) {

            if ($posts->diff($request->user()->deleteds)->find($id)) {
                $request->user()->deleteds()->attach($id, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                return response()->json(['response' => true, 'message' => ['Donation Deleted']]);
            } else {
                return response()->json(['response' => false, 'message' => ['Cannot Delete This Post']]);
            }
        } else {
            return response()->json(['response' => false, 'message' => ['No Donation Found']]);
        }
    }

    public function detail($id)
    {
        $post = Post::find($id);

        if ($post) {
            return (new PostResource($post))->additional([
                'response' => true,
                'message' => ['Post Details']
            ]);
        } else {
            return response()->json(['response' => false, 'message' => 'No Post Found']);
        }
    }

    public function faqs(Request $request)
    {
        $faq = AppFaq::where('type', $request->user()->role)->get();

        if ($faq->count() > 0) {
            return response()->json([
                'data' => [
                    'faqs' => AppFaqResource::collection($faq)
                ],
                'response' => true,
                'message' => ['Faq Data Found']
            ]);
        } else {
            return response()->json([
                'response' => false,
                'message' => ['No Data Found']
            ]);
        }
    }
}

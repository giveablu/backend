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
        $posts = Post::get();

        if ($posts->count() > 0) {
            $nondels = $posts->diff($request->user()->refresh()->deleteds);

            if ($nondels->count() > 0) {
                return DonorHomeResource::collection($nondels->toQuery()->OrderBy('id', 'DESC')->paginate(4))->additional([
                    'response' => true,
                    'message' => ['Donor Home']
                ]);
            } else {
                return response()->json(['response' => false, 'message' => ['No Donation Found']]);
            }
        } else {
            return response()->json(['response' => false, 'message' => ['No Data Found']]);
        }
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

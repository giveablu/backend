<?php

namespace App\Http\Controllers\Api\Donor;

use App\Models\Post;
use App\Models\Donation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;

class DonorDonationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:donor']);
    }

  public function index(Request $request)
    {
        $donors = Donation::where('user_id', $request->user()->id)->where('activity', 0)->latest()->get();

        if ($donors->isEmpty()) {
            return response()->json([
                'response' => true,
                'message' => 'No paid donations found.'
            ]);
        }
        return DonationResource::collection($donors)->additional([
            'response' => true,
            'message' => 'Paid Donation List'
        ]);
    }

    public function detail(Request $request, $id)
    {
        $donor = Donation::where('user_id', $request->user()->id)->find($id);

        if ($donor) {
            return (new DonationResource($donor))->additional([
                'response' => true,
                'message' => ['Donation Details']
            ]);
        } else {
            return response()->json(['response' => false, 'message' => ['No data sound']]);
        }
    }

    public function delete(Request $request, $id)
    {
        $donation = Donation::where('user_id', $request->user()->id)->find($id);

        if ($donation) {
            if (!$donation->activity) {
                $donation->update([
                    'activity' => true
                ]);
                return response()->json(['response' => true, 'message' => ['Donation Deleted']]);
            } else {
                return response()->json(['response' => true, 'message' => ['Already Deleted']]);
            }
        } else {
            return response()->json(['response' => false, 'message' => ['No data sound']]);
        }
    }
}

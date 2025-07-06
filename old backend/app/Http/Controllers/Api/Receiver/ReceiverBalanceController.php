<?php

namespace App\Http\Controllers\Api\Receiver;

use App\Models\Post;
use App\Models\Donation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Receiver\ReceiverBalanceResource;
use App\Http\Resources\Receiver\ReceiverWithdrawResource;

class ReceiverBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:receiver']);
    }
    
    public function index(Request $request)
    {
        // print_r($request->user()->post->id);exit;
        $receives = Donation::where('post_id', $request->user()->post->id)->latest()->get();

        $withdraws = $request->user()->withdraws()->count() > 0
        ? ReceiverWithdrawResource::collection($request->user()->withdraws()->latest()->get()) // Orders by `created_at`
        : null; 
        
        return response()->json([
            'data' => ['receives' => ReceiverBalanceResource::collection($receives), 'withdraws' => $withdraws],
            'response' => true,
            'message' => ['Received & Withdraw Data']
        ]);

        
    }
}

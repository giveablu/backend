<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        $users = User::get();
        
        return view('pages.admin.dashboard', [
            'tDonors' => $users->where('role', 'donor')->count(),
            'tReceivers' => $users->where('role', 'receiver')->count(),
            'tPosts' => Post::count(),
            'tWithdraws' => Withdraw::count(),
        ]);
    }
}

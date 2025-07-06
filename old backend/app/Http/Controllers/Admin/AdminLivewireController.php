<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminLivewireController extends Controller
{
    public function users(){
        return view('pages.admin.user');
    }

    public function tags(){
        return view('pages.admin.tags');
    }

    public function appFaq(){
        return view('pages.admin.faqs');
    }

    public function setting(){
        return view('pages.admin.setting');
    }

    public function withdraw(){
        return view('pages.admin.withdraws');
    }

    public function post(){
        return view('pages.admin.posts');
    }
    public function create(){
        return view('pages.admin.addUser');
    }
    
}

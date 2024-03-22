<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller {

    public function __construct(){
        $this->middleware('auth');
    }

    public function loginAs($id) {
        Auth::loginUsingId($id, true);
        return redirect()->route('home', ['success' => 'Login successful!']);
    }

    public function clearFeed(Request $request) {
        Session::forget('feed');
    }

}
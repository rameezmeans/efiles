<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(){

        $this->middleware('auth', [ 'except' => [ 'feedbackLink' ] ]);

    }

    public function cart() {
        
    }
}

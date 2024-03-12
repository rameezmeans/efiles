<?php

namespace App\Http\Controllers;

use ECUApp\SharedCode\Controllers\FilesMainController;
use ECUApp\SharedCode\Models\File;
use ECUApp\SharedCode\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $filesMainObj;

    public function __construct(){

        $this->middleware('auth', [ 'except' => [ 'feedbackLink' ] ]);
        $this->filesMainObj = new FilesMainController();

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function fileHistory()
    {
        $frontendID = 2;
        $user = User::findOrFail(Auth::user()->id);
        $files = $this->filesMainObj->getFiles($user, $frontendID);

        return view('files.file_history', [ 'files' => $files, 'user' => $user ]);
    }
}

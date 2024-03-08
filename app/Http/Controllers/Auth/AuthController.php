<?php
  
namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use ECUApp\SharedCode\Controllers\AuthMainController;
use ECUApp\SharedCode\Models\Tool;
use ECUApp\SharedCode\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

    private $authMainObj;

    public function __construct()
    {   
        $this->authMainObj = new AuthMainController();
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('auth.login');
    }  
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registration()
    {
        $masterTools = Tool::where('type', 'master')->get();
        $slaveTools = Tool::where('type', 'slave')->get();
        return view('auth.registration', ['masterTools' => $masterTools, 'slaveTools' => $slaveTools]);
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postLogin(Request $request)
    {
        
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        
        $frontEndID = 2;
        $user = User::where('email', $request->email)->where('front_end_id', $frontEndID)->first();

        if($user){

            if( $this->authMainObj->loginRule($frontEndID, $user) ){

                $credentials = $request->only('email', 'password');

                if (Auth::attempt($credentials)) {

                    return redirect()->intended('home')
                                ->withSuccess('You have Successfully loggedin!');
                }
            }
            else{

                return redirect("login")->withSuccess('You have entered invalid credentials!');
            }
        }
  
        return redirect("login")->withSuccess('You have entered invalid credentials!');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postRegistration(Request $request)
    {   
        $data = $request->all();
        $frontEndID = 2;
        
        $validationArray = $this->authMainObj->getValidationRules($data);
        $request->validate($validationArray);

        $user = $this->authMainObj->registration($data);
         
        if( $this->authMainObj->loginRule($frontEndID, $user) ){

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {

                return redirect()->intended('home')
                            ->withSuccess('You have Successfully loggedin!');
            }
        }
        else{

            return redirect("login")->withSuccess('You have entered invalid credentials!');
        }
  
        return redirect("login")->withSuccess('You have entered invalid credentials!');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function home()
    {
        if(Auth::check()){
            
            $user = User::findOrFail(Auth::user()->id);
            return view('home', ['user' => $user]);
        }
  
        return redirect("login")->withSuccess('Opps! You do not have access');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function logout() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }
}
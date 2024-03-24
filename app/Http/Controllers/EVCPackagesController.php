<?php

namespace App\Http\Controllers;

use ECUApp\SharedCode\Controllers\AuthMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Models\Group;
use ECUApp\SharedCode\Models\Package;
use ECUApp\SharedCode\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EVCPackagesController extends Controller
{
    private $authMainObj;
    private $paymenttMainObj;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authMainObj = new AuthMainController();
        $this->paymenttMainObj = new PaymentsMainController();
    }

    public function history(){

        $user = Auth::user();

        $records = null;

        try{
            
            $response = Http::get('https://evc.de/services/api_resellercredits.asp?apiid=j34sbc93hb90&username=161134&password=MAgWVTqhIBitL0wn&verb=getrecentaccountchanges&lastndays=9999&customer='.$user->evc_customer_id);

            $body = $response->body();

            $ok = substr($body, 0, 2);
            
            if($ok == 'ok'){
                $json = substr( $body, 18 );
                $res = json_decode($json);
               
                if($res->status == 'OK'){
                    $records = $res->data;
                }
            }
            
        }
        catch(ConnectionException $e){
            return redirect()->route('evc-credits-shop')->with('danger', 'EVC history can not be loaded. Server Down!');
        }
        
        return view('evc_history', ['records' => $records, 'user' => $user]);
    }

    public function buyEVCPackage(Request $request){

        $user = Auth::user();

        $price = $request->price;
        $credits = $request->credits;
        $packageID = $request->packageID;

        $packages = $this->paymenttMainObj->getEVCPackages();

        if($user->exclude_vat_check) {

            if(!$user->group_id){
                $vat0Group = Group::where('slug', 'VAT0')->first();
                $user->group_id = $vat0Group->id;
                $user->save();
            }
        }

        else{

            $this->authMainObj->VATCheckPolicy($user);

        }

        $factor = 0;
        $tax = 0;

        if($user->group->tax > 0){
            $tax = (float) $user->group->tax;
        }

        if($user->group->raise > 0){
            $factor = (float)  ($user->group->raise / 100) * $price->value;
        }

        if($user->group->discount > 0){
            $factor =  -1* (float) ($user->group->discount / 100) * $price->value;
        }

        return view('cart_evc_package', ['packageID' => $packageID, 'user' => $user, 'credits' => $credits,'packages' => $packages, 'price' => $price, 'tax' => $tax, 'factor' => $factor, 'group' => $user->group] );
    }

    public function packages(){

        $user = Auth::user();
        $evcPackages = $this->paymenttMainObj->getEVCPackages();

        return view('evc_packages', ['evcPackages' => $evcPackages, 'user' => $user]);
    }
    
    public function successEVC(Request $request){
        
        $packageID = $request->packageID;
        $user = Auth::user();
        $type = $request->type;

        $package = Package::findOrFail($packageID);

        $frontendID = 2;

        if($type == 'stripe'){
            $sessionID = $request->get('session_id');
        }
        else{
            $sessionID = $request->get('paymentId');
        }
        
        $flag = $this->paymenttMainObj->addCreditsEVC($user, $sessionID, $package, $type, $frontendID);

        if($flag){
            return redirect()->route('evc-credits-shop')->with('success', 'EVC Credits added, successfully.');
        }

        return redirect()->route('evc-credits-shop')->with('danger', 'EVC Credits not added!');

    }

    public function checkoutEVCPackages(Request $request){

        $type = $request->type;
        $user = Auth::user();
        $package = Package::findOrFail($request->packageID);

        if($type == 'stripe'){
            return $this->paymenttMainObj->redirectStripeEVCPackage($user, $package); 
        }
        else{
            return $this->paymenttMainObj->redirectPaypalEVCPackage($user, $package); 
        }
    }
    
    public function cancelEVCPackages(Request $request){
        return redirect()->route('evc-credits-shop')->with('danger', 'Credits not added!');

    }
}

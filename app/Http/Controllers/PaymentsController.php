<?php

namespace App\Http\Controllers;

use ECUApp\SharedCode\Controllers\AuthMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Models\Credit;
use ECUApp\SharedCode\Models\Group;
use ECUApp\SharedCode\Models\IntegerMeta;
use ECUApp\SharedCode\Models\Package;
use ECUApp\SharedCode\Models\PaymentLog;
use ECUApp\SharedCode\Models\Price;
use ECUApp\SharedCode\Models\Product;
use ECUApp\SharedCode\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentsController extends Controller
{
    private $paymenttMainObj;
    private $authMainObj;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->paymenttMainObj = new PaymentsMainController();
        $this->authMainObj = new AuthMainController();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function shopProduct(){

        $frontendID = 2;
        $user = User::findOrFail(Auth::user()->id);
        
        $price = $this->paymenttMainObj->getPrice();
        $packages = $this->paymenttMainObj->getPackages($frontendID);

        return view('shop_product', ['packages' => $packages, 'price' => $price, 'group' => $user->group, 'user' => $user]);
    }

    public function cart()
    {
        $frontendID = 2;

        $user = User::findOrFail(Auth::user()->id);
        $price = $this->paymenttMainObj->getPrice();
        $packages = $this->paymenttMainObj->getPackages($frontendID);

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

        
        return view('cart', ['packages' => $packages, 'price' => $price, 'tax' => $tax, 'factor' => $factor, 'group' => $user->group, 'user' => $user] );

    }

    public function successStripe(Request $request){

        $user = User::findOrFail(Auth::user()->id);

        $sessionID = $request->get('session_id'); 
        $credits = $request->credits;
        $unitPrice = $this->paymenttMainObj->getPrice()->value;

        $this->paymenttMainObj->addCredits($user, $sessionID, $unitPrice, $credits);
        
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

        // $logInstance = new PaymentLog();
        // $logInstance->payment_id = $credit->id;
        // $logInstance->user_id = $credit->user_id;
        // $logInstance->save();

        // $account = $user->stripe_payment_account();

        // if($user->zohobooks_id == NULL){
                    
        //     $clientArr = $this->createZohobooksCustomer($user);

        //     if(!$clientArr['success']){
        //         $logInstance->reason_to_skip_zohobooks_id = $clientArr['reason'];
        //         $logInstance->save();
        //     }
        // }

        // $invoiceArr = $this->createZohobooksInvoice( $user, $credit, false, 'stripe',  $logInstance );

        // if($invoiceArr['success_invoice']){
        //     $logInstance->zohobooks_id = $credit->zohobooks_id;
        //     $logInstance->save();
        // }
        // else{
        //     $logInstance->reason_to_skip_zohobooks_id = $invoiceArr['reason_invoice'];
        //     $logInstance->save();
        // }

        // if($invoiceArr['success_payment']){
        //     $logInstance->zohobooks_payment = true;
        //     $logInstance->save();
        // }
        // else{
        //     $logInstance->zohobooks_payment = false;
        //     $logInstance->reason_to_skip_zohobooks_payment_id = $invoiceArr['reason_payment'];
        //     $logInstance->save();
        // }

        // $userGroup = Group::where('id', $user->group_id)->first();
        // $taxRate = $userGroup->tax;
        
        // if($account->elorus){

        //     $clientID = null;

        //     if($user->elorus_id){
        //         $clientID = $user->elorus_id;
        //     }
        //     else{
        //         $clientID = $this->createElorusCustomer($user);
        //     }

        //     if(country_to_continent($user->country) == 'Europe'){

        //         $code = elorus_policy($user);
        //         $this->createElorusInvoice($credit, $clientID, $user, $taxRate, $code, $invoiceSequence );

        //     }

        // }

        \Cart::remove(101);

        return redirect()->route('shop-product')->with('success', 'Credits are added!');
        

    }

    public function stripeCheckout(Request $request){
       
        $user = User::findOrFail(Auth::user()->id);
        $unitPrice =  $this->paymenttMainObj->getPrice()->value;
        $credits = $request->credits_for_checkout;
        return $this->paymenttMainObj->redirectStripe($user, $unitPrice, $credits);
        
    }

    public function cancel(){
        return redirect()->route('shop-product')->with('danger', 'Credits Not Added!');
    }

    public function getCartQuantity(){
        $item = \Cart::get(101);
        if($item){
            return $item['quantity'];
        }
        else{
            return 0;
        }
    }

    public function clearCart(){
        \Cart::remove(101);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function addToCart(Request $request)
    {

        $empty = \Cart::isEmpty();

        if($empty){

            $product = Product::findOrFail(1);
            $request->cart;

            \Cart::add(array(
                'id' => 101,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'attributes' => array(),
                'associatedModel' => $product
            ));
        }

        else{

            \Cart::update(101, array(
                'quantity' => $request->cart, 
              ));
        }

        
        return response()->json(['success'=>'Item Added to cart']);
    }
}

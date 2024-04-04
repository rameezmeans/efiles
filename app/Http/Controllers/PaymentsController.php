<?php

namespace App\Http\Controllers;

use ECUApp\SharedCode\Controllers\AuthMainController;
use ECUApp\SharedCode\Controllers\ElorusMainController;
use ECUApp\SharedCode\Controllers\FilesMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Controllers\ZohoMainController;
use ECUApp\SharedCode\Models\Group;
use ECUApp\SharedCode\Models\Package;
use ECUApp\SharedCode\Models\Product;
use ECUApp\SharedCode\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentsController extends Controller
{
    private $paymenttMainObj;
    private $authMainObj;
    private $filesMainObj;
    private $zohoMainObj;
    private $elorusMainObj;
    private $frontendID;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->frontendID = 2;
        $this->middleware('auth');
        $this->paymenttMainObj = new PaymentsMainController();
        $this->authMainObj = new AuthMainController();
        $this->filesMainObj = new FilesMainController();
        $this->zohoMainObj = new ZohoMainController();
        $this->elorusMainObj = new ElorusMainController();
    }

    public function offerCheckout(Request $request) {

        $creditsToBuy = $request->credits_to_buy;
        $creditsForCheckout = $request->credits_for_checkout;
        $fileID = $request->file_id;

        $user = Auth::user();

        $price = $this->paymenttMainObj->getPrice();

        $packages =  $this->paymenttMainObj->getPackages($this->frontendID);

        if($user->exclude_vat_check) {

            if(!$user->group_id){
                $vat0Group = Group::where('slug', 'VAT0')->first();
                $user->group_id = $vat0Group->id;
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

        return view('files.cart_offer', [

            'file_id' => $fileID,
            'credits_to_buy' => $creditsToBuy,
            'credits_for_checkout' => $creditsForCheckout, 
            'packages' => $packages, 
            'price' => $price, 
            'tax' => $tax, 
            'factor' => $factor, 
            'group' => $user->group, 
            'user' => $user
        ]);

    }

    public function buyOffer(Request $request){

        $creditsToBuy = $request->total_credits_to_submit;
        $creditsForFile = $request->credits_for_checkout;
        $fileID = $request->file_id;

        $type = $request->type;
        $user = Auth::user();
        $unitPrice =  $this->paymenttMainObj->getPrice()->value;
        
        if($type == 'stripe'){
            return $this->paymenttMainObj->redirectStripeOffer($user, $unitPrice, $creditsToBuy, $creditsForFile, $fileID);
        }
        else{
            return $this->paymenttMainObj->redirectPaypalOffer($user, $unitPrice, $creditsToBuy, $creditsForFile, $fileID);
        }
    }


    public function fileCart(Request $request){

        $creditsToBuy = $request->credits_to_buy;
        $creditsForFile = $request->credits_for_file;

        $fileID = $request->file_id;

        $user = Auth::user();

        $price = $this->paymenttMainObj->getPrice();
        $packages = $this->paymenttMainObj->getPackages($this->frontendID);

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

        return view('files.file_cart', ['user' => $user, 'file_id' => $fileID,'creditsToBuy' => $creditsToBuy, 'creditsForFile' => $creditsForFile, 'packages' => $packages, 'price' => $price, 'tax' => $tax, 'factor' => $factor, 'group' => $user->group] );

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function shopProduct(){

        $user = Auth::user();
        
        $price = $this->paymenttMainObj->getPrice();
        $packages = $this->paymenttMainObj->getPackages($this->frontendID);

        return view('shop_product', ['packages' => $packages, 'price' => $price, 'group' => $user->group, 'user' => $user]);
    }

    public function cart(){
        
        $user = Auth::user();
        $price = $this->paymenttMainObj->getPrice();
        $packages = $this->paymenttMainObj->getPackages($this->frontendID);

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

    public function createTestElorusCustomer($userID){

        $user = User::findOrFail($userID);
        $this->elorusMainObj->createTestElorusCustomer($user);

    }

    public function success(Request $request){

        $frontendID =  2;

        $package = false;
        $offer = false;
        $fileFlag = false;

        if(isset($request->purpose) && $request->purpose == 'offer'){
            $offer = true;
            $fileID = $request->file_id;
        }

        if(!$offer) {
            
            if(isset($request->file_id)){
                $fileFlag = true;
                $fileID = $request->file_id;
            }

        }

        $user = Auth::user();
        $type = $request->type;

        if($type == 'stripe'){
            $sessionID = $request->get('session_id');
        }
        else{
            $sessionID = $request->get('paymentId');
        }

        if($offer){ 
            $creditsForFile = $request->creditsForFile;
            $credits = $request->creditsToBuy;
            $invoice = $this->paymenttMainObj->addCredits($user, $sessionID, $credits, $type);
            $file = $this->filesMainObj->acceptOfferFinalise($user, $fileID, $creditsForFile, $frontendID);
        }

        if($fileFlag){

            $creditsForFile = $request->creditsForFile;
            $credits = $request->creditsToBuy;
            $invoice = $this->paymenttMainObj->addCredits($user, $sessionID, $credits, $type);
            $file = $this->filesMainObj->saveFile($user, $fileID, $creditsForFile);

        }

        else{

            if($request->packageID == 0){

                $credits = $request->credits;
                $invoice = $this->paymenttMainObj->addCredits($user, $sessionID, $credits, $type);
            }
            else{

                $package = true;
                $packageID = $request->packageID;
                $package = Package::findOrFail($packageID);
                $credits = $package->credits;
                $invoice = $this->paymenttMainObj->addCreditsPackage($user, $sessionID, $package, $type);
                
            }
        }

        if($user->zohobooks_id == NULL){

            $this->zohoMainObj->createZohoAccount($user);
        }

        if($user->zohobooks_id){
            $this->zohoMainObj->createZohobooksInvoice($user, $invoice, $package, $type, $request->packageID);
        }

        if($user->zohobooks_id == NULL){

            $this->zohoMainObj->createZohoAccount($user);
        }

        if($type == 'stripe'){
            $account = $user->stripe_payment_account();
            
        }
        else{
            $account = $user->paypal_payment_account();
        }

        if($account->elorus){

                    $clientID = null;

                    if($user->elorus_id){
                        $clientID = $user->elorus_id;
                    }
                    else{
                        $clientID = $this->elorusMainObj->createElorusCustomer($user);
                    }
                    
                    if(country_to_continent($user->country) == 'Europe'){

                        $this->elorusMainObj->createElorusInvoice($invoice, $clientID, $user, $package);

                    }
        }

        \Cart::remove(101);

        if($offer){
            return redirect()->route('file', $file->id)->with('success', 'Offer is accepted!');
        }

        if($fileFlag){
            return redirect()->route('history')->with('success', 'Task is created!');
        }

        return redirect()->route('shop-product')->with('success', 'Credits are added!');
        

    }

    public function buyPackage(Request $request){

        $user = Auth::user();

        $price = $request->price;
        $package = $request->package;
        $credits = $request->credits;

        $packages = $this->paymenttMainObj->getPackages($this->frontendID);

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
            $factor = (float)  ($user->group->raise / 100) * $price;
        }

        if($user->group->discount > 0){
            $factor =  -1* (float) ($user->group->discount / 100) * $price;
        }

        
        return view('cart_package', ['package' => $package,'credits' => $credits,'packages' => $packages, 'price' => $price, 'tax' => $tax, 'factor' => $factor, 'group' => $user->group, 'user' => $user] );

    }

    public function checkoutPackagesPaypal(Request $request){

        $user = Auth::user();
        $package =  Package::findOrFail($request->package);

        return $this->paymenttMainObj->redirectPaypal($user, $package->discounted_price, $package->credits, $package->id);        
    }

    public function checkoutPackagesStripe(Request $request){

        $user = Auth::user();
        $package =  Package::findOrFail($request->package);

        return $this->paymenttMainObj->redirectStripe($user, $package->discounted_price, $package->credits, $package->id);

    }

    public function paypalCheckout(Request $request){

        $user = Auth::user();
        $unitPrice =  $this->paymenttMainObj->getPrice()->value;
        $credits = $request->credits_for_checkout;
        return $this->paymenttMainObj->redirectPaypal($user, $unitPrice, $credits);

    }
    
    public function checkoutFile(Request $request){

        $creditsToBuy = $request->creditsToBuy;
        $creditsForFile = $request->creditsForFile;
        $fileID = $request->file_id;

        $type = $request->type;
        $user = Auth::user();
        $unitPrice =  $this->paymenttMainObj->getPrice()->value;
        
        if($type == 'stripe'){
            return $this->paymenttMainObj->redirectStripeFile($user, $unitPrice, $creditsToBuy, $creditsForFile, $fileID);
        }
        else{
            return $this->paymenttMainObj->redirectPaypalFile($user, $unitPrice, $creditsToBuy, $creditsForFile, $fileID);
        }

    }

    public function stripeCheckout(Request $request){
       
        $user = Auth::user();
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

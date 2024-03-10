<?php

namespace App\Http\Controllers;

use ECUApp\SharedCode\Controllers\PriceListMainController;
use ECUApp\SharedCode\Models\Service;
use ECUApp\SharedCode\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricelistController extends Controller
{

    private $pricelistMainObj;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->pricelistMainObj = new PriceListMainController();
    }

    /**
    * get Price List page.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */

    public function index(Request $request)
    {
        $user = User::findOrFail( Auth::user()->id );

        $type = $request->type;
        $vehicleType = $request->vehicle_type;
        
        $servicies = $this->pricelistMainObj->getPrices($vehicleType);
        
        return view('price_list', ['vehicleType' => $vehicleType, 'type' => $type, 'stages' => $servicies['stages'], 'options' => $servicies['options'], 'user' => $user]);
    }
}

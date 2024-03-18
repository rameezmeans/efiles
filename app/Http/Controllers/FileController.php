<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use ECUApp\SharedCode\Controllers\FilesMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Models\Credit;
use ECUApp\SharedCode\Models\File;
use ECUApp\SharedCode\Models\FileService;
use ECUApp\SharedCode\Models\Log;
use ECUApp\SharedCode\Models\Price;
use ECUApp\SharedCode\Models\Service;
use ECUApp\SharedCode\Models\StagesOptionsCredit;
use ECUApp\SharedCode\Models\TemporaryFile;
use ECUApp\SharedCode\Models\Tool;
use ECUApp\SharedCode\Models\User;
use ECUApp\SharedCode\Models\Vehicle;
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
    private $paymentMainObj;

    public function __construct(){

        $this->middleware('auth', [ 'except' => [ 'feedbackLink' ] ]);
        $this->filesMainObj = new FilesMainController();
        $this->paymentMainObj = new PaymentsMainController();

    }

    public function autoDownload(Request $request){

        $file = File::findOrFail($request->id);
        $user = User::findOrFail(Auth::user()->id);

        return view('files.auto_download', [ 'user' => $user, 'file' => $file ]);
    }

    public function addCredits(Request $request) {

        $user = User::findOrFail(Auth::user()->id);

        $account = $user->stripe_payment_account();

        $credits = $request->credits;
        $head =  get_head();

        $kess3Label = Tool::where('label', 'Kess_V3')->where('type', 'slave')->first();
        
        $creditsInAccount = $user->credits->sum('credits');

        if($creditsInAccount >= $request->credits){
            
            $tempFile = TemporaryFile::findOrFail($request->file_id)->toArray();

            $credit = new Credit();

            $credit->credits = -$credits;
            $credit->price_payed = 0;
            $credit->front_end_id = 2;
            $credit->invoice_id = 'INV-'.$account->prefix.mt_rand(100,999);
            $credit->user_id = $user->id;
            $credit->save();

            $tempFile['credit_id'] = $credit->id;
            $tempFile['checked_by'] = "customer";
            
            $tempFile['user_id'] = $user->id;
            $tempFile['username'] =  $user->name;
            $tempFile['assigned_to'] =  $head->id; // assigned to Nick

            if(File::where('credit_id', $credit->id)->first() === NULL){

                $file = File::create($tempFile);
                if($file->tool_type == 'slave' && $file->tool_id == $kess3Label->id){
                    $file->checking_status = 'undecided';
                }

                $file->credits = $credits;
                $file->credit_id = $credit->id;
                $file->front_end_id = $user->front_end_id;
                
                $file->assignment_time = Carbon::now();
                
                $modelToAdd = str_replace( '/', '', $file->model );
                $directoryToMake = public_path('uploads'.'/'.$file->brand.'/'.$modelToAdd.'/'.$file->id.'/');
                
                if($file->original_file_id == NULL){
                
                    if (!file_exists($directoryToMake)) {
                        $oldmask = umask(000);
                        mkdir( $directoryToMake , 0777, true);
                        umask($oldmask);        
                    }
                }

                if(file_exists(public_path('uploads').'/'.$file->file_attached)){
                    copy(public_path('uploads').'/'.$file->file_attached, $directoryToMake.$file->file_attached);
                    unlink(public_path('uploads').'/'.$file->file_attached);
                }

                if($file->original_file_id){
                    $file->file_path = '/uploads/'.$file->brand.'/'.$modelToAdd.'/'.$file->original_file_id.'/';
                }
                else{
                    $file->file_path = '/uploads/'.$file->brand.'/'.$modelToAdd.'/'.$file->id.'/';
                }

                $file->save();

                $logs = Log::where('temporary_file_id', $request->file_id)->update( ['file_id' => $file->id, 'temporary_file_id' => 0 ]);
                $services = FileService::where('temporary_file_id', $request->file_id)->update( ['file_id' => $file->id, 'temporary_file_id' => 0 ]);
                // $alientechFileFlag = AlientechFile::where('temporary_file_id', $request->file_id)->update( ['file_id' => $file->id, 'temporary_file_id' => 0 ]);
                
                $temporaryFileDelete = TemporaryFile::findOrFail($request->file_id)->delete();

                //download decoded files
                
                // if($file->tool_type == 'slave' && $file->tool_id == $kess3Label->id){

                //     if( $alientechFileFlag ){
                //         $alientechFile = AlientechFile::where('file_id', $file->id)->first();
                //         $fileName = $this->alientechObj->process( $alientechFile->guid );
                //         if($fileName){
                //             $file->checking_status = 'unchecked';
                //         }
                //     }
                    
                // }
                
                $credit->file_id = $file->id;
                $credit->save();
                
            }
            else{
                return view('505');   
            }

            $file->stage = Service::findOrFail($file->stages_services->service_id)->name;
            $file->is_credited = 1; // finally is_credited now ... 
            $file->save();
        }

        return redirect()->route('auto-download',['id' => $file->id]);
        
    }

    public function postStages(Request $request) {

        $stage = Service::FindOrFail($request->stage);
        $stageName = $stage->name;

        $rules = $this->filesMainObj->getStep3ValidationStage($stageName);

        $request->validate($rules);
        
        $fileID = $request->file_id;
        $DTCComments = $request->dtc_off_comments;
        $vmaxComments = $request->vmax_off_comments;

        $file = $this->filesMainObj->saveStagesInfo($fileID, $DTCComments, $vmaxComments);
        
        FileService::where('service_id', $stage->id)->where('temporary_file_id', $file->id)->delete();
        
        $servieCredits = 0;

        $servieCredits += $this->filesMainObj->saveFileStages($file, $stage);

        $options = $request->options;

        $servieCredits += $this->filesMainObj->saveFileOptions($file, $stage, $options);

        $price = $this->paymentMainObj->getPrice();

        $user = User::findOrFail(Auth::user()->id);

        return view( 'files.pay_credits', [ 
        'file' => $file, 
        'credits' => $servieCredits, 
        'price' => $price,
        'factor' => 0,
        'tax' => 0,
        'group' =>  $user->group,
        'user' =>  $user
        ] );

    }

    public function termsAndConditions() {

        return view('files.terms_and_conditions');

    }

    public function norefundPolicy() {

        return view('files.norefund_policy');

    }

    public function getOptionsForStage(Request $request) {

        $stageID = $request->stage_id;
        $optionsArray = $this->filesMainObj->getStagesAndOptions($stageID);

        return json_encode($optionsArray);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function step1(){

        $user = User::findOrFail(Auth::user()->id);

        $masterTools = $this->filesMainObj->getMasterTools($user);
        $slaveTools = $this->filesMainObj->getSlaveTools($user);

        $brands = $this->filesMainObj->getBrands();

        return view('files.step1', ['user' => $user, 'brands' => $brands,'masterTools' => $masterTools, 'slaveTools' => $slaveTools]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getModels(Request $request)
    {
        $brand = $request->brand;

        $models = $this->filesMainObj->getModels($brand);
        
        return response()->json( [ 'models' => $models ] );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getVersions(Request $request)
    {

        $model = $request->model;
        $brand = $request->brand;

        $versions = $this->filesMainObj->getVersians($brand, $model);

        return response()->json( [ 'versions' => $versions ] );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEngines(Request $request)
    {
        $model = $request->model;
        $brand = $request->brand;
        $version = $request->version;

        $engines = $this->filesMainObj->getEngines($brand, $model, $version);

        return response()->json( [ 'engines' => $engines ] );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getECUs(Request $request)
    {
        $model = $request->model;
        $brand = $request->brand;
        $version = $request->version;
        $engine = $request->engine;
       
        $ecusArray = $this->filesMainObj->getECUs($brand, $model, $version, $engine);
        return response()->json( [ 'ecus' => $ecusArray ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function step3(Request $request) {

        $user = User::findOrFail(Auth::user()->id);

        $frontendID = 2;

        $file = TemporaryFile::findOrFail($request->file_id);
        $vehicle = $file->vehicle();
        $vehicleType = $vehicle->type;

        $stages = $this->filesMainObj->getStagesForStep3($frontendID, $vehicleType);
        $options = $this->filesMainObj->getOptionsForStep3($frontendID, $vehicleType);

        $firstStage = $stages[0];
        
        return view( 'files.set_stages', ['user' => $user, 'firstStage' => $firstStage, 'vehicleType' => $vehicleType,'file' => $file, 'stages' => $stages, 'options' => $options] );

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function step2(Request $request) {

        $rules = $this->filesMainObj->getStep1ValidationTempfile();
        $file = $request->validate($rules);

        $data = $request->all();
        
        return $this->filesMainObj->addStep1InforIntoTempFile($data);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createTempFile(Request $request) {

        $user = User::findOrFail(Auth::user()->id);
        $frontendID = 2;
        $file = $request->file('file');

        $toolType = $request->tool_type_for_dropzone;
        $toolID = $request->tool_for_dropzone;

        $tempFile = $this->filesMainObj->createTemporaryFile($user, $file, $toolType, $toolID, $frontendID);

        return response()->json(['tempFileID' => $tempFile->id]);


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

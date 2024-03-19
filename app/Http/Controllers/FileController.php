<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use ECUApp\SharedCode\Controllers\FilesMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Models\Comment;
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showFile($id)
    {

        $user = User::findOrFail(Auth::user()->id);

        $file = File::where('id',$id)
        ->where('user_id', Auth::user()->id)
        ->whereNull('original_file_id')
        ->where('is_credited', 1)
        ->first();

        $kess3Label = Tool::where('label', 'Kess_V3')->where('type', 'slave')->first();

        if(!$file){
            abort(404);
        }

        $vehicle = Vehicle::where('Make', $file->brand)
        ->where('Model', $file->model)
        ->where('Generation', $file->version)
        ->where('Engine', $file->engine)
        ->first();
        
         if($file->checked_by == 'engineer'){
            $file->checked_by = 'seen';
            $file->save();
        }

        $slaveTools =  $user->tools_slave;
        $masterTools =  $user->tools_master;

        $comments = $this->getCommentsOnFileShowing($file);

        $showComments = false;

        $selectedOptions = [];

        foreach($file->options_services as $selected){
            $selectedOptions []= $selected->service_id;
        }

        if($comments){
            foreach($comments as $comment){
                if( in_array( $comment->service_id, $selectedOptions) ){
                    $showComments = true;
                }
            }
        }
        
        return view('files.show_file', ['user' => $user, 'showComments' => $showComments, 'comments' => $comments,'kess3Label' => $kess3Label,  'file' => $file, 'masterTools' => $masterTools,  'slaveTools' => $slaveTools, 'vehicle' => $vehicle ]);
    }

    public function getCommentsOnFileShowing($file){

        if($file->automatic){
            return null;
        }

        if($file->ecu){

            // $commentObj = Comment::where('engine', $file->engine)
            // ->whereNull('subdealer_group_id');

            $commentObj = Comment::where('comment_type', 'download')
            ->whereNull('subdealer_group_id');

            // $commentObj = $commentObj->where('comment_type', 'download');

            if($file->make){
                $commentObj->where('make',$file->make);
            }

            // if($file->model){
            //     $commentObj->where('model', $file->model);
            // }

            if($file->ecu){
                $commentObj->where('ecu',$file->ecu);
            }

            // if($file->generation){
            //     $commentObj->where('generation', $file->generation);
            // }

            $comments = $commentObj->get();
        }
        else{

            $comments = null;
        }
         
        return $comments;

    }

    public function saveFile(Request $request) {

        $fileID = $request->file_id;
        $credits = $request->credits;
        
        $user = User::findOrFail(Auth::user()->id);

        $file = $this->filesMainObj->saveFile($user, $fileID, $credits);

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

    public function getUploadComments(Request $request){
        
        $tempFileID = $request->file_id;
        $serviceID = $request->service_id;

        $comment = $this->filesMainObj->getStagePageComments($tempFileID, $serviceID);

        return response()->json(['comment'=> $comment]);
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

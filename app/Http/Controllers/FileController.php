<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use ECUApp\SharedCode\Controllers\AlientechMainController;
use ECUApp\SharedCode\Controllers\AutotunerMainController;
use ECUApp\SharedCode\Controllers\FilesMainController;
use ECUApp\SharedCode\Controllers\MagicsportsMainController;
use ECUApp\SharedCode\Controllers\NotificationsMainController;
use ECUApp\SharedCode\Controllers\PaymentsMainController;
use ECUApp\SharedCode\Models\AlientechFile;
use ECUApp\SharedCode\Models\Comment;
use ECUApp\SharedCode\Models\Credit;
use ECUApp\SharedCode\Models\EmailReminder;
use ECUApp\SharedCode\Models\EngineerFileNote;
use ECUApp\SharedCode\Models\FilesStatusLog;
use ECUApp\SharedCode\Models\ECU;
use ECUApp\SharedCode\Models\Modification;
use ECUApp\SharedCode\Models\File;
use ECUApp\SharedCode\Models\FileFeedback;
use ECUApp\SharedCode\Models\FileInternalEvent;
use ECUApp\SharedCode\Models\FileService;
use ECUApp\SharedCode\Models\FileUrl;
use ECUApp\SharedCode\Models\FrontEnd;
use ECUApp\SharedCode\Models\Log;
use ECUApp\SharedCode\Models\MagicEncryptedFile;
use ECUApp\SharedCode\Models\AutotunerEncrypted;
use ECUApp\SharedCode\Models\Price;
use ECUApp\SharedCode\Models\ProcessedFile;
use ECUApp\SharedCode\Models\RequestFile;
use ECUApp\SharedCode\Models\Service;
use ECUApp\SharedCode\Models\StagesOptionsCredit;
use ECUApp\SharedCode\Models\TemporaryFile;
use ECUApp\SharedCode\Models\Tool;
use ECUApp\SharedCode\Models\User;
use ECUApp\SharedCode\Models\Vehicle;
use ECUApp\SharedCode\Models\BrandECUComments;
use ECUApp\SharedCode\Models\FileReplySoftwareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log as FacadesLog;

use Illuminate\Support\Facades\Http;


use Pusher\Pusher;

class FileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    private $filesMainObj;
    private $paymentMainObj;
    private $notificationsMainObj;
    private $frontendID;
    private $alientechMainObj;
    private $magicMainObj;
    private $autoTunerMainObj;

    public function __construct(){

        $this->frontendID = 3;

        $this->middleware('auth', [ 'except' => [ 'feedbackLink' ] ]);
        $this->filesMainObj = new FilesMainController();
        $this->paymentMainObj = new PaymentsMainController();
        $this->notificationsMainObj = new NotificationsMainController();
        $this->alientechMainObj = new AlientechMainController();
        $this->magicMainObj = new MagicsportsMainController();
        $this->autoTunerMainObj = new AutotunerMainController();
    }

    public function acmFileUpload(Request $request){

        $file = File::findOrFail($request->file_id);

        $fileUploaded = $request->file('acm_file');
        $fileName = $fileUploaded->getClientOriginalName();
        $fileName = $this->filesMainObj->getFilename($fileName);
        $fileUploaded->move(public_path($file->file_path),$fileName);

       
        $file->acm_file = $fileName;
        $file->save();

        return redirect()->back()->with('success', 'ACM file successfully Added!');

    }

    public function authPusher(Request $request){

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
        );

        $chatUser = User::findOrFail(env('LIVE_CHAT_ID'));

        // Auth data
        $authData = json_encode([
            'user_id' => $chatUser->id,
            'user_info' => [
                'name' => $chatUser->name
            ]
        ]);
        
        return $pusher->socket_auth(
            $request->channel_name,
            $request->socket_id,
            $authData
        );
            
        // if not authorized
        return response()->json(['message'=>'Unauthorized'], 401);
        
    }

    public function getDownloadButton(Request $request){

        $file = File::findOrFail($request->file_id);

        if($file->no_longer_auto == 0){

            $downloadButton = '<a class="btn" style="background: #f02429 !important;" href="'.route("download", [$file->id,$file->engineer_file->request_file]).'">
            <i class="fa fa-download"></i> Download
            </a>';
        }
        else{
            
            $downloadButton = "<p>Your file will be processed by our engineers, you will hear from them very soon.</p>";
        }
        
        // $downloadButton = 
        //         '
        //         <p>Success, your file is ready for download.</p>
        //         <button style="background: #f02429 !important;" class="btn btn-download" 
        //         data-make="'.$file->brand.'" 
        //         data-engine="'.$file->engine.'" 
        //         data-ecu="'.$file->ecu.'" 
        //         data-model="'.$file->model.'" 
        //         data-generation="'.$file->version.'" 
        //         data-file_id="'.$file->id.'" 
        //         data-path="'.route("download", [$file->id, $file->engineer_file->request_file]).'"
        //         >
                    
        //             <i class="fa fa-download"></i>
        //             Download
        //         </button>';

        return  response()->json( ['download_button' => $downloadButton] );
    }

    public function changeCheckingStatus(Request $request){

        $file = File::findOrFail($request->file_id);
        if($file->checking_status == 'unchecked'){
            $file->checking_status = 'fail';
            $file->save();
            return  response()->json( ['msg' => 'status set to fail', 'fail' => 1, 'file_id' => $file->id] );
        }
        if($file->checking_status == 'completed'){
            return response()->json( ['msg' => 'status was completed', 'fail' => 2, 'file_id' => $file->id] );
        }
        return response()->json( ['msg' => 'status not set to fail', 'fail' => 0, 'file_id' => $file->id] );
    }

    public function getComments(Request $request){

        $file = File::findOrFail($request->file_id);

        if($request->ecu){

            // $commentObj = Comment::where('engine', $request->engine);

            $commentObj = Comment::where('comment_type', 'download')->whereNull('subdealer_group_id');

            // $commentObj = Comment::where('engine', $request->engine);
            // $commentObj = $commentObj->where('comment_type', 'download');

            if($request->make){
                $commentObj->where('make',$request->make);
            }

            // if($request->model){
            //     $commentObj->where('model', $request->model);
            // }

            if($request->ecu){
                $commentObj->where('ecu',$request->ecu);
            }

            // if($request->generation){
            //     $commentObj->where('generation', $request->generation);
            // }

            $comments = $commentObj->get()->toArray();
        }
        else{

            $comments = [];
        }

        // if($file->show_comments == 0){
        //     $comments = [];
        // }
        
        $optionsArray = [];

        foreach($file->options_services as $option){
            $optionsArray []= Service::findOrFail($option->service_id)->id;
        }

        $optionComment = "";

        if(sizeof($comments) != 0){

            $optionComment .= '<ul class="bullets">';

            foreach($comments as $comment){
                if(in_array($comment['service_id'],$optionsArray)){
                    $optionComment  .= '<li class="comments">'.__($comment['comments']).'</li>';
                }
            }

            $optionComment .= '</ul>';
        }

        return response()->json(['comments'=> $optionComment]);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function fileURL(Request $request)
    {

        $validated = $request->validate([
            'file_url' => 'required'
        ]);

        $file = File::findOrFail($request->file_id);
        $message = new FileUrl();
        $message->file_url = $request->file_url;
       
        if($request->file('file_url_attachment')){
            $attachment = $request->file('file_url_attachment');
            $fileName = $attachment->getClientOriginalName();
            $attachment->move(public_path($file->file_path),$fileName);
            $message->file_url_attachment = $fileName;
        }

        $message->file_id = $request->file_id;
        $message->save();
        return redirect()->back()->with('success', 'Personal Note successfully Added!');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function addCustomerNote(Request $request)
    {
        $file = File::findOrFail($request->id);
        $file->name = $request->name;
        $file->phone = $request->phone;
        $file->email = $request->email;
        $file->customer_internal_notes = $request->customer_internal_notes;
        $file->save();
        return redirect()->back()->with('success', 'File successfully Edited!');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function fileEngineersNotes(Request $request)
    {
        $validated = $request->validate([
            'egnineers_internal_notes' => [
                'required',
                'max:1024',
                // Prevent PHP or JS code in notes
                'not_regex:/<\s*script/i',
                'not_regex:/<\?php/i',
            ],
            'engineers_attachement' => [
                'nullable',
                'file',
                'max:20480', // 20 MB
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());

                    // Block only PHP and JS extensions
                    if (in_array($extension, ['php', 'js'])) {
                        $fail("The {$attribute} must not be a PHP or JS file.");
                    }
                },
            ],
        ]);

        $file = File::findOrFail($request->file_id);

        $noteItSelf = $request->egnineers_internal_notes;

        $reply = new EngineerFileNote();
        $reply->egnineers_internal_notes = $request->egnineers_internal_notes;

        if ($request->file('engineers_attachement')) {
            $attachment = $request->file('engineers_attachement');
            $originalName = $attachment->getClientOriginalName();

            $sanitizedName = str_replace(['/', '\\', '#', ' '], ['', '', '', '_'], $originalName);

            // Add timestamp before file extension
            $timestamp = time();
            $extension = $attachment->getClientOriginalExtension();
            $nameWithoutExt = pathinfo($sanitizedName, PATHINFO_FILENAME);
            $fileName = $nameWithoutExt . '_' . $timestamp . '.' . $extension;

            $attachment->move(public_path($file->file_path), $fileName);
            $reply->engineers_attachement = $fileName;
        }

        $reply->file_id = $request->file_id;
        $reply->request_file_id = $request->request_file_id;
        $reply->sent_by = 'engineer';
        $reply->save();

        if($file->support_status == 'closed'){

            $this->changeStatusLog($file, 'open', 'support_status', "Customer sent a message in chat");
            $file->support_status = "open";
            $file->timer = NULL;
            $file->assigned_to = NULL;
            $file->save();

        }

        // if($file->original_file_id != NULL){
        //     $ofile = File::findOrFail($file->original_file_id);

        //     if($ofile->support_status == 'closed'){
        //         $this->changeStatusLog($ofile, 'open', 'support_status', "Customer sent a message in chat in request file.");
        //         $ofile->support_status = "open";
        //         $ofile->timer = NULL;
        //         $ofile->save();
        //     }
        // }

        $engPermissions = array(
            0 => 'msg_cus_eng_email',
            1 => 'msg_cus_eng_sms',
            2 => 'msg_cus_eng_whatsapp'
        );

        if($file->assigned_to){

            $uploader = User::findOrFail($file->user_id);
            $engineer = User::FindOrFail($file->assigned_to);
            $subject = "E-files: Client support message!";
            $this->notificationsMainObj->sendNotification($engineer, $file, $uploader, $this->frontendID, $subject, 'mess-to-eng', 'message_to_engineer', $engPermissions);

            $adminPermissions = array(
                0 => 'msg_cus_admin_email',
                1 => 'msg_cus_admin_sms',
                2 => 'msg_cus_admin_whatsapp'
            );

            $uploader = User::findOrFail($file->user_id);
            $admin = get_admin();
            $subject = "E-files: Client support message!";
            $this->notificationsMainObj->sendNotification($admin, $file, $uploader, $this->frontendID, $subject, 'mess-to-eng', 'message_to_engineer', $adminPermissions);
        }

        return redirect()->back()->with('success', 'Engineer note successfully Added!');
    }

    public function acceptOffer(Request $request) {

        $fileID = $request->file_id;
        $file = File::findOrFail($fileID);
        $user = Auth::user();

        $this->filesMainObj->acceptOfferWithoutPayingCredits($file, $user);

        $customerPermission = array(
            0 => 'status_change_cus_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp'
        );

        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($customer, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $customerPermission);

        $adminPermission = array(
            0 => 'status_change_admin_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp'
        );

        $admin = get_admin();
        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($admin, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $adminPermission);

    }

    public function rejectOffer(Request $request) {

        $fileID = $request->file_id;
        $file = File::findOrFail($fileID);
        $user = Auth::user();

        $this->filesMainObj->rejectOffer($file, $user);

        $customerPermission = array(
            0 => 'status_change_cus_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp'
        );

        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($customer, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $customerPermission);

        $adminPermission = array(
            0 => 'status_change_admin_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp'
        );

        $admin = get_admin();
        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($admin, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $adminPermission);

    }   

    public function payCreditsOffer($id) {

        $file = File::findOrfail($id);
 
        $proposedCredits = $this->filesMainObj->getOfferedCredits($file);
        $differece = $proposedCredits - $file->credits;
        
        $price = Price::where('label', 'credit_price')->where('front_end_id', 3)->first();
 
        $user = Auth::user();
 
        $factor = 0;
        $tax = 0;
 
        if($user->group){
            if($user->group->tax > 0){
                $tax = (float) $user->group->tax;
            }

            if($user->group->raise > 0){
                $factor = (float)  ($user->group->raise / 100) * $price->value;
            }

            if($user->group->discount > 0){
                $factor =  -1* (float) ($user->group->discount / 100) * $price->value;
            }
         }
 
        return view( 'files.pay_credits_offer', [ 
         'file_id' => $file->id, 
         'file' => $file, 
         'credits' => $differece, 
         'price' => $price,
         'factor' => $factor,
         'tax' => $tax,
         'group' =>  $user->group,
         'user' =>  $user
         ] );
 
     }
 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function fileFeedback(Request $request)
    {
        FileFeedback::where('request_file_id','=', $request->request_file_id)->delete();

        $reminder = EmailReminder::where('file_id', $request->file_id)->where('request_file_id', $request->request_file_id)->where('user_id', Auth::user()->id)->first();
       
        if($reminder){
            $reminder->delete();
        }

        $requestFile = new FileFeedback();
        $requestFile->file_id = $request->file_id;
        $requestFile->request_file_id = $request->request_file_id;
        $requestFile->type = $request->type;
        $requestFile->save();

        return response()->json($requestFile);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createNewrequest(Request $request)
    {
        $rules = $this->filesMainObj->getNewReqValidationRules();
        $request->validate($rules);
        $data = $request->all();
        $file = $request->file('request_file');

        return $this->filesMainObj->createNewRequest($data, $file);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function EditMilage(Request $request)
    {
        
        $file = File::findOrFail($request->id);
        $file->vin_number = $request->vin_number;
        $file->license_plate = $request->license_plate;
        $file->first_registration = $request->first_registration;
        $file->kilometrage = $request->kilometrage;
        $file->vehicle_internal_notes = $request->vehicle_internal_notes;
        $file->save();
        return redirect()->back()->with('success', 'File successfully Edited!');

    }

    public function download($id,$fileName) {

        // if($engFileID){
        //     $engFile = RequestFile::findOrFail($engFileID);
        // }
        
        $file = File::findOrFail($id); 

        $kess3Label = Tool::where('label', 'Kess_V3')->where('type', 'slave')->first();
        $flexLabel = Tool::where('label', 'Flex')->where('type', 'slave')->first();
        $autoTunerLabel = Tool::where('label', 'Autotuner')->where('type', 'slave')->first();

        $engFile = RequestFile::where('request_file', $fileName)->where('file_id', $file->id)->first();

        if($engFile){
            $engFile->downloaded_at = Carbon::now();
            $engFile->save();
        }
        
        if($file->tool_type == 'slave' && $file->tool_id == $kess3Label->id){

            // if($file->original_file_id == NULL){

            $engFile = RequestFile::where('request_file', $fileName)->where('file_id', $file->id)->first();
            
            if($engFile && $engFile->uploaded_successfully){

            $notProcessedAlientechFile = AlientechFile::where('file_id', $file->id)
            ->where('purpose', 'decoded')
            ->where('type', 'download')
            ->where('processed', 0)
            ->first();

            if($notProcessedAlientechFile){
               
                $fileNameEncoded = $this->alientechMainObj->downloadEncodedFile($id, $notProcessedAlientechFile, $fileName);
                $notProcessedAlientechFile->processed = 1;
                $notProcessedAlientechFile->save();
                
                $file_path = public_path($file->file_path).$fileNameEncoded;
                return response()->download($file_path);
            }
            else{
                $encodedFileNameToBe = $fileName.'_encoded_api';
                $processedFile = ProcessedFile::where('name', $encodedFileNameToBe)->where('type', 'encoded')->first();

                if($processedFile){

                // if($processedFile->extension != ''){
                //     $finalFileName = $processedFile->name.'.'.$processedFile->extension;
                // }
                // else{
                    $finalFileName = $processedFile->name;
                // }

                $file_path = public_path($file->file_path).$finalFileName;
                return response()->download($file_path);

                }
                else{
                    abort(404);
                }
            }
        }
        else{
            // abort(404);
            $file_path = public_path($file->file_path).$fileName;
            return response()->download($file_path);
        }
    }

    else if($file->tool_type == 'slave' && $file->tool_id == $flexLabel->id){
            
            $magicFile = MagicEncryptedFile::where('file_id', $file->id)
            ->where('name', $fileName.'_magic_encrypted.mmf')
            ->where('downloadable', 1)
            ->first();

            if($magicFile){
    
                $file_path = public_path($file->file_path).$magicFile->name;
                return response()->download($file_path);
            }
            else{
                $file_path = public_path($file->file_path).$fileName; // quick fix. need to work a bit more.
                return response()->download($file_path);
            }
        }

        else if($file->tool_type == 'slave' && $file->tool_id == $autoTunerLabel->id){
            
            $autotunerFile = AutotunerEncrypted::where('file_id', $file->id)
            ->where('name', $fileName.'_encrypted.slave')
            ->first();
            
            if($autotunerFile){
    
                $file_path = public_path($file->file_path).$autotunerFile->name;
                return response()->download($file_path);
            }
            else{
                $file_path = public_path($file->file_path).$fileName;
                return response()->download($file_path);
            }

        }

        else{
            $file_path = public_path($file->file_path).$fileName;
            return response()->download($file_path);
        }
    }
    
    public function autoDownload(Request $request){

        $file = File::findOrFail($request->id);
        $user = Auth::user();

        return view('files.auto_download', [ 'user' => $user, 'file' => $file ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showFile($id)
    {
        $user = Auth::user();
        $kess3Label = Tool::where('label', 'Kess_V3')->where('type', 'slave')->first();

        $file = $this->filesMainObj->getFile($id, $user);
        $ofile = $this->filesMainObj->getFile($id, $user);
        $vehicle = $this->filesMainObj->getVehicle($file);
        
        $slaveTools =  $user->tools_slave;
        $masterTools =  $user->tools_master;

        $comments = $this->filesMainObj->getCommentsOnFileShowing($file);

        $selectedOptions = $this->filesMainObj->getSelectedOptions($file);

        $showComments = $this->filesMainObj->getShowComments($selectedOptions, $comments);
        
        return view('files.show_file', ['user' => $user, 'showComments' => $showComments, 'comments' => $comments,'kess3Label' => $kess3Label,  'file' => $file, 'ofile' => $ofile, 'masterTools' => $masterTools,  'slaveTools' => $slaveTools, 'vehicle' => $vehicle ]);
    }

    public function addOfferToFile(Request $request) {

        $fileID = $request->file_id;
        $creditsToBuy = $request->credits;

        $user = Auth::user();

        $file = $this->filesMainObj->acceptOfferFinalise($user, $fileID, $creditsToBuy, $this->frontendID);

        $customerPermission = array(
            0 => 'status_change_cus_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp',
        );

        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($customer, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $customerPermission);

        $adminPermission = array(
            0 => 'status_change_admin_email',
            1 => 'status_change_cus_sms',
            2 => 'status_change_cus_whatsapp',
        );

        $admin = get_admin();
        $customer = Auth::user();
        $subject = "E-files: File Status Changed!";
        $this->notificationsMainObj->sendNotification($admin, $file, $customer, $this->frontendID, $subject, 'sta-cha', 'status_change', $adminPermission);

        if($file->original_file_id){
            return redirect(route('file', $file->original_file_id))->with(['success' => 'Engineer offer accepted!']);
        }

        else{
            return redirect(route('file', $fileID))->with(['success' => 'Engineer offer accepted!']);
        }
    
    }

    public function saveFile(Request $request) {

        $tempFileID = $request->file_id;
        $credits = $request->credits;
        
        $user = Auth::user();
        $file = $this->filesMainObj->saveFile($user, $tempFileID, $credits);
        
        // $this->filesMainObj->notifications($file);
        
        // return redirect()->route('auto-download',['id' => $file->id]);
        return redirect()->route('history');
        
    }

    public function postStages(Request $request) {

        $stage = Service::FindOrFail($request->stage);
        $stageName = $stage->name;

        $options = $request->options;

        $validation = $this->filesMainObj->getStep3ValidationStage($stageName, $options);

        $request->validate($validation['rules'], $validation['messages']);
        
        $fileID = $request->file_id;
        // $DTCComments = $request->dtc_off_comments;
        // $vmaxComments = $request->vmax_off_comments;

        $optionComments = $request->option_comments;

        $file = $this->filesMainObj->saveStagesInfo($fileID, $optionComments);
        
        FileService::where('service_id', $stage->id)->where('temporary_file_id', $file->id)->delete();
        
        $serviceCredits = 0;

        $serviceCredits += $this->filesMainObj->saveFileStages($file, $stage, $this->frontendID);


        $serviceCredits += $this->filesMainObj->saveFileOptions($file, $stage, $options, $this->frontendID);

        $price = $this->paymentMainObj->getPrice();

        $user = Auth::user();
        
        return view( 'files.pay_credits', [ 
        'file' => $file, 
        'credits' => $serviceCredits, 
        'price' => $price,
        'factor' => 0,
        'tax' => 0,
        'group' =>  $user->group,
        'user' =>  $user
        ] );

    }

    public function termsAndConditions() {

        $user = Auth::user();

        return view('files.terms_and_conditions', ['user' => $user]);

    }

    public function norefundPolicy() {

        $user = Auth::user();

        return view('files.norefund_policy', ['user' => $user]);

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
    public function fileEventsNotes(Request $request)
    {

        $file = File::findOrFail($request->file_id);

       $validated = $request->validate([
            'events_internal_notes' => [
                'required',
                'max:1024',
                // Prevent PHP or JS code in notes
                'not_regex:/<\s*script/i',
                'not_regex:/<\?php/i',
            ],
            'events_attachement' => [
                'nullable',
                'file',
                'max:20480', // 20 MB
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());

                    // Block only PHP and JS extensions
                    if (in_array($extension, ['php', 'js'])) {
                        $fail("The {$attribute} must not be a PHP or JS file.");
                    }
                },
            ],
        ]);

        $reply = new FileInternalEvent();
        $reply->events_internal_notes = $request->events_internal_notes;
       
        if($request->file('events_attachement')){
            $attachment = $request->file('events_attachement');
            $fileName = $attachment->getClientOriginalName();
            $attachment->move( public_path($file->file_path) ,$fileName);
            $reply->events_attachement = $fileName;
        }

        $reply->file_id = $request->file_id;
        $reply->request_file_id = $request->request_file_id;
        $reply->save();
        return redirect()->back()->with('success', 'Events note successfully Added!');
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
    public function addFileLog(Request $request){
        $this->filesMainObj->addFileLog($request->event, $request->disc);
    }

    public function getCommentByBrandEcuDownloadType(Request $request){

        // dd($request->all());

        $brand = $request->input('brand');
        $ecu = $request->input('ecu');

        $comment = BrandECUComments::where('brand', $brand)
            ->where('ecu', $ecu)
            ->where('type', 'download')
            ->first();

        if ($comment) {
            return response()->json([
                'success' => true,
                'comment' => $comment->comment
            ]);
        }

        return response()->json([
            'success' => false
        ]);
    }
    
    public function getCommentByBrandEcuUploadType(Request $request)
    {
        $brand = $request->input('brand');
        $ecu = $request->input('ecu');

        $comment = BrandECUComments::where('brand', $brand)
            ->where('ecu', $ecu)
            ->where('type', 'upload')
            ->first();

        if ($comment) {
            return response()->json([
                'success' => true,
                'comment' => $comment->comment
            ]);
        }

        return response()->json([
            'success' => false
        ]);
    }

    public function step1(){

        $user = Auth::user();

        $frontend = FrontEnd::findOrFail($user->front_end_id);
        $cautionText = $frontend->caution_text;

		$gearboxECUs = ECU::all();

        $modifications = Modification::all();

        $masterTools = $this->filesMainObj->getMasterTools($user);
        $slaveTools = $this->filesMainObj->getSlaveTools($user);

        $brands = $this->filesMainObj->getBrands();

        return view('files.step1', [ 'modifications' => $modifications,'cautionText' => $cautionText, 'gearboxECUs' => $gearboxECUs, 'user' => $user, 'brands' => $brands,'masterTools' => $masterTools, 'slaveTools' => $slaveTools]);
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
    public function getType(Request $request){
        
        $model = $request->model;
        $brand = $request->brand;
        $version = $request->version;
        $engine = $request->engine;

        $vehicle = Vehicle::where('Make', '=', $brand)
        ->where('Model', '=', $model)
        ->where('Generation', '=', $version)
        ->where('Engine', '=', $engine)
        // ->whereNotNull('Brand_image_url') // url_chnage
        ->first();

        if($vehicle){
            return response()->json( [ 'type' => $vehicle->type ]);
        }
        else{
            return response()->json( [ 'type' => 'no type' ]);
        }

    }

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

        $user = Auth::user();

        $file = TemporaryFile::findOrFail($request->file_id);
        $vehicle = $file->vehicle();
        
        if($vehicle != NULL){
            $vehicleType = $vehicle->type;
        }
        else{
            return redirect()->route('upload')->with('success', 'There is no Vehilce with Specification you entered.');
        }

        $stages = $this->filesMainObj->getStagesForStep3($this->frontendID, $vehicleType);
        $options = $this->filesMainObj->getOptionsForStep3($this->frontendID, $vehicleType);
        
        $firstStage = $stages[0];
        
        return view( 'files.set_stages', ['user' => $user, 'firstStage' => $firstStage, 'vehicleType' => $vehicleType,'file' => $file, 'stages' => $stages, 'options' => $options] );

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function step2(Request $request) {
        
        $fileUploaded = $request->file('acm_file');
        $rules = $this->filesMainObj->getStep1ValidationTempfile($request->all());
        $file = $request->validate($rules);

        $data = $request->all();
        
        return $this->filesMainObj->addStep1InforIntoTempFile($data, $fileUploaded);
    }

    public function downloadFile(Request $request){

        $mode = $request->mode;
        // dd($mode);
        $stage = Service::FindOrFail($request->stage);
        // $stageName = $stage->name;
        $outputFileUrl = $request->output_file_url;

        // $options = $request->options;
        // $validation = $this->filesMainObj->getStep3ValidationStage($stageName, $options);
        // $request->validate($validation['rules'], $validation['messages']);
        
        $fileID = $request->file_id;
        // $DTCComments = $request->dtc_off_comments;
        // $vmaxComments = $request->vmax_off_comments;

        $optionComments = $request->option_comments;
        $file = $this->filesMainObj->saveStagesInfo($fileID, $optionComments);
        FileService::where('service_id', $stage->id)->where('temporary_file_id', $file->id)->delete();
        
        $serviceCredits = 0;
        $serviceCredits += $this->filesMainObj->saveFileStages($file, $stage, $this->frontendID);
        // $serviceCredits += $this->filesMainObj->saveFileOptions($file, $stage, $options, $this->frontendID);

        $price = $this->paymentMainObj->getPrice();
        $user = Auth::user();
        
        return view( 'files.pay_credits_download_file', [ 
        'file' => $file, 
        'outputFileUrl' => $outputFileUrl, 
        'mode' => $mode, 
        'credits' => $serviceCredits, 
        'price' => $price,
        'factor' => 0,
        'tax' => 0,
        'group' =>  $user->group,
        'user' =>  $user
        ] );
    }

    public function downloadAutoFileAndCreateTask(Request $request){

        // dd($request->all());

        $tempFileID = $request->file_id;
        $credits = $request->credits;
        $outputFileUrl = $request->outputFileUrl;
        
        $user = Auth::user();
        $file = $this->filesMainObj->saveFile($user, $tempFileID, $credits);

        

        // Ensure folder path exists
    $destinationFolder = public_path(trim($file->file_path, '/')); // e.g. public/uploads/Porsche/Turbo/9865
    // Get filename from URL
    $fileName = basename(parse_url($outputFileUrl, PHP_URL_PATH));
    

    $engineerFile = new RequestFile();
    $engineerFile->request_file = $fileName;
    $engineerFile->old_name = $fileName;
    $engineerFile->file_type = 'engineer_file';
    $engineerFile->tool_type = 'not_relevant';
    $engineerFile->master_tools = 'not_relevant';
    $engineerFile->file_id = $file->id;
    $engineerFile->user_id = Auth::user()->id;
    $engineerFile->engineer = true;

    $engineerFile->save();

    $newRecord = new FileReplySoftwareService();
    $newRecord->file_id = $file->id;
    $newRecord->service_id = $file->stage_services->service_id;
    $newRecord->software_id = 9;
    $newRecord->reply_id = $engineerFile->id;
    $newRecord->save();

    $middleName = $file->id;
        $middleName .= date("dmy");
        
        // dd($file->softwares);

        foreach($file->softwares as $s){
            if($s->service_id != 1){
                if($s->reply_id == $engineerFile->id){
                    $middleName .= $s->service_id.$s->software_id;
                }
            }
        }

    $newFileName = $file->brand.'_'.$file->model.'_'.$middleName.'_v'.$file->files->count();
        
        $newFileName = str_replace('/', '', $newFileName);
        $newFileName = str_replace('\\', '', $newFileName);
        $newFileName = str_replace('#', '', $newFileName);
        $newFileName = str_replace(' ', '_', $newFileName);

        $destinationPath = $destinationFolder . '/' . $newFileName;

    try{
            $fileContents = file_get_contents($outputFileUrl);

            // dd($fileContents);

            if ($fileContents === false) {

                dd("404");
                return back()->with('error', 'Failed to download file from remote server.');
            }

            // dd($destinationFolder);

            // Save the file to target folder
            file_put_contents($destinationPath, $fileContents);

            $engineerFile->request_file = $newFileName;
            $engineerFile->save();

            if($file->status == 'submitted'){
                $file->status = 'completed';
                $file->status = 'completed';
                $file->support_status = "closed";
                $file->checked_by = 'engineer';
                $file->save();
            }

            $file->reupload_time = Carbon::now();
            $file->response_time = $this->getResponseTimeAutoAPI($file);
            $file->automatic = 1;
            $file->save();


            return response()->view('files.download_then_redirect', [
                'downloadUrl' => asset(str_replace(public_path(), '', $destinationPath)),
                'redirectUrl' => route('file', $file->id),
            ]);

            // return redirect()->route('file', $file->id);

        } catch (\Exception $e) {

            dd($e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        // dd($engineerFile);

        return redirect()->route('file', $file->id);

    }

    public function getResponseTimeAutoAPI($file){
        
        $fileAssignmentDateTime = Carbon::parse($file->created_at);
        $carbonUploadDateTime = Carbon::parse($file->reupload_time);

        $responseTime = $carbonUploadDateTime->diffInSeconds( $fileAssignmentDateTime );

        return $responseTime;
    }

    public function checkAutoFile(Request $request){

        // Resolve stage -> mode slug
        $stage = Service::findOrFail($request->stage_id);
        $mode  = Str::of($stage->name)->lower()->replace(' ', '_'); // e.g. "Stage 1" -> "stage_1"

        // Build arguments expected by the external service
        $arguments = [
            // 'file_id'         => $request->found_file_id,   // <- uncomment if your API needs it
            // 'input_file_path' => $request->found_file_path, // <- uncomment if your API needs it
            'input_file_path'             => 'undefined',
            'mode'                        => $mode, // keep as-is if your API expects this literal
            'ENABLE_MAX_DIFF_AREA'        => 'off',
            'max_diff_area'               => 2000,
            'ENABLE_MAX_DIFF_BYTES'       => 'on',
            'max_diff_byte'               => 10000,
            'MIN_SIMILARITY_DIFF_THRESHOLD' => 0.85,
            'timeout'                     => 10,
            'loop'                        => 10,
        ];

        try {
            // Normalize nulls
            $safe = array_map(fn($v) => $v ?? '', $arguments);

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withBody(json_encode($safe), 'application/json')
                ->post('http://212.205.214.152:5000/external-api2');

            // HTTP-level failures
            if ($response->failed()) {
                FacadesLog::warning('Auto-check HTTP failure', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'available' => false,
                    'mode'      => (string) $mode,
                    'message'   => 'Automatic delivery unavailable (remote service error).',
                ]);
            }

            // Parse and normalize the remote payload
            $data = $response->json() ?? [];

            // Accept a few possible shapes:
            // 1) { STATUS: "SUCCESS", OUTPUT_FILE_URL: "http://..." }
            // 2) { available: true/false, output_file_url?: "http://...", message?: "..." }
            // 3) Anything else -> treat as manual
            $status          = strtoupper((string)($data['STATUS'] ?? ''));
            $availableFlag   = $data['available'] ?? null; // may not exist
            $remoteUrl       = $data['OUTPUT_FILE_URL'] ?? $data['output_file_url'] ?? null;
            $remoteMessage   = $data['message'] ?? null;

            // Case A: explicit boolean from API
            if (is_bool($availableFlag)) {
                if ($availableFlag && $remoteUrl) {
                    return response()->json([
                        'available'       => true,
                        'mode'            => (string) $mode,
                        'message'         => $remoteMessage ?: 'This modification can be delivered automatically.',
                        'output_file_url' => $remoteUrl,
                    ]);
                }

                return response()->json([
                    'available' => false,
                    'mode'      => (string) $mode,
                    'message'   => $remoteMessage ?: 'Automatic delivery not available for this selection.',
                ]);
            }

            // Case B: STATUS contract
            if ($status === 'SUCCESS' && $remoteUrl) {
                return response()->json([
                    'available'       => true,
                    'mode'            => (string) $mode,
                    'message'         => 'This modification can be delivered automatically.',
                    'output_file_url' => $remoteUrl,
                ]);
            }

            // Fallback: treat as manual (no URL)
            return response()->json([
                'available' => false,
                'mode'      => (string) $mode,
                'message'   => $remoteMessage ?: 'Automatic delivery not available (no file returned).',
            ]);

        } catch (\Throwable $e) {
            FacadesLog::error('Auto-check exception', ['error' => $e->getMessage()]);

            return response()->json([
                'available' => false,
                'mode'      => (string) $mode,
                'message'   => 'Automatic delivery unavailable (exception).',
            ]);
        }
    }

    // public function checkAutoFile(Request $request){

    //     try {
    //         // ✅ Get stage name and convert to mode
    //         $stage = Service::findOrFail($request->stage_id);
    //         $stageName = $stage->name ?? 'Unknown Stage';
    //         $mode = strtolower(str_replace(' ', '_', $stageName));

    //         // ✅ Prepare arguments for API call (currently not used)
    //         $foundFilID = $request->found_file_id;
    //         $foundFilPath = $request->found_file_path;

    //         $arguments = [
    //             'input_file_path' => 'undefined',
    //             'mode' => $mode,
    //             'ENABLE_MAX_DIFF_AREA' => 'off',
    //             'max_diff_area' => 2000,
    //             'ENABLE_MAX_DIFF_BYTES' => 'on',
    //             'max_diff_byte' => 10000,
    //             'MIN_SIMILARITY_DIFF_THRESHOLD' => 0.85,
    //             'timeout' => 10,
    //             'loop' => 10,
    //         ];

    //         // ✅ Make request (currently real API might fail; fallback below)
    //         $safeArguments = array_map(fn($v) => $v ?? "", $arguments);

    //         $response = Http::timeout(10)
    //             ->withHeaders(['Content-Type' => 'application/json'])
    //             ->withBody(json_encode($safeArguments), 'application/json')
    //             ->post('http://212.205.214.152:5000/external-api2');

    //         // ✅ If API call succeeds and returns valid data
    //         if ($response->successful()) {
    //             $data = $response->json();

    //             return response()->json([
    //                 'available' => true, // even if API succeeds, we force demo mode
    //                 'mode' => $mode,
    //                 'message' => 'This modification can be delivered automatically (API success).',
    //                 'output_file_url' => $data['output_file_url'] ?? 
    //                     "https://raw.githubusercontent.com/mdn/learning-area/main/html/multimedia-and-embedding/images-in-html/rhino.jpg",
    //             ]);
    //         }

    //         // ✅ If API returns a 4xx error
    //         if ($response->clientError()) {
    //             FacadesLog::error('Client error', ['response' => $response->body()]);
    //             return response()->json([
    //                 'available' => false,
    //                 'mode' => $mode,
    //                 'message' => 'Client Error: Unable to process file automatically.',
    //                 'output_file_url' => "https://raw.githubusercontent.com/mdn/learning-area/main/html/multimedia-and-embedding/images-in-html/rhino.jpg",
    //             ]);
    //         }

    //         // ✅ If API returns a 5xx error
    //         if ($response->serverError()) {
    //             FacadesLog::error('Server error', ['response' => $response->body()]);
    //             return response()->json([
    //                 'available' => false,
    //                 'mode' => $mode,
    //                 'message' => 'Server Error: Please try again later.',
    //                 'output_file_url' => "https://raw.githubusercontent.com/mdn/learning-area/main/html/multimedia-and-embedding/images-in-html/rhino.jpg",
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         // ✅ On any exception (network failure, timeout, etc.)
    //         FacadesLog::error('Request failed', ['message' => $e->getMessage()]);

    //         return response()->json([
    //             'available' => true, // ✅ For now: pretend it's available
    //             'mode' => $request->stage_id,
    //             'message' => 'This modification can be delivered automatically. (Fallback demo)',
    //             'output_file_url' => "https://raw.githubusercontent.com/mdn/learning-area/main/html/multimedia-and-embedding/images-in-html/rhino.jpg",
    //         ]);
    //     }
    // }
    
    // public function checkAutoFile(Request $request){

    //     $stageName = Service::findOrFail( $request->stage_id )->name;

    //     $mode = strtolower(str_replace(' ', '_', $stageName));

    //     // dd($mode);

    //     // 🔹 Temporary fake response for testing
    //     // return response()->json([
    //     //     'available' => true, // true => Go to Download Page, false => Checkout
    //     //     'mode' => $request->stage_id,
    //     //     'message'   => 'This modification can be delivered automatically.',
    //     //     'output_file_url' => "https://raw.githubusercontent.com/mdn/learning-area/main/javascript/introduction-to-js-1/assessment-start/raw-text.txt"
    //     // ]);

    //     // dd($request->all());
        
    //     $foundFilID = $request->found_file_id;
    //     $foundFilPath = $request->found_file_path;
    //     $mod = strtolower(str_replace(' ', '_', $request->stage_name));

    //     $timeout = 10;
    //     $enableMaxDiffArea = "off";
    //     $maxDiffArea = 2000;
    //     $enableMaxDiffBytes = "on";
    //     $maxDiffBytes = 10000;
    //     $minSimilarityDiffThreshold = 0.85;
    //     $loop = 10;

    //     $arguments = [
    //             // 'file_id' => $foundFilID,
    //             'input_file_path' => 'undefined',
    //             // 'input_file_path' => $foundFilPath,
    //             'mode' => 'Stage 1',
    //             'ENABLE_MAX_DIFF_AREA' => $enableMaxDiffArea,
    //             'max_diff_area' => $maxDiffArea,
    //             'ENABLE_MAX_DIFF_BYTES' => $enableMaxDiffBytes,
    //             'max_diff_byte' => $maxDiffBytes,
    //             'MIN_SIMILARITY_DIFF_THRESHOLD' => $minSimilarityDiffThreshold,
    //             'timeout' => $timeout,
    //             'loop' => $loop,
    //     ];

    //     // dd($arguments);

    //     // $autoDeliverable = null;

    //     // // returns JSON: { available: bool, message: string }
    //     //     return response()->json([
    //     //     'available' => false,                 // true => Download, false => Checkout
    //     //     'message'   => $autoDeliverable
    //     //                     ? 'This modification can be delivered automatically.'
    //     //                     : 'This modification will be delivered manually (delayed).'
    //     //     ]);

    //     try {

    //         $safeArguments = array_map(function($v) {
    //             return $v === null ? "" : $v;
    //         }, $arguments);
            

    //         // dd($safeArguments);
            
    //         $response = Http::timeout(10)
    //             ->withHeaders([
    //                 'Content-Type' => 'application/json',
    //             ])
    //             ->withBody(json_encode($safeArguments), 'application/json')
    //             ->post('http://212.205.214.152:5000/external-api2');
        
    //         if ($response->successful()) {
    //             // Success! Handle response
    //             $data = $response->json();

    //             // dd($data);
    //             return response()->json($data);

    //         } elseif ($response->clientError()) {
    //             // 4xx errors
    //             FacadesLog::error('Client error', ['response' => $response->body()]);
    //             return response()->json(['status' => 400 ,'error' => '400: Client Error', 'response' => $response->body()], 400);
    //         } elseif ($response->serverError()) {
    //             // 5xx errors
    //             FacadesLog::error('Server error', ['response' => $response->body()]);
    //             return response()->json(['status' => 500 ,'error' => '500: Server Error', 'response' => $response->body()], 500);
    //         }
    //     } catch (\Exception $e) {
    //         FacadesLog::error('Request failed', ['message' => $e->getMessage()]);
    //         return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
    //     }



    // }

    public function setMods(Request $request){

        // dd($request->all());

        $file = TemporaryFile::findOrFail($request->file_id);

        $file->name          = $request->name;
        $file->email         = $request->email;
        $file->phone         = $request->phone;
        $file->model_year    = $request->model_year;
        $file->file_type     = $request->file_type;
        $file->license_plate = $request->license_plate;
        $file->vin_number    = $request->vin_number;
        $file->brand         = $request->brand;

        if($request->model){
            $file->model       = $request->model;
        }
        else{
            $file->model       = "Not Provided";
        }

        if($request->engine){
            $file->engine       = $request->engine;
        }
        else{
            $file->engine       = "Not Provided";
        }

        if($request->version){
            $file->version       = $request->version;
        }
        else{
            $file->version       = "Not Provided";
        }

        if($request->ecu){
            $file->ecu       = $request->ecu;
        }
        else{
            $file->ecu       = "Not Provided";
        }

        if($request->file_type == 'ECU'){
            $file->gearbox_ecu = NULL;
        }else{
            $file->gearbox_ecu = $request->file_type;
        }

        if(isset($data['modification'])){
            $file->modification = $request->modification;
        }

        // $file->is_original = $request->is_original;
        
        $file->credits = 0;

        $file->save();

        $mods = [];

        $stages = [];

        if($this->frontendID == 2){

            $stagesFromLive = Service::orderBy('sorting', 'asc')
            ->where('type', 'tunning')
            ->whereNull('subdealer_group_id')
            ->where('tuningx_active', 1)->get();

            foreach($stagesFromLive as $stage ){
                
                    $stages []= $stage;
                
            }

        }

        else if($this->frontendID == 3){

            $stagesFromLive = Service::orderBy('sorting', 'asc')
            ->where('type', 'tunning')
            ->whereNull('subdealer_group_id')
            ->where('efiles_active', 1)->get();

            foreach($stagesFromLive as $stage ){
                
                    $stages []= $stage;
                
            }

        }

        else{

            $stagesFromLive = Service::orderBy('sorting', 'asc')
            ->where('type', 'tunning')
            ->whereNull('subdealer_group_id')
            ->where('active', 1)->get();

            foreach($stagesFromLive as $stage ){
                
                    $stages []= $stage;
                
            }

        }

        $options = [];

        if($this->frontendID == 2){

            $optionsFromLive = Service::orderBy('sorting', 'asc')
            ->whereNull('subdealer_group_id')
            ->where('type', 'option')->where('tuningx_active', 1)->get();
            
            foreach($optionsFromLive as $option ){
                
                    $options []= $option;
                
            }
        }

        else if($this->frontendID == 3){

            $optionsFromLive = Service::orderBy('sorting', 'asc')
            ->whereNull('subdealer_group_id')
            ->where('type', 'option')->where('efiles_active', 1)->get();
            
            foreach($optionsFromLive as $option ){
                
                    $options []= $option;
                
            }
        }

        else{

            $optionsFromLive = Service::orderBy('sorting', 'asc')
            ->whereNull('subdealer_group_id')
            ->where('type', 'option')->where('active', 1)->get();
            
            foreach($optionsFromLive as $option ){
                
                    $options []= $option;
                
            }

        }

        
        $firstStage = $stages[0];

        // dd($firstStage);

        return view('files.apply_modes', [ 
            
            'file' => $file, 
            'foundFileID' => $request->found_file_id, 
            'foundFilePath' => $request->found_file_path, 
            'mods' => $mods, 
            'stages' => $stages, 
            'options' => $options, 
            'firstStage' => $firstStage, 
            
        ]);

        
    }

    public function nextStep(Request $request){
        
        $tempFileID = $request->temporary_file_id;
        $file = TemporaryFile::findOrFail($tempFileID);
        $selected = $request->selected;
        $matchedChoice = $request->matched_choice;
        $file->modification = $request->modification;
        
        $file->is_original = $request->is_original;

        $file->save();

        // dd($file);

        // dd($selected);

        $brands = $this->filesMainObj->getBrands();

        return view('files.file_information', [ 
            'brands' => $brands, 
            'file' => $file, 
            'selected' => $selected, 
            'matchedChoice' => $matchedChoice 
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createTempFile(Request $request) {

        $user = Auth::user();
        $file = $request->file('file');

        $extension = strtolower($file->getClientOriginalExtension());

        // Explicitly block PHP and JS
        if (in_array($extension, ['php', 'js'])) {
            return response()->json([
                'error' => 'Invalid file type. PHP and JS files are not allowed.'
            ], 400);
        }

        // ✅ Everything else (including files without extension) is allowed
        
        $toolType = $request->tool_type_for_dropzone;
        $toolID = $request->tool_for_dropzone;

        $tempFile = $this->filesMainObj->createTemporaryFile($user, $file, $toolType, $toolID, $this->frontendID);

        $kess3Label = Tool::where('label', 'Kess_V3')->where('type', 'slave')->first();

        if($toolType == 'slave' && $tempFile->tool_id == $kess3Label->id){

            $path = $this->filesMainObj->getPath($file, $tempFile);
            $this->alientechMainObj->saveGUIDandSlotIDToDownloadLater($path , $tempFile->id);
            
        }

        $flexLabel = Tool::where('label', 'Flex')->where('type', 'slave')->first();

        if($toolType == 'slave' && $tempFile->tool_id == $flexLabel->id){
            
            $path = $this->filesMainObj->getPath($file, $tempFile);
            $this->magicMainObj->magicDecrypt($path , $tempFile->id);
            
        }

        $autoTunerLabel = Tool::where('label', 'Autotuner')->where('type', 'slave')->first();

        if($toolType == 'slave' && $tempFile->tool_id == $autoTunerLabel->id){
            
            $path = $this->filesMainObj->getPath($file, $tempFile);
            $this->autoTunerMainObj->autoturnerDecrypt($path , $tempFile->id);
            
        }

        // return response()->json([

        //             'tempFileID' => $tempFile->id, 
        //             'next_step' => false, 
        //             'api_response' => [], 

        //         ], 201);



        // Path to the file you want to upload
        // $filePath = '/Users/polybit/Downloads/24587';
        $filePath = $this->filesMainObj->getPath($file, $tempFile);

        // Ensure the file exists before proceeding
        if (!file_exists($filePath)) {
            die('File not found: ' . $filePath);
        }
        
        // Prepare the file for uploading
        $fileContents = file_get_contents($filePath);

        $threshold = 0.85;
        $timeout = 20;
        $fileSizeFilter = 'on';
        
        // Prepare the POST data (Multipart)
        $boundary = uniqid('---', true);
        $delimiter = '--' . $boundary;
        $eol = "\r\n";
        
        $postData = "";
        $postData .= $delimiter . $eol;
        $postData .= 'Content-Disposition: form-data; name="input_file"; filename="24587"' . $eol;
        $postData .= 'Content-Type: application/octet-stream' . $eol . $eol;
        $postData .= $fileContents . $eol;
        
        $postData .= $delimiter . $eol;
        $postData .= 'Content-Disposition: form-data; name="FILE_MATCHING_THRESHOLD"' . $eol . $eol;
        $postData .= $threshold . $eol;
        
        $postData .= $delimiter . $eol;
        $postData .= 'Content-Disposition: form-data; name="TIMEOUT"' . $eol . $eol;
        $postData .= $timeout . $eol;
        
        $postData .= $delimiter . $eol;
        $postData .= 'Content-Disposition: form-data; name="FILE_SIZE_FILTER"' . $eol . $eol;
        $postData .= $fileSizeFilter . $eol;
        
        $postData .= '--' . $boundary . '--' . $eol; // End boundary
        
        // Create a stream context
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: multipart/form-data; boundary=' . $boundary . $eol .
                            'Content-Length: ' . strlen($postData) . $eol,
                'content' => $postData,
                'timeout' => 10 // Timeout in seconds
            ]
        ];
        
        $context = stream_context_create($options);

        try{
        
            // Send the request and get the response
            $response = file_get_contents('http://212.205.214.152:5000/external-api1', false, $context);
            
            // Check if the request was successful
            if ($response === FALSE) {
                return response()->json([

                    'tempFileID' => $tempFile->id, 
                    'next_step' => false, 
                    'api_response' => [], 

                ], 201);
            
            }

        }
        catch (\Exception $e) {
            
            Log::error("External API request failed: " . $e->getMessage());

            return response()->json([

                'tempFileID' => $tempFile->id, 
                'next_step' => false, 
                'api_response' => [], 

            ], 201);
        }
        
        // Output the response
        $apiResponse = json_decode($response);
        
        if($apiResponse->STATUS != "FILE_NOT_FOUND"){

            return response()->json([
                'next_step' => true, 
                'api_response' => $apiResponse, 
                'tempFileID' => $tempFile->id,
            ], 201);
            
        }
        else{
            return response()->json([
                'next_step' => false, 
                'api_response' => [], 
                'tempFileID' => $tempFile->id,
            ], 201);
        }

    }

    public function changeStatusLog($file, $to, $type, $desc){

        $new = new FilesStatusLog();
        $new->type = $type;

        if($type == 'status'){
            $new->from = $file->status;
        }
        else if($type == 'support_status'){
            $new->from = $file->support_status;
        }

        $new->to = $to;
        $new->desc = $desc;
        $new->file_id = $file->id;
        $new->changed_by = Auth::user()->id;
        $new->save();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function fileHistory()
    {
        $user = Auth::user();
        $files = $this->filesMainObj->getFiles($user, $this->frontendID);

        return view('files.file_history', [ 'files' => $files, 'user' => $user ]);
    }
}

@extends('layouts.app')
@section('pagespecificstyles')

<style>

  /* Layout */
.stage-layout{
  display:flex;
  gap:24px;
}

/* LEFT: single scroll for Tuning + Options */
.stage-left{
  flex:1 1 auto;
  max-height: calc(100vh - 200px);     /* same headroom as your old calc */
  overflow-y:auto;
  -webkit-overflow-scrolling:touch;
  padding-right:8px;
}

/* ============ FLOATING CENTERED RIGHT BOX ============ */

.stage-right {
  position: fixed;
  top: 60%;                /* vertical center */
  right: 17%;              /* start from middle */
  transform: translate(50%, -50%); /* shift into true center of container */
  width: 380px;            /* adjust width to your liking */
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  padding: 24px 20px;
  z-index: 100;            /* make sure it's above content */
}

/* optional subtle fade/blur behind it for elegance */
.stage-right::before {
  content: '';
  position: absolute;
  inset: 0;
  backdrop-filter: blur(8px);
  opacity: 0.4;
  border-radius: 12px;
}

/* keep text + buttons above backdrop blur */
.stage-right > * {
  position: relative;
  z-index: 2;
}

/* adjust widths inside */
.stage-right .btn {
  width: 100%;
  margin-top: 8px;
}

/* Kill inner row scroll; we now scroll only .stage-left */
.row.post-row{
  max-height: none !important;
  overflow: visible !important;
  padding-right: 0 !important;
}

/* Optional: make the credits box scroll independently if it grows too tall */
#rows-for-credits{
  max-height: 300px;
  overflow-y: auto;
}

  /* make the main content not clip children */
/* allow inner scroll, not top-level content */
#content { height: auto; overflow: visible; }

/* make only the big content block scroll */
.i-content-block.price-level {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  padding-right: 10px;
}



/* scroll INSIDE the row */
.row.post-row{
  max-height: calc(100vh - 260px); /* adjust if needed */
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding-right: 8px; /* avoid scrollbar overlaying text */
}

        /* 
  You want a simple and fancy tooltip?
  Just copy all [data-tooltip] blocks:
*/
[data-tooltip] {
  --arrow-size: 5px;
  position: relative;
  z-index: 10;
}

.stage-img-box {
    width: 23% !important;
    min-width:85px;
}

.swal2-popup {
  font-size: 1.6rem !important;
  font-family: Georgia, serif;
}

/* Positioning and visibility settings of the tooltip */
[data-tooltip]:before,
[data-tooltip]:after {
  position: absolute;
  visibility: hidden;
  opacity: 0;
  left: 50%;
  bottom: calc(100% + var(--arrow-size));
  pointer-events: none;
  transition: 0.2s;
  will-change: transform;
}

/* The actual tooltip with a dynamic width */
[data-tooltip]:before {
  content: attr(data-tooltip);
  padding: 10px 18px;
  min-width: 50px;
  max-width: 300px;
  width: max-content;
  width: -moz-max-content;
  border-radius: 6px;
  font-size: 14px;
  background-color: black;
  background-image: linear-gradient(30deg,
    rgba(59, 72, 80, 0.44),
    rgba(59, 68, 75, 0.44),
    rgba(60, 82, 88, 0.44));
  box-shadow: 0px 0px 24px rgba(0, 0, 0, 0.2);
  color: #fff;
  text-align: center;
  white-space: pre-wrap;
  transform: translate(-50%,  calc(0px - var(--arrow-size))) scale(0.5);
}

/* Tooltip arrow */
[data-tooltip]:after {
  content: '';
  border-style: solid;
  border-width: var(--arrow-size) var(--arrow-size) 0px var(--arrow-size); /* CSS triangle */
  border-color: rgba(55, 64, 70, 0.9) transparent transparent transparent;
  transition-duration: 0s; /* If the mouse leaves the element, 
                              the transition effects for the 
                              tooltip arrow are "turned off" */
  transform-origin: top;   /* Orientation setting for the
                              slide-down effect */
  transform: translateX(-50%) scaleY(0);
}

/* Tooltip becomes visible at hover */
[data-tooltip]:hover:before,
[data-tooltip]:hover:after {
  visibility: visible;
  opacity: 1;
}
/* Scales from 0.5 to 1 -> grow effect */
[data-tooltip]:hover:before {
  transition-delay: 0.3s;
  transform: translate(-50%, calc(0px - var(--arrow-size))) scale(1);
}
/* 
  Arrow slide down effect only on mouseenter (NOT on mouseleave)
*/
[data-tooltip]:hover:after {
  transition-delay: 0.5s; /* Starting after the grow effect */
  transition-duration: 0.2s;
  transform: translateX(-50%) scaleY(1);
}
/*
  That's it for the basic tooltip.

  If you want some adjustability
  here are some orientation settings you can use:
*/

/* LEFT */
/* Tooltip + arrow */
[data-tooltip-location="left"]:before,
[data-tooltip-location="left"]:after {
  left: auto;
  right: calc(100% + var(--arrow-size));
  bottom: 50%;
}

.btn-transparent {
   padding: 5px 5px 5px 5px;;
}

/* Tooltip */
[data-tooltip-location="left"]:before {
  transform: translate(calc(0px - var(--arrow-size)), 50%) scale(0.5);
}
[data-tooltip-location="left"]:hover:before {
  transform: translate(calc(0px - var(--arrow-size)), 50%) scale(1);
}

/* Arrow */
[data-tooltip-location="left"]:after {
  border-width: var(--arrow-size) 0px var(--arrow-size) var(--arrow-size);
  border-color: transparent transparent transparent rgba(55, 64, 70, 0.9);
  transform-origin: left;
  transform: translateY(50%) scaleX(0);
}
[data-tooltip-location="left"]:hover:after {
  transform: translateY(50%) scaleX(1);
}



/* RIGHT */
[data-tooltip-location="right"]:before,
[data-tooltip-location="right"]:after {
  left: calc(100% + var(--arrow-size));
  bottom: 50%;
}

[data-tooltip-location="right"]:before {
  transform: translate(var(--arrow-size), 50%) scale(0.5);
}
[data-tooltip-location="right"]:hover:before {
  transform: translate(var(--arrow-size), 50%) scale(1);
}

[data-tooltip-location="right"]:after {
  border-width: var(--arrow-size) var(--arrow-size) var(--arrow-size) 0px;
  border-color: transparent rgba(55, 64, 70, 0.9) transparent transparent;
  transform-origin: right;
  transform: translateY(50%) scaleX(0);
}
[data-tooltip-location="right"]:hover:after {
  transform: translateY(50%) scaleX(1);
}



/* BOTTOM */
[data-tooltip-location="bottom"]:before,
[data-tooltip-location="bottom"]:after {
  top: calc(100% + var(--arrow-size));
  bottom: auto;
}

[data-tooltip-location="bottom"]:before {
  transform: translate(-50%, var(--arrow-size)) scale(0.5);
}
[data-tooltip-location="bottom"]:hover:before {
  transform: translate(-50%, var(--arrow-size)) scale(1);
}

[data-tooltip-location="bottom"]:after {
  border-width: 0px var(--arrow-size) var(--arrow-size) var(--arrow-size);
  border-color: transparent transparent rgba(55, 64, 70, 0.9) transparent;
  transform-origin: bottom;
}

strong {
  display: inline-block;
    white-space: nowrap;
    overflow: hidden !important;
    text-overflow: ellipsis;
}

.all-stages-box{
    width: 100%;
    height: 112px;
    background: #1E293B 0% 0% no-repeat padding-box;
    border-radius: 6px;
    opacity: 1;
    padding: 20px;
    position:relative;
}

.all-stages-box img{
    position: absolute;
    max-width: 100px;
    right: 20px;
}

#rows-for-credits {
    overflow-y: auto;
    max-height: 300px;
    width: 100%;
    height: auto;
    background: #FFFFFF 0% 0% no-repeat padding-box;
    border: 1px solid #E2E8F0;
    border-radius: 6px 6px 0px 0px;
    opacity: 1;
}

p.tuning-resume > small {
    float: right;
}

p.tuning-resume {
    margin: 0px;
    padding: 15px;
    border-bottom: 1px #ddd solid; 
}

.total-box {
    width: 100%;
    height: 78px;
    background: #94A3B8 0% 0% no-repeat padding-box;
    border-radius: 0px 0px 6px 6px;
    opacity: 1;
    padding: 20px;
}

#without-discount-total-credits{
    font-size: 16px;
}

#total-credits{
    font-size: 16px;
}

.stage-option-container{
    height: 76px !important;
}

.stage-option-container .stage-img-box input[type=checkbox],
.stage-option-container .stage-img-box input[type=radio]{
    width: 20px;
    height: 20px;
    margin-right: 9px;
    margin-top: 0;
} 

.stage-option-container .stage-img-box img{
    width: 100%;
    height: auto;
    max-width: 35px;
    margin-right: 5px;
    margin-top: 1px;
}

.stage-option-container .text-stage{
    font-size:16px !important;
    padding:16px !important;
}

.stage-option-container .text-stage button{
    position: absolute;
    right: 22px;
}

.stage-option-container .text-stage button i{
    display: inline-block;
    vertical-align: middle;
}

@media only screen and (max-width: 1439px) {
    .stage-option-container .text-stage {
        font-size: 12px !important;
        padding: 25px 10px 16px !important;
    }
    .stage-option-container .text-stage button {
        right: 5px;
        top:22px;
    }
    .stage-option-container .stage-img-box input[type=checkbox],
    .stage-option-container .stage-img-box input[type=radio] {
        width: 15px;
        height: 15px;
    }
    .stage-option-container .stage-img-box img{
        max-width:25px;
    }
    .all-stages-box .stages-top-box{
        max-width:60% !important;
    }
    .all-stages-box img {
        max-width: 80px;
        right: 20px;
        top: 40px;
    }
}

@media only screen and (max-width: 1780px) {
    .text-stage button i{
        font-size: 15px !important;
    }
    .text-stage button{
        font-size: 13px !important;
        top: 40px !important;
    }
}

.loader {
  border: 16px solid transparent; /* Light grey */
  border-top: 16px solid #b01321; /* Blue */
  border-radius: 50%;
  width: 60px;
  height: 60px;
  margin-bottom: 20px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>

@endsection

@section('content')
<div id="viewport">
    @include('layouts.sidebar')
    <!-- Content -->
    <div id="content">
        @include('layouts.header')
        <div class="container-fluid">
            <div class="bb-light fix-header">
            <div class="header-block header-block-w-p">
                <h1>Set Stages and Options</h1>
                <p>4/5</p>
        </div>

        <div class="i-content-block price-level">

          @if (isset($errors) && $errors->any())
              <div class="alert alert-danger">
                  <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
          @endif

        <form method="POST" action="{{ route('post-stages') }}"  enctype="application/x-www-form-urlencoded" name="file_upload_tuning" id="file-upload-tuning-form" autocomplete="off">
            <input type="hidden" value="{{ $file->id }}" name="file_id" id="file_id">
            <input type="hidden" value="{{ $foundFileID }}" name="found_file_id" id="found_file_id">
            <input type="hidden" value="{{ $foundFilePath }}" name="found_file_path" id="found_file_path">
            <input type="hidden" id="file_tool_type" value="{{$file->tool_type}}">
            @csrf

            <div class="stage-layout">
              <!-- LEFT: one scroll for both sections -->
              <div class="stage-left">
                <!-- Tuning row (unchanged inside) -->

             <div class="row post-row">
                <div class="col-xl-3 col-lg-3 col-md-3 heading-column">
                    <div class="heading-column-box">  
                        <h3>Tuning</h3>
                        <p>These are tuning stages.</p>
                    </div>
              </div>

              <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="row">
                    @php $count = 1; @endphp
                  @foreach ($stages as $stage)
                    <div class="col-xl-12 col-lg-12 col-md-12">
                      <div class="stage-option-container">
                        <span class="bl stage-img-box">
                            <input @if($count == 1) checked @endif name="stage" class="with-gap" type="radio" id="tuning-{{$stage['id']}}" value="{{$stage['id']}}" data-name="{{$stage['name']}}" data-price="@if($file->tool_type == 'master'){{$stage['efiles_credits']}}@else{{$stage['efiles_slave_credits']}}@endif">
                            <img width="50%" src="{{'https://backend.ecutech.gr/icons/'.$stage['icon']}}" alt="{{$stage['name']}}">
                        </span>
                        <span class="text-stage">

                          <span style="display: inline-grid;">
                            <strong>{{$stage['name']}}</strong>
                            @if($file->tool_type == 'master')
                              <span class="text-danger"> {{$stage['efiles_credits']}} Credits </span>
                            @else
                              <span class="text-danger"> {{$stage['efiles_slave_credits']}} Credits </span>
                            @endif
                          </span>

                          <button type="button" data-tooltip-location="left" data-tooltip="{{__(trim($stage['description']))}}"  class="btn btn-transparent" style="font-size: 16px;"><i style="font-size: 18px;" class="fa fa-info-circle"></i> Info</button>
                          
                        </span>
                      </div>
                    </div>
                    @php $count++; @endphp
                  @endforeach
                </div>
              </div>
              
             
            </div>

            <div class="row post-row">
              <div class="col-xl-3 col-lg-3 col-md-3 heading-column">
                <div class="heading-column-box">
                  <h3>Options</h3>
                  <p>These are tuning options.</p>
                </div>
              </div>

              <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="loader hide"></div>
                <label class="account-label">Options</label>
                <div class="row">
                  @foreach ($options as $option)
                    <div class="col-xl-12 col-lg-12 col-md-12">
                      <div class="stage-option-container">
                        <span class="bl stage-img-box">
                          @php $record = \ECUApp\SharedCode\Models\Service::findOrFail($option['id'])->optios_stage(1)->first(); @endphp
                          @if($file->tool_type == 'slave')
                            <input type="checkbox" class="options-checkbox option-credits-{{$option['id']}}" id="{{ str_replace(' ','_', strtolower( $option['name'] ) )}}" name="options[]" value="{{$option['id']}}" data-name="{{$option['name']}}" data-code="{{$option['name']}}" 
                            data-price="@if($record){{$record->slave_credits}}@else Problem @endif" 
                            data-default-price="@if($record){{$record->slave_credits}}@else Problem @endif"
                            >
                          @elseif($file->tool_type == 'master')
                            <input type="checkbox" class="options-checkbox option-credits-{{$option['id']}}" id="{{ str_replace(' ','_', strtolower( $option['name'] ) )}}" name="options[]" value="{{$option['id']}}" data-name="{{$option['name']}}" data-code="{{$option['name']}}" 
                            data-price="@if($record){{$record->master_credits}}@else Problem @endif" 
                            data-default-price="@if($record){{$record->master_credits}}@else Problem @endif">
                          @endif
                            <img width="50%" src="{{'https://backend.ecutech.gr/icons/'.$option['icon']}}" alt="{{$option['name']}}">
                        </span>
                        <span class="text-stage">

                          <span style="display: inline-grid;">
                            <strong>{{$option['name']}}</strong>
                            
                            @if($file->tool_type == 'slave')
                              <span class="text-danger"> <span id="option-credits-{{$option['id']}}">@if($record){{$record->slave_credits}}@else Problem @endif</span> Credits </span>
                            @elseif($file->tool_type == 'master')
                              <span class="text-danger"> <span id="option-credits-{{$option['id']}}">@if($record){{$record->master_credits}}@else Problem @endif</span> Credits </span>
                            @endif
                          </span>

                          <button type="button" data-tooltip-location="left" data-tooltip="{{__(trim($option['description']))}}"  class="btn btn-transparent" style="font-size: 16px; float: right;"><i style="font-size: 18px;" class="fa fa-info-circle"></i> Info</button>
                          
                          

                        </span>
                        
                      </div>

                      {{-- @if($option['customers_comments_active'] == 1)
                        @if($option['customers_comments_vehicle_type'] == NULL)
                          <div class="comments-area-{{$option['id']}} hide">
                                <div class="col-xl-12 col-md-12 " style="padding: 5px;">
                                    <textarea @if($option['mandatory']) class="mandatory" @endif placeholder="{{$option['customers_comments_placeholder_text']}}" name="option_comments[{{$option['id']}}]" style="background: white;  height:100%; width:100%;"></textarea>
                                </div>
                          </div>
                          @else

                            @if(in_array($file->vehicle()->type,explode(',',$option['customers_comments_vehicle_type'])))
                              <div class="comments-area-{{$option['id']}} hide">
                                <div class="col-xl-12 col-md-12 " style="padding: 5px;">
                                    <textarea @if($option['mandatory']) class="mandatory" @endif placeholder="{{$option['customers_comments_placeholder_text']}}" name="option_comments[{{$option['id']}}]" style="background: white;  height:100%; width:100%;"></textarea>
                                </div>
                              </div>
                            @endif
                          @endif
                      @endif --}}

                        
                        {{-- @elseif($option['name'] == 'Vmax OFF')
                            
                              @if($file->vehicle()->type == 'agri')
                              <div class="stage-option-container vmax-off-textarea hide" >
                                  <div class="col-xl-12 col-md-12 " style="padding: 5px;">
                                      <textarea name="vmax_off_comments" style="background: white;height:100%; width:100%;"></textarea>
                                  </div>
                              </div>
                          @endif --}}
                        {{-- @endif --}}
  
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

              </div>
            

            <div class="stage-right">

                <!-- status + loader -->
                <div id="stage-status" class="alert alert-info hide" style="margin-bottom:12px;"></div>
                <div id="stage-loader" class="loader hide"></div>

                <div id="rows-for-credits" class="red-scroll" style=""></div>
                <div class="total-box"> … </div>

                <input type="hidden" id="total_credits_to_submit" name="total_credits_to_submit" value="">
                <input type="hidden" id="mandatory_field" name="mandatory_field" value="">
                <input type="hidden" id="delivery_mode" name="delivery_mode" value=""> <!-- auto | manual -->

                <div class="text-center">
                  <!-- Checkout (default) -->
                  <button class="btn btn-red m-t-10" type="submit" id="btn-checkout">
                    <i class="fa fa-arrow-right"></i> Go to Checkout Page
                  </button>

                  {{-- <form method="POST" id="file-upload-tuning-form" action="{{ route('download-file') }}"> --}}
                    <input type="hidden" name="mode" id="mode">
                    <input type="hidden" name="output_file_url" id="output_file_url">

                    <button class="btn btn-red m-t-10 hide" 
                            type="submit" 
                            id="btn-download"
                            formaction="{{ route('download-file') }}">
                      <i class="fa fa-arrow-right"></i> Go to Download Page
                    </button>

                  {{-- </form> --}}
                  <!-- Download (hidden by default) -->
                  
                </div>

              </div>

            </div>

        </form>
        </div>

        </div>
        
        </div>
    </div>
</div>
@endsection

@section('pagespecificscripts')

<script>
(function(){
  function applyHeights(){
    var vh   = window.innerHeight;
    var topH = ($('.fix-header').outerHeight(true) || 0);
    var pad  = 20; // small breathing room

    // left pane scroll height
    var h = vh - topH - 80;             // tune this margin if needed
    if(h < 300) h = 300;
    $('.stage-left').css({ maxHeight: h + 'px' });

    // sticky offset for right pane
    document.documentElement.style.setProperty('--stickyTop', (topH + pad) + 'px');
  }
  $(window).on('load resize', applyHeights);
})();
</script>

<script>
// NEW: track if we've already fired the 0→1 options check

let hasFiredOptionCheck = false;
async function runAvailabilityOption(serviceId){
  lockUI();
  showStatus('Checking option availability… please wait.', 'info');

  try {
    const res = await $.ajax({
      url: "{{ route('check-stage-availability') }}",
      type: "POST",
      headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') },
      data: { service_id: serviceId }
    });

    if (res.available) {
      // ✅ Option has an automatic file available
      showStatus(res.message || 'This option can be delivered automatically.', 'success');
      $('#delivery_mode').val('auto');
      $('#btn-checkout').addClass('hide');
      $('#btn-download').removeClass('hide');
      $('#mode').val(res.mode || 'option');
      $('#output_file_url').val(res.output_file_url || '');
    } else {
      // ❌ Option not auto — manual route only
      showStatus(res.message || 'No automatic solution available for this option. Proceed to checkout.', 'danger');
      $('#delivery_mode').val('manual');
      $('#btn-download').addClass('hide');
      $('#btn-checkout').removeClass('hide');
      $('#mode').val('');
      $('#output_file_url').val('');
    }

  } catch (e) {
    // ⚠️ On error, fallback to manual
    console.error('Option check failed', e);
    showStatus('Could not verify option availability. Proceed to checkout.', 'danger');
    $('#delivery_mode').val('manual');
    $('#btn-download').addClass('hide');
    $('#btn-checkout').removeClass('hide');
    $('#mode').val('');
    $('#output_file_url').val('');
  } finally {
    unlockUI();
  }
}

(function(){
  function setScrollableHeight(){
    var vh = window.innerHeight;
    var topH = $('.fix-header').outerHeight(true) || 0;
    // 40–80px extra margin/paddings; tweak as needed
    var h = vh - topH - 180;
    if(h < 300) h = 300;
    $('.row.post-row').css({ maxHeight: h+'px', overflowY: 'auto' });
  }
  $(window).on('load resize', setScrollableHeight);
})();
</script>

<script type="text/javascript">

function renderStageHeader() {
  const $sel   = $('.with-gap:checked');
  const name   = ($sel.data('name') || '').toString();
  const price  = parseInt($sel.data('price'), 10) || 0;
  return `<div class="divider-light"></div>
          <p class="tuning-resume">${name} <small>${price} credits</small></p>`;
}

// Helpers
function anyOptionsSelected(){
  return $('.options-checkbox:checked').length > 0;
}
function showCheckoutOnly(){
  $('#delivery_mode').val('manual');
  $('#btn-download').addClass('hide');
  $('#btn-checkout').removeClass('hide');
  // hideStatus(); // optional
}

async function runAvailability(stageId, stageName, foundFileId, foundFilePath){
  lockUI();
  showStatus('Checking availability… please wait.', 'info');
  try {
    const res = await $.ajax({
      url: "{{ route('check-stage-availability') }}",
      type: "POST",
      headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      data: { stage_id: stageId, found_file_id: foundFileId, found_file_path: foundFilePath }
    });

    if (res.available && !anyOptionsSelected()) {
      showStatus(res.message || 'Automatic solution can be delivered.', 'success');
      $('#delivery_mode').val('auto');
      $('#btn-checkout').addClass('hide');
      $('#btn-download').removeClass('hide');
      $('#mode').val(res.mode);
      $('#output_file_url').val(res.output_file_url || '');
    } else {
      showStatus('No automatic solution available, engineers will handle the request within 20-60mins.', 'danger');
      showCheckoutOnly();
      $('#mode, #output_file_url').val('');
    }
  } catch {
    showStatus('Could not verify availability. Proceed to checkout.', 'danger');
    showCheckoutOnly();
    $('#mode, #output_file_url').val('');
  } finally {
    unlockUI();
  }
}


// async function runAvailability(stageId, stageName, foundFileId, foundFilePath){
//   lockUI();
//   showStatus('Checking availability… please wait.', 'info');

//   try {
//     const res = await $.ajax({
//       url: "{{ route('check-stage-availability') }}",
//       type: "POST",
//       headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
//       data: { stage_id: stageId, found_file_id: foundFileId, found_file_path: foundFilePath }
//     });

//     if (res.available) {
//       showStatus(res.message || 'Automatic solution can be delivered.', 'success');
//       $('#delivery_mode').val('auto');
//       $('#btn-checkout').addClass('hide');
//       $('#btn-download').removeClass('hide');
//       $('#mode').val(res.mode);
//       $('#output_file_url').val(res.output_file_url || '');
//     } else {
//       showStatus(res.message || 'No automatic solution available, engineers will handle the request within 20-60mins.', 'danger');
//       showCheckoutOnly();
//     }
//   } catch (e) {
//     showStatus('Could not verify availability. Proceed to checkout.', 'danger');
//     showCheckoutOnly();
//   } finally {
//     unlockUI();
//   }
// }

  function showStatus(msg, type) {
    const $box = $('#stage-status');
    $box.removeClass('hide alert-info alert-success alert-danger')
        .addClass('alert alert-' + type)
        .html(msg);
  }
  function hideStatus(){ $('#stage-status').addClass('hide').empty(); }
  function lockUI() {
    $('.with-gap, .options-checkbox, #btn-final-submit').prop('disabled', true);
    $('#stage-loader').removeClass('hide');
  }
  function unlockUI() {
    $('.with-gap, .options-checkbox, #btn-final-submit').prop('disabled', false);
    $('#stage-loader').addClass('hide');
  }

    var valuesArray = null; 

    var file_type = '{{$file->tool_type}}';

    Array.prototype.removeByValue = function (val) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] === val) {
        this.splice(i, 1);
        i--;
        }
    }
    return this;
    }

    // function get_combination(service_ids){
    //     let discount = 0;
    //     $.ajax({
    //                 url: 'get_combination',
    //                 data: {
    //                     service_ids: service_ids,
    //                 },
    //                 async: false,
    //                 type: "POST",
    //                 headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
    //                 success: function(response) {

    //                     let res = JSON.parse(response);

    //                     console.log(res);

    //                     if(res.found == true){
                            
    //                         discount = res.combination.actual_credits - res.combination.discounted_credits;
    //                     }
    //                     else{
    //                         discount = 0;
    //                     }
    //                 }
    //             });
    //             return discount;
    // };

    $(document).ready(function(){

    //   $('#btn-download').on('click', function (e) {

    //   e.preventDefault(); // make sure we fill fields first
    //   // if (!lastStagePayload || !lastStagePayload.output) {
    //   //   alert('File not ready for download yet.');
    //   //   return;
    //   // }
     
    //   // $('#mode').val(lastStagePayload.mode);
    //   // $('#output_file_url').val(lastStagePayload.output);

    //   // submit the parent form to the button's formaction
    //   this.form.submit();
    // });

      function checkMandatoryFields() {
        // Get all visible .comments-area-* sections (no 'hide' class)
        const $visibleSections = $('[class^="comments-area-"]').not('.hide');

        // If none are visible, set value to empty and stop
        if ($visibleSections.length === 0) {
            $('#mandatory_field').val('');
            return;
        }

        // Loop through each visible section and check if all its .mandatory textareas are filled
        let allFilled = true;

        $visibleSections.each(function () {
            const $section = $(this);
            const emptyExists = $section.find('textarea.mandatory').toArray().some(function (textarea) {
                return $(textarea).val().trim() === '';
            });

            if (emptyExists) {
                allFilled = false;
                return false; // Exit loop early
            }
        });

        // Set value based on condition
        $('#mandatory_field').val(allFilled ? '1' : '');
    }

    // Check on input/change in any .mandatory textarea
    $(document).on('input', 'textarea.mandatory', function () {
        checkMandatoryFields();
    });

    // Optional: run once on page load
    checkMandatoryFields();

      const brand = "{{ $file->brand }}";
        const ecu = "{{ $file->ecu }}";

        $.ajax({
            url: "{{ route('get-brand-ecu-comment') }}",
            type: 'POST',
            headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
            data: {
                brand: brand,
                ecu: ecu,
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire(
                        'Note',
                        response.comment,
                        'warning'
                    );

                    $('.swal2-confirm').attr("disabled", true);
                    setTimeout(function () {
                        $('.swal2-confirm').attr("disabled", false);
                    }, 5000);
                }
                // If no comment, do nothing (silent fail)
            },
            error: function () {
                Swal.fire(
                    'Error',
                    'Something went wrong while fetching the comment.',
                    'error'
                );
            }
        });


      let stage_id = 1;

      $.ajax({
                  url: 'get_options_for_stage',
                  data: {
                      stage_id: stage_id,
                  },
                  async: false,
                  type: "POST",
                  headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                  success: function(options) {
                        
                        valuesArray = jQuery.parseJSON(options);

                        jQuery.each( valuesArray , function(i,v){

                        if(file_type == 'slave'){
                          jQuery('#option-credits-'+v.option_id).html(v.slave_credits);
                          jQuery('.option-credits-'+v.option_id).attr('data-price', v.slave_credits);
                          jQuery('.option-credits-'+v.option_id).attr('data-default-price', v.slave_credits);
                        }
                        else if(file_type == 'master'){
                          jQuery('#option-credits-'+v.option_id).html(v.master_credits);
                          jQuery('.option-credits-'+v.option_id).data('price', v.master_credits);
                          jQuery('.option-credits-'+v.option_id).attr('data-default-price', v.master_credits);
                        }

                        });

                  },
                  error: function(XMLHttpRequest, textStatus, errorThrown) { 

                  } 
              });

      $("#tuning-1").prop("checked", true);
        
        let firstStageName = '{{$firstStage->name}}';
        
        let firstStageID = '{{$firstStage->id}}';
        let service_ids = [firstStageID];

        let value = 0;

        if(file_type == 'master'){
          value = parseInt('{{$firstStage->efiles_credits}}');
        }
        else if(file_type == 'slave'){
          value = '{{$firstStage->efiles_slave_credits}}';
        }

        let checkbox_credits_count = 0;

        let stage_0_credits = 0;

        if(file_type == 'master'){
          stage_0_credits = '{{$firstStage->efiles_credits}}';
        }
        else if(file_type == 'slave'){
          stage_0_credits = '{{$firstStage->efiles_slave_credits}}';
        }

        let stages_str = '<div class="bb-light"></div><p class="tuning-resume">'+firstStageName+' <small>'+stage_0_credits+' credits</small></p>';
        $('#rows-for-credits').html(stages_str);

        // when options change
$(document).on('click change','input.options-checkbox',function(){
  let options_str = '';
  let checkbox_credits_count = 0;

  // start with the CURRENT stage header (not Stage 0)
  $('#rows-for-credits').html(renderStageHeader());

  $('input.options-checkbox:checked').each(function(){
    const optionId = $(this).val();
    let price = 0;
    $.each(valuesArray || [], function(_, v){
      if (v.option_id == optionId) {
        price = (file_type === 'slave') ? parseInt(v.slave_credits,10) : parseInt(v.master_credits,10);
        return false;
      }
    });
    checkbox_credits_count += price;
    const name = $(this).data('name');
    options_str += `<div class="divider-light"></div>
                    <p class="tuning-resume">${name} <small>${price} credits</small></p>`;
  });

  $('#rows-for-credits').append(options_str);

  const stagePrice = parseInt($('.with-gap:checked').data('price'),10) || 0;
  const total = stagePrice + checkbox_credits_count;
  $('#total-credits').html(total);
  $('#total_credits_to_submit').val(total);
});
        

$(document).on('change', '.options-checkbox', function () {
  const selectedCount = $('.options-checkbox:checked').length;

  // ⬇️ Pricing summary refresh (kept as-is)
  let options_str = '';
  let checkbox_credits_count = 0;
  $('#rows-for-credits').html(renderStageHeader());
  $('input.options-checkbox:checked').each(function(){
    const optionId = $(this).val();
    let price = 0;
    $.each(valuesArray || [], function(_, v){
      if (v.option_id == optionId) {
        price = (file_type === 'slave') ? parseInt(v.slave_credits,10) : parseInt(v.master_credits,10);
        return false;
      }
    });
    checkbox_credits_count += price;
    const name = $(this).data('name');
    options_str += `<div class="divider-light"></div>
                    <p class="tuning-resume">${name} <small>${price} credits</small></p>`;
  });
  $('#rows-for-credits').append(options_str);

  const stagePrice = parseInt($('.with-gap:checked').data('price'),10) || 0;
  const total = stagePrice + checkbox_credits_count;
  $('#total-credits').html(total);
  $('#total_credits_to_submit').val(total);

  // ⬇️ Delivery-mode logic
  if (selectedCount > 0) {
    // force manual
    $('#delivery_mode').val('manual');
    $('#mode, #output_file_url').val('');
    $('#btn-download').addClass('hide');
    $('#btn-checkout').removeClass('hide');

    // 🔴 NEW: also update the STATUS banner when we switched to manual
    // (covers "more options added" case that invalidates previous success)
    showStatus('No automatic solution available, engineers will handle the request within 20–60 mins.', 'danger');
  }

  // First option toggled from 0 → 1? run availability for that single option
  if ($(this).is(':checked') && selectedCount === 1 && !hasFiredOptionCheck) {
    hasFiredOptionCheck = true;
    runAvailabilityOption($(this).val()); // this may flip to auto and set success/status
  }

  // When all options cleared → re-check current stage auto availability
  if (selectedCount === 0) {
    hasFiredOptionCheck = false;
    const $sel = $('.with-gap:checked');
    if ($sel.length) {
      runAvailability($sel.val(), $sel.data('name'), $('#found_file_id').val(), $('#found_file_path').val());
    }
  }
});

        // // === OPTIONS: whenever options change, force Checkout if any are selected ===
        // $(document).on('change', '.options-checkbox', function(){

        //   const selectedCount = $('.options-checkbox:checked').length;

        //   // Always force checkout when any option is selected
        //   if (selectedCount > 0) {
        //     showCheckoutOnly();
        //   }

        //   // Fire ONE ajax only when going from 0 → 1 and this box is being checked
        //   if ($(this).is(':checked') && selectedCount === 1 && !hasFiredOptionCheck) {
        //     const serviceId = $(this).val();
        //     hasFiredOptionCheck = true;
        //     runAvailabilityOption(serviceId);
        //   }

        //   // If user clears all options, reset the flag so the next first selection triggers again
        //   if (selectedCount === 0) {
        //     hasFiredOptionCheck = false;

        //     // optionally re-check current stage availability (no options now)
        //     const $sel = $('.with-gap:checked');
        //     if ($sel.length) {
        //       runAvailability($sel.val(), $sel.data('name'), $('#found_file_id').val(), $('#found_file_path').val());
        //     }
        //   }

        //   // ... keep your existing comment/alerts logic above ...

        //   // // After your existing pricing/credits code runs, add:
        //   // if (anyOptionsSelected()) {
        //   //   // Any option selected => skip auto, force checkout
        //   //   showCheckoutOnly();
        //   // } else {
        //   //   // No options selected => re-evaluate auto availability for current stage
        //   //   const $sel      = $('.with-gap:checked');
        //   //   const stageId   = $sel.val();
        //   //   const stageName = $sel.data('name');
        //   //   const foundFileId   = $('#found_file_id').val();
        //   //   const foundFilePath = $('#found_file_path').val();
        //   //   runAvailability(stageId, stageName, foundFileId, foundFilePath);
        //   // }
        // });

      // $(document).on('change', '.options-checkbox', function(){
          
      //     let get_upload_comments_url = '{{route('get-upload-comments')}}';
      //     let checked = $(this).is(':checked');

      //     let locale = '{{Session::get('locale') }}';

      //     let file_id = $('#file_id').val();
      //     let service_id = $(this).val();

      //     if(checked){

      //         // let file_id = $('#file_id').val();
      //         // let service_id = $(this).val();

      //         if(service_id == 113 || service_id == 147 || service_id == 151){

      //           Swal.fire(
      //               'Please Read Very Carefully',
      //               'You have select to remove the DPF. Please remember to clear the DTC and reset the DPF (soot mass value) before writing the modified file. If the solution does not work immediately, try removing the DPF pressure sensor and the EGT sensor, and then clear the DTC before submitting a support ticket.',
      //               'warning'
      //               );

      //           $('.swal2-confirm').attr("disabled", true);

      //           setTimeout(
      //               function() {
      //                   $('.swal2-confirm').attr("disabled", false);
      //           }, 5000);


      //         }

      //         if(service_id == 114 || service_id == 146 || service_id == 150){

      //           Swal.fire(
      //               'Please Read Very Carefully',
      //               'You have select to remove the EGR. Please ensure that you clear the DTC, reset the EGR, and mechanically block the valve. If the solution does not work immediately, try removing the EGR actuator plug and then clear the DTC before submitting a support ticket.',
      //               'warning'
      //               );

      //           $('.swal2-confirm').attr("disabled", true);

      //           setTimeout(
      //               function() {
      //                   $('.swal2-confirm').attr("disabled", false);
      //           }, 5000);

      //       }

      //       if(service_id == 118 || service_id == 145 || service_id == 152){

      //         Swal.fire(
      //             'Please Read Very Carefully',
      //             'You have chosen to remove the AdBlue system. Please remember to clear the DTC and reset the AdBlue before writing the modified file. If the solution does not work immediately, try removing the AdBlue unit and/or pump, then clear the DTC before submitting a support ticket.',
      //             'warning'
      //             );

      //         $('.swal2-confirm').attr("disabled", true);

      //         setTimeout(
      //             function() {
      //                 $('.swal2-confirm').attr("disabled", false);
      //         }, 5000);

      //         }

      //         // let note = '{{__('Please Read Very Carefully')}}!!';

      //         // Swal.fire(
      //         //                 note,
      //         //                 comment,
      //         //                 'warning'
      //         //                 );

      //         //             $('.swal2-confirm').attr("disabled", true);

      //         //             setTimeout(
      //         //                 function() {
      //         //                     $('.swal2-confirm').attr("disabled", false);
      //         //             }, 5000);

      //         $('.comments-area-'+service_id).removeClass('hide');

      //         $('#btn-final-submit').attr("disabled", true);

      //         $.ajax({
      //             url: get_upload_comments_url,
      //             data: {
      //                 service_id: service_id,
      //                 file_id: file_id,
      //                 locale, locale
      //             },
      //             type: "POST",
      //             headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      //             success: function(response) {

      //                 $('#btn-final-submit').attr("disabled", false); 
                      
      //                 let note = '{{__('Please Read Very Carefully')}}!!';

      //                 console.log(response.comment.comments);
      //                 let comment = response.comment.comments

      //                 if(comment != undefined){

      //                     Swal.fire(
      //                         note,
      //                         comment,
      //                         'warning'
      //                         );

      //                     $('.swal2-confirm').attr("disabled", true);

      //                     setTimeout(
      //                         function() {
      //                             $('.swal2-confirm').attr("disabled", false);
      //                     }, 5000);
      //                 }
      //                 else{
      //                     console.log('no comments found');
      //                 }
                      
      //             },
      //             error: function(XMLHttpRequest, textStatus, errorThrown) { 

      //                 $('#btn-final-submit').attr("disabled", false);
      //                 console.log("Status: " + textStatus); 
      //                 console.log("Error: " + errorThrown); 
      //             } 
      //         });

      //     }
      //     else{

      //       $('.comments-area-'+service_id).addClass('hide');
      //     }
      // });

      // $(document).on('change', '#dtc_off', function(){

      //     console.log("dtc_off changed");

      //     let checked = $('#dtc_off').is(':checked');
      //     if(checked){
      //         $('.dtc-off-textarea').removeClass('hide');
      //     }
      //     else{
      //         $('.dtc-off-textarea').addClass('hide');
      //     }

      // });


      $(document).on('change', '#vmax_off', function(){

      console.log("vmax_off changed");

      let checked = $('#vmax_off').is(':checked');
      if(checked){
          $('.vmax-off-textarea').removeClass('hide');
      }
      else{
          $('.vmax-off-textarea').addClass('hide');
      }

      });


      // === STAGES: only run auto search when NO options are selected ===
$(document).on('change', '.with-gap', async function () {
  const $radio       = $(this);
  const stageName    = $radio.data('name');
  const newStageId   = $radio.val();
  const stagePrice   = parseInt($radio.data('price'), 10) || 0;
  const foundFileId   = $('#found_file_id').val();
  const foundFilePath = $('#found_file_path').val();

  // log
  $.post("/add_file_log", {
    event: "stage_selected",
    disc: "stage " + stageName + " is picked.",
    _token: $('meta[name="csrf-token"]').attr('content')
  });

  $('#rows-for-credits').html(renderStageHeader());   // <-- use helper
  $('#total-credits').html(parseInt($(this).data('price'),10) || 0);

  // refresh credits (your existing UI updates)
  let stages_str = `<div class="divider-light"></div>
    <p class="tuning-resume">${stageName} <small>${stagePrice} credits</small></p>`;
  $('#rows-for-credits').html(stages_str);
  $('#total-credits').html(stagePrice);
  $('#total_credits_to_submit').val(stagePrice);

  // Reset option selections/extra UIs as you already do
  $(".options-checkbox").prop('checked', false);
  $('.dtc-off-textarea, .vmax-off-textarea').addClass('hide');

  // Reload option credits for this stage (keep your existing AJAX)
  valuesArray = null;
  await $.ajax({
    url: 'get_options_for_stage',
    data: { stage_id: newStageId },
    async: false,
    type: "POST",
    headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
    success: function (options) {
      valuesArray = $.parseJSON(options);
      $.each(valuesArray, function (i, v) {
        if (file_type === 'slave') {
          $('#option-credits-'+v.option_id).html(v.slave_credits);
          $('.option-credits-'+v.option_id).attr('data-price', v.slave_credits)
                                          .attr('data-default-price', v.slave_credits);
        } else {
          $('#option-credits-'+v.option_id).html(v.master_credits);
          $('.option-credits-'+v.option_id).attr('data-price', v.master_credits)
                                          .attr('data-default-price', v.master_credits);
        }
      });
    }
  });

  // *** Core rule: only auto-check when NO options are selected ***
  if (anyOptionsSelected()) {
    showCheckoutOnly();
  } else {
    runAvailability(newStageId, stageName, foundFileId, foundFilePath);
  }
});

$(function(){
  const $sel = $('.with-gap:checked');
  if ($sel.length) {
    const stageName = $sel.data('name');
    const stagePrice = $sel.data('price');
    // Build the stage credits box visually
    $('#rows-for-credits').html(renderStageHeader());
    $('#total-credits').html(stagePrice);
    $('#total_credits_to_submit').val(stagePrice);
    // Just show info text, no backend request
    showStatus(`Select a stage to check availability for ${stageName}.`, 'info');
    // Make sure checkout button visible by default
    showCheckoutOnly();
  }
});

// === On load: first stage is checked; run availability if no options selected ===

 
// $(function(){
//   if (!anyOptionsSelected()) {
//     const $sel      = $('.with-gap:checked');
//     if ($sel.length) {
//       runAvailability(
//         $sel.val(),
//         $sel.data('name'),
//         $('#found_file_id').val(),
//         $('#found_file_path').val()
//       );
//     }
//   } else {
//     showCheckoutOnly();
//   }
// });



      // $(document).on('change', '.with-gap', async function () {
      //     const $radio     = $(this);
      //     const stageName  = $radio.data('name');
      //     const newStageId = $radio.val();
      //     const stagePrice = parseInt($radio.data('price'), 10) || 0;
      //     const foundFileId     = $('#found_file_id').val();
      //     const foundFilePath     = $('#found_file_path').val();

      //     // lock UI and show loader in the right column
      //     lockUI();
      //     showStatus('Checking availability… please wait.', 'info');

      //     // log (as you already do)
      //     $.post("/add_file_log", {
      //       event: "stage_selected",
      //       disc: "stage " + stageName + " is picked.",
      //       _token: $('meta[name="csrf-token"]').attr('content')
      //     });

      //     console.log('here we are');

      //     try {
      //       const res = await $.ajax({
      //         url: "{{ route('check-stage-availability') }}",
      //         type: "POST",
      //         headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      //         data: { stage_id: newStageId, found_file_id: foundFileId, found_file_path: foundFilePath }
      //       });

      //       if (res.available) {
      //         // AUTO delivery → show Download, hide Checkout
      //         showStatus(res.message || 'This modification can be delivered automatically.', 'success');
      //         $('#delivery_mode').val('auto');
      //         $('#btn-checkout').addClass('hide');
      //         $('#btn-download').removeClass('hide');

      //         // set fields needed by download-file route
      //         // server may send res.mode; if missing, derive from stageName
              
      //         $('#mode').val(res.mode);
      //         $('#output_file_url').val(res.output_file_url || '');

      //       } else {
      //         // MANUAL delivery → show Checkout, hide Download
      //         showStatus(res.message || 'This modification will be delivered manually (delayed).', 'danger');
      //         $('#delivery_mode').val('manual');
      //         $('#btn-download').addClass('hide');
      //         $('#btn-checkout').removeClass('hide');
      //       }
      //     } catch (e) {
      //       // On error, fall back to checkout
      //       // console.log(e.message);
      //       showStatus('Could not verify availability. Proceed to checkout.', 'danger');
      //       $('#delivery_mode').val('manual');
      //       $('#btn-download').addClass('hide');
      //       $('#btn-checkout').removeClass('hide');
      //     }

      //     // ---- your existing credits/UI refresh ----
      //     let stages_str = `<div class="divider-light"></div>
      //       <p class="tuning-resume">${stageName} <small>${stagePrice} credits</small></p>`;
      //     $('#rows-for-credits').html(stages_str);
      //     $('#total-credits').html(stagePrice);
      //     $('#total_credits_to_submit').val(stagePrice);

      //     // reset options and reload option credits for this stage (your current logic)
      //     $(".options-checkbox").prop('checked', false);
      //     $('.dtc-off-textarea, .vmax-off-textarea').addClass('hide');

      //     valuesArray = null;
      //     await $.ajax({
      //       url: 'get_options_for_stage',
      //       data: { stage_id: newStageId },
      //       async: false,
      //       type: "POST",
      //       headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
      //       success: function (options) {
      //         valuesArray = $.parseJSON(options);
      //         $.each(valuesArray, function (i, v) {
      //           if (file_type === 'slave') {
      //             $('#option-credits-'+v.option_id).html(v.slave_credits);
      //             $('.option-credits-'+v.option_id).attr('data-price', v.slave_credits)
      //                                             .attr('data-default-price', v.slave_credits);
      //           } else {
      //             $('#option-credits-'+v.option_id).html(v.master_credits);
      //             $('.option-credits-'+v.option_id).attr('data-price', v.master_credits)
      //                                             .attr('data-default-price', v.master_credits);
      //           }
      //         });
      //       }
      //     });

      //     // finally unlock UI
      //     unlockUI();
      //   });

      Array.prototype.getUnique = function() {
              var o = {}, a = []
              for (var i = 0; i < this.length; i++) o[this[i]] = 1
              for (var e in o) a.push(e)
              return a
      }

      window.onpopstate = function() {
            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "back_button_click",
                    'disc': "customer clicked back button on stages page.",
                },
                success: function(res) {
                    console.log(res);
                }
            });
        }

      $(document).on("contextmenu", "#content", function(e){
            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "right_click",
                    'disc': "customer right clicked on stages page.",
                },
                success: function(res) {
                    console.log(res);
                }
            });
            return false;
        });

      $(document).on('click','input[type="checkbox"]',function(){
          let name = $(this).data('name');
          if($(this).prop("checked") == false){ 
              $.ajax({
                  url: "/add_file_log",
                  type: "POST",
                  headers: {
                      'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                  },
                  data: {
                      'event': "option_unpicked",
                      'disc': "option "+name+" unpicked.",
                  },
                  success: function(res) {
                      console.log(res);
                  }
                });
          }
      });

      $(document).on('click','input[type="checkbox"]',function(){

          checkbox_credits_count = 0;
          let options_str = '';
          $('#rows-for-credits').html(stages_str);
          $('input[type="checkbox"]').each(function () {
              if($(this).prop("checked") == true){
                  // console.log($(this).data('price'));
                  let option_id = $(this).val();
                  // console.log(valuesArray);
                  let price = 0;
                  jQuery.each(valuesArray, function(i,v){
                    // console.log(v.option_id);
                    if(v.option_id == option_id){
                      // console.log(v);
                      if(file_type == 'slave')
                        price = v.slave_credits;
                      else
                        price = v.master_credits;
                    }
                  });

                  console.log('price: '+price);

                  checkbox_credits_count +=  parseInt(price);
                  service_ids.push($(this).val());
                  let name = $(this).data('name');

                  $.ajax({
                      url: "/add_file_log",
                      type: "POST",
                      headers: {
                          'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                      },
                      data: {
                          'event': "option_picked",
                          'disc': "option "+name+" is picked.",
                      },
                      success: function(res) {
                          console.log(res);
                      }
                  });

                  options_str += '<div class="divider-light"></div><p class="tuning-resume">'+name+' <small>'+price+' credits</small></p>';
              }
              else{

                  service_ids.removeByValue($(this).val());
              }
          });

          let discount = 0;
          let unique_service_ids = service_ids.getUnique();
          console.log('thing: '+checkbox_credits_count);
          
          $('#rows-for-credits').append(options_str);           
          $('#total-credits').html(parseInt(value)+parseInt(checkbox_credits_count)-parseInt(discount));

          if(discount > 0){
              $('#without-discount-total-credits').removeClass('hide');
              $('#without-discount-total-credits').html(value+checkbox_credits_count+" ");
          }
          else{
              $('#without-discount-total-credits').addClass('hide');
          }

          $('#total_credits_to_submit').val(parseInt(value)+parseInt(checkbox_credits_count)-parseInt(discount));

      });

    });
</script>

@endsection
@extends('layouts.app')
@section('pagespecificstyles')

<style>

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
                <h1>Add Information</h1>
                <p>3/5</p>
        </div>
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

        <form method="POST" action="{{ route('set-mods') }}"  enctype="application/x-www-form-urlencoded" name="file_upload_tuning" id="file-upload-tuning-form" autocomplete="off">
            <input type="hidden" value="{{ $file->id }}" name="file_id" id="file_id">
            <input type="hidden" id="file_tool_type" value="{{$file->tool_type}}">
            @csrf

             <div class="row post-row">
                

                <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="form-group">
                                  <label for="exampleInputCompanyLP1">Brand *</label>
                                  
                                    <select name="brand" id="brand" class="select-dropdown form-control">
                                        @if(!old('brand'))
                                        <option selected value="brand">{{__('Brand')}}</option>
                                        @endif
                                        @foreach ($brands as $b)
                                            <option @if(old('brand')==$b) selected @endif value="{{ $b }}">{{$b}}</option>
                                        @endforeach
                                    </select>
                                
                                </div>
                              </div>

                              <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="form-group">
                                  <label for="exampleInputCompanyLP1">Model *</label>
                                  
                                <select name="model" id="model" class="select-dropdown form-control" disabled>
                                    <option value="model" @if(!old('model')) selected @endif disabled>{{__('Model')}}</option>
                                </select>
                                
                                </div>
                              </div>

                              <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="form-group">
                                  <label for="exampleInputCompanyLP1">Version *</label>
                                  
                                <select name="version" id="version" class="select-dropdown form-control" disabled>
                                    <option value="version" @if(!old('version')) selected @endif disabled>{{__('Version')}}</option>
                                </select>
                                
                                </div>
                              </div>

                              <div class="col-xl-6 col-lg-6 col-md-6">
                                <div class="form-group">
                                  <label for="exampleInputCompanyLP1">Engine *</label>
                                  
                                <select name="engine" id="engine" class="select-dropdown form-control" disabled>
                                    <option value="engine" @if(!old('engine')) selected @endif disabled>{{__('Engine')}}</option>
                                </select>
                                
                                </div>
                              </div>

                              <div class="col-xl-6 col-lg-6 col-md-6" id="ecu_box">
                                <div class="form-group">
                                  <label for="exampleInputCompanyLP1">ECU Type *</label>
                                  
                                <select name="ecu" id="ecu" class="select-dropdown form-control" disabled>
                                    <option value="ecu" @if(!old('ecu')) selected @endif disabled>{{__('ECU')}}</option>
                                </select>
                                
                                </div>

                                

                              </div>

                              
                              <div class="col-xl-6 col-lg-6 col-md-6">
                                  <div class="form-group">
                                      <label for="file_type">File Type *</label>
                                      <input type="text" 
                                            id="file_type" 
                                            name="file_type" 
                                            class="form-control" 
                                            value="{{ $selected['file_type'] ?? 'ECU' }}" 
                                            readonly>
                                  </div>
                              </div>

                              <div class="col-xl-6 col-lg-6 col-md-6">
                                  <div class="form-group">
                                      <label for="license_plate">License Plate *</label>
                                      <input type="text" 
                                            id="license_plate" 
                                            name="license_plate" 
                                            class="form-control" 
                                            value="" 
                                            >
                                  </div>
                              </div>

                              <input type="hidden" 
                                            id="found_file_id" 
                                            name="found_file_id" 
                                            
                                            value="{{ $selected['id'] }}" 
                                            >

             
             <div class="col-xl-12 col-lg-12 col-md-12">
            <button type="submit" id="next_step" class="waves-effect waves-light btn btn-red">{{__('Next')}}</button>
             </div>
            </div>

            
        </form>
        </div>
        </div>
    </div>
</div>
@endsection

@section('pagespecificscripts')

<script>
$(function () {
  const $btn = $('#next_step');

  function isSet(sel){
    const $el = $(sel);
    const v = $el.val();
    // Treat placeholder values equal to the element id (brand/model/...) as empty
    return v && v !== $el.attr('id') && v !== 'brand' && v !== 'model' && v !== 'version' && v !== 'engine' && v !== 'ecu';
  }

  function validateForm() {
    const ok =
      isSet('#brand') &&
      isSet('#model') &&
      isSet('#version') &&
      isSet('#engine') &&
      isSet('#ecu') &&
      $.trim($('#license_plate').val()) !== '';

    $btn.prop('disabled', !ok);
  }

  // Run once on load
  validateForm();

  // Re-validate on every change/input
  $(document).on('change', '#brand,#model,#version,#engine,#ecu', validateForm);
  $(document).on('input',  '#license_plate', validateForm);

  // Extra safety: block submit if somehow invalid
  $('#file-upload-tuning-form').on('submit', function(e){
    validateForm();
    if ($btn.prop('disabled')) e.preventDefault();
  });
});
</script>

<script>
  $(function () {
  const preselected = @json($selected ?? null);

  if (preselected && preselected.brand) {
    // Set brand immediately in the select
    const $brand = $('#brand');
    // If Porsche not in dropdown yet, add it
    if ($brand.find(`option[value="${preselected.brand}"]`).length === 0) {
      $brand.append($('<option>', { value: preselected.brand, text: preselected.brand }));
    }
    $brand.val(preselected.brand);
  }

  if (preselected && preselected.model) {
    const $model = $('#model');
    if ($model.find(`option[value="${preselected.model}"]`).length === 0) {
      $model.append($('<option>', { value: preselected.model, text: preselected.model }));
    }
    $model.removeAttr('disabled').val(preselected.model);
  }

  if (preselected && preselected.version) {
    const $version = $('#version');
    if ($version.find(`option[value="${preselected.version}"]`).length === 0) {
      $version.append($('<option>', { value: preselected.version, text: preselected.version }));
    }
    $version.removeAttr('disabled').val(preselected.version);
  }

  if (preselected && preselected.engine) {
    const $engine = $('#engine');
    if ($engine.find(`option[value="${preselected.engine}"]`).length === 0) {
      $engine.append($('<option>', { value: preselected.engine, text: preselected.engine }));
    }
    $engine.removeAttr('disabled').val(preselected.engine);
  }

  if (preselected && preselected.ecu_type) {
    const $ecu = $('#ecu');
    if ($ecu.find(`option[value="${preselected.ecu_type}"]`).length === 0) {
      $ecu.append($('<option>', { value: preselected.ecu_type, text: preselected.ecu_type }));
    }
    $ecu.removeAttr('disabled').val(preselected.ecu_type);
  }
});
</script>

<script type="text/javascript">

    

    function disable_dropdowns() {

        $('#model').children().remove();
        $('#model').append('<option selected id="model">Model</option>');
        $('#version').children().remove();
        $('#version').append('<option selected id="version">Version</option>');
        $('#ecu').children().remove();
        $('#ecu').append('<option selected id="ecu">ECU</option>');
        $('#gear_box').children().remove();
        $('#engine').append('<option selected id="ecu">Engine</option>');
        $('#engine').children().remove();

        $('#model').attr('disabled', 'disabled');
        $('#version').attr('disabled', 'disabled');
        $('#ecu').attr('disabled', 'disabled');
        $('#engine').attr('disabled', 'disabled');
        $('#gear_box').attr('disabled', 'disabled');
    }

    $(document).ready(function(){

        $(document).on('change', '#brand', function(e) {
            let brand = $(this).val();
            disable_dropdowns();

            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "brand_selected",
                    'disc': "brand "+brand+" is picked.",
                },
                success: function(res) {
                    console.log(res);
                }
            });

            $.ajax({
                url: "/get_models",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'brand': brand
                },
                success: function(items) {
                    console.log(items);

                    $('#model').removeAttr('disabled');
                    $('#version').attr('disabled', 'disabled');
                    $('#tools').attr('disabled', 'disabled');
                    $('#gear_box').attr('disabled', 'disabled');

                    $.each(items.models, function(i, item) {
                        console.log(item.model);
                        $('#model').append($('<option>', {
                            value: item.model,
                            text: item.model
                        }));
                    });
                }
            });
        });

        $(document).on('change', '#model', function(e) {
            // disable_dropdowns();
            $('#version').children().remove();
            $('#version').append('<option selected id="version">Version</option>');
            $('#ecu').children().remove();
            $('#ecu').append('<option selected id="ecu">ECU</option>');
            $('#gear_box').children().remove();

            $('#version').attr('disabled', 'disabled');
            $('#ecu').attr('disabled', 'disabled');
            $('#gear_box').attr('disabled', 'disabled');

            let model = $(this).val();
            let brand = $('#brand').val();

            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "model_selected",
                    'disc': "model "+model+" is picked.",
                },
                success: function(res) {
                    console.log(res);
                }
            });

            $.ajax({
                url: "/get_versions",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'model': model,
                    'brand': brand
                },
                success: function(items) {
                    console.log(items);
                    $('#model').removeAttr('disabled');
                    $('#version').removeAttr('disabled');
                    $('#tools').attr('disabled', 'disabled');
                    $('#gear_box').attr('disabled', 'disabled');
                    $.each(items.versions, function(i, item) {
                        console.log(item.generation);
                        $('#version').append($('<option>', {
                            value: item.generation,
                            text: item.generation
                        }));
                    });

                }
            });
        });

        $(document).on('change', '#version', function(e) {
            // disable_dropdowns();
            $('#engine').children().remove();
            $('#engine').append('<option selected value"engine" disabled>Engine</option>');
            
            // $('#model').attr('disabled', 'disabled');
            // $('#version').attr('disabled', 'disabled');
            $('#engine').attr('disabled', 'disabled');


            let version = $(this).val();
            let brand = $('#brand').val();
            let model = $('#model').val();

            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "version_selected",
                    'disc': "version "+version+" is picked.",
                },
                success: function(res) {
                    console.log(res);
                }
            });

            $.ajax({
                url: "/get_engines",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'model': model,
                    'brand': brand,
                    'version': version,
                },
                success: function(items) {
                    $('#engine').removeAttr('disabled');

                    console.log(items.engines);

                    $.each(items.engines, function(i, item) {
                        $('#engine').append($('<option>', {
                            value: item.engine,
                            text: item.engine
                        }));
                    });
                }
            });
        });

        $(document).on('change', '#engine', function(e) {
            // disable_dropdowns();
            $('#ecu').children().remove();
            $('#ecu').append('<option selected value="ecu" disabled>ECU</option>');
            // $('#model').attr('disabled', 'disabled');
            // $('#version').attr('disabled', 'disabled');
            $('#ecu').attr('disabled', 'disabled');
            let engine = $(this).val();
            let brand = $('#brand').val();
            let model = $('#model').val();
            let version = $('#version').val();

            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "engine_selected",
                    'disc': "engine "+engine+" is picked.",
                },
                success: function(res) {
                    console.log(res);
                }
            });

            $.ajax({
                url: "/get_type",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'model': model,
                    'brand': brand,
                    'version': version,
                    'engine': engine,
                },
                success: function(response) {
                    console.log(response);
                    if(response.type == 'truck' || response.type == 'agri' || response.type == 'machine'){
                      $('#acm_box').removeClass('hide');
                    }
                }
            });

            $.ajax({
                url: "/get_ecus",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'model': model,
                    'brand': brand,
                    'version': version,
                    'engine': engine,
                },
                success: function(items) {
                    console.log(items);
                    $('#ecu').removeAttr('disabled');
                    $('#gear_box').removeAttr('disabled');
                    $.each(items.ecus, function(i, item) {
                        $('#ecu').append($('<option>', {
                            value: item,
                            text: item
                        }));
                    });
                }
            });
        });
      

    });
</script>

@endsection
@extends('layouts.app_v1')

@section('pagespecificstyles')

@endsection
@section('content')
<div id="viewport">
    @include('layouts.sidebar_v1')
    <!-- Content -->
    <div id="content" style="padding-top: 100px !important; ">
      @include('layouts.header_v1')
      <div class="container-fluid">
        <div class="file-title-block"> 
                <span style="display: inline-flex;" class="file-heading-area">
                  <div>
                    <h3 class="m-t-5">
                        Terms and Conditions
                    </h3>
                  </div>
                </span>
        </div>
        <div class="row bt">
          
          <div class="col-xl-12 col-lg-12 col-md-12 m-t-40" style="background: white;">
            {!!\App\Models\Text::where('slug', 'terms-and-conditions')->first()->text!!}
          </div>
          
          
    </div>
  </div>
</div>

  
@endsection

@section('pagespecificscripts')

@endsection
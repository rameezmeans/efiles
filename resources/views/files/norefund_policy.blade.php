@extends('layouts.app')

@section('pagespecificstyles')

@endsection
@section('content')
<div id="viewport">
    @include('layouts.sidebar')
    <!-- Content -->
    <div id="content" style="padding-top: 100px !important; ">
      @include('layouts.header')
      <div class="container-fluid">
        <div class="file-title-block"> 
                <span style="display: inline-flex;" class="file-heading-area">
                  <div>
                    <h3 class="m-t-5">
                        No Refund Policy
                    </h3>
                  </div>
                </span>
        </div>
        <div class="row bt">
          <div class="col-xl-12 col-lg-12 col-md-12 m-t-40" >
            <p>The client's payment is for the services rendered by our engineers rather than solely for the final outcome. We are committed to ensuring the delivery of a high-quality product and offering comprehensive support. In case a review is needed we provide free of charge alternative verions for the same file. All labor performed by our team is billable, and therefore, we do not offer partial or full refunds.</p>
          </div>
    </div>
  </div>
</div>

  
@endsection

@section('pagespecificscripts')

@endsection
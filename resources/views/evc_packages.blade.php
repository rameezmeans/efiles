@extends('layouts.app')

@section('pagespecificstyles')

<style>

.card-header {
  background: #1E293B;
  color: white;
}

.card {
  padding: 0px !important;
}

.card-header {
  padding: 20px;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.card-content {
  padding: 20px;
}

.btn-cart {
  width: 100%;
  background: transparent;
  border: 1px #ddd solid;
}

.btn-cart:hover {
  background: #B01321;
  color: white;
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
                    <h1>EVC Credits Shop</h1>
                </div>
        </div>

       <div class="i-content-block price-level  shop-cards">
                
          @if($user->is_evc_customer())
            <button class="btn btn-info redirect-click m-b-30" data-redirect="{{route('evc-history')}}">EVC Credits History</button>
          @endif
          <div class="row post-row">
          
            @if(!$user->is_evc_customer())
              <div class="row text-center m-b-30">
                <label class="label label-danger">You can not purchase EVC credits. Please enter your valid EVC customer ID in <a href="{{route('account')}}">Accounts Settings</a> and become our valuable EVC customer.</label>
              </div>
            @endif
            <div class="col-xl-3 col-lg-3 col-md-3 heading-column">
              <div class="heading-column-box">
                <h3>EVC Packages</h3>
                <p>Buy package of EVC credits.</p>
                </div>
            </div>
  
            <div class="col-xl-6 col-lg-6 col-md-6">
              <div class="row">
                @foreach($evcPackages as $package)
                  <div class="col-xl-6 col-lg-6 col-md-6">
                    <div class="card" >
                      <div class="card-header">
                        <strong>{{$package->name}}</strong>
                      </div>
                      <div class="card-content">
                        <h3>{{$package->credits}} Credits</h3>
                        <span style="text-decoration: line-through;">{{$package->actual_price}}</span> <strong class="text-red">{{$package->discounted_price}}Є</strong>
                        <p class="m-t-20">{{$package->desc}}</p>
                        <form method="POST" action="{{route('buy.evc.package')}}">
                          @csrf
                          <input type="hidden" name="price" value="{{$package->discounted_price}}">
                          <input type="hidden" name="credits" value="{{$package->credits}}">
                          <input type="hidden" name="packageID" value="{{$package->id}}">
                          <button type="submit" class="btn btn-cart" @if(!$user->is_evc_customer()) disabled @endif><i class="fa fa-shopping-cart"></i> Buy</button>
                        </form>
                      </div>
                    </div>
                  </div>
                @endforeach
  
              </div>
            </div>
          </div>
          </div>
          </div>
      </div>

    </div>
  </div>
@endsection

@section('pagespecificscripts')

<script type="text/javascript">

    $( document ).ready(function(event) {

    });

</script>

@endsection
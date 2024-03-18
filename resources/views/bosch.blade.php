@extends('layouts.app')
@section('content')
<div id="viewport">
    @include('layouts.sidebar')
    <!-- Content -->
    <div id="content">
      @include('layouts.header')
      <div class="container-fluid">
        <div class="bb-light fix-header">
            <div class="header-block header-block-w-p">
                <h1>Bosch ECU Number</h1>
                <p>Page description</p>
            </div>
        </div>
        <div class="i-content-block price-level">
            
          <div class="row m-t-20">
            <div class="col-xl-3 col-lg-3 col-md-3"></div>
            <div class="col-xl-9 col-lg-9 col-md-9" style="margin-top: 50px;">
              <label class="">Search ECU Number</label>
              <form class="form-inline">
                <div class="form-group">
                <input class="form-control number-ecu" style="width: 500px;" placeholder="Enter a Bosch ECU number" type="text" autofocus="">
                <button type="button" class="btn-md btn-info" style="display: inline-block;vertical-align: inherit;border-radius:6px"><i class="fa-solid fa-magnifying-glass" style="font-size: 18px;margin-right: 5px;"></i> Search</button>
              </form>
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
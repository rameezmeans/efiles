@extends('layouts.app')

@section('pagespecificstyles')

<style>
.credits-box {
    width: 100%;
    height: 105px;
    background: #FCFCFC 0% 0% no-repeat padding-box;
    box-shadow: 0px 3px 32px #0000000A;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    opacity: 1;
    padding: 20px;
}

.credits-box .flex-row-total{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

.text-red {
    color: #b01321;
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
                    <h1>Pay Credits</h1>
                    <p></p>
                </div>
            </div>
            
            <div class="i-content-block price-level">
                <div class="row post-row">
                <div class="col-xl-3 col-lg-3 col-md-3  heading-column">
                    <div class="heading-column-box">  
                        <h3>Engieer Offer Payment</h3>
                        <p>Pay Credits to avail the offer.</p>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-4">
                    <div class="credits-box">
                        <strong class="flex-row-total totals text-red">{{__('Credits Required')}} <strong id="total-credits" style="float: right;">{{$credits}} credits</strong> </strong>
                            <strong class="totals"><strong class="">{{__('Account Credits')}}<strong><strong style="float: right;" id="account-credits">{{ $user->credits->sum('credits') }} credits</strong> </strong></strong></strong>
                            @if($user->credits->sum('credits') > $credits)
                                <strong class="totals"><strong class="flex-row-total ">{{__('Credits Remained')}}<strong><strong style="float: right;" id="required-credits1">{{ $user->credits->sum('credits') - $credits  }} credits</strong> </strong></strong></strong>
                            @else
                                <strong class="totals"><strong class="flex-row-total ">{{__('Credits To Buy')}}<strong style="float: right;"><strong id="to-buy-credits">@if($user->credits->sum('credits') > $credits){{ $user->credits->sum('credits') - $credits  }}@else {{ $credits -  $user->credits->sum('credits') }} @endif</strong> credits</strong></strong></strong>
                            @endif
                    </div>
                        <div class="m-t-20 text-center">
                    @if( $user->credits->sum('credits') > $credits )
                        <form class="text-center m-t-10" method="POST" action="{{ route('add-offer-to-file'); }}">
                            @csrf
                            <input type="hidden" name="credits" value={{ $credits }}>
                            <input type="hidden" name="file_id" value={{ $file->id }}>
                            <button  class="btn btn-success" style="background: #237E02 0% 0% no-repeat padding-box;" type="submit">
                                {{__('Pay Credits')}}
                            </button>
                        </form>
                    @else 
                        @if( $credits - $user->credits->sum('credits') == 0 )
                            <form method="POST" action="{{ route('add-offer-to-file'); }}">
                                @csrf
                                <input type="hidden" name="credits" value={{ $credits }}>
                                <input type="hidden" name="file_id" value={{ $file->id }}>
                                <button  class="btn btn-success " type="submit">
                                    {{__('Pay Credits')}}
                                </button>
                            </form>
                        @endif
                    @endif
                        </div>
                
                    @if( $user->credits->sum('credits') < $credits )
                        

                    
                        <div class="card m-t-20">
                            
                                <div class="card-header">
                                    <span class="number" id="credits-buying"></span>
                                    <span class="description"><strong>Required Tuning credit (reseller) </strong><strong style="float: right;"> {{$credits - $user->credits->sum('credits')}} </strong></span>
                                </div>
                            
                            <div class="card-content">
                                
                                
                                <strong class="price-title-new">Price </strong><strong style="float: right;">{{($credits - $user->credits->sum('credits'))*($price->value+$factor)}} €</strong>
                                <span class="price-title-description">({{__('Original Price')}})</span>
                            </div>
                            <div class="card-footer text-center">
                                <form method="POST" action="{{route('offer-checkout')}}" >
                                    @csrf
                                    <input type="hidden" name="price_per_unit" id="price_per_unit" value="{{$price->value}}" />
                                    <input type="hidden" name="factor" id="factor" value="{{$factor}}" />
                                    <input type="hidden" name="file_id"  value="{{$file->id}}" />
                                    <input type="hidden" name="tax" id="tax" value="{{$tax}}" />
                                    <input type="hidden" name="credits_to_buy" value="{{$credits - $user->credits->sum('credits')}}" />
                                    <input type="hidden" name="credits_for_checkout" value="{{$credits}}" />
                                    <button type="submit" class="btn btn-red waves-effect waves-light m-sm">
                                        {{__('Buy')}}
                                    </button>
                                </form>
                            </div>
                        
                        
                            
                    @endif
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

        $('form').submit(function () {
            $(this).find(':submit').attr('disabled', 'disabled');
        });

    });

</script>

@endsection
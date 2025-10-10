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
                    <h1>Downlaod File</h1>
                    <p></p>
                </div>
            </div>
            
            <div class="i-content-block price-level">
                <div class="row post-row">
                

                <div class="col-xl-4 col-lg-4 col-md-4">
                    
                    <!-- Start the download in a hidden iframe -->
  <iframe src="{{ $downloadUrl }}" style="display:none" onload="go()"></iframe>
  
                
                    
                </div>
                </div>
                </div>
            </div>
            

        </div>
    </div>

@endsection

@section('pagespecificscripts')

<script>
    // Fallback in case onload doesn't fire quickly on some browsers
    let kicked = false;
    function go(){ if(!kicked){ kicked = true; window.location = "{{ $redirectUrl }}"; } }
    setTimeout(go, 2000); // ensures redirect even if iframe onload is delayed
  </script>    

<script type="text/javascript">

    $( document ).ready(function(event) {

        

        window.onpopstate = function() {
            $.ajax({
                url: "/add_file_log",
                type: "POST",
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'event': "back_button_click",
                    'disc': "customer clicked back button on credits page.",
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
                    'disc': "customer right clicked on credits paying page.",
                },
                success: function(res) {
                    console.log(res);
                }
            });
            return false;
        });

        $('form').submit(function () {
            $(this).find(':submit').attr('disabled', 'disabled');
        });

    });

</script>

@endsection
@extends('layouts.app')
@section('content')
<div id="viewport">
    @include('layouts.sidebar')
    <!-- Content -->
    <div id="content">
      @include('layouts.header')
      <div class="container-fluid">
        
          <h1 class="m-t-40">EVC Purchase History</h1>
          <div class="row bt m-t-40">
                <div style="margin-top: 60px;">
                <table class="table table-hover datatable">
                  <thead>
                    <tr>
                      <th>Transaction Time</th>
                      <th>Credits</th>
                      <th>Total Credits</th>
                      <th>File Name</th>
                     
                    </tr>
                  </thead>
                  <tbody>
                      @if($records)
                        @foreach ($records as $record)
                            <tr class="" href="#">
                                <td>{{$record->Date}}</td>
                                <td>@if($record->CreditsAfter-$record->CreditsBefore > 0) <label class="label label-success"> {{$record->CreditsAfter-$record->CreditsBefore}} </label> @else <label class="label label-danger"> {{$record->CreditsAfter-$record->CreditsBefore}} </label> @endif</td>
                                <td><label class="label label-success">{{$record->CreditsAfter}}</label></td>
                                <td>{{$record->Filename}}</td>
                                
                            </tr>
                        @endforeach
                    @endif
                  </tbody>
                </table>

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
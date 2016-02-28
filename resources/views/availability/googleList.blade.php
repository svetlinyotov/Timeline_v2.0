@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/fullcalendar.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/scheduler.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/fullcalendar.print.css")}}" media="print">
    <link rel="stylesheet" href="{{asset("css/plugins/iCheck/custom.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/toastr/toastr.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/daterangepicker/daterangepicker-bs3.css")}}">
@stop

@section('script')
    <script src="{{asset("js/plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script src="{{asset("js/plugins/toastr/toastr.min.js")}}"></script>

    <script>
        $(function () {

        });

    </script>
@stop

@section('title')
    <h1>
        Google Accounts
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("/")}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{asset("/availability")}}">Availability</a></li>
        <li class="active">Google Accounts</li>
    </ol>
@stop

@section('body')

    <div class="box">
        <div class="box-body">
            @if(Session::has('message'))
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> {!! Session::get('message') !!}
                </div>
            @endif

            <a href="{{asset('auth/google/provider')}}" class="btn btn-primary"><i class="fa fa-google"></i> Add Account</a>
        </div>
    </div>

@stop
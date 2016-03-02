@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/toastr/toastr.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/sweetalert/sweetalert.css")}}">
    <style>
        .table tbody>tr>td.vertical{
            vertical-align: middle;
        }
    </style>
@stop

@section('script')
    <script src="{{asset("js/plugins/toastr/toastr.min.js")}}"></script>
    <script src="{{asset("js/plugins/sweetalert/sweetalert.min.js")}}"></script>

    <script>
        $(function () {
            $('.btn-delete-account').click(function (e) {
                var name = $(this).data('name');
                var href = $(this).data('href');

                swal({
                    title: "Are you sure you want to unlink " + name + "?",
                    text: "You can add it later again",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, unlink it",
                    cancelButtonText: "No",
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: href,
                            type: 'DELETE',
                            data: {"_token":"{{csrf_token()}}"},
                            success: function(result) {
                                setTimeout(function () {
                                    window.location = "{{asset('availability/google')}}?message=The account "+name+" is unlinked successfully";
                                }, 1400);
                                swal({
                                    title:"Deleted!",
                                    text:"The account is unlinked",
                                    type:"success",
                                    timer:1500
                                });
                            },
                            error: function(result) {
                                console.log(result);
                                swal("Error", "Internal error. Please contact global administrator. Error "+result.status+": " + result.statusText, "error");
                            }
                        });
                    } else {
                        swal({title:"Cancelled", text:"No data is affected", type:"info", timer:1500});
                    }
                });
            });
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
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            @if(Session::has('message') || isset($_GET['message']))
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button>
                    <i class="fa fa-check"></i> {!! Session::get('message') !!} {!! $_GET['message']??"" !!}
                </div>
            @endif

            <a href="{{asset('auth/google/provider')}}" class="btn btn-primary"><i class="fa fa-google"></i> Add Account</a><br><br>

            <table class="table table-bordered table-responsive">

                <tbody>
                    @foreach($data as $profile)
                        <tr>
                            <td width="55"><img src="{{$profile->avatar}}" class="img img-responsive img-circle"></td>
                            <td class="vertical">{{$profile->email}}</td>
                            <td class="vertical">{{$profile->names}}</td>
                            <td class="vertical">
                                <a href="{{asset('/availability/google/'.$profile->id)}}" class="btn btn-info btn-sm">Calendars </a>
                                <a href="#" class="btn btn-danger btn-sm btn-delete-account" data-href="{{asset('/availability/google/'.$profile->id)}}" data-name="{{$profile->email}}"><i class="fa fa-trash"></i> </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop
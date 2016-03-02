@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/toastr/toastr.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/switchery/switchery.css")}}">
@stop

@section('script')
    <script src="{{asset("js/plugins/switchery/switchery.js")}}"></script>
    <script src="{{asset("js/plugins/toastr/toastr.min.js")}}"></script>

    <script>
        $(function () {
            function setSwitchery(switchElement, checkedBool) {
                if((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
                    switchElement.setPosition(true);
                    switchElement.handleOnchange(true);
                }
            }

            $('.js-switch').each(function(e, obj){
                var input_check = new Switchery(obj, { color: '#5cb85c', secondaryColor: '#FFF', jackColor: '#FFF', jackSecondaryColor: '#d43f3a', size: 'small' });

                obj.onchange = function(input) {
                    var data_input = input.target.dataset;
                    var cal_id = data_input.calId;
                    var user_id = data_input.userId;

                    $.ajax({
                        url: "{{asset('availability/google')}}/" + user_id,
                        method: "POST",
                        data: {"calendar_id": cal_id, "_token":"{{csrf_token()}}"},
                        dataType: "json",
                        complete: function (msg) {
                            if (msg.status != 200) {
                                setSwitchery(input_check, false);
                                var error = [];
                                if(msg.responseJSON) {
                                    $.each(msg.responseJSON, function (idx2, val2) {
                                        toastr["error"](val2);
                                    });
                                }else{
                                    toastr["error"]("Error: " + msg.status + ": " + msg.statusText);
                                }
                            } else {
                                toastr["success"](msg.responseJSON.msg);
                            }
                        }
                    });
                }
            });

            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

        });

    </script>
@stop

@section('title')
    <h1>
        <a href="{{asset("/availability/google")}}" class="btn btn-xs btn-circle btn-info pull-left" style="margin: 5px 5px 0 0"><i class="fa fa-arrow-left"></i> </a>
        <img src="{{$user->avatar}}" class="img img-responsive img-circle pull-left" width="38">
        {{$user->email}}
        <small></small>
    </h1>
    <ol class="breadcrumb clear">
        <li><a href="{{asset("/")}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{asset("/availability")}}">Availability</a></li>
        <li><a href="{{asset("/availability/google")}}">Google Accounts</a></li>
        <li class="active">{{$user->email}}</li>
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
            @if(Session::has('message'))
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> {!! Session::get('message') !!}
                </div>
            @endif

            <div class="row">
                <ul class="list-group col-md-6">
                    @foreach($data as $calendar)
                        <li class="list-group-item">
                            <span class="badge">
                                <input type="checkbox" class="js-switch" data-cal-id="{{$calendar->id}}" data-user-id="{{$user->id}}" {{in_array($calendar->id, $on_calendars)?"checked":""}}/>
                                <!--<a href="#" class="btn btn-info btn-xs">View events</a>-->
                            </span>

                            <span class="label label-primary" style="background-color: {{$calendar->backgroundColor}}">&nbsp;</span>
                            {{$calendar->summary}}
                        </li>
                    @endforeach
                </ul>
            </div>


        </div>
    </div>
@stop
@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/daterangepicker/daterangepicker.css")}}">
    <style>hr{margin:10px 0} .top-warning{border-top-color:#f39c12 !important;} </style>
@stop

@section('script')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp&sensor=true"></script>
    <script src="{{asset("js/mapAddUser.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script>
        $(function () {

            $('.dateinput').daterangepicker({
                timePicker: false,
                autoUpdateInput:false,
                maxDate: new Date(new Date().setDate(new Date().getDate()-1)),
                singleDatePicker: true,
                showDropdowns: true,
                autoApply:true,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            }).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });


        });
        window.onload = function() {
            var timer;
            document.getElementById("address_input").onkeyup=function(){
                timer = setTimeout("codeAddress()", 2000);
            };
            document.getElementById("address_input").onkeydown=function(){
                clearTimeout(timer);
            };
        }
    </script>
@stop

@section('title')
    <h1>
        @if(Request::segment(1) != 'profile')<a href="@if(isset($_GET['rel']) && $_GET['rel'] == "payment"){{asset("payments?company_id=".$user->company_id)}} @else {{asset("users")}} @endif" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
            Users - {{$user->info->names}}
            <small>view</small>
        @else
            Profile
        @endif
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-user"></i> Home</a></li>
        @if(Request::segment(1) != 'profile')
        <li><a href="{{asset("users")}}">Users</a></li>
        <li>{{$user->info->names}}</li>
        @else
        <li>{{$user->info->names}}</li>
        @endif
    </ol>
@stop

@section('body')
    @if(Session::has('message'))
        <div class="callout callout-success callout-sm">
            <i class="fa fa-check"></i> {!! Session::get('message') !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">

            <div class="box box-primary">
                <div class="box-body box-profile">
                    <img class="profile-user-img img-responsive img-circle" src="{{asset('avatar/'.$user->info->avatar)}}" alt="User profile picture">
                    <h3 class="profile-username text-center">{{$user->info->names}}</h3>
                    <p class="text-muted text-center">{{$user->email}}</p>
<!--
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Rosters count last month</b> <a class="pull-right">0</a>
                        </li>
                        <li class="list-group-item">
                            <b>Working time last month</b> <a class="pull-right">0</a>
                        </li>
                        <li class="list-group-item">
                            <b>Amount earned last month</b> <a class="pull-right">0</a>
                        </li>
                    </ul>
-->
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">About</h3>
                </div>
                <div class="box-body">
                    <strong><i class="fa fa-building margin-r-5"></i>  Company</strong>
                    <p class="text-muted">
                        {{$user->company != null ? $user->company->name:''}}
                    </p>

                    <hr>

                    <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>
                    <p class="text-muted">{{$user->info->address}}</p>

                    <hr>

                    @if($user->info->birth_date)
                    <strong><i class="fa fa-calendar margin-r-5"></i> Birth date</strong>
                    <p class="text-muted">{{$user->info->birth_date}}</p>

                    <hr>
                    @endif

                    <strong><i class="fa fa-mobile margin-r-5"></i> Mobile</strong>
                    <p class="text-muted">{{$user->info->mobile}}</p>

                    <hr>

                    @if($user->info->home_phone)
                    <strong><i class="glyphicon glyphicon-phone-alt margin-r-5"></i> Home phone</strong>
                    <p class="text-muted">{{$user->info->home_phone}}</p>

                    <hr>
                    @endif

                    @if($user->info->work_phone)
                    <strong><i class="fa fa-phone margin-r-5"></i> Work phone</strong>
                    <p class="text-muted">{{$user->info->work_phone}}</p>

                    <hr>
                    @endif

                    @if($user->info->fax)
                    <strong><i class="fa fa-fax margin-r-5"></i> Fax</strong>
                    <p class="text-muted">{{$user->info->fax}}</p>

                    <hr>
                    @endif

                    @if($user->info->other)
                    <strong><i class="fa fa-sticky-note-o margin-r-5"></i> Other</strong>
                    <p class="text-muted">{{$user->info->other}}</p>

                    <hr>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <!--<li><a href="#calendar" data-toggle="tab">Calendar</a></li>-->
                    <li class="active"><a href="#notifications" data-toggle="tab">Notifications &nbsp;@if($notification_count > 0) <small class="label pull-right bg-yellow">{{$notification_count}}</small> @endif </a></li>
                    <!--<li><a href="#messages" data-toggle="tab">Messages &nbsp;<small class="label pull-right bg-yellow">12</small></a></li>-->
                    <li class="pull-right top-warning"><a href="{{asset('users/'.$user->id.'/edit?rel=edit')}}"><i class="fa fa-pencil"></i> Edit</a></li>
                </ul>
                <div class="tab-content">
                    <!--
                    <div class="active tab-pane" id="calendar">
                        <h3>Calendar in development</h3>
                    </div>
                    -->
                    <div class="active tab-pane" id="notifications">
                        <div class="list-group">
                            @foreach($notification_list as $notification)
                                <a href="{{$notification['link']}}?noti={{$notification['id']}}" class="list-group-item @if($notification['is_read'] == 0) list-group-item-warning @endif">
                                    {!! $notification['text'] !!}
                                    <small class="pull-right">{{$notification['date']}}</small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
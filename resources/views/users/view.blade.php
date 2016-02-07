@extends('layouts.master')

@section('style')
    <style>hr{margin:10px 0} .top-warning{border-top-color:#f39c12 !important;} </style>
@stop

@section('script')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp&sensor=true"></script>
    <script src="{{asset("js/mapAddUser.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script>

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

        @if(Request::segment(1) != 'profile')
            <a href="@if(isset($_GET['rel']) && $_GET['rel'] == "payment"){{asset("payments?company_id=".$user->company_id)}} @else {{asset("users")}} @endif" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
            {{$user->info->names}}
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
            <li>Profile</li>
        @endif
    </ol>
@stop

@section('body')
    @if(Session::has('message'))
        <div class="alert alert-success">
            <i class="fa fa-check"></i> {!! Session::get('message') !!}
        </div>
    @endif

    <div class="row m-b-lg m-t-lg">
        <div class="col-md-6">

            <div class="profile-image">
                <img src="{{asset('avatar/'.Auth::user()->info->avatar)}}" class="img-circle circle-border m-b-md" alt="profile">
            </div>
            <div class="profile-info">
                <div class="">
                    <div>
                        <h2 class="no-margins">
                            {{$user->info->names}}
                        </h2>
                        <h4>{{$user->role}}</h4>
                        <small>
                            <strong>Companies:</strong> {{implode(", ",$user->company->pluck('name')->toArray())}}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <table class="table small m-b-xs">
                <tbody>
                <tr>
                    <td>
                        Rosters count last month <strong class="pull-right">0</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        Working time last  <strong class="pull-right">0</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        Amount earned last month <strong class="pull-right">0</strong>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-3">
            <small>Working last week</small>
            <h2 class="no-margins">206 480</h2>
            <div id="sparkline1"></div>
        </div>


    </div>
    <div class="row">

        <div class="col-lg-3">

            <a href="@if(Request::segment(1) != 'profile'){{asset('/users/'.$user->id.'/edit')}}@else{{asset('/profile/edit')}}@endif" class="btn btn-primary btn-block"><i class="fa fa-edit"></i> Edit</a><br>
            <div class="ibox">
                <div class="ibox-content">
                    <h3>About {{$user->info->names}}</h3>
                    <hr>

                    <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>
                    <p class="text-muted">{{$user->info->address}}</p>


                    @if($user->info->birth_date)
                        <strong><i class="fa fa-calendar margin-r-5"></i> Birth date</strong>
                        <p class="text-muted">{{$user->info->birth_date}}</p>

                    @endif

                    <strong><i class="fa fa-mobile margin-r-5"></i> Mobile</strong>
                    <p class="text-muted">{{$user->info->mobile}}</p>


                    @if($user->info->home_phone)
                        <strong><i class="glyphicon glyphicon-phone-alt margin-r-5"></i> Home phone</strong>
                        <p class="text-muted">{{$user->info->home_phone}}</p>

                    @endif

                    @if($user->info->work_phone)
                        <strong><i class="fa fa-phone margin-r-5"></i> Work phone</strong>
                        <p class="text-muted">{{$user->info->work_phone}}</p>

                    @endif

                    @if($user->info->fax)
                        <strong><i class="fa fa-fax margin-r-5"></i> Fax</strong>
                        <p class="text-muted">{{$user->info->fax}}</p>

                    @endif

                    @if($user->info->other)
                        <strong><i class="fa fa-sticky-note-o margin-r-5"></i> Other</strong>
                        <p class="text-muted">{{$user->info->other}}</p>

                    @endif

                </div>
            </div>

            @if(Auth::user()->id != $user->id)
            <div class="ibox">
                <div class="ibox-content">
                    <h3>Private message</h3>

                    <p class="small">
                        Send message to {{$user->info->names}}
                    </p>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="email" class="form-control" placeholder="Message subject">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" placeholder="Your message" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block">Send</button>

                </div>
            </div>
            @endif

        </div>

        <div class="col-lg-5">

            <div class="ibox">
                <div class="ibox-content">
                    <h3><a href="@if(Request::segment(1) != 'profile'){{asset('/users/'.$user->id.'/notifications')}}@else{{asset('/profile/notifications')}}@endif">Notifications <small>(unseen)</small></a></h3>

                    <div class="list-group">
                        @foreach($notification_list as $notification)
                            <a href="{{$notification['link']}}?noti={{$notification['id']}}" class="list-group-item @if($notification['is_read'] == 0) list-group-item-warning @endif">
                                {!! $notification['text'] !!}
                                <small class="pull-right">{{$notification['date']}}</small>
                            </a>
                        @endforeach
                        <a href="@if(Request::segment(1) != 'profile'){{asset('/users/'.$user->id.'/notifications')}}@else{{asset('/profile/notifications')}}@endif" class="list-group-item list-group-item-info text-center">
                            <h3>See All</h3>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <h3>Messages <small>(unseen)</small> </h3>

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

        <div class="col-lg-4 m-b-lg">
            <div id="vertical-timeline" class="vertical-container light-timeline no-margins">
                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon navy-bg">
                        <i class="fa fa-briefcase"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>Meeting</h2>
                        <p>Conference on the sales results for the previous year. Monica please examine sales trends in marketing and products. Below please find the current status of the sale.
                        </p>
                        <a href="#" class="btn btn-sm btn-primary"> More info</a>
                                    <span class="vertical-date">
                                        Today <br>
                                        <small>Dec 24</small>
                                    </span>
                    </div>
                </div>

                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon blue-bg">
                        <i class="fa fa-file-text"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>Send documents to Mike</h2>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since.</p>
                        <a href="#" class="btn btn-sm btn-success"> Download document </a>
                                    <span class="vertical-date">
                                        Today <br>
                                        <small>Dec 24</small>
                                    </span>
                    </div>
                </div>

                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon lazur-bg">
                        <i class="fa fa-coffee"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>Coffee Break</h2>
                        <p>Go to shop and find some products. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's. </p>
                        <a href="#" class="btn btn-sm btn-info">Read more</a>
                        <span class="vertical-date"> Yesterday <br><small>Dec 23</small></span>
                    </div>
                </div>

                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon yellow-bg">
                        <i class="fa fa-phone"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>Phone with Jeronimo</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iusto, optio, dolorum provident rerum aut hic quasi placeat iure tempora laudantium ipsa ad debitis unde? Iste voluptatibus minus veritatis qui ut.</p>
                        <span class="vertical-date">Yesterday <br><small>Dec 23</small></span>
                    </div>
                </div>

                <div class="vertical-timeline-block">
                    <div class="vertical-timeline-icon navy-bg">
                        <i class="fa fa-comments"></i>
                    </div>

                    <div class="vertical-timeline-content">
                        <h2>Chat with Monica and Sandra</h2>
                        <p>Web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like). </p>
                        <span class="vertical-date">Yesterday <br><small>Dec 23</small></span>
                    </div>
                </div>
            </div>

        </div>

    </div>

@stop
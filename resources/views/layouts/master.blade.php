<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TimeTracker</title>

    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('font-awesome/css/font-awesome.css')}}" rel="stylesheet">
    <link href="{{asset('css/animate.css')}}" rel="stylesheet">
    <link href="{{asset('css/style.css')}}" rel="stylesheet">

</head>

<body class=" md-skin">

<div id="wrapper">

    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element"> <span>
                            <img alt="Profile Image" class="img-circle" src="{{asset('avatar/'.Auth::user()->info->avatar)}}" width="60" />
                             </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">{{Auth::user()->info->names}}</strong>
                             </span> <span class="text-muted text-xs block">{{Auth::user()->role}} <b class="caret"></b></span> </span> </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a href="{{asset('profile')}}">Profile</a></li>
                            <li><a href="#">Notifications</a></li>
                            <li><a href="#">Messages</a></li>
                            <li class="divider"></li>
                            <li><a href="{{asset('logout')}}">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        TT
                    </div>
                </li>

                <li class="{{ Request::segment(1) == 'profile' ? "active" : null }}">
                    <a href="{{asset("profile")}}">
                        <i class="fa fa-user"></i> <span class="nav-label">Profile</span>
                    </a>
                </li>
                @if(Auth::user()->role == "worker")
                        <!-- <li class="{{ Request::segment(1) == 'availability' ? "active" : null }}">
                        <a href="{{asset("availability")}}">
                            <i class="fa fa-clock-o"></i> <span class="nav-label">Availability</span>
                        </a>
                    </li>-->
                @endif
                @if(Auth::user()->role == "supadmin")
                    <li class="{{ Request::segment(1) == 'companies' ? "active" : null }}">
                        <a href="{{asset("companies")}}">
                            <i class="fa fa-building"></i> <span class="nav-label">Companies</span>
                        </a>
                    </li>
                @endif
                @if(Auth::user()->role != "worker")
                    <li class="{{ Request::segment(1) == 'users' ? "active" : null }}">
                        <a href="{{asset("users")}}">
                            <i class="fa fa-users"></i> <span class="nav-label">Users</span>
                        </a>
                    </li>
                @endif
                <li class="{{ Request::segment(1) == 'rosters' ? "active" : null }}">
                    <a href="{{asset("rosters")}}">
                        <i class="fa fa-calendar"></i> <span class="nav-label">Rosters</span>
                    </a>
                </li>
                @if(Auth::user()->role != "worker")
                    <li class="{{ Request::segment(1) == 'payments' ? "active" : null }}">
                        <a href="{{asset("payments")}}">
                            <i class="fa fa-money"></i> <span class="nav-label">Payments</span>
                        </a>
                    </li>
                    @endif
                    @if(Auth::user()->role == "supadmin")
                            <!--<li class="{{ Request::segment(1) == 'archive' ? "active" : null }}">
                        <a href="{{asset("archive")}}">
                            <i class="fa fa-archive"></i> <span class="nav-label">Archive data</span>
                        </a>
                    </li>-->
                @endif
            </ul>

        </div>
    </nav>

    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <span class="m-r-sm text-muted welcome-message">Welcome to TimeTracker</span>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-envelope"></i>  <span class="label label-warning">16</span>
                        </a>
                        <ul class="dropdown-menu dropdown-messages">
                            <li>
                                <div class="dropdown-messages-box">
                                    <a href="profile.html" class="pull-left">
                                        <img alt="image" class="img-circle" src="img/a7.jpg">
                                    </a>
                                    <div class="media-body">
                                        <small class="pull-right">46h ago</small>
                                        <strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>. <br>
                                        <small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>

                            <li>
                                <div class="text-center link-block">
                                    <a href="mailbox.html">
                                        <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                            <i class="fa fa-bell"></i>  <span class="label label-primary">{{$user_notification_count}}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-alerts">
                            @if($user_notification_count == 0)
                                <li class="header">You have no new notifications</li>
                            @else
                                @foreach($user_notification_list as $list)
                                    <li>
                                        <a href="{{$list['link']}}?noti={{$list['id']}}">
                                            <div>
                                                <i class="fa fa-{{$list['icon']}} fa-fw"></i> {!! $list['text'] !!}
                                                <span class="pull-right text-muted small">{{$list['date']}}</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                @endforeach
                            @endif

                            <li>
                                <div class="text-center link-block">
                                    <a href="#">
                                        <strong>See All</strong>
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>


                    <li>
                        <a href="{{asset('logout')}}">
                            <i class="fa fa-sign-out"></i> Log out
                        </a>
                    </li>
                </ul>

            </nav>
        </div>
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-10">

                @yield('title')

            </div>
            <div class="col-lg-2">

            </div>
        </div>
        <div class="wrapper wrapper-content animated fadeInRight">

            @yield('body')

        </div>
        <div class="footer">
            <div class="pull-right">
                10GB of <strong>250GB</strong> Free.
            </div>
            <div>
                <strong>Copyright</strong> Example Company &copy; 2014-2015
            </div>
        </div>

    </div>
</div>



<!-- Mainly scripts -->
<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/bootstrap.js')}}"></script>
<script src="{{asset('js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
<script src="{{asset('js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

<!-- Custom and plugin javascript -->
<script src="{{asset('js/inspinia.js')}}"></script>
<script src="{{asset('js/plugins/pace/pace.min.js')}}"></script>

<!-- Sparkline -->
<script src="{{asset('js/plugins/sparkline/jquery.sparkline.min.js')}}"></script>

<script>
    $(document).ready(function() {


        $("#sparkline1").sparkline([34, 43, 43, 35, 44, 32, 44, 48], {
            type: 'line',
            width: '100%',
            height: '50',
            lineColor: '#1ab394',
            fillColor: "transparent"
        });


    });
</script>

</body>

</html>

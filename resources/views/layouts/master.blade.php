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
    <link href="{{asset('css/pace.css')}}" rel="stylesheet">
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">

    @yield('style')
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
                            <li><a href="{{asset('profile/notifications')}}">Notifications</a></li>
                            <li><a href="{{asset('profile/messages')}}">Messages</a></li>
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
                                    <a href="{{asset('profile/messages')}}">
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
                                    <a href="{{asset('profile/notifications')}}">
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
        <div class="footer fixed">
            <div class="pull-right">
                Version: <strong>2.5</strong>
            </div>
            <div>
                <strong>Copyright</strong> <a href="snsdevelop.com">SnSDevelop</a> &copy; {{date("Y")}}
            </div>
        </div>

    </div>
</div>



<!-- Mainly scripts -->
<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/bootstrap.js')}}"></script>
<script src="{{asset('js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>

<script src="{{asset("js/plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
<script src="{{asset("js/plugins/niceScroll/jquery.nicescroll.js")}}"></script>

<script src="{{asset('js/inspinia.js')}}"></script>
<script src="{{asset('js/plugins/pace/pace.min.js')}}"></script>

@yield('script')
<script>
    $(function () {
        $('body').niceScroll({
            autohidemode: 'false',
            cursorborderradius: '10px',
            background: '#E5E9E7',
            cursorwidth: '10px',
            cursorcolor: '#999999'
        });

        $(".btn-loading, input[type='submit']").data("loading-text", "");

        $("form").submit(function() {
            $('.btn-loading').button('loading');
        });

        if(window.location.hash) {
            var hash = window.location.hash;
            if($(hash).hasClass('modal')) {
                $(hash).modal('toggle');
            }

            if (hash) {
                $('.nav-tabs a[href='+hash+']').tab('show');
            }
            $('.nav-tabs a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            });
        }
    });
</script>
</body>

</html>

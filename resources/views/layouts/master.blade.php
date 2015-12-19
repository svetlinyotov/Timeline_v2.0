<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>TIMELINE v2.0 | Blank Page</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{asset("css/bootstrap.min.css")}}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{asset("css/AdminLTE.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/skins/_all-skins.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/custom.css")}}">
    @yield('style')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-yellow sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="{{asset("/")}}" class="logo">
            <span class="logo-mini"><b>T</b></span>
            <span class="logo-lg"><b>Timeline</b> v2.0</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">

                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <span class="label label-success">4</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have 4 messages</li>
                            <li>
                                <ul class="menu">
                                    <li><!-- start message -->
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="{{asset('avatar/'.Auth::user()->info->photo)}}" class="img-circle" alt="User Image">
                                            </div>
                                            <h4>
                                                Support Team
                                                <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li><!-- end message -->
                                </ul>
                            </li>
                            <li class="footer"><a href="#">See All Messages</a></li>
                        </ul>
                    </li>

                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning">10</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have 10 notifications</li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer"><a href="#">View all</a></li>
                        </ul>
                    </li>

                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{asset('avatar/'.Auth::user()->info->photo)}}" class="user-image" alt="User Image">
                            <span class="hidden-xs">{{Auth::user()->info->names}}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="{{asset('avatar/'.Auth::user()->info->photo)}}" class="img-circle" alt="User Image">
                                <p>
                                    {{Auth::user()->info->names}}
                                    <small>Member since {{date('jS M Y', strtotime(Auth::user()->created_at))}}</small>
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <li class="user-body">
                                <div class="col-xs-6 text-center">
                                    <a href="#">Messages</a>
                                </div>
                                <div class="col-xs-6 text-center">
                                    <a href="#">Notifications</a>
                                </div>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="#" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{asset('logout')}}"><i class="fa fa-sign-out"></i></a>
                    </li>

                </ul>
            </div>
        </nav>
    </header>

    <!-- =============================================== -->

    <aside class="main-sidebar">

        <section class="sidebar">

            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{asset('avatar/'.Auth::user()->info->photo)}}" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p>{{Auth::user()->info->names}}</p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>


            <ul class="sidebar-menu">
                <li class="header">MAIN NAVIGATION</li>

                <li class="{{ Request::segment(1) == 'dashboard' ? "active" : null }}">
                    <a href="{{asset("dashboard")}}">
                        <i class="fa fa-dashboard"></i> <span>Home</span>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'companies' ? "active" : null }}">
                    <a href="{{asset("companies")}}">
                        <i class="fa fa-building"></i> <span>Companies</span>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'users' ? "active" : null }}">
                    <a href="{{asset("users")}}">
                        <i class="fa fa-users"></i> <span>Users</span><small class="label pull-right bg-yellow">12</small>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'settings' ? "active" : null }}">
                    <a href="{{asset("settings")}}">
                        <i class="fa fa-gear"></i> <span>Settings</span>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'rosters' ? "active" : null }}">
                    <a href="{{asset("rosters")}}">
                        <i class="fa fa-calendar"></i> <span>Rosters</span>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'payments' ? "active" : null }}">
                    <a href="{{asset("payments")}}">
                        <i class="fa fa-money"></i> <span>Payments</span>
                    </a>
                </li>
                <li class="{{ Request::segment(1) == 'archive' ? "active" : null }}">
                    <a href="{{asset("archive")}}">
                        <i class="fa fa-archive"></i> <span>Archive data</span>
                    </a>
                </li>

            </ul>
        </section>

    </aside>

    <!-- =============================================== -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            @yield('title')
        </section>

        <!-- Main content -->
        <section class="content">

            @yield('body')

        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.0
        </div>
        <strong>Copyright &copy; 2014-2015 <a href="http://snsdevelop.com">SnS Develop</a>.</strong> All rights reserved.
    </footer>


</div>

<script src="{{asset("plugins/jQuery/jQuery-2.1.4.min.js")}}"></script>
<script src="{{asset("js/bootstrap.min.js")}}"></script>
<script src="{{asset("plugins/fastclick/fastclick.min.js")}}"></script>
<script src="{{asset("js/app.js")}}"></script>
<script src="{{asset("js/demo.js")}}"></script>
@yield('script')
</body>
</html>

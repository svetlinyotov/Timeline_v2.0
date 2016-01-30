<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TimeTracker | Login</title>

    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('font-awesome/css/font-awesome.css')}}" rel="stylesheet">

    <link href="{{asset('css/plugins/iCheck/custom.css')}}" rel="stylesheet">
    <link href="{{asset('css/animate.css')}}" rel="stylesheet">
    <link href="{{asset('css/style.css')}}" rel="stylesheet">

</head>

<body class="gray-bg md-skin">

<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div>
            <h1 class="logo-name">TT<sup><small>v2.5</small></sup></h1>
        </div>

        <h3>Welcome to TimeTracker v2.5</h3>
        <p>Sign in to start your session
        </p>
        <form class="m-t" role="form" action="{{asset('/login')}}" method="post">
            {!! csrf_field() !!}

            @if ($errors->any())
                <div class="alert alert-danger">{{ implode('', $errors->all(':message')) }}</div>
            @endif

            <div class="form-group has-feedback">
                <input type="email" name="email" class="form-control" placeholder="Email" required="" value="{{ old('email') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="Password" required="">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8 text-left">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                    </div>
                </div>

                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat m-b">Sign In</button>
                </div>

            </div>

            <a href="#"><small>Forgot password?</small></a>
            <p class="text-muted text-center"><small>Do not have an account?</small></p>
            <a class="btn btn-sm btn-white btn-block" href="register.html">Create an account</a>
        </form>
        <p class="m-t"> <small>&copy; {{date("Y")}} SnSDevelop</small> </p>
    </div>
</div>

<!-- Mainly scripts -->
<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/bootstrap.min.js')}}"></script>
<script src="{{asset('js/plugins/iCheck/icheck.min.js')}}"></script>

<script>
    $(document).ready(function(){
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
</script>

</body>

</html>
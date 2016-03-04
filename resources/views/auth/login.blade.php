@extends('layouts.login')

@section('form')
    <h3>Welcome to TimeTracker v2.5</h3>
    <p>Sign in to start your session</p>

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

        <a href="{{asset('password/email')}}"><small>Forgot password?</small></a>
        <!--
        <p class="text-muted text-center"><small>Do not have an account?</small></p>
        <a class="btn btn-sm btn-white btn-block" href="register.html">Create an account</a>
        -->
    </form>
@stop
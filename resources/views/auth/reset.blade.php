@extends('layouts.login')

@section('form')
    <div class="animated fadeInDown">
        <div class="row">

            <div class="col-md-12">
                <div class="ibox-content">

                    <h2 class="font-bold">Forgot password reset</h2>

                    <p>
                        Enter the new password that you will use to access your account.
                    </p>

                    <div class="row">

                        <div class="col-lg-12">
                            <form method="POST" action="{{asset('/password/reset')}}">
                                {!! csrf_field() !!}
                                <input type="hidden" name="token" value="{{ $token }}">

                                @if (count($errors) > 0)
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Email address" required="" value="{{ old('email') }}">
                                </div>

                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required="" value="">
                                </div>

                                <div class="form-group">
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required="" value="">
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Reset Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--
        <hr/>
        <div class="row">
            <div class="col-md-6">
                Copyright Example Company
            </div>
            <div class="col-md-6 text-right">
                <small>© 2014-2015</small>
            </div>
        </div>
        -->
    </div>
@stop
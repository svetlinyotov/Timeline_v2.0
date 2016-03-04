@extends('layouts.login')

@section('form')
    <div class="animated fadeInDown">
        <div class="row">

            <div class="col-md-12">
                <div class="ibox-content">

                    <h2 class="font-bold">Forgot password</h2>

                    <p>
                        Enter your email address and your password will be reset and emailed to you.
                    </p>

                    <div class="row">

                        <div class="col-lg-12">
                            <form class="m-t" role="form" method="POST" action="{{asset('/password/email')}}">
                                {!! csrf_field() !!}
                                @if (count($errors) > 0)
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if(Session::has('status'))
                                    <div class="alert alert-success alert-dismissable">
                                        <i class="fa fa-check"></i> {!! Session::get('status') !!}
                                    </div>
                                @endif

                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Email address" required="" value="{{ old('email') }}">
                                </div>

                                <div class="btn-group">
                                    <a href="{{asset('login')}}" class="btn btn-info"><i class="fa fa-arrow-left"></i> </a>
                                    <button type="submit" class="btn btn-primary">Send new password</button>
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
@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/timepicker/bootstrap-timepicker.min.css")}}">
@stop

@section('script')
    <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script src="{{asset("plugins/timepicker/bootstrap-timepicker.min.js")}}"></script>
    <script>
        $(function () {
            $(".timepicker").timepicker({
                showInputs: false
            });
        });
    </script>
@stop

@section('title')
    <h1>
        <a href="{{asset("companies")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        {{$company_name}} - Shifts
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-building-o"></i> Home</a></li>
        <li class="active"><a href="{{asset("companies")}}">Companies</a></li>
        <li class="active">{{$company_name}}</li>
        <li class="active">Shifts</li>
    </ol>
@stop

@section('body')
    <div class="box">
        <div class="box-body">
            @if(Session::has('message'))
                <div class="callout callout-success callout-sm">
                    <i class="fa fa-check"></i> {!! Session::get('message') !!}
                </div>
            @endif
            @if ($errors->any())
                <div class='callout callout-danger callout-sm' role='alert'>
                    <i class="fa fa-times"></i> You must enter only numeric data.
                </div>
            @endif

            <form action="{{asset('/companies/'.Request::segment(2).'/shifts')}}" method="post">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="put">
                <div class="row">
                    <div class="col-md-6">
                        <div class="bootstrap-timepicker">
                            <div class="form-group">
                                <label for="start">Day Shift Start</label>
                                <div class="input-group @if($errors->first("shift_day_start")) has-error @endif">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input type="text" name="shift_day_start" class="form-control timepicker" id="start" value="{{old('shift_day_start') ?? $shift_day_start}}">
                                </div>
                                {!! $errors->first('shift_day_start', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bootstrap-timepicker">
                            <div class="form-group">
                                <label for="end">Night Shift Start</label>
                                <div class="input-group @if($errors->first("shift_night_start")) has-error @endif">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input type="text" name="shift_night_start" class="form-control timepicker" id="end" value="{{old('shift_night_start') ?? $shift_night_start}}">
                                </div>
                                {!! $errors->first('shift_night_start', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                            </div>
                        </div>
                    </div>
                </div>
                <input type="submit" class="btn btn-block btn-primary btn-sm" value="Update">
            </form>

        </div>
    </div>

@stop
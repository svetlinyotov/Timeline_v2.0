@extends('layouts.master')

@section('style')

@stop

@section('script')
    <script>

    </script>
@stop

@section('title')
    <h2>
        @if(Request::segment(1) != 'profile')
            <a href="{{asset("/users/".$user->id)}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
            {{$user->info->names}}
            <small>Notifications</small>
        @else
            <a href="{{asset("profile")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
            Notifications
        @endif
    </h2>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-user"></i> Home</a></li>
        @if(Request::segment(1) != 'profile')
            <li><a href="{{asset("users")}}">Users</a></li>
            <li><a href="{{asset("users/".$user->id)}}">{{$user->info->names}}</a></li>
        @else
            <li><a href="{{asset("profile")}}">Profile</a></li>
        @endif
        <li>Notifications</li>
    </ol>
@stop

@section('body')
    @if(Session::has('message'))
        <div class="alert alert-success">
            <i class="fa fa-check"></i> {!! Session::get('message') !!}
        </div>
    @endif

    <div class="list-group">
        @foreach($notifications as $notification)
            <a href="{{$notification['link']}}?noti={{$notification['id']}}" class="list-group-item @if($notification['is_read'] == 0) list-group-item-warning @endif">
                @if($notification['is_read'] == 0) <i class="fa fa-circle"></i> @else <i class="fa fa-circle-o"></i> @endif
                {!! $notification['text'] !!}
                <small class="pull-right">{{$notification['date']}}</small>
            </a>
        @endforeach
    </div>

    {!! $notifications_obj->render() !!}

@stop
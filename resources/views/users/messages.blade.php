@extends('layouts.master')

@section('style')

@stop

@section('script')
    <script>

    </script>
@stop

@section('title')
    <h2>
        <a href="{{asset("profile")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Messages
    </h2>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-envelope"></i> Home</a></li>
        <li><a href="{{asset("profile")}}">Profile</a></li>
        <li>Messages</li>
    </ol>
@stop

@section('body')
<div class="row">
    <div class="mail-box">
        <div class="col-lg-12 animated fadeInRight">
            <a href="{{asset("/profile/messages/compose")}}" class="btn btn-primary">Compose</a><br><br>

            @if(Session::has('message') || isset($_GET['message']))
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button>
                    <i class="fa fa-check"></i> {!! Session::get('message') !!} {!! $_GET['message']??"" !!}
                </div>
            @endif

            @if(count($messages) == 0)
                <div class="alert alert-info">
                    No messages in your inbox
                </div>
            @endif

            <table class="table table-hover table-mail">
                <tbody>
                    @foreach($messages as $message)
                        <tr class="{{$message->is_read?"read":"unread"}}">
                            <td class="check-mail">
                                <img alt="Profile Image" class="img-circle" src="{{asset('avatar/'.$message->user->info->avatar)}}" height="40" />
                            </td>
                            <td class="mail-ontact"><a href="{{asset("/profile/messages/".$message->id)}}">{{$message->user->info->names}} ({{$message->user->email}})</a></td>
                            <td class="mail-subject"><a href="{{asset("/profile/messages/".$message->id)}}">{{$message->title}}</a></td>
                            <td class="">{{mb_substr(strip_tags($message->text), 0, 50)}}...</td>
                            <td class="text-right mail-date"><span  data-toggle="tooltip" data-placement="left" title="{{$message->created_at}}">{{\App\Common::timeAgo($message->created_at)}}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


        </div>
    </div>
</div>

@stop
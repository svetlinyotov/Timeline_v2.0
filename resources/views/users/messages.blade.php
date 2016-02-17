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
        @if(count($messages) == 0)
            <div class="alert alert-info">
                No messages in your inbox
            </div>
        @endif
        <div class="col-lg-12 animated fadeInRight">
            <table class="table table-hover table-mail">
                <tbody>
                    @foreach($messages as $message)
                        <tr class="{{$message->is_read?"read":"unread"}}">
                            <td class="check-mail">
                                <i class="fa {{$message->is_read?"fa-envelope":"fa-envelope-o"}}"></i>
                            </td>
                            <td class="mail-ontact"><a href="{{asset("/profile/messages/".$message->id)}}">{{$message->user->info->names}} ({{$message->user->email}})</a></td>
                            <td class="mail-subject"><a href="{{asset("/profile/messages/".$message->id)}}">{{$message->title}}</a></td>
                            <td class="">{{mb_substr($message->text, 0, 50)}}...</td>
                            <td class="text-right mail-date" data-toggle="tooltip" data-placement="left" title="{{$message->created_at}}">{{\App\Common::timeAgo($message->created_at)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


        </div>
    </div>
</div>

@stop
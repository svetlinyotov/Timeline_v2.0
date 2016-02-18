@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/summernote/summernote.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/summernote/summernote-bs3.css")}}">
@stop

@section('script')
    <script src="{{asset("js/plugins/summernote/summernote.min.js")}}"></script>
    <script>
        $(document).ready(function(){
            $('.summernote').summernote();

        });
    </script>
@stop

@section('title')
    <h2>
        <a href="{{((isset($_GET['rel']) && $_GET['rel'] == 'users')?asset("/users"):null)??asset("profile/messages")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Compose message
    </h2>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-mail-reply-all"></i> Home</a></li>
        <li><a href="{{asset("profile")}}">Profile</a></li>
        <li><a href="{{asset("profile/messages")}}">Messages</a></li>
        <li>Compose</li>
    </ol>
@stop

@section('body')

    <div class="row">


        <div class="col-lg-12 animated fadeInRight">
            @if ($errors->any())
                <div class="alert alert-danger">{!! implode('<br>', $errors->all(':message')) !!}</div>
            @endif
            @if(Session::has('message'))
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> {!! Session::get('message') !!}
                </div>
            @endif
            @if(Session::has('info'))
                <div class="alert alert-info">
                    <i class="fa fa-info"></i> {!! Session::get('info') !!}
                </div>
            @endif

            <form class="form-horizontal" method="post" action="{{asset('/profile/messages/compose')}}">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="put">

                <div class="mail-box">
                    <div class="mail-body">
                        <div class="form-group"><label class="col-sm-1 control-label">To:</label>

                            <div class="col-sm-11"><input type="text" class="form-control" name="to" value="{{old('to')??$_GET['to']??""}}" placeholder="Email"></div>
                        </div>
                        <div class="form-group"><label class="col-sm-1 control-label">Subject:</label>

                            <div class="col-sm-11"><input type="text" class="form-control" name="subject" value="{{old('subject')??(isset($_GET['title'])?"RE: ".$_GET['title']:null)??""}}"></div>
                        </div>
                    </div>

                    <div class="mail-text h-200">

                        <textarea class="summernote" title="" name="text">{!! old('text')??(isset($_GET['text'])?"<br><hr><b>Replied message:</b> <br>".$_GET['text']:null)??"" !!}</textarea>
                        <div class="clearfix"></div>
                    </div>
                    <div class="mail-body text-right tooltip-demo">
                        <button type="submit" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="Send"><i class="fa fa-reply"></i> Send</button>
                        <a href="{{asset("/profile/messages")}}" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Discard"><i class="fa fa-times"></i> Cancel</a>
                    </div>
                    <div class="clearfix"></div>


                </div>
            </form>
        </div>

    </div>

@stop
@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/sweetalert/sweetalert.css")}}">
@stop

@section('script')
    <script src="{{asset("js/plugins/sweetalert/sweetalert.min.js")}}"></script>
    <script>
        $('.btn-del-msg').click(function (e) {
            var href = $(this).data('href');
            var title = $(this).data('title');

            swal({
                title: "Delete message: "+title,
                text: "This action cannot be undone!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it",
                cancelButtonText: "No",
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
                closeOnCancel: false },
            function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: href,
                        type: 'DELETE',
                        data: {"_token":"{{csrf_token()}}"},
                        success: function(result) {
                            setTimeout(function () {
                                window.location = "{{asset('profile/messages/')}}?message=Message "+title+" is deleted";
                            }, 1400);
                            swal({
                                title:"Deleted!",
                                text:"The message is deleted",
                                type:"success",
                                timer:1500
                            });
                        },
                        error: function(result) {
                            console.log(result);
                            swal("Error", "Internal error. Please contact global administrator. Error "+result.status+": " + result.statusText, "error");
                        }
                    });
                } else {
                    swal({title:"Cancelled", text:"No data is affected", type:"info", timer:1500});
                }
            });
        });
    </script>
@stop

@section('title')
    <h2>
        <a href="{{((isset($_GET['rel']) && $_GET['rel'] == 'profile')?asset("/profile"):null)??asset("profile/messages")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        {{$message->title}}
    </h2>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-envelope"></i> Home</a></li>
        <li><a href="{{asset("profile")}}">Profile</a></li>
        <li><a href="{{asset("profile/messages")}}">Messages</a></li>
        <li>{{$message->title}}</li>
    </ol>
@stop

@section('body')

    <div class="row">

        <div class="col-lg-12 animated fadeInRight">
            <div class="mail-box-header">
                <div class="pull-right tooltip-demo">
                    <a href="{{asset("/profile/messages/compose?title=".$message->title."&to=".$message->user->email."&text=".$message->text)}}" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Reply"><i class="fa fa-reply"></i> Reply</a>
                    <button class="btn btn-white btn-sm btn-del-msg" data-toggle="tooltip" data-placement="top" title="Delete" data-title="{{$message->title}}" data-href="{{asset("/profile/messages/".$message->id)}}"><i class="fa fa-trash-o"></i> </button>
                </div>
                <div class="mail-tools tooltip-demo m-t-md">


                    <h3>
                        <span class="font-noraml">Subject: </span>{{$message->title}}
                    </h3>
                    <h5>
                        <span class="pull-right font-noraml">{{\App\Common::timeAgo($message->created_at)}} <small>{{$message->created_at}}</small></span>
                        <span class="font-noraml">From: </span>{{$message->user->info->names}} ({{$message->user->email}})
                    </h5>
                </div>
            </div>
            <div class="mail-box">


                <div class="mail-body">
                    <p>
                        {!! $message->text !!}
                    </p>
                </div>

                <div class="clearfix"></div>


            </div>
        </div>
    </div>

@stop
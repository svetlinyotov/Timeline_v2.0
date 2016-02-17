@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/dataTables/datatables.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/sweetalert/sweetalert.css")}}">
@stop

@section('script')
    <script src="{{asset("js/plugins/dataTables/datatables.min.js")}}"></script>
    <script src="{{asset("js/plugins/sweetalert/sweetalert.min.js")}}"></script>
    <script>
        $(function () {

            $('[data-toggle="tooltip"]').tooltip();

            $("#data").DataTable({
                dom: '<"html5buttons"B>lTfgitp',
                buttons: [
                    { extend: 'copy', title: 'Users List'},
                    {extend: 'csv', title: 'Users List'},
                    {extend: 'excel', title: 'Users List'},
                    {extend: 'pdf', title: 'Users List'},
                    {extend: 'print',
                        customize: function (win){
                            $(win.document.body).addClass('white-bg');
                            $(win.document.body).css('font-size', '10px');
                            $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                        }
                    }
                ]

            });

            @if(Auth::user()->role != "mod")
                $('.btn-delete-alert').click(function (e) {
                    var name = $(this).data('name');
                    var href = $(this).data('href');

                    swal({
                        title: "Are you sure you want to delete " + name + "?",
                        text: "You can add it later again but all of his/her data will be deleted!",
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
                                        window.location = "{{asset('users')}}?message=The user "+name+" is deleted successfully";
                                    }, 1400);
                                    swal({
                                        title:"Deleted!",
                                        text:"The user and his/her data is deleted",
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

                @if(Auth::user()->role != "mod")
                    $('.btn-unlink-company').click(function (e) {
                        var company = $(this).data('company');
                        var href = $(this).data('href');
                        var name = $(this).data('name');

                        swal({
                            title: "Are you sure you want to unlink " + name + " from " + company + "?",
                            text: "You can add it later again meanwhile the user will not be able to access the data from the specified company!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, unlink it",
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
                                                window.location = "{{asset('users')}}?message=User "+name+" is successfully unlinked from "+company+"";
                                            }, 1400);
                                            swal({
                                                title:"Unlinked!",
                                                text:"The user is unlinked",
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
                    $('.btn-unlink-all').click(function (e) {
                        var href = $(this).data('href');
                        var name = $(this).data('name');

                        swal({
                            title: "Are you sure you want to unlink all companies from " + name + "?",
                            text: "You can add it later again meanwhile the user will not be able to access the data from the specified companies!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, unlink",
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
                                            window.location = "{{asset('users')}}?message=All companies are successfully unlinked from "+name+"";
                                        }, 1400);
                                        swal({
                                            title:"Unlinked!",
                                            text:"The user is unlinked",
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
                @endif

            @endif

        });
    </script>
@stop

@section('title')
    <h1>
        Users
        <small></small>
        @if(Auth::user()->role == "supadmin")
            <a href="{{asset('/users/create')}}" class="btn btn-success btn-xs margin-right-50"><i class="fa fa-plus"></i> Add</a>
        @else
            <a class="btn btn-success btn-xs margin-right-50" data-toggle="modal" data-target="#link_user"><i class="fa fa-plus"></i> Add</a>
        @endif
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-users"></i> Home</a></li>
        <li class="active">Users</li>
    </ol>
@stop

@section('body')

    @if(Session::has('message') || isset($_GET['message']))
        <div class="alert alert-success alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button>
            <i class="fa fa-check"></i> {!! Session::get('message') !!} {!! $_GET['message']??"" !!}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-9">
            <table id="data" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th><span class="contact-type"><i class="fa fa-envelope"></i></span></th>
                    <th><span class="contact-type"><i class="fa fa-phone"></i></span></th>
                    <th>Type</th>
                    @if(Auth::user()->role == "supadmin") <th>Company</th> @endif
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $user)
                    <?php $i = 0; $len = count($user->company->toArray()); ?>
                    <tr>
                        <td class="client-avatar"><a href=""><img alt="image" src="{{asset('avatar/'.$user->info->avatar)}}"></a> </td>
                        <td><a data-toggle="tab" href="#contact-{{$user->id}}" class="client-link">{{$user->info->names}}</a></td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->info->mobile}}</td>
                        <td>{{$user->role}}</td>
                        @if(Auth::user()->role == "supadmin")
                        <td>
                            @if($user->role == "worker")
                                @foreach($user->company->toArray() as $company)
                                    <a href="#" data-company="{{$company['name']}}" data-name="{{$user->info->names}} ({{$user->email}})" data-href="{{asset('/users/'.$user->id.'/unlink/'.$company['id'])}}" data-toggle="tooltip" data-placement="bottom" title="Unlink this company" class="btn-unlink-company">
                                        {{$company['name']}}
                                    </a>{!! ++$i != $len ? " <hr style='margin:0'> " : "" !!}
                                @endforeach
                            @else
                                {{$user->company->pluck('name')->first()}}
                            @endif
                        </td>
                        @endif
                        <td class="client-status">
                            <div class="m-t-xs btn-group">
                                <a href="{{asset('users/'.$user->id)}}" class="btn btn-info btn-xs" data-toggle="tooltip" data-placement="bottom" title="View profile"><i class="fa fa-eye"></i> | <i class="fa fa-calendar"></i> </a>
                                @if($user->role == "worker" && Auth::user()->role == "supadmin")
                                    <a href="{{asset('users/'.$user->id.'/edit/link?rel=list')}}" class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="bottom" title="Link company"><i class="fa fa-link"></i></a>
                                @endif
                                <a href="{{asset('users/'.$user->id.'/edit?rel=list')}}" class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="bottom" title="Edit profile"><i class="fa fa-pencil"></i></a>
                                @if(Auth::user()->role != "mod")
                                    @if(Auth::user()->role == "supadmin" && $len > 0 && $user->role == "worker")
                                        <button type="button" data-href="{{asset('users/'.$user->id.'/unlink')}}"
                                                data-toggle="tooltip" data-placement="bottom" title="Unlink from all companies"
                                                data-name="{{$user->info->names}} ({{$user->email}})" class="btn btn-danger btn-xs btn-unlink-all">
                                            <i class="fa fa-unlink"></i></button>
                                    @endif
                                    @if(Auth::user()->role == "supadmin")
                                        <button type="button" data-href="{{asset('users/'.$user->id)}}"
                                                data-toggle="tooltip" data-placement="bottom" title="Delete user"
                                                data-name="{{$user->info->names}} ({{$user->email}})" class="btn btn-danger btn-xs btn-delete-alert">
                                            <i class="fa fa-trash"></i></button>
                                    @endif
                                    @if(Auth::user()->role == "admin" && $user->role == "worker")
                                        <a href="#" data-company="{{$company_name}}" data-name="{{$user->info->names}} ({{$user->email}})" data-href="{{asset('/users/'.$user->id.'/unlink/'.$company_id)}}" data-toggle="tooltip" data-placement="bottom" title="Unlink user" class="btn btn-danger btn-xs btn-unlink-company">
                                            <i class="fa fa-unlink"></i>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="tab-content">
                        <?php $i=0;?>
                        @foreach($data as $user)
                        <div id="contact-{{$user->id}}" class="tab-pane {{$i++==0?"active":""}}">
                            <div class="row m-b-lg">
                                <div class="col-lg-12 text-center">
                                    <h2>{{$user->info->names}}</h2>

                                    <div class="m-b-sm">
                                        <img alt="image" class="img-circle" src="{{asset('avatar/'.$user->info->avatar)}}"
                                             style="width: 62px">
                                    </div>

                                    <strong>

                                    </strong>

                                    <p>

                                    </p>
                                    <button type="button" class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#msg_user"><i
                                                class="fa fa-envelope"></i> Send Message
                                    </button>
                                </div>
                            </div>

                            <div class="client-detail">

                                    <strong>Details</strong>

                                    <ul class="list-group clear-list">

                                        <li class="list-group-item">
                                            <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>
                                            <span class="text-muted pull-right" style="width:60%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis" title="{{$user->info->address}}">{{$user->info->address}}</span>
                                        </li>

                                        @if($user->info->birth_date)
                                            <li class="list-group-item">
                                                <strong><i class="fa fa-calendar margin-r-5"></i> Birth date</strong>
                                                <span class="text-muted pull-right">{{$user->info->birth_date}}</span>
                                            </li>
                                        @endif

                                        <li class="list-group-item">
                                            <strong><i class="fa fa-mobile margin-r-5"></i> Mobile</strong>
                                            <span class="text-muted pull-right">{{$user->info->mobile}}</span>
                                        </li>


                                        @if($user->info->home_phone)
                                            <li class="list-group-item">
                                                <strong><i class="glyphicon glyphicon-phone-alt margin-r-5"></i> Home phone</strong>
                                                <span class="text-muted pull-right">{{$user->info->home_phone}}</span>
                                            </li>
                                        @endif

                                        @if($user->info->work_phone)
                                            <li class="list-group-item">
                                                <strong><i class="fa fa-phone margin-r-5"></i> Work phone</strong>
                                                <span class="text-muted pull-right">{{$user->info->work_phone}}</span>
                                            </li>
                                        @endif

                                        @if($user->info->fax)
                                            <li class="list-group-item">
                                                <strong><i class="fa fa-fax margin-r-5"></i> Fax</strong>
                                                <span class="text-muted pull-right">{{$user->info->fax}}</span>
                                            </li>
                                        @endif
                                    </ul>
                                    @if($user->info->other)
                                    <strong>Other</strong>
                                    <p>
                                        {{$user->info->other}}
                                    </p>
                                    @endif

                                </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-info fade" id="link_user" tabindex="-1" role="dialog" aria-labelledby="modal_link_user_label"
         aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content panel-info">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0"><i class="fa fa-plus"></i> Add user</h3>
                </div>
                <form action="{{asset('/companies/'.$company_id.'/link')}}" method="post" role="form">
                    {{csrf_field()}}
                    <input type="hidden" name="_method" value="put">
                    <div class="modal-body">
                        <p>Enter the email of the user that you wish to add. If he/she exists one will be linked to your company, otherwise you will be prompted to create new one.</p>
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif
                        <div class="form-group @if($errors->first('email')) has-error @endif">
                            <label class="control-label" for="email">Email: </label>
                            <input type="email" name="email" id="email" class="form-control" value="{{old("email")}}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info btn-loading">Next ></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal modal-info fade" id="msg_user" tabindex="-1" role="dialog" aria-labelledby="modal_msg_user_label"
         aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content panel-info">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0"><i class="fa fa-envelope"></i> Send message to <p id="msg_user"></p></h3>
                </div>
                <form action="{{asset('/messages')}}" method="post" role="form">
                    {{csrf_field()}}
                    <div class="modal-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif
                        <div class="form-group @if($errors->first('title')) has-error @endif">
                            <label class="control-label" for="title">Subject: </label>
                            <input type="text" name="title" id="title" class="form-control" value="{{old("title")}}">
                        </div>
                        <div class="form-group @if($errors->first('text')) has-error @endif">
                            <label class="control-label" for="text">Text: </label>
                            <textarea name="text" id="text" class="form-control">{{old("text")}}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info btn-loading">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(Auth::user()->role != "mod")
    <div class="modal modal-danger fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="modal_delete_label"
         aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content panel-danger">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0"><i class="fa fa-warning"></i> Warning</h3>
                </div>
                <div class="modal-body">
                    Are you sure that you want to delete the user <span class="text-bold" id="name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-outline btn-ok" href="#" data-method="delete" data-token="{{ csrf_token() }}">Delete</a>
                </div>
            </div>
        </div>
    </div>
    @endif

@stop
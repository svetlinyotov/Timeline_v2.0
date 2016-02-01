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

                @if(Auth::user()->role == "supadmin")
                    $('.btn-unlink-company').click(function (e) {
                        var company = $(this).data('company');
                        var href = $(this).data('href');
                        var name = $(this).data('name');

                        swal({
                            title: "Are you sure you want to unlink " + company + " from " + name + "?",
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
                                                window.location = "{{asset('users')}}?message=Company "+company+" is successfully unlinked from "+name+"";
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
    <div class="box">
        <div class="box-body">
            @if(Session::has('message') || isset($_GET['message']))
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button"><i class="fa fa-times"></i></button>
                    <i class="fa fa-check"></i> {!! Session::get('message') !!} {!! $_GET['message']??"" !!}
                </div>
            @endif

            <table id="data" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    @if(Auth::user()->role == "supadmin") <th>Company</th> @endif
                    <th>Type</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $user)
                    <tr>
                        <td>{{$user->info->names}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->info->mobile}}</td>
                        @if(Auth::user()->role == "supadmin")
                        <td>
                            <?php $i = 0; $len = count($user->company->toArray()); ?>
                            @if($user->role == "worker")
                                @foreach($user->company->toArray() as $company)
                                    <a href="#" data-company="{{$company['name']}}" data-name="{{$user->info->names}} ({{$user->email}})" data-href="{{asset('/users/'.$user->id.'/unlink/'.$company['id'])}}" data-toggle="tooltip" data-placement="bottom" title="Unlink this company" class="btn-unlink-company">
                                        {{$company['name']}}
                                    </a>{{++$i != $len ? " | " : ""}}
                                @endforeach
                            @else
                                {{$user->company->pluck('name')->first()}}
                            @endif
                        </td>
                        @endif
                        <td>{{$user->role}}</td>
                        <td>
                            <a href="{{asset('users/'.$user->id)}}" class="btn btn-info btn-xs" data-toggle="tooltip" data-placement="bottom" title="View profile"><i class="fa fa-eye"></i> | <i class="fa fa-calendar"></i> </a>
                            @if($user->role == "worker")
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
                                @if((Auth::user()->role == "admin" && $len == 1) || Auth::user()->role == "supadmin")
                                <button type="button" data-href="{{asset('users/'.$user->id)}}"
                                        data-toggle="tooltip" data-placement="bottom" title="Delete user"
                                        data-name="{{$user->info->names}} ({{$user->email}})" class="btn btn-danger btn-xs btn-delete-alert">
                                    <i class="fa fa-trash"></i></button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal modal-info fade" id="link_user" tabindex="-1" role="dialog" aria-labelledby="modal_link_user_label"
         aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content panel-info">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0"><i class="fa fa-warning"></i> Add user</h3>
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
@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/datatables/dataTables.bootstrap.css")}}">
@stop

@section('script')
    <script src="{{asset("plugins/datatables/jquery.dataTables.min.js")}}"></script>
    <script src="{{asset("plugins/datatables/dataTables.bootstrap.js")}}"></script>
    <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script>
        $(function () {

            $("#data").DataTable({
                columnDefs: [
                    {orderable: false, targets: -1}
                ],
                "paging": false,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": false,
                "autoWidth": true,
                //"order": [[ 0, "desc" ]]
            });

            @if(Auth::user()->role != "mod")
            $('#delete').on('show.bs.modal', function (e) {
                $(this).find('#name').text($(e.relatedTarget).data('name'));
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            @endif

        });
    </script>
@stop

@section('title')
    <h1>
        Users
        <small></small>
        <a href="{{asset('/users/create')}}" class="btn btn-success btn-xs margin-right-50"><i class="fa fa-plus"></i> Add</a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-users"></i> Home</a></li>
        <li class="active">Users</li>
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

            <table id="data" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Company</th>
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
                        <td>{{$user->company == null ?: $user->company->name}}</td>
                        <td>{{$user->role}}</td>
                        <td>
                            <a href="{{asset('users/'.$user->id)}}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> | <i class="fa fa-calendar"></i> </a>
                            <a href="{{asset('users/'.$user->id.'/edit?rel=list')}}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                            @if(Auth::user()->role != "mod")
                            <button type="button" data-href="{{asset('users/'.$user->id)}}"
                                    data-name="{{$user->info->names}} ({{$user->email}})" class="btn btn-danger btn-xs" data-toggle="modal"
                                    data-target="#delete"><i class="fa fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
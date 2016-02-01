@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/dataTables/datatables.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/sweetalert/sweetalert.css")}}">
    <link href="{{asset('css/plugins/iCheck/custom.css')}}" rel="stylesheet">
@stop

@section('script')
    <script src="{{asset("js/plugins/dataTables/datatables.min.js")}}"></script>
    <script src="{{asset("js/plugins/sweetalert/sweetalert.min.js")}}"></script>
    <script src="{{asset('js/plugins/iCheck/icheck.min.js')}}"></script>

    <script>
        $(document).ready(function(){
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
    </script>
@stop

@section('title')
    <h1>
        <a href="{!! (isset($_GET['rel']) && $_GET['rel'] == 'edit') ? asset('users/'.$user->id) : asset('users') !!}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Users - {{$user->info->names}}
        <small>Link company</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-link"></i> Home</a></li>
        <li><a href="{{asset("users")}}">Users</a></li>
        <li><a href="{{asset("users/".$user->id)}}">{{$user->info->names}}</a></li>
        <li class="active">Link Company</li>
    </ol>
@stop

@section('body')

    @if($user->role == "worker")
    <h2>Choose companies to link:</h2>
    <hr>

    <form action="{{asset('users/'.$user->id.'/edit/link')}}" method="post">
        {{csrf_field()}}
        <input type="hidden" name="_method" value="PUT">

        @foreach($companies as $company)
            <div class="checkbox icheck">
                <label>
                    <input type="checkbox" name="company[{{$company->id}}]"> {{$company->name}}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary col-md-3">Link</button>
    </form>
    @else
    <h2>Access Denied</h2>
        <a href="{{asset('users')}}" class="btn btn-primary">Back</a>
    @endif


@stop
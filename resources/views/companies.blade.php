@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/datatables/dataTables.bootstrap.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/select2/select2.min.css")}}">
@stop

@section('script')
    <script src="{{asset("plugins/datatables/jquery.dataTables.min.js")}}"></script>
    <script src="{{asset("plugins/datatables/dataTables.bootstrap.js")}}"></script>
    <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script src="{{asset("plugins/select2/select2.full.min.js")}}"></script>
    <script>
        $(function () {
            $(".timezone_input").select2();

            $("#data").DataTable({
                columnDefs: [
                    {orderable: false, targets: -1}
                ]
            });

            $('#edit').on('show.bs.modal', function (e) {
                var row_btn = $(e.relatedTarget);

                $(this).find('.name').val($(row_btn).closest('tr').find('td.name').html());
                $(this).find('.city').val($(row_btn).closest('tr').find('td.city').html());
                $(this).find('.post_code').val($(row_btn).closest('tr').find('td.post_code').html());
                $(this).find('.address').val($(row_btn).closest('tr').find('td.address').html());
                $(this).find('.timezone').val($(row_btn).closest('tr').find('td.timezone').html()).change();
                $(this).find('.currency option').filter(function () {
                    return $(this).text() == $(row_btn).closest('tr').find('td.currency').html()
                }).prop('selected', true);

            });

            $('#delete').on('show.bs.modal', function (e) {
                $(this).find('#name').text($(e.relatedTarget).data('name'));
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

        });
    </script>
@stop

@section('title')
    <h1>
        Companies
        <small></small>
        <button type="button" class="btn btn-success btn-xs margin-right-50" data-toggle="modal" data-target="#add"><i
                    class="fa fa-plus"></i> Add
        </button>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-building-o"></i> Home</a></li>
        <li class="active">Companies</li>
    </ol>
@stop

@section('body')
    <div class="box">
        <div class="box-body">

            <table id="data" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>City</th>
                    <th>Post Code</th>
                    <th>Address</th>
                    <th>TimeZone</th>
                    <th>Currency</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $company)
                    <tr>
                        <td class="name">{{$company->name}}</td>
                        <td class="city">{{$company->city}}</td>
                        <td class="post_code">{{$company->post_code}}</td>
                        <td class="address">{{$company->address}}</td>
                        <td class="timezone">{{$company->timezone}}</td>
                        <td class="currency">{{$company->currency->title}}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-xs btn-edit" data-toggle="modal"
                                    data-action="{{asset('companies/'.$company->id)}}" data-target="#edit"><i
                                        class="fa fa-pencil"></i></button>
                            <button type="button" data-href="{{asset('companies/'.$company->id)}}"
                                    data-name="{{$company->name}}" class="btn btn-danger btn-xs" data-toggle="modal"
                                    data-target="#delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal modal-danger fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="modal_delete_label"
         aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content panel-danger">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0"><i class="fa fa-warning"></i> Warning</h3>
                </div>
                <div class="modal-body">
                    Are you sure that you want to delete the company <span class="text-bold" id="name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-outline btn-ok" href="#" data-method="delete" data-token="{{ csrf_token() }}">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add" role="dialog" aria-labelledby="modal_add_label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel-success">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0">Add</h3>
                </div>
                <form action="" method="POST">
                    {{csrf_field()}}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name_input">Name</label>
                            <input type="text" class="form-control name" id="name_input" name="name"
                                   value="{{old('name')}}" placeholder="Name">
                        </div>
                        <div class="form-group">
                            <label for="city_input">City</label>
                            <input type="text" class="form-control city" id="city_input" name="city"
                                   value="{{old('city')}}" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label for="post_code_input">Post Code</label>
                            <input type="text" class="form-control post_code" id="post_code_input" name="post_code"
                                   value="{{old('post_code')}}" placeholder="Post Code">
                        </div>
                        <div class="form-group">
                            <label for="address_input">Address</label>
                            <input type="text" class="form-control address" id="address_input" name="address"
                                   value="{{old('address')}}" placeholder="Address">
                        </div>
                        <div class="form-group">
                            <label for="timezone_input">Timezone</label>
                            <select class="form-control timezone timezone_input" id="timezone_input" name="timezone"
                                    style="width: 100%;">
                                @foreach($timezones as $timezone)
                                    <option value="{{$timezone}}"
                                            @if($timezone == old('timezone')) selected @endif>{{$timezone}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="currency_input">Currency</label>
                            <select class="form-control currency" name="currency" id="currency_input">
                                @foreach($currency as $item)
                                    <option value="{{$item->id}}"
                                            @if($item->id == old('currency')) selected @endif>{{$item->title}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit" role="dialog" aria-labelledby="modal_edit_label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel-warning">
                <div class="modal-header panel-heading">
                    <h3 class="margin-0">Edit</h3>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    {{csrf_field()}}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name_input">Name</label>
                            <input type="text" class="form-control name" id="name_input" name="name_edit"
                                   value="{{old('name_edit')}}" placeholder="Name">
                        </div>
                        <div class="form-group">
                            <label for="city_input">City</label>
                            <input type="text" class="form-control city" id="city_input" name="city_edit"
                                   value="{{old('city_edit')}}" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label for="post_code_input">Post Code</label>
                            <input type="text" class="form-control post_code" id="post_code_input" name="post_code_edit"
                                   value="{{old('post_code_edit')}}" placeholder="Post Code">
                        </div>
                        <div class="form-group">
                            <label for="address_input">Address</label>
                            <input type="text" class="form-control address" id="address_input" name="address_edit"
                                   value="{{old('address_edit')}}" placeholder="Address">
                        </div>
                        <div class="form-group">
                            <label for="timezone_input">Timezone</label>
                            <select class="form-control timezone timezone_input" id="timezone_input"
                                    name="timezone_edit" style="width: 100%;">
                                @foreach($timezones as $timezone)
                                    <option value="{{$timezone}}"
                                            @if($timezone == old('timezone_edit')) selected @endif>{{$timezone}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="currency_input">Currency</label>
                            <select class="form-control currency" name="currency_edit" id="currency_input">
                                @foreach($currency as $item)
                                    <option value="{{$item->id}}"
                                            @if($item->id == old('currency_edit')) selected @endif>{{$item->title}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
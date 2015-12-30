@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/datatables/dataTables.bootstrap.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/daterangepicker/daterangepicker.css")}}">
@stop

@section('script')
    <script src="{{asset("plugins/datatables/jquery.dataTables.min.js")}}"></script>
    <script src="{{asset("plugins/datatables/dataTables.bootstrap.js")}}"></script>
    <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script>
        $(function () {

            $('.time_picker').daterangepicker({
                timePicker: true,
                timePickerIncrement: 10,
                minDate: new Date(new Date().setDate(new Date().getDate()-1)),
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                },
                autoApply: true
            });

            $("#data").DataTable({
                columnDefs: [
                    {orderable: false, targets: -1}
                ],
                "order": [[ 0, "desc" ]],
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false
            });

            $('#edit, #add').on('hidden.bs.modal', function (e) {
                history.pushState("", document.title, window.location.pathname);
                $(this).find('.has-error').removeClass('has-error');
                $(this).find('.text-danger').addClass('hidden');
                $(this).find('.callout').addClass('hidden');
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
        <a href="{{asset("companies")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        {{$company_name}}
        <small>Payment</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-building-o"></i> Home</a></li>
        <li class="active"><a href="{{asset("companies")}}">Companies</a></li>
        <li class="active">{{$company_name}}</li>
        <li class="active">Payment</li>
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
            @if ($errors->any())
                <div class='callout callout-danger callout-sm' role='alert'>
                    <i class="fa fa-times"></i> You must enter only numeric data.
                </div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th colspan="1">Day</th>
                    <th colspan="2">Day Shift</th>
                    <th colspan="2">Night Shift</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <th>Worker</th>
                    <th>Supervisor</th>
                    <th>Worker</th>
                    <th>Supervisor</th>
                </tr>
                </thead>
                <tbody>
                <form action="{{asset('/companies/'.Request::segment(2).'/payment')}}" method="post">
                    {{csrf_field()}}
                    <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; ?>
                    @for($day=0, $index=0; $day<7; $day++, $index+=4)
                        <tr>
                            <td>{{$days[$day]}}</td>
                            <td>
                                <div class="@if($errors->first("payment_day_supervisor.$day")) has-error @endif input-group">
                                    <input type="text" class="form-control" name="payment_day_worker[]" value="{{old("payment_day_worker.$day") ?? $week_data[$index]['amount'] ?? "0.00"}}">
                                    <span class="input-group-addon">{{$currency}}</span>
                                </div>
                            </td>
                            <td>
                                <div class="@if($errors->first("payment_day_supervisor.$day")) has-error @endif input-group">
                                    <input type="text" class="form-control" name="payment_day_supervisor[]" value="{{old("payment_day_supervisor.$day") ?? $week_data[$index+1]['amount'] ?? "0.00"}}">
                                    <span class="input-group-addon">{{$currency}}</span>
                                </div>
                            </td>
                            <td>
                                <div class="@if($errors->first("payment_day_supervisor.$day")) has-error @endif input-group">
                                    <input type="text" class="form-control" name="payment_night_worker[]" value="{{old("payment_night_worker.$day") ?? $week_data[$index+2]['amount'] ?? "0.00"}}">
                                    <span class="input-group-addon">{{$currency}}</span>
                                </div>
                            </td>
                            <td>
                                <div class="@if($errors->first("payment_day_supervisor.$day")) has-error @endif input-group">
                                    <input type="text" class="form-control" name="payment_night_supervisor[]" value="{{old("payment_night_supervisor.$day") ?? $week_data[$index+3]['amount'] ?? "0.00"}}">
                                    <span class="input-group-addon">{{$currency}}</span>
                                </div>
                            </td>
                        </tr>
                    @endfor
                    <tr>
                        <td colspan="5"><input type="submit" class="btn btn-primary btn-block" value="Update"></td>
                    </tr>
                </form>
                </tbody>
            </table>

        </div>
    </div>

    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title">Custom Payment</h3>
            <button type="button" class="btn btn-success btn-xs margin-left-20" data-toggle="modal" data-target="#add"><i class="fa fa-plus"></i> Add</button>
        </div>
        <div class="box-body">
            @if(Session::has('custom_message'))
                <div class="callout callout-success callout-sm">
                    <i class="fa fa-check"></i> {!! Session::get('custom_message') !!}
                </div>
            @endif

            <table id="data" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="hidden"></th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($custom_data as $item)
                        <?php
                            $time_start = \App\Common::formatDateTimeFromSQL($item->time_start);
                            $time_end = \App\Common::formatDateTimeFromSQL($item->time_end);
                        ?>
                        <tr>
                            <td class="hidden">{{$item->id}}</td>
                            <td>{{$time_start}}</td>
                            <td>{{$time_end}}</td>
                            <td>{{$item->amount}} {{$currency}}</td>
                            <td>{{$item->description}}</td>
                            <td>
                                <button type="button" data-href="{{asset('companies/'.$item->company_id.'/payment/custom/'.$item->id)}}"
                                        data-name="{{$time_start}} - {{$time_end}} | {{$item->amount}}" class="btn btn-danger btn-xs" data-toggle="modal"
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
                    Are you sure that you want to delete the payment period <br> <span class="text-bold" id="name"></span>
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
                <form action="{{asset('/companies/'.Request::segment(2).'/payment/custom')}}#add" method="POST">
                    {{csrf_field()}}
                    <div class="modal-body">
                        @if ($errors->custom->any())
                            <div class='callout callout-danger callout-sm' role='alert'>
                                <i class="fa fa-times"></i> You should fix the errors below.
                            </div>
                        @endif

                        <div class="form-group @if($errors->custom->first('time_range')) has-error @endif">
                            <label for="time_range_input">Date and time range</label>
                            <input type="text" class="form-control time_picker" id="time_range_input" name="time_range" value="{{old('time_range')}}"
                                   placeholder="Range">
                            {!! $errors->custom->first('time_range', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                        <div class="@if($errors->custom->first("amount")) has-error @endif form-group">
                            <label for="amount_input">Amount</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="amount_input" name="amount" value="{{old("amount") ?? "0.00"}}">
                                <span class="input-group-addon">{{$currency}}</span>
                            </div>
                            {!! $errors->custom->first('amount', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                        <div class="form-group @if($errors->custom->first('description')) has-error @endif">
                            <label for="description_input">Description</label>
                            <input type="text" class="form-control" id="description_input" name="description" value="{{old('description')}}"
                                   placeholder="Description">
                            {!! $errors->custom->first('description', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
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
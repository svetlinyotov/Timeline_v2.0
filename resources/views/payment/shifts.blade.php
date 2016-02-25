@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("/css/plugins/dataTables/datatables.min.css")}}">
    <link rel="stylesheet" href="{{asset("/css/plugins/select2/select2.min.css")}}">
    <link rel="stylesheet" href="{{asset("/css/plugins/daterangepicker/daterangepicker-bs3.css")}}">
@stop

@section('script')
    <script src="{{asset("/js/plugins/dataTables/datatables.min.js")}}"></script>
    <script src="{{asset("/js/plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script src="{{asset("/js/plugins/select2/select2.full.min.js")}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("/js/plugins/daterangepicker/daterangepicker.js")}}"></script>
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

            $('.time-picker').daterangepicker({
                timePicker: true,
                timePickerIncrement: 5,
                singleDatePicker: true,
                autoApply: true,
                maxDate: new Date(new Date().setDate(new Date().getDate()+1)),
                "buttonClasses": "hidden",
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                },
            }).each(function(i, obj) {
                if($(obj).val() == "Invalid date") {
                    $(obj).val("");
                }
            });;

        });
    </script>
@stop

@section('title')
    <h1>
        <a href="{{asset("payments?company_id=".$user_company_id)}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Shifts
        <small>{{$user_email}}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-money"></i> Home</a></li>
        <li class="active"><a href="{{asset("payments?company_id".$user_company_id)}}">Payments</a></li>
        <li class="active"><a href="{{asset('users/'.$user_id.'?rel=payment')}}">{{$user_email}}</a></li>
        <li class="active">Shifts</li>
    </ol>
@stop

@section('body')
    <div class="box">
        <div class="box-body">
            @if(Session::has('message'))
                <div class="alert alert-success">
                    <i class="fa fa-check"></i> {!! Session::get('message') !!}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    Some date time is not in valid format.
                </div>
            @endif

            <form action="{{asset("payments/user/$user_id/shifts")}}" method="post">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="put">
                <table id="data" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Start</th>
                        <th>End</th>
                        <th>Real Start</th>
                        <th>Real End</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($data) > 0)
                        @foreach($data as $row)
                            <?php
                                $date_start = old("real_start.".$row['id']) ?? \App\Common::formatDateTimeFromSQLSingle($row['real_start']) ?? \App\Common::formatDateTimeFromSQLSingle($row['start']) ?? null;
                                $date_end = old("real_end.".$row['id']) ?? \App\Common::formatDateTimeFromSQLSingle($row['real_end']) ?? \App\Common::formatDateTimeFromSQLSingle($row['end']) ?? null;
                            ?>
                            <tr>
                                <td>{{$row['start']}}</td>
                                <td>{{$row['end']}}</td>
                                <td class="@if($errors->first("real_start.".$row['id'])) has-error @endif">
                                    <input type="text" name="real_start[{{$row['id']}}]"
                                           class="form-control time-picker"
                                           onfocus='$(this).data("daterangepicker").setStartDate("{{$date_start}}"); $(this).data("daterangepicker").setEndDate("{{$date_start}}"); '
                                           value="{{old("real_start.".$row['id']) ?? \App\Common::formatDateTimeFromSQLSingle($row['real_start']) ?? "No"}}"
                                    />
                                </td>
                                <td class="@if($errors->first("real_end.".$row['id'])) has-error @endif">
                                    <input type="text" name="real_end[{{$row['id']}}]"
                                           class="form-control time-picker"
                                           onfocus='$(this).data("daterangepicker").setStartDate("{{$date_end}}"); $(this).data("daterangepicker").setEndDate("{{$date_end}}"); '
                                           value="{{old("real_end.".$row['id']) ?? \App\Common::formatDateTimeFromSQLSingle($row['real_end']) ?? "No"}}"
                                    />
                                </td>
                                <td>{{$row['address']}}</td>
                                <td class="text-right">{{$row['amount']}}</td>
                                <td>{{$currency}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <input type="submit" class="btn btn-primary btn-block" value="Update">
            </form>
        </div>
    </div>

@stop
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
                ],
                "paging": false,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": false,
                "autoWidth": true,
                //"order": [[ 0, "desc" ]]
            });

            $('#edit').on('show.bs.modal', function (e) {
                var row_btn = $(e.relatedTarget);
                if(typeof $(row_btn).data('id') != "undefined") {
                    $(this).find('form').attr('action', $(row_btn).data('action'));
                    $(this).find('.name').val($(row_btn).closest('tr').find('td.name').html());
                    $(this).find('.city').val($(row_btn).closest('tr').find('td.city').html());
                    $(this).find('.post_code').val($(row_btn).closest('tr').find('td.post_code').html());
                    $(this).find('.address').val($(row_btn).closest('tr').find('td.address').html());
                    $(this).find('.timezone').val($(row_btn).closest('tr').find('td.timezone').html()).change();
                    $(this).find('.currency option').filter(function () {
                        return $(this).text() == $(row_btn).closest('tr').find('td.currency').html()
                    }).prop('selected', true);
                }

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
        Payments
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-money"></i> Home</a></li>
        <li class="active">Payments</li>
    </ol>
@stop

@section('body')
    @if(Auth::user()->role == "supadmin")
        Company: <select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            <option></option>
            @foreach($companies as $company)
                <option value="{{asset('/payments')}}?company_id={{$company->id}}" @if(isset($_GET['company_id']) && $_GET['company_id'] == $company->id) selected @endif>{{$company->name}}</option>
            @endforeach
        </select>
        <hr>
    @endif
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
                    <th>E-mail</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Salary</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @if(count($data) > 0)
                @foreach($data as $user)
                    <tr>

                        <td>{{$user['email']}}</td>
                        <td>{{$user['names']}}</td>
                        <td>{{$user['mobile']}}</td>
                        <td class="text-right">{{$user['salary']}}</td>
                        <td>{{$currency}}</td>
                        <td>
                            <a href="{{asset('payments/user/'.$user['id'].'/shifts')}}" class="btn btn-info btn-xs"><i class="fa fa-clock-o"></i> </a>
                            <a href="{{asset('users/'.$user['id'].'?rel=payment')}}" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> </a>
                        </td>
                    </tr>
                @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>

@stop
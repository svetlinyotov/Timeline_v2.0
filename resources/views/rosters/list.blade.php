@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/fullcalendar.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/scheduler.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/fullcalendar/fullcalendar.print.css")}}" media="print">
    <link rel="stylesheet" href="{{asset("css/plugins/iCheck/custom.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/toastr/toastr.min.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/daterangepicker/daterangepicker-bs3.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/chosen/chosen.css")}}">
    <style>
        .color-gray {
            border-color: #c0c0c0 !important;
            border-width: 4px;
            border-radius: 6px;
            color: #000;
        }
        .color-green {
            border-color: #378006 !important;
            border-width: 4px;
            border-radius: 6px;
            color: #FFF;
        }
        .color-red {
            border-color: #b60003 !important;
            border-width: 4px;
            border-radius: 6px;
            color: #FFF;
        }
        .color-white {
            border-color: #0007a5 !important;
            border-width: 4px;
            border-radius: 6px;
            color: #000;
            opacity: .3;
        }
        .modal {
            overflow: auto;
        }
    </style>
@stop

@section('script')
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("js/plugins/fullcalendar/fullcalendar.js")}}"></script>
    <script src="{{asset("js/plugins/fullcalendar/scheduler.min.js")}}"></script>
    @if(Auth::user()->role != "worker")
        <script src="{{asset("js/mapAddUser.js")}}"></script>
    @endif
    <script src="{{asset("js/mapRosters.js")}}"></script>
    <script src="{{asset("js/plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script src="{{asset("js/plugins/iCheck/icheck.min.js")}}"></script>
    <script src="{{asset("js/plugins/toastr/toastr.min.js")}}"></script>
    <script src="{{asset("js/plugins/chosen/chosen.jquery.js")}}"></script>

    <script>
        $(function () {
            Array.prototype.getColumn = function(name) {
                return this.map(function(el) {
                    // gets corresponding 'column'
                    if (el.hasOwnProperty(name)) return el[name];
                    // removes undefined values
                }).filter(function(el) { return typeof el != 'undefined'; });
            };


            $('input[type="checkbox"]').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $('.chosen-add-user').chosen();

            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            $('.time_picker').daterangepicker({
                timePicker: true,
                timePickerIncrement: 5,
                minDate: new Date(new Date().setDate(new Date().getDate()-1)),
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                }
            });

            var update_function = function(event, delta, revertFunc) {
                $.ajax({
                    url: '{{asset('/rosters/events')}}/'+event.id,
                    method: "POST",
                    data: {new_time_start:event.start.format(), new_time_end:event.end.format(), user_id:event.resourceId, "_token":'{{csrf_token()}}'},
                    success: function () {
                        refreshCalendar();
                        toastr["success"]("Event "+event.title+" updated.","New times:<br>"+event.start.format()+" <br> "+event.end.format());
                    },
                    error: function (msg) {
                        revertFunc();

                        toastr["error"]("Error("+msg.status+"): " + msg.responseJSON['range']);
                    }
                });
            }

            $('#calendar').fullCalendar({
                @if(Auth::user()->role != "worker")
                    editable: true,
                @endif
                height: $(window).height()-300,
                nowIndicator: true,
                scrollTime: '00:00',
                resourceAreaWidth: 220,
                firstDay: 1,
                businessHours: {
                    start: '{{\App\Common::formatTimeFromSQL24($shift_start)}}',
                    end: '{{\App\Common::formatTimeFromSQL24($shift_end)}}',
                    dow: [ 1, 2, 3, 4, 5, 6, 7]
                },

                //selectable: true,
                //selectHelper: true,

                header: {
                    left: 'today prev,next',
                    center: 'title',
                    @if(Auth::user()->role != "worker")
                        right: 'timelineDay,timelineThreeDays,agendaWeek,month'
                    @else
                        right: 'agendaDay,agendaWeek,month'
                    @endif
                },
                defaultView: '{{Auth::user()->role != "worker" ? 'timelineDay' : 'agendaWeek'}}',

                eventOverlap: false,
                resourceLabelText: 'Workers',
                @if(Auth::user()->role != "worker")
                    resources: {
                        url: '{{asset('/rosters/workers/'.$company_id)}}',
                        error: function(msg) {
                            toastr["error"]("Error("+msg.status+"): " + msg.statusText);
                        }
                    },
                @endif
                events: {
                    url: '{{asset('/rosters/events/'.$company_id)}}',
                    error: function(msg) {
                        toastr["error"]("Error("+msg.status+"): " + msg.statusText);
                    }
                },
                @if(Auth::user()->role != "worker")
                eventDrop:update_function,
                eventResize:update_function,
                @endif
                eventClick:  function(event, jsEvent, view) {

                    $.get("{{asset('rosters/event')}}/"+event.id, { },
                        function (data, status) {

                            if(status == "success") {
                                var modal = $('#edit');

                                $('#edit_error').hide();
                                modal.find('.select-status').hide();
                                modal.find('.time-range-text').hide();
                                modal.find('.name-text').hide();
                                modal.find('.other-text').hide();
                                modal.find('.status-text').hide();
                                modal.find("div#users").empty();
                                modal.find('#update_roster').show();

                                @if(Auth::user()->role == "worker")
                                    modal.find('.time-range-edit').hide();
                                    modal.find('.name-edit').hide();
                                    modal.find('.other-edit').hide();
                                    modal.find('.time-range-text').show();
                                    modal.find('.name-text').show();
                                    modal.find('.other-text').show();
                                    modal.find('.supervisor-text').show();
                                    modal.find('.address-text').hide();

                                    if(data.users[0].pivot.status != '' && data.users[0].pivot.status != 'pending'){
                                        modal.find('.status-select-group').hide();
                                        modal.find('.status-text').show();
                                        modal.find('#update_roster').hide();
                                        modal.find('#status-text').html(event.status);
                                    }
                                @endif

                                modal.find('.modal-title').text(event.title);
                                modal.find(".user_id_input").val(data.users.getColumn('id').join());
                                modal.find("#time_range_edit").val(moment(event.start).format('M/D/YYYY h:mm A') + ' - ' + moment(event.end).format('M/D/YYYY h:mm A'));
                                modal.find("#time_range_edit_text").html(moment(event.start).format('M/D/YYYY h:mm A') + ' - ' + moment(event.end).format('M/D/YYYY h:mm A'));
                                modal.find("#name_edit").val(event.title);
                                modal.find("#name_edit_text").html(event.title);
                                modal.find("#address_search, #address_edit").val(data.address);
                                modal.find("#address_edit_text").html(data.address);
                                modal.find("#coordinates_edit").val(data.coordinates);
                                modal.find("#coordinates_edit_text").html(data.coordinates);
                                modal.find("#other_edit").val(data.other);

                                modal.find("#other_edit_text").html(data.other);
                                modal.find("#supervisor_edit").iCheck(Boolean(event.supervisor)?'check':'uncheck');
                                modal.find("#supervisor-text-check").iCheck(Boolean(event.supervisor)?'check':'uncheck');

                                modal.find('input[name=_action]').val('{{asset('/rosters')}}/'+event.id+'#edit');


                                var now = new Date();
                                var selectedDate = new Date(moment(event.start).format('M/D/YYYY h:mm A'));
                                if (selectedDate > now) {
                                    modal.find('.status-select').show();
                                }

                                codeAddressNew();

                                @if(Auth::user()->role != "worker")
                                $("div#users").append(
                                        "<a type='button' class='btn btn-default btn-sm' data-toggle='modal' data-target='#add_user_to_roster' data-id='"+event.id+"'><i class='fa fa-plus'></i></a>"
                                );
                                @endif

                                $.each(data.users, function (i, user) {

                                    @if(Auth::user()->role != "worker")
                                        $("div#users").append(
                                                "<h2>"+ user.email +"</h2>" +
                                                '<label>Supervisor: <input type="checkbox" id="supervisor_edit_'+i+'" name="supervisor['+user.id+']" value="1"></label>' +
                                                '<div class="form-group status-select-group">' +
                                                '<label>Status *</label>' +
                                                    '<div class="input-group">' +
                                                        '<select class="form-control" id="status_select_input_'+i+'" name="status['+user.id+']">' +
                                                            '<option value="pending">pending</option>' +
                                                            '<option value="accepted">accept</option>' +
                                                            '<option value="declined">decline</option>' +
                                                            '<option value="canceled">cancel</option>' +
                                                        '</select>' +
                                                    '</div>' +
                                                '</div>'
                                        );
                                    @else
                                        $("div#users").append(
                                                "<h2>"+ user.email +"</h2>" +
                                                '<label>Supervisor: <input type="checkbox" id="supervisor_edit_'+i+'" disabled value="1"></label>' +
                                                '<div class="form-group status-select-group">' +
                                                '<label>Status *</label>' +
                                                    '<div class="input-group">' +
                                                        '<select class="form-control" id="status_select_input_'+i+'" name="status['+user.id+']">' +
                                                            '<option value=""></option>' +
                                                            '<option value="accepted">accept</option>' +
                                                            '<option value="declined">decline</option>' +
                                                        '</select>' +
                                                    '</div>' +
                                                '</div>'
                                        );
                                    @endif

                                    modal.find("#supervisor_edit_"+i).iCheck(Boolean(user.pivot.is_supervisor)?'check':'uncheck');
                                    modal.find("#status_select_input_"+i).val(user.pivot.status);

                                    if(user.pivot.status == "accepted" || user.pivot.status == "declined"){
                                        modal.find("#status_select_input_"+i).prop('disabled', 'disabled');
                                    }

                                });

                                modal.modal();
                            }
                        }
                    );

                },
                select: function(start, end, allDay) {

                    //TODO implement this logyc for new roster
                    /*
                    $.post("{{asset('availability')}}", {
                                "start": moment(start).format('DD-MM-YYYY HH:mm:ss'),
                                "end": moment(end).format('DD-MM-YYYY HH:mm:ss'),
                                "allDay":!start.hasTime() && !end.hasTime(),
                                "_token": "{{csrf_token()}}"
                            },
                            function (data, status) {
                                console.log(data);
                                console.log(status);
                                if(status == "success") {
                                    refreshCalendar();
                                    unselectCalendar();
                                    toastr["success"](data.msg);
                                }
                            }
                    );
                    */
                }
            });

            function refreshCalendar() {
                $("#calendar").fullCalendar('refetchEvents');
            }
            function unselectCalendar() {
                $("#calendar").fullCalendar('unselect');
            }

            $('#add_user_to_roster').on('show.bs.modal', function (event) {
                var zIndex = 2080 + (10 * $('.modal:visible').length);
                $(this).css('cssText', 'z-index: '+zIndex+' !important');

                setTimeout(function() {
                    $('.modal-backdrop').attr('style', 'z-index: '+ (zIndex - 1) +' !important').addClass('modal-stack');
                }, 100);

                $(".chosen-add-user").find('option').remove().end().trigger("chosen:updated");

                var button = $(event.relatedTarget);
                var id = button.data('id');
                var modal = $(this);

                modal.find(".roster_id").val(id);

                $.get("{{asset('rosters/event')}}/"+id+"/unlinkedUsers", {  },
                        function (data, status) {

                            $.each(data, function (i, user) {
                                $(".chosen-add-user").append(
                                        $('<option></option>').val(user.id).html(user.names + "(" + user.email + ")")
                                );
                            });
                            $(".chosen-add-user").trigger("chosen:updated");
                        }
                );

            }).on('hide.bs.modal', function (event) {
                setTimeout(function() {
                    $('.modal-backdrop').attr('style', 'z-index: 2040 !important').addClass('modal-stack');
                }, 100);
            });

            $('#add').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var name = button.data('name');

                var modal = $(this);
                modal.find("#address_type").val("");
                modal.find("#address").html("");
                modal.find("#coordinates_type").val("");
                modal.find("#coordinates").html("");
                modal.find('.modal-title').text('New roster - ' + name);
                modal.find('input[name=_action]').val('{{asset('/users')}}/'+id+'/roster?company_id={{$_GET['company_id']??''}}');
            }).on('hidden.bs.modal', function (e) {
                //history.pushState("", document.title, window.location.pathname);
                $('#add_error').hide();
                document.getElementById("add_roster").reset();
            });

            $('#save_roster').click(function (e) {
                $.ajax({
                    url: $('#add').find('input[name=_action]').val(),
                    method: "POST",
                    data: $('#add').find('form').serialize(),
                    dataType: "json",
                    complete : function (msg) {
                        if (msg.status != 200) {
                            var error = [];
                            $.each(msg.responseJSON, function (idx2, val2) {
                                error.push(val2);
                            });

                            $('#add_error').html(error.join("<br>")).show();
                        }else{
                            $('#add').modal('hide');
                            $("#calendar").fullCalendar('refetchEvents');
                            toastr["success"]("Event saved");
                        }
                    }
                });
                $("html, body").animate({ scrollTop: 0 }, 200);
                $("#add").animate({ scrollTop: 0 }, 800);

            });
            $('#update_roster').click(function (e) {
                $.ajax({
                    url: $('#edit').find('input[name=_action]').val(),
                    method: "POST",
                    data: $('#edit').find('form').serialize(),
                    dataType: "json",
                    complete : function (msg) {
                        if (msg.status != 200) {
                            var error = [];
                            $.each(msg.responseJSON, function (idx2, val2) {
                                error.push(val2);
                            });

                            $('#edit_error').html(error.join("<br>")).show();
                        }else{
                            $('#edit').modal('hide');
                            $("#calendar").fullCalendar('refetchEvents');
                            toastr["success"]("Event updated");
                        }
                    }
                });
                $("html, body").animate({ scrollTop: 0 }, 200);
                $("#edit").animate({ scrollTop: 0 }, 800);

            });

            $("#add_new_users_roster").click(function (e) {
                $.ajax({
                    url: "{{asset('/rosters/event')}}/"+$(".roster_id").val()+"/users",
                    method: "POST",
                    data: $('#add_new_users_roster_form').serialize(),
                    dataType: "json",
                    complete : function (msg) {
                        if (msg.status != 200) {
                            var error = [];
                            $.each(msg.responseJSON, function (idx2, val2) {
                                error.push(val2);
                            });

                            $('#edit_error').html(error.join("<br>")).show();
                        }else{
                            $('#add_user_to_roster').modal('hide');
                            $("div#users").empty();
                            $('#edit').find('.user_id_input').val(msg.responseJSON.getColumn('id').join());

                            $.each(msg.responseJSON, function (i, user) {

                                $("div#users").append(
                                        "<h2>"+ user.email +"</h2>" +
                                        '<label>Supervisor: <input type="checkbox" id="supervisor_edit_'+i+'" name="supervisor['+user.id+']" value="1"></label>' +
                                        '<div class="form-group status-select-group">' +
                                        '<label>Status *</label>' +
                                        '<div class="input-group">' +
                                        '<select class="form-control" id="status_select_input_'+i+'" name="status['+user.id+']">' +
                                        '<option value="pending">pending</option>' +
                                        '<option value="accepted">accept</option>' +
                                        '<option value="declined">decline</option>' +
                                        '@if(Auth::user()->role != "worker") <option value="canceled">cancel</option> @endif' +
                                        '</select>' +
                                        '</div>' +
                                        '</div>'
                                );
                                $('#edit').find("#supervisor_edit_"+i).iCheck(Boolean(user.pivot.is_supervisor)?'check':'uncheck');
                                $('#edit').find("#status_select_input_"+i).val(user.pivot.status);

                            });

                        }
                    }
                });
                $("html, body").animate({ scrollTop: 0 }, 200);
                $("#add_new_users_roster").animate({ scrollTop: 0 }, 800);
            })

            $(window).on('resize', function(){
                $('#calendar').fullCalendar('option', 'height', $(window).height()-200);
            });
        });

        window.onload = function() {
            var timer;
            @if(Auth::user()->role != "worker")
            codeAddress();
            document.getElementById("address_input").onkeyup=function(){
                timer = setTimeout("codeAddress()", 2000);
            };
            document.getElementById("address_input").onkeydown=function(){
                clearTimeout(timer);
            };
            @endif

            codeAddressNew();
            document.getElementById("address_search").onkeyup=function(){
                timer = setTimeout("codeAddressNew()", 2000);
            };
            document.getElementById("address_search").onkeydown=function(){
                clearTimeout(timer);
            };
        }
    </script>
@stop

@section('title')
    <h1>
        Rosters
        <small></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("/")}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Rosters</li>
    </ol>
@stop

@section('body')
    @if(Auth::user()->role == "supadmin")
        <div class="row">
            <div class="col-md-6">
        <div class="input-group">
            <label class="input-group-addon">Company: </label>
            <select class="selectpicker form-control" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                <option></option>
                @foreach($companies as $company)
                    <option value="{{asset('/rosters')}}?company_id={{$company->id}}" @if(isset($_GET['company_id']) && $_GET['company_id'] == $company->id) selected @endif>{{$company->name}}</option>
                @endforeach
            </select>
        </div>
                </div>
            </div>
        <br>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body no-padding">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role != "worker")
    <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="add">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">New Roster</h4>
                </div>
                <form action="" method="post" id="add_roster">
                    {{csrf_field()}}
                    <input type="hidden" name="_action" value="">
                    <input type="hidden" name="user_id" class="user_id_input" value="">
                    <div class="modal-body">

                        <div id="add_error" class="alert alert-danger" style="display: none"></div>

                        <label>Supervisor <input type="checkbox" name="is_supervisor" value="1"></label>
                        <div class="form-group @if($errors->custom->first('time_range')) has-error @endif">
                            <label for="time_range_input">Time range</label>
                            <input type="text" class="form-control time_picker" id="time_range_input" name="time_range" value="{{old('time_range')}}"
                                   placeholder="Range">
                            {!! $errors->custom->first('time_range', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                        <div class="form-group">
                            <label>Name *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-pencil"></i> </div>
                                <input type="text" name="name" class="form-control" id="name_input" value="{{old('name')}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i> </div>
                                <input type="text" name="address" class="form-control" id="address_input" value="{{old('address')}}">
                            </div>
                        </div>
                        <div class="col-md-12" style="height: 300px; margin-bottom: 10px;">
                            <div id="map-address" style="width: 100%; height: 100%;"></div>
                        </div>
                        <div class="input-group @if($errors->first("coordinates")) has-error @endif">
                            <div class="input-group-addon">&nbsp;<i class="fa fa-map-marker"></i>&nbsp;</div>
                            <div id="coordinates" class="form-control">{{old('coordinates')}}</div><input type="hidden" name="coordinates" id="coordinates_type" value="{{old('coordinates')}}" />
                        </div>
                        <div class="input-group @if($errors->first("address")) has-error @endif">
                            <div class="input-group-addon"><i class="fa fa-map"></i> </div>
                            <div id="address" class="form-control">{{old('address')}}</div><input type="hidden" name="address" id="address_type" value="{{old('address')}}" />
                        </div>
                        {!! $errors->first('coordinates', "<span class='text-danger'><i class='fa fa-times-circle-o'></i> Use the map pin o r the search bar above it to select address and coordinates</span>") !!}
                        <br>
                        <div class="form-group">
                            <label>Other</label>
                            <textarea name="other" class="form-control" rows="4">{{old('other')}}</textarea>
                            {!! $errors->first('other', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="save_roster">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="edit">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit Roster</h4>
                </div>
                <form action="" method="post" id="add_roster">
                    {{csrf_field()}}
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="_action" value="">
                    <input type="hidden" name="user_id" class="user_id_input" value="">
                    <div class="modal-body">

                        <div id="edit_error" class="alert alert-danger" style="display: none"></div>
                        @if(Auth::user()->role != "worker")
                        <div class="form-group time-range-edit">
                            <label for="time_range_edit_input">Time range</label>
                            <input type="text" class="form-control time_picker" name="time_range" id="time_range_edit" placeholder="Range">
                        </div>
                        <div class="form-group name-edit">
                            <label>Name *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-pencil"></i> </div>
                                <input type="text" name="name" class="form-control" id="name_edit">
                            </div>
                        </div>
                        @endif
                        <div class="form-group time-range-text">
                            <label>Time range: </label>
                            <span id="time_range_edit_text"></span>
                        </div>
                        <div class="form-group name-text disabled">
                            <label>Name: </label>
                                <span id="name_edit_text"></span>
                        </div>

                        <div class="form-group status-text">
                            <label>Status: </label>
                            <span id="status-text"></span>
                        </div>
                        <div class="form-group address-text">
                            <label>Address *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i> </div>
                                <input type="text" name="address_search" class="form-control" id="address_search">
                            </div>
                        </div>
                        <div class="col-md-12" style="height: 200px; margin-bottom: 10px;">
                            <div id="map-address-new" style="width: 100%; height: 100%;"></div>
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon">&nbsp;<i class="fa fa-map-marker"></i>&nbsp;</div>
                            <div id="coordinates_edit_text" class="form-control"></div><input type="hidden" name="coordinates" id="coordinates_edit"/>
                        </div>
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-map"></i> </div>
                            <div id="address_edit_text" class="form-control"></div><input type="hidden" name="address" id="address_edit" />
                        </div>
                        <br>
                        @if(Auth::user()->role != "worker")
                        <div class="form-group other-edit">
                            <label>Other</label>
                            <textarea name="other" id="other_edit" class="form-control" rows="4"></textarea>
                        </div>
                        @endif
                        <div class="form-group other-text">
                            <label>Other: </label>
                            <span id="other_edit_text"></span>
                        </div>
                        <hr>
                        <div id="users">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="update_roster">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(Auth::user()->role != "worker")
    <div class="modal fade" id="add_user_to_roster" tabindex="-1" role="dialog" aria-labelledby="edit">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add Users to Roster</h4>
                </div>
                <form action="" method="post" id="add_new_users_roster_form">
                    {{csrf_field()}}
                    <input type="hidden" name="roster_id" class="roster_id" value="">
                    <div class="modal-body">

                        <div id="edit_error" class="alert alert-danger" style="display: none"></div>

                        <label>Choose the users you wish to add:</label>

                        <select name="users[]" data-placeholder="Choose a user..." class="chosen-add-user" multiple style="width:350px;" tabindex="4">

                        </select>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="add_new_users_roster">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@stop
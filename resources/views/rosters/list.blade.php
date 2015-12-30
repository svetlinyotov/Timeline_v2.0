@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/fullcalendar/fullcalendar.min.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/fullcalendar/scheduler.min.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/fullcalendar/fullcalendar.print.css")}}" media="print">
    <link rel="stylesheet" href="{{asset("plugins/daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/iCheck/all.css")}}">
    <style>
        .color-gray {
            background-color: #c0c0c0;
            border-color: #c0c0c0;
            color: #000;
        }
        .color-green {
            background-color: #378006;
            border-color: #378006;
            color: #FFF;
        }
        .color-red {
            background-color: #b60003;
            border-color: #b60003;
            color: #FFF;
        }
        .color-white {
            background-color: #0007a5;
            border-color: #0007a5;
            color: #000;
            opacity: .3;
        }
    </style>
@stop

@section('script')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp&sensor=true"></script>
    <script src="{{asset("/plugins/fullcalendar/fullcalendar.min.js")}}"></script>
    <script src="{{asset("/plugins/fullcalendar/scheduler.min.js")}}"></script>
    @if(Auth::user()->role != "worker")
        <script src="{{asset("js/mapAddUser.js")}}"></script>
    @endif
    <script src="{{asset("js/mapRosters.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script src="{{asset("plugins/iCheck/icheck.min.js")}}"></script>
    <script src="{{asset("plugins/bootstrap-notify/bootstrap-notify.min.js")}}"></script>
    <script src="{{asset("js/fullcalendar.extention.js")}}"></script>

    <script>
        $(function () {

            $('input[type="checkbox"]').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $('.time_picker').daterangepicker({
                timePicker: true,
                timePickerIncrement: 5,
                minDate: new Date(new Date().setDate(new Date().getDate()-1)),
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                },
            });

            var update_function = function(event, delta, revertFunc) {
                $.ajax({
                    url: '{{asset('/rosters/events')}}/'+event.id,
                    method: "POST",
                    data: {new_time_start:event.start.format(), new_time_end:event.end.format(), user_id:event.resourceId, "_token":'{{csrf_token()}}'},
                    success: function () {
                        $(this).fullCalendar('updateEvent', event);
                        $.notify({
                            message: "Event "+event.title+" updated.<br>New times: "+event.start.format()+" - "+event.end.format()
                        },{
                            type: 'success'
                        });
                    },
                    error: function (msg) {
                        revertFunc();
                        $.notify({
                            message: msg.responseJSON['range']
                        },{
                            type: 'danger'
                        });
                    }
                });
            }

            $('#calendar').fullCalendar({
                @if(Auth::user()->role != "worker")
                    editable: true,
                @endif
                height: $(window).height()-200,
                scrollTime: '00:00',
                resourceAreaWidth: 220,
                firstDay: 1,
                businessHours: {
                    start: '{{Auth::user()->company != null ? \App\Common::formatTimeFromSQL24(Auth::user()->company->shift_day_start) : '08:00'}}',
                    end: '{{Auth::user()->company != null ? \App\Common::formatTimeFromSQL24(Auth::user()->company->shift_night_start) : '20:00'}}',
                    dow: [ 1, 2, 3, 4, 5],
                },
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
                            $.notify({
                                message: msg.responseJSON
                            },{
                                type: 'danger'
                            });
                        }
                    },
                @endif
                events: {
                    url: '{{asset('/rosters/events/'.$company_id)}}',
                    error: function(msg) {
                        $.notify({
                            message: msg.responseJSON
                        },{
                            type: 'danger'
                        });
                    }
                },
                @if(Auth::user()->role != "worker")
                eventDrop:update_function,
                eventResize:update_function,
                @endif
                eventClick:  function(event, jsEvent, view) {
                    var modal = $('#edit');

                    $('#edit_error').hide();
                    modal.find('.select-status').hide();
                    modal.find('.time-range-text').hide();
                    modal.find('.name-text').hide();
                    modal.find('.other-text').hide();
                    modal.find('.status-text').hide();

                    @if(Auth::user()->role == "worker")
                        modal.find('.time-range-edit').hide();
                        modal.find('.name-edit').hide();
                        modal.find('.other-edit').hide();
                        modal.find('.time-range-text').show();
                        modal.find('.name-text').show();
                        modal.find('.other-text').show();
                        modal.find('.supervisor-text').show();
                        modal.find('.address-text').hide();


                        if(event.status != '' && event.status != 'pending'){
                            modal.find('.status-select-group').hide();
                            modal.find('.status-text').show();
                            modal.find('#update_roster').hide();
                            modal.find('#status-text').html(event.status);
                        }
                    @endif

                    modal.find("#time_range_edit").val(moment(event.start).format('M/D/YYYY h:mm A') + ' - ' + moment(event.end).format('M/D/YYYY h:mm A'));
                    modal.find("#time_range_edit_text").html(moment(event.start).format('M/D/YYYY h:mm A') + ' - ' + moment(event.end).format('M/D/YYYY h:mm A'));
                    modal.find("#name_edit").val(event.title);
                    modal.find("#name_edit_text").html(event.title);
                    modal.find("#address_search").val(event.address);
                    modal.find("#address_edit").val(event.address);
                    modal.find("#address_edit_text").html(event.address);
                    modal.find("#coordinates_edit").val(event.coordinates);
                    modal.find("#coordinates_edit_text").html(event.coordinates);
                    modal.find("#supervisor_edit").iCheck(Boolean(event.supervisor)?'check':'uncheck');
                    modal.find("#supervisor-text-check").iCheck(Boolean(event.supervisor)?'check':'uncheck');
                    modal.find("#other_edit").val(event.description);
                    modal.find("#other_edit_text").html(event.description);
                    modal.find("#status_select_input").val(event.status);
                    modal.find('.modal-title').text(event.title);
                    modal.find('input[name=_action]').val('{{asset('/rosters')}}/'+event.id+'#edit');

                    var now = new Date();
                    var selectedDate = new Date(moment(event.start).format('M/D/YYYY h:mm A'));
                    if (selectedDate > now) {
                        modal.find('.status-select').show();
                    }

                    modal.modal();
                    codeAddressNew();
                },
                viewRender: function(view) {
                    if(typeof(timelineInterval) != 'undefined'){
                        window.clearInterval(timelineInterval);
                    }
                    timelineInterval = window.setInterval(setTimeline, 300000);
                    try {
                        setTimeline();
                    } catch(err) {}
                },
            });

            $('#add').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var name = button.data('name');

                var modal = $(this)
                modal.find('.modal-title').text('New roster - ' + name);
                modal.find('input[name=_action]').val('{{asset('/users')}}/'+id+'/roster#add');
            }).on('hidden.bs.modal', function (e) {
                history.pushState("", document.title, window.location.pathname);
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
                        }
                    }
                });
                $("html, body").animate({ scrollTop: 0 }, 200);
                $("#edit").animate({ scrollTop: 0 }, 800);

            });

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
        Company: <select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            <option></option>
            @foreach($companies as $company)
                <option value="{{asset('/rosters')}}?company_id={{$company->id}}" @if(isset($_GET['company_id']) && $_GET['company_id'] == $company->id) selected @endif>{{$company->name}}</option>
            @endforeach
        </select>
        <hr>
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
                    <h4 class="modal-title">New Roster</h4>
                </div>
                <form action="" method="post" id="add_roster">
                    {{csrf_field()}}
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="_action" value="">
                    <div class="modal-body">

                        <div id="edit_error" class="alert alert-danger" style="display: none"></div>

                        <label class="supervisor-text">Supervisor <input type="checkbox" name="is_supervisor" id="supervisor-text-check" value="1" disabled></label>
                        @if(Auth::user()->role != "worker")
                        <label>Supervisor <input type="checkbox" name="is_supervisor" id="supervisor_edit" value="1"></label>
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

                        <div class="form-group status-select-group">
                            <label>Status *</label>
                            <div class="input-group">
                                <select class="form-control" id="status_select_input" name="status">
                                    <option></option>
                                    <option value="accepted">accept</option>
                                    <option value="declined">decline</option>
                                    @if(Auth::user()->role != "worker")
                                    <option value="canceled">cancel</option>
                                    @endif
                                </select>
                            </div>
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
                        <div class="col-md-12" style="height: 300px; margin-bottom: 10px;">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="update_roster">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@stop
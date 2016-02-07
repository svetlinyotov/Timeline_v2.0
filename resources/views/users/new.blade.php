@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("css/plugins/fileinput/fileinput.css")}}">
    <link rel="stylesheet" href="{{asset("css/plugins/daterangepicker/daterangepicker-bs3.css")}}">
@stop

@section('script')
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp"></script>
    <script src="{{asset("js/plugins/fileinput/fileinput.js")}}"></script>
    <script src="{{asset("js/mapAddUser.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("js/plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script src="{{asset("js/plugins/jsKnob/jquery.knob.js")}}"></script>
    <script>
        $(function () {
            $('.fileinput').fileinput();

            $(".dial").knob();

            $('.dateinput').daterangepicker({
                timePicker: false,
                autoUpdateInput: false,
                maxDate: new Date(new Date().setDate(new Date().getDate() - 1)),
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                }
            }).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            }).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

            $('#delete').on('show.bs.modal', function (e) {
                $(this).find('#name').text($(e.relatedTarget).data('name'));
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

        });
        window.onload = function () {
            var timer;
            document.getElementById("address_input").onkeyup = function () {
                timer = setTimeout("codeAddress()", 2000);
            };
            document.getElementById("address_input").onkeydown = function () {
                clearTimeout(timer);
            };
        }
    </script>
@stop

@section('title')
    <h1>
        <a href="{{asset("users")}}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Users
        <small>add</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-users"></i> Home</a></li>
        <li><a href="{{asset("users")}}">Users</a></li>
        <li class="active">New</li>
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
            @if(Session::has('info'))
                <div class="alert alert-info">
                    <i class="fa fa-info"></i> {!! Session::get('info') !!}
                </div>
            @endif

            <form action="{{asset('users')}}" method="post" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-md-6">
                        @if(Auth::user()->role == "supadmin")
                            <div class="form-group">
                                <label>Company *</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-building"></i></div>
                                    <select name="company" class="form-control">
                                        @foreach($companies as $company)
                                            <option value="{{$company->id}}" {{old('company')==$company->id?'selected':null}}>{{$company->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Type *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-list-alt"></i></div>
                                <select name="type" class="form-control">
                                    <option value="worker" {{old('type')=="worker"?'selected':null}}>Worker</option>
                                    <option value="admin" {{old('type')=="admin"?'selected':null}}>Admin</option>
                                    <option value="mod" {{old('type')=="mod"?'selected':null}}>Moderator</option>
                                    @if(Auth::user()->role == "supadmin")
                                        <option value="supadmin" {{old('type')=="supadmin"?'selected':null}}>Super
                                            admin
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Email *</label>
                            <div class="input-group @if($errors->first("email")) has-error @endif has-feedback">
                                <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                <input type="email" name="email" class="form-control" value="{{old('email')??$_GET['email']??""}}">
                            </div>
                            {!! $errors->first('email', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Names *</label>
                            <div class="input-group @if($errors->first("names")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                <input type="text" name="names" class="form-control" value="{{old('names')}}">
                            </div>
                            {!! $errors->first('names', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Address *</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <input type="text" name="address" class="form-control" id="address_input"
                                       value="{{old('address')}}">
                            </div>
                        </div>
                        <div class="col-md-12" style="height: 300px; margin-bottom: 10px;">
                            <div id="map-address" style="width: 100%; height: 100%;"></div>
                        </div>
                        <div class="input-group @if($errors->first("coordinates")) has-error @endif">
                            <div class="input-group-addon">&nbsp;<i class="fa fa-map-marker"></i>&nbsp;</div>
                            <div id="coordinates" class="form-control">{{old('coordinates')}}</div>
                            <input type="hidden" name="coordinates" id="coordinates_type"
                                   value="{{old('coordinates')}}"/>
                        </div>
                        <div class="input-group @if($errors->first("address")) has-error @endif">
                            <div class="input-group-addon"><i class="fa fa-map"></i></div>
                            <div id="address" class="form-control">{{old('address')}}</div>
                            <input type="hidden" name="address" id="address_type" value="{{old('address')}}"/>
                        </div>
                        {!! $errors->first('coordinates', "<span class='text-danger'><i class='fa fa-times-circle-o'></i> Use the map pin o r the search bar above it to select address and coordinates</span>") !!}

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender</label>
                            <div class="input-group btn-group" data-toggle="buttons">
                                <label class="btn btn-default @if(old('gender') == 1) active @endif">
                                    <input type="radio" name="gender" value="1"
                                           @if(old('gender') == 1) checked @endif /> Male
                                </label>
                                <label class="btn btn-default @if(old('gender') == 2) active @endif">
                                    <input type="radio" name="gender" value="2"
                                           @if(old('gender') == 2) checked @endif /> Female
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Birth date</label>
                            <div class="input-group @if($errors->first("birth_date")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" name="birth_date" class="form-control dateinput"
                                       value="{{old('birth_date')}}">
                            </div>
                            {!! $errors->first('birth_date', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Profile picture</label>
                                    <div class="input-group @if($errors->first("avatar")) has-error @endif">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                <img src="http://placehold.it/300?text=no image" alt="">
                                            </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail"
                                                 style="max-width: 200px; max-height: 150px;"></div>
                                            <div>
                                            <span class="btn btn-default btn-file">
                                                <span class="fileinput-new">Select image</span>
                                                <span class="fileinput-exists">Change</span>
                                                <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif,.tiff,.bmp">
                                            </span>
                                            <a href="#" class="btn btn-danger fileinput-exists"
                                                   data-dismiss="fileinput">Remove</a>
                                            </div>
                                        </div>
                                    </div>
                                    {!! $errors->first('avatar', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                                </div>
                            </div>
                            <div class="col-sm-6 text-center">
                                @if(Auth::user()->role != "worker")
                                <label>Max working hours <small>(per month)</small></label>
                                <div class="m-r-lg inline">
                                    <input type="text" value="744" class="dial m-r" style="border: 1px solid #555" data-fgColor="#ED5565" data-width="110" data-height="110" data-min="1" data-max="744" data-angleOffset="-125" data-angleArc="250" />
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label>CV file</label>
                            <div class="input-group @if($errors->first("cv")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-file-word-o"></i></div>
                                <input type="file" name="cv" class="form-control" value="{{old('cv')}}"
                                       accept=".doc,.docx,.ppt,.pps,.pptx,.ppsx,.xls,.xlsx">
                            </div>
                            {!! $errors->first('cv', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mobile *</label>
                            <div class="input-group @if($errors->first("mobile")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-mobile"></i></div>
                                <input type="text" name="mobile" class="form-control" value="{{old('mobile')}}">
                            </div>
                            {!! $errors->first('mobile', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                        <div class="form-group">
                            <label>Home phone</label>
                            <div class="input-group @if($errors->first("home_phone")) has-error @endif">
                                <div class="input-group-addon"><i class="glyphicon glyphicon-phone-alt"></i></div>
                                <input type="text" name="home_phone" class="form-control" value="{{old('home_phone')}}">
                            </div>
                            {!! $errors->first('home_phone', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Work phone</label>
                            <div class="input-group @if($errors->first("work_phone")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                <input type="text" name="work_phone" class="form-control" value="{{old('work_phone')}}">
                            </div>
                            {!! $errors->first('work_phone', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                        <div class="form-group">
                            <label>Fax</label>
                            <div class="input-group @if($errors->first("fax")) has-error @endif">
                                <div class="input-group-addon"><i class="fa fa-fax"></i></div>
                                <input type="text" name="fax" class="form-control" value="{{old('fax')}}">
                            </div>
                            {!! $errors->first('fax', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Other</label>
                    <textarea name="other" class="form-control" rows="4">{{old('other')}}</textarea>
                    {!! $errors->first('other', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="btn-group btn-group-justified">
                    <div class="btn-group" style="width:30px;">
                        <input type="submit" class="btn btn-primary" value="Create">
                    </div>
                    <div class="btn-group">
                        <a href="{{asset("users")}}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
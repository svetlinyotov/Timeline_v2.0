@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="{{asset("plugins/datatables/dataTables.bootstrap.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/fileinput/fileinput.css")}}">
    <link rel="stylesheet" href="{{asset("plugins/daterangepicker/daterangepicker.css")}}">
@stop

@section('script')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_KEY')}}&v=3.exp&sensor=true"></script>
    <script src="{{asset("plugins/datatables/jquery.dataTables.min.js")}}"></script>
    <script src="{{asset("plugins/datatables/dataTables.bootstrap.js")}}"></script>
    <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}"></script>
    <script src="{{asset("plugins/fileinput/fileinput.js")}}"></script>
    <script src="{{asset("js/mapAddUser.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="{{asset("plugins/daterangepicker/daterangepicker.js")}}"></script>
    <script>
        $(function () {
            $('.fileinput').fileinput();

            $('.dateinput').daterangepicker({
                timePicker: false,
                autoUpdateInput:false,
                maxDate: new Date(new Date().setDate(new Date().getDate()-1)),
                singleDatePicker: true,
                showDropdowns: true,
                autoApply:true,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            }).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

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

            $('#delete').on('show.bs.modal', function (e) {
                $(this).find('#name').text($(e.relatedTarget).data('name'));
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

        });
        window.onload = function() {
            var timer;
            document.getElementById("address_input").onkeyup=function(){
                timer = setTimeout("codeAddress()", 2000);
            };
            document.getElementById("address_input").onkeydown=function(){
                clearTimeout(timer);
            };
        }
    </script>
@stop

@section('title')
    <h1>
        <a href="{!! (isset($_GET['rel']) && $_GET['rel'] == 'edit') ? asset('users/'.$user->id) : asset('users') !!}" class="btn btn-xs btn-circle btn-info"><i class="fa fa-arrow-left"></i> </a>
        Users - {{$user->info->names}}
        <small>edit</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{asset("dashboard")}}"><i class="fa fa-users"></i> Home</a></li>
        <li><a href="{{asset("users")}}">Users</a></li>
        <li><a href="{{asset("users/".$user->id)}}">{{$user->info->names}}</a></li>
        <li class="active">Edit</li>
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
            @if($errors->any())
                <div class="alert alert-danger">
                    Please fix the errors below.
                </div>
            @endif

            <form action="{{asset('users/'.$user->id)}}" method="post" enctype="multipart/form-data" class="col-md-6">
                {{csrf_field()}}
                <input type="hidden" name="_method" value="put">
                @if(Auth::user()->role == "supadmin")
                <div class="form-group">
                    <label>Company *</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-building"></i> </div>
                        <select name="company" class="form-control">
                            @foreach($companies as $company)
                                <option value="{{$company->id}}" {{(old('company')??$user->company_id)==$company->id?'selected':null}}>{{$company->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label>Email: {{$user->email}}</label>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <div class="input-group @if($errors->first("password")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-key"></i> </div>
                        <input type="text" name="password" class="form-control" value="{{old('password')}}">
                    </div>
                    {!! $errors->first('password', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                @if(Auth::user()->role != "worker")
                <div class="form-group">
                    <label>Type *</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-list-alt"></i> </div>
                        <select name="type" class="form-control">
                            <option value="worker" {{(old('type')??$user->role)=="worker"?'selected':null}}>Worker</option>
                            <option value="admin" {{(old('type')??$user->role)=="admin"?'selected':null}}>Admin</option>
                            <option value="mod" {{(old('type')??$user->role)=="mod"?'selected':null}}>Moderator</option>
                            @if(Auth::user()->role == "supadmin")
                                <option value="supadmin" {{(old('type')??$user->role)=="supadmin"?'selected':null}}>Super admin</option>
                            @endif
                        </select>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label>Names *</label>
                    <div class="input-group @if($errors->first("names")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-user"></i> </div>
                        <input type="text" name="names" class="form-control" value="{{old('names')??$user->info->names}}">
                    </div>
                    {!! $errors->first('names', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-search"></i> </div>
                        <input type="text" name="address" class="form-control" id="address_input" value="{{old('address')??$user->info->address}}">
                    </div>
                </div>
                <div class="col-md-12" style="height: 300px; margin-bottom: 10px;">
                    <div id="map-address" style="width: 100%; height: 100%;"></div>
                </div>
                <div class="input-group @if($errors->first("coordinates")) has-error @endif">
                    <div class="input-group-addon">&nbsp;<i class="fa fa-map-marker"></i>&nbsp;</div>
                    <div id="coordinates" class="form-control">{{old('coordinates')??$user->info->coordinates}}</div><input type="hidden" name="coordinates" id="coordinates_type" value="{{old('coordinates')??$user->info->coordinates}}" />
                </div>
                <div class="input-group @if($errors->first("address")) has-error @endif">
                    <div class="input-group-addon"><i class="fa fa-map"></i> </div>
                    <div id="address" class="form-control">{{old('address')??$user->info->address}}</div><input type="hidden" name="address" id="address_type" value="{{old('address')??$user->info->address}}" />
                </div>
                {!! $errors->first('coordinates', "<span class='text-danger'><i class='fa fa-times-circle-o'></i> Use the map pin o r the search bar above it to select address and coordinates</span>") !!}
                <br>

                <div class="form-group">
                    <label>Gender</label>
                    <div class="input-group btn-group" data-toggle="buttons">
                        <label class="btn btn-default @if((old('gender')??$user->info->gender) == 1) active @endif">
                            <input type="radio" name="gender" value="1" @if((old('gender')??$user->info->gender) == 1) checked @endif /> Male
                        </label>
                        <label class="btn btn-default @if((old('gender')??$user->info->gender) == 2) active @endif">
                            <input type="radio" name="gender" value="2" @if((old('gender')??$user->info->gender) == 2) checked @endif /> Female
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Birth date</label>
                    <div class="input-group @if($errors->first("birth_date")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i> </div>
                        <input type="text" name="birth_date" class="form-control dateinput" value="{{old('birth_date')??$user->info->birth_date}}">
                    </div>
                    {!! $errors->first('birth_date', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Mobile *</label>
                    <div class="input-group @if($errors->first("mobile")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-mobile"></i> </div>
                        <input type="text" name="mobile" class="form-control" value="{{old('mobile')??$user->info->mobile}}">
                    </div>
                    {!! $errors->first('mobile', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Home phone</label>
                    <div class="input-group @if($errors->first("home_phone")) has-error @endif">
                        <div class="input-group-addon"><i class="glyphicon glyphicon-phone-alt"></i> </div>
                        <input type="text" name="home_phone" class="form-control" value="{{old('home_phone')??$user->info->home_phone}}">
                    </div>
                    {!! $errors->first('home_phone', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Work phone</label>
                    <div class="input-group @if($errors->first("work_phone")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-phone"></i> </div>
                        <input type="text" name="work_phone" class="form-control" value="{{old('work_phone')??$user->info->work_phone}}">
                    </div>
                    {!! $errors->first('work_phone', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Fax</label>
                    <div class="input-group @if($errors->first("fax")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-fax"></i> </div>
                        <input type="text" name="fax" class="form-control" value="{{old('fax')??$user->info->fax}}">
                    </div>
                    {!! $errors->first('fax', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Profile picture</label>
                    <div class="input-group @if($errors->first("avatar")) has-error @endif">
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                <img src="{{asset('avatar/'.$user->info->avatar)}}" alt="">
                            </div>
                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
                            <div>
                                <span class="btn btn-default btn-file">
                                    <span class="fileinput-new">Select image</span>
                                    <span class="fileinput-exists">Change</span>
                                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif,.tiff,.bmp">
                                </span>
                                <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput">Remove</a>
                            </div>
                        </div>
                    </div>
                    {!! $errors->first('avatar', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>CV file</label>
                    <div class="input-group @if($errors->first("cv")) has-error @endif">
                        <div class="input-group-addon"><i class="fa fa-file-word-o"></i> </div>
                        <input type="file" name="cv" class="form-control" value="{{old('cv')}}" accept=".doc,.docx,.ppt,.pps,.pptx,.ppsx,.xls,.xlsx">
                    </div>
                    @if($user->info->cv != null) <a href="{{asset('cv/'.$user->info->cv)}}">Uploaded file</a><br> @endif
                    {!! $errors->first('cv', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="form-group">
                    <label>Other</label>
                    <textarea name="other" class="form-control" rows="4">{{old('other')??$user->info->other}}</textarea>
                    {!! $errors->first('other', "<span class='text-danger'><i class='fa fa-times-circle-o'></i>:message</span>") !!}
                </div>
                <div class="btn-group btn-group-justified">
                    <div class="btn-group" style="width:30px;">
                        <input type="submit" class="btn btn-primary" value="Update">
                    </div>
                    <div class="btn-group">
                        <a href="{{asset("users")}}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
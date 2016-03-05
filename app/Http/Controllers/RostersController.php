<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\ExternalRequest;
use App\GoogleUser;
use App\GoogleUserCalendar;
use App\Notification;
use App\Roster;
use App\Tokens;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RostersController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('auth.notWorker', ['except' => ['index', 'events', 'update']]);
    }

    public function index(Request $request)
    {
        if(Auth::user()->role == "supadmin") {
            $company_id = $request->get('company_id');
        }elseif(Auth::user()->role == "worker"){
            $company_id = implode(",", $this->company_id);
        }else{
            $company_id = $this->company_id[0];
        }

        $start_shift = $end_shift = "00:00";

        if($company_id && Auth::user()->role != "worker"){
            $start_shift = Company::find($company_id)->shift_day_start;
            $end_shift = Company::find($company_id)->shift_night_start;
        }

        $companies = Company::listAll();
        $workers = Company::workers($company_id);
        return view('rosters.list')->with(['company_id' => $company_id, 'shift_start'=>$start_shift, 'shift_end' => $end_shift, 'workers' => $workers, 'companies' => $companies]);
    }

    public function store(Request $request, $user_id)
    {
        if(Auth::user()->role == "supadmin") {
            $company_id = $request->get('company_id');
        }else{
            $company_id = $this->company_id[0];
        }

        $this->validate($request, [
            'time_range' => ['regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],
            'address' => 'required',
            'coordinates' => 'required',
            'name' => 'required',
        ]);

        list($start, $end) = explode(' - ', $request->input('time_range'));

        if(Roster::overlap($user_id, Common::formatDateTimeForSQL($start), Common::formatDateTimeForSQL($end)) === true){
            return response()->json(['range' => 'The time range overlaps for this user.'], 422);
        }
        if(!Common::isInTheFuture(Common::formatDateTimeForSQL($start))){
            return response()->json(['range' => 'You cannot add event in the past'], 422);
        }

        $roster = [];
        $roster['id'] = $user_id;
        $roster['name'] = $request->input('name');
        $roster['is_supervisor'] = $request->input('is_supervisor');
        $roster['start_time'] = Common::formatDateTimeForSQL($start);
        $roster['end_time'] = Common::formatDateTimeForSQL($end);
        $roster['other'] = $request->input('other');
        $roster['address'] = $request->input('address');
        $roster['coordinates'] = $request->input('coordinates');
        $roster['added_by'] = Auth::user()->id;
        $roster['company_id'] = $company_id;

        Roster::add($roster);

        Notification::add($user_id, 'CREATE_EVENT', ['start'=>Common::formatDateTimeForSQL($start), 'end'=>Common::formatDateTimeForSQL($end), 'admin_id' => Auth::user()->id]);

        return response()->json("Done", 200);
    }

    public function updateEvent(Request $request, $event_id)
    {
        $roster = Roster::find($event_id);

        if(!Common::isInTheFuture(Common::formatDateTimeForSQL($request->input('new_time_start')))){
            return response()->json(['range' => 'You cannot update past event'], 422);
        }
        if(Roster::overlap($request->input('user_id'), Common::formatDateTimeForSQL($request->input('new_time_start')), Common::formatDateTimeForSQL($request->input('new_time_end')), $event_id) === true){
            return response()->json(['range' => 'The time range overlaps for this user.'], 422);
        }

        $roster->start_time = $request->input('new_time_start');
        $roster->end_time = $request->input('new_time_end');
        $roster->save();

        Notification::add($request->input('user_id'), 'UPDATE_EVENT', ['start'=>$request->input('new_time_start'), 'end'=>$request->input('new_time_end'), 'title'=>$roster->name, 'admin_id' => Auth::user()->id]);
    }

    public function update(Request $request, $event_id)
    {
        $users_ids = explode(",", $request->input('user_id'));
        $roster_data = Roster::where('id', '=', $event_id)->first();
        $roster = Roster::find($event_id);

        if(!Common::isInTheFuture(Common::formatDateTimeForSQL($roster_data->start_time))){
            return response()->json(['range' => 'You cannot update past event'], 422);
        }
        $status = $request->input('status');
        $supervisor = $request->input('supervisor');

        if(Auth::user()->role != "worker") {
            $this->validate($request, [
                'time_range' => ['regex:/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],
                'address' => 'required',
                'coordinates' => 'required',
                'name' => 'required',
            ]);

            list($start, $end) = explode(' - ', $request->input('time_range'));
            $overlapping_users = [];

            foreach ($users_ids as $user_id) {
                if (Roster::overlap($user_id, Common::formatDateTimeForSQL($start), Common::formatDateTimeForSQL($end), $event_id) === true) {
                    $overlapping_users[] = User::getNameById($user_id);
                }
            }

            if(count($overlapping_users)){
                return response()->json(['range' => 'The time range overlaps for '. implode(', ', $overlapping_users) .'.'], 422);
            }

            foreach ($users_ids as $user_id) {
                $roster->users()->updateExistingPivot($user_id, ['status'=>$status[$user_id]??'pending', 'is_supervisor'=>$supervisor[$user_id]??0]);
            }

            $roster->name = $request->input('name');
            $roster->start_time = Common::formatDateTimeForSQL($start);
            $roster->end_time = Common::formatDateTimeForSQL($end);
            $roster->other = $request->input('other');
            $roster->address = $request->input('address');
            $roster->coordinates = $request->input('coordinates');

            if($roster_data->start_time != $start) {
                foreach ($users_ids as $user_id) {
                    Notification::add($user_id, 'UPDATE_EVENT', ['start' => Common::formatDateTimeForSQL($start), 'end' => Common::formatDateTimeForSQL($end), 'title' => $roster->name, 'admin_id' => Auth::user()->id]);
                }
            }else{
                if($roster_data->end_time != $end){
                    foreach ($users_ids as $user_id) {
                        Notification::add($user_id, 'UPDATE_EVENT', ['start' => Common::formatDateTimeForSQL($start), 'end' => Common::formatDateTimeForSQL($end), 'title' => $roster->name, 'admin_id' => Auth::user()->id]);
                    }
                }
            }

        }else{
            $id = Auth::user()->id;
            $status = $status[$id];
            $roster_status = Roster::where('id', '=', $event_id)->with(['users'=>function($q)use($id){$q->select('status', 'user_id', 'roster_id')->where('users.id', '=', $id);}])->first()->users[0]->status;

            if($status == "canceled")
                return response()->json(['range' => 'You are not authorized to cancel event'], 422);

            if($roster_status != 'pending')
                return response()->json(['range' => 'You cannot update this event'], 422);

            $roster->users()->updateExistingPivot(Auth::user()->id, ['status'=>$status]);
        }

        $roster->save();

        return response()->json(['Data is updated'], 200);
    }

    public function workers($company_id)
    {
        return response()->json(Company::workers($company_id));
    }

    public function events($company_id, Request $request)
    {
        $google_calendars = [];
        $users = Auth::user();
        if(Auth::user()->role != "worker") {
            $users = User::whereHas('company', function ($q) use ($company_id) {
                $q->where('companies.id', '=', $company_id);
            })->select('id', 'events_color')->get();
            $users = array_column($users->toArray(), 'events_color', 'id');

            foreach ($users as $user_id => $event_color) {
                $google_calendars[$user_id] = GoogleUserCalendar::freeBusy($user_id, $request->get('start'), $request->get('end'));
            }

            $jsonResponse = Roster::eventsJSON($company_id, ['start'=>$request->get('start'), 'end'=>$request->get('end')]);
        }else{
            $jsonResponse = User::events(Auth::user()->id, ['start'=>$request->get('start'), 'end'=>$request->get('end')]);
            $google_calendars[Auth::user()->id] = GoogleUserCalendar::freeBusy(Auth::user()->id, $request->get('start'), $request->get('end'));
        }

        foreach ($google_calendars as $user_id => $google_calendar_list) {

            foreach ($google_calendar_list as $calendar) {
                $events = $calendar->busy;
                if(count($events) > 0) {
                    foreach ($events as $event) {
                        array_push($jsonResponse, (object)[
                            "id" => "google_" . rand(100, 700),
                            "resourceId" => $user_id,
                            "start" => $event->start,
                            "end" => $event->end,
                            "rendering" => "background",
                            "color" => $users[$user_id]
                        ]);
                    }
                }
            }

        }

        return response()->json($jsonResponse);
    }

    public function getEvent($id)
    {
        if(Auth::user()->role == "worker") {
            $user_id = Auth::user()->id;
            $jsonResponse = Roster::where('id', $id)->with(['users'=>function($q)use($user_id){$q->where('users.id', '=', $user_id);}])->first()->toArray();
        }else{
            $jsonResponse = Roster::where('id', $id)->with('users')->first()->toArray();
        }
        return response()->json($jsonResponse);
    }

    public function unlinkedUsers($id)
    {
        return response()->json(User::notLinkedWithRoster($id), 200);
    }

    public function addUsers($id, Request $request)
    {
        $users = $request->input('users');
        $roster_data = Roster::where('id', '=', $id)->first();

        $roster = Roster::find($id)->users();

        foreach ($users as $user) {
            $roster->attach($user);
            Notification::add($user, 'CREATE_EVENT', ['start'=>Common::formatDateTimeForSQL($roster_data->start_time), 'end'=>Common::formatDateTimeForSQL($roster_data->end_time), 'admin_id' => Auth::user()->id]);
        }

        return response()->json(Roster::where('id', $id)->with('users')->first()->toArray()['users'], 200);
    }
}

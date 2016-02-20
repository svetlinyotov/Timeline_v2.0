<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\Notification;
use App\Roster;
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
        }else{
            $company_id = $this->company_id[0];
        }
        $start_shift = $end_shift = "";
        if($company_id){
            $start_shift = Company::find($company_id)->shift_day_start;
            $end_shift = Company::find($company_id)->shift_night_start;
        }

        $companies = Company::listAll();
        $workers = Company::workers($company_id);
        return view('rosters.list')->with(['company_id' => $company_id, 'shift_start'=>$start_shift, 'shift_end' => $end_shift, 'workers' => $workers, 'companies' => $companies]);
    }

    public function store(Request $request, $user_id)
    {
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

        $roster = new Roster();
        $roster->name = $request->input('name');
        $roster->is_supervisor = $request->input('is_supervisor');
        $roster->start_time = Common::formatDateTimeForSQL($start);
        $roster->end_time = Common::formatDateTimeForSQL($end);
        $roster->other = $request->input('other');
        $roster->address = $request->input('address');
        $roster->coordinates = $request->input('coordinates');
        $roster->added_by = Auth::user()->id;
        $roster->save();

        User::find($user_id)->rosters()->attach($roster->id);

        Notification::add($user_id, 'CREATE_EVENT', ['start'=>Common::formatDateTimeForSQL($start), 'end'=>Common::formatDateTimeForSQL($end), 'admin_id' => Auth::user()->id]);

        return response()->json("Done", 200);
    }

    public function updateEvent(Request $request, $event_id)
    {
        $roster = Roster::find($event_id);
        if(Roster::overlap($request->input('user_id'), Common::formatDateTimeForSQL($request->input('new_time_start')), Common::formatDateTimeForSQL($request->input('new_time_end')), $event_id) === true){
            return response()->json(['range' => 'The time range overlaps for this user.'], 422);
        }
        if(!Common::isInTheFuture(Common::formatDateTimeForSQL($request->input('new_time_start')))){
            return response()->json(['range' => 'You cannot update past event'], 422);
        }

        $roster->start_time = $request->input('new_time_start');
        $roster->end_time = $request->input('new_time_end');
        $roster->save();

        Notification::add($request->input('user_id'), 'UPDATE_EVENT', ['start'=>$request->input('new_time_start'), 'end'=>$request->input('new_time_end'), 'title'=>$roster->name, 'admin_id' => Auth::user()->id]);
    }

    public function update(Request $request, $event_id)
    {
        $user_id = $request->input('user_id');
        $roster = Roster::where('rosters.id', $event_id)->
                        leftJoin('roster_user as ru', function($join) use ($user_id) {
                            $join->on('ru.roster_id', '=', 'rosters.id')
                                 ->where('ru.user_id', '=', $user_id);
                        })->select('rosters.*', 'ru.status')->first();
        $roster_users = Roster::find($roster->id)->users()->select('users.id')->get()->pluck('id')->toArray();

        if(Auth::user()->role != "worker") {
            $this->validate($request, [
                'time_range' => ['regex:/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],
                'address' => 'required',
                'coordinates' => 'required',
                'name' => 'required',
            ]);

            list($start, $end) = explode(' - ', $request->input('time_range'));
            $overlapping_users = [];

            foreach ($roster_users as $roster_user) {
                if (Roster::overlap($roster_user, Common::formatDateTimeForSQL($start), Common::formatDateTimeForSQL($end), $event_id) === true) {
                    $overlapping_users[] = $roster_user;
                }
            }

            if(count($overlapping_users)){
                return response()->json(['range' => 'The time range overlaps for '. implode(', ', $overlapping_users) .'.'], 422);
            }
        }else{
            $start = $roster->start_time;
            $end = $roster->end_time;
        }

        if(Common::isInTheFuture(Common::formatDateTimeForSQL($start))){
            if(Auth::user()->role == "worker" && $request->input('status') == "canceled")
                return response()->json(['range' => 'You are not authorized to cancel event'], 422);

            if(($roster->status == '' || $roster->status == 'pending') || Auth::user()->role != "worker"){
                DB::update("UPDATE roster_user SET status = ? WHERE user_id = ? AND roster_id = ?", [$request->input('status'), $user_id, $roster->id]);
            }else{
                return response()->json(['range' => 'You cannot update this event'], 422);
            }
        }else{
            return response()->json(['range' => 'You cannot update past event'], 422);
        }

        if(Auth::user()->role != "worker") {
            $roster->name = $request->input('name');
            $roster->is_supervisor = $request->input('is_supervisor');
            $roster->start_time = Common::formatDateTimeForSQL($start);
            $roster->end_time = Common::formatDateTimeForSQL($end);
            $roster->other = $request->input('other');
            $roster->address = $request->input('address');
            $roster->coordinates = $request->input('coordinates');

            foreach ($roster_users as $roster_user) {
                Notification::add($roster_user, 'UPDATE_EVENT', ['start'=>Common::formatDateTimeForSQL($start), 'end'=>Common::formatDateTimeForSQL($end), 'title'=>$roster->name, 'admin_id' => Auth::user()->id]);
            }
        }
        $roster->save();
    }

    public function workers($company_id)
    {
        return response()->json(Company::workers($company_id));
    }
    public function events($company_id, Request $request)
    {
        if(Auth::user()->role == "worker")
            return response()->json(User::events(Auth::user()->id, ['start'=>$request->get('start'), 'end'=>$request->get('end')]));

        return response()->json(Roster::eventsJSON($company_id, ['start'=>$request->get('start'), 'end'=>$request->get('end')]));
    }
}

<?php

namespace App\Http\Controllers;



use App\Availability;
use App\Common;
use App\GoogleUser;
use App\GoogleUserCalendar;
use App\Tokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index()
    {
        return view("availability.list");
    }

    public function googleList()
    {
        $data = GoogleUser::getUserData(Auth::user()->id);

        return view("availability.googleList", ['data'=> $data]);
    }

    public function googleListCalendars($user_id)
    {
        $user = GoogleUser::getUserByGoogleId($user_id);
        $calendars = GoogleUserCalendar::where('user_id', '=', $user_id)->get()->lists('calendar_id')->toArray();

        if($user->user_id != Auth::user()->id){
            abort(401, "Not authorized to access this data from the current session");
        }

        $data = GoogleUser::getCalendarsByUser($user_id);

        return view("availability.googleCalendars", ['data'=> $data->items, 'user' => $user, 'on_calendars' => $calendars]);
    }

    public function googleSaveCalendar($user_id, Request $request)
    {
        $calendar_id = $request->input('calendar_id');
        $data = GoogleUserCalendar::where('user_id', '=', $user_id)->where('calendar_id', '=', $calendar_id)->first();
        $msg = "Error";

        if($data) {
            GoogleUserCalendar::destroy($data->id);
            $msg = "Calendar is disabled";
        }else{
            GoogleUserCalendar::create(['user_id' => $user_id, 'calendar_id' => $calendar_id]);
            $msg = "Calendar is enabled";
        }

        return response()->json(['msg' => $msg], 200);
    }

    public function deleteGoogleProfile($id)
    {
        $token = GoogleUser::getTokenById($id);
        GoogleUser::destroy($id);
        Tokens::revokeToken($token);
        return response()->json(['msg' => 'User disconnected'], 200);
    }

    public function create(Request $request)
    {
        Availability::create([
            'user_id' => Auth::user()->id,
            'start_time' => Common::formatDateTimeForSQL($request->input('start')),
            'end_time' => Common::formatDateTimeForSQL($request->input('end')),
            'all_day' => $request->input('allDay') == "true" ? "1" : "0",
        ]);
        return response()->json(['msg' => 'Time shift is added'], 200);
    }

    public function update($id, Request $request)
    {
        $event = Availability::find($id);
        $event->start_time = Common::formatDateTimeForSQL($request->input('new_time_start'));
        $event->end_time = Common::formatDateTimeForSQL($request->input('new_time_end'));
        $event->save();
        return response()->json(['msg' => 'Time updated'], 200);
    }

    public function destroy($id)
    {
        Availability::destroy($id);
        return response()->json(['msg' => 'Time deleted'], 200);
    }

    public function events(Request $request)
    {
        return response()->json(Availability::events(Auth::user()->id, $request->get('start'), $request->get('end')));
    }


}
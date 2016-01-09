<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\Payment;
use App\Roster;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('auth.notWorker', ['except' => ['index', 'events', 'update']]);
    }

    public function index(Request $request)
    {
        $start_time = $request->get('start') ?? date("Y-m-d", strtotime("-1 month"));
        $end_time = $request->get('end') ?? date("Y-m-d", strtotime("now"));
        if(Auth::user()->role == "supadmin") {
            $company_id = $request->get('company_id');
        }else{
            $company_id = Auth::user()->company_id;
        }

        $currency = $company_id!=null ? Company::find($company_id)->currency->title : null;

        if(Auth::user()->role == "supadmin") {
            $data = Payment::users($company_id, $start_time, $end_time);
        } else
            $data = Payment::users(Auth::user()->company_id, $start_time, $end_time);

        return view('payment.list_users')->with(['data' => $data, 'currency' => $currency, 'companies' => Company::listAll()]);
    }

    public function edit($user_id, Request $request)
    {
        $start_time = $request->get('start') ?? date("Y-m-d", strtotime("-1 month"));
        $end_time = $request->get('end') ?? date("Y-m-d", strtotime("now"));

        $email = User::where('id', $user_id)->select('email')->pluck('email');
        $company_id = User::where('id', $user_id)->select('company_id')->pluck('company_id');
        $currency = Company::find($company_id)->currency->title;
        $data = Payment::shifts($user_id, $start_time, $end_time);

        return view('payment.shifts')->with(['data' => $data, 'currency' => $currency, 'user_email' => $email, 'user_id' => $user_id, 'user_company_id' => $company_id]);
    }

    public function update(Request $request, $user_id)
    {
        $this->validate($request, [
            'real_start.*' => ['regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],
            'real_end.*' => ['regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],

        ]);
        $start_times_arr = $request->get('real_start');
        $end_times_arr = $request->get('real_end');

        if(count(array_filter($start_times_arr)) > 0) {
            foreach ($start_times_arr as $roster_id => $time) {
                $start = $time;
                $end = $end_times_arr[$roster_id];

                $roster = Roster::find($roster_id);
                if($start != null && $start != "") {
                    $roster->real_start_time = Common::formatDateTimeForSQL($start);
                }
                if($end != null && $end != "") {
                    $roster->real_end_time = Common::formatDateTimeForSQL($end);
                }

                $roster->save();
            }
        }

        return redirect()->back()->with(['message' => 'Data successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

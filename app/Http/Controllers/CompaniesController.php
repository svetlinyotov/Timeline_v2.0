<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\CompanyHolidays;
use App\Currency;
use App\CustomPayment;
use App\Http\Requests;
use App\WeekPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompaniesController extends Controller
{
    public function __construct()
    {
        if(Auth::user()->role != "supadmin") abort(402, "Unauthorized");
    }

    public function index()
    {
        $data = Company::with('currency')->get();
        $currency = Currency::all();
        $timezones = Common::timezone();

        return view('companies.list', ['data' => $data, 'currency' => $currency, 'timezones' => $timezones]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|min:3',
            'city' => 'required|max:100',
            'post_code' => 'numeric',
            'address' => 'required|max:255',
            'timezone' => 'required',
            'currency' => 'required',
        ]);

        $company = new Company();
        $company->id = uniqid();
        $company->name = $request->get('name');
        $company->city = $request->get('city');
        $company->post_code = $request->get('post_code');
        $company->address= $request->get('address');
        $company->timezone= $request->get('timezone');
        $company->currency_id= $request->get('currency');
        $company->save();

        return redirect('/companies#')->with(['message' => 'New company added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name_edit' => 'required|max:255|min:3',
            'city_edit' => 'required|max:100',
            'post_code_edit' => 'numeric',
            'address_edit' => 'required|max:255',
            'timezone_edit' => 'required',
            'currency_edit' => 'required',
        ]);

        $company = Company::find($id);
        $company->name = $request->get('name_edit');
        $company->city = $request->get('city_edit');
        $company->post_code = $request->get('post_code_edit');
        $company->address= $request->get('address_edit');
        $company->timezone= $request->get('timezone_edit');
        $company->currency_id= $request->get('currency_edit');
        $company->save();

        return redirect('/companies#')->with(['message' => 'Company <b>'.$request->get('name_edit').'</b> edited successfully.']);
    }

    public function destroy($id)
    {
        Company::destroy($id);
        return redirect('/companies#')->with(['message' => 'The company is deleted successfully.']);
    }

    public function holidaysShow($company_id)
    {
        $company_name = Company::find($company_id)->name;
        $holidays = CompanyHolidays::selectHolidays($company_id);
        return view('companies.holidays')->with(['data' => $holidays, 'company_name' => $company_name]);
    }

    public function holidaysStore($company_id, Request $request)
    {
        $this->validate($request,[
            'date' => 'required|date_format:d/m/Y',
        ]);

        list($day, $month, $year) = explode("/", $request->get('date'));
        if($request->get('annual') != null){
            $year = null;
        }

        $holiday = new CompanyHolidays();
        $holiday->day = $day;
        $holiday->month = $month;
        $holiday->year = $year;
        $holiday->name = $request->get('name');

        Company::find($company_id)->holidays()->save($holiday);
        return redirect('/companies/'.$company_id.'/holidays#')->with(['message' => 'The holiday is added successfully.']);
    }

    public function holidaysDestroy($company_id, $holiday_id)
    {
        CompanyHolidays::deleteHoliday($company_id, $holiday_id);
        return redirect('/companies/'.$company_id.'/holidays#')->with(['message' => 'The holiday is deleted successfully.']);
    }

    public function paymentShow($company_id)
    {
        $company_name = Company::find($company_id)->name;
        $currency = Company::find($company_id)->currency->title;
        $week_data = WeekPayment::where('company_id', $company_id)->orderBy('day', 'asc')->orderBy('period', 'asc')->orderBy('type', 'asc')->get()->toArray();
        $custom_week_data = CustomPayment::where('company_id', $company_id)->orderBy('time_start', 'desc')->get();

        return view('companies.payment')->with(['company_name' => $company_name, 'currency' => $currency, 'week_data' => $week_data, 'custom_data' => $custom_week_data]);
    }

    public function paymentStore($company_id, Request $request)
    {
        $rules = [];
        for ($day = 0; $day < 7; $day++) {
            $rules["payment_day_worker.$day"] = 'numeric';
            $rules["payment_day_supervisor.$day"] = 'numeric';
            $rules["payment_night_worker.$day"] = 'numeric';
            $rules["payment_night_supervisor.$day"] = 'numeric';
        }
        $this->validate($request, $rules);

        echo "<h1>Saving data</h1>";

        if(WeekPayment::where('company_id', $company_id)->count() == 0) {
            $data = [];

            for ($i = 0; $i < 7; $i++) {
                array_push($data, [
                    'company_id' => $company_id,
                    'day' => $i,
                    'period' => 'day',
                    'type' => 'worker',
                    'amount' => $request->input('payment_day_worker.' . $i) == 0 ? null : $request->input('payment_day_worker.' . $i),
                ]);
                array_push($data, [
                    'company_id' => $company_id,
                    'day' => $i,
                    'period' => 'day',
                    'type' => 'supervisor',
                    'amount' => $request->input('payment_day_supervisor.' . $i) == 0 ? null : $request->input('payment_day_supervisor.' . $i),
                ]);
                array_push($data, [
                    'company_id' => $company_id,
                    'day' => $i,
                    'period' => 'night',
                    'type' => 'worker',
                    'amount' => $request->input('payment_night_worker.' . $i) == 0 ? null : $request->input('payment_night_worker.' . $i),
                ]);
                array_push($data, [
                    'company_id' => $company_id,
                    'day' => $i,
                    'period' => 'night',
                    'type' => 'supervisor',
                    'amount' => $request->input('payment_night_supervisor.' . $i) == 0 ? null : $request->input('payment_night_supervisor.' . $i),
                ]);
            }

            WeekPayment::insert($data);
        }else {
            for ($i = 0; $i < 7; $i++) {
                WeekPayment::where('company_id', $company_id)->where('day', $i)->where('period', 'day')->where('type', 'worker')->update(['amount' => $request->input('payment_day_worker.' . $i) == 0 ? null : $request->input('payment_day_worker.' . $i)]);
                WeekPayment::where('company_id', $company_id)->where('day', $i)->where('period', 'day')->where('type', 'supervisor')->update(['amount' => $request->input('payment_day_supervisor.' . $i) == 0 ? null : $request->input('payment_day_supervisor.' . $i)]);
                WeekPayment::where('company_id', $company_id)->where('day', $i)->where('period', 'night')->where('type', 'worker')->update(['amount' => $request->input('payment_night_worker.' . $i) == 0 ? null : $request->input('payment_night_worker.' . $i)]);
                WeekPayment::where('company_id', $company_id)->where('day', $i)->where('period', 'night')->where('type', 'supervisor')->update(['amount' => $request->input('payment_night_supervisor.' . $i) == 0 ? null : $request->input('payment_night_supervisor.' . $i)]);
            }
        }

        return redirect('/companies/'.$company_id.'/payment')->with(['message' => 'The payment amounts are edited successfully.']);
    }

    public function paymentCustomStore($company_id, Request $request)
    {
        $this->validatesRequestErrorBag = 'custom';
        $this->validate($request, [
            'time_range' => ['regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)\s-\s[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{1,2}:[0-9]{2}\s(AM|PM)/'],
            'amount' => 'numeric'
        ]);
        list($start, $end) = explode(' - ', $request->input('time_range'));

        $payment = new CustomPayment();
        $payment->company_id = $company_id;
        $payment->time_start = Common::formatDateTimeForSQL($start);
        $payment->time_end = Common::formatDateTimeForSQL($end);
        $payment->amount = $request->input('amount');
        $payment->description = $request->input('description');
        $payment->save();

        return redirect('/companies/'.$company_id.'/payment#')->with(['custom_message' => 'The custom payment is added successfully.']);

    }

    public function paymentCustomDestroy($company_id, $payment_id)
    {
        CustomPayment::deletePayment($company_id, $payment_id);
        return redirect('/companies/'.$company_id.'/payment#')->with(['custom_message' => 'The custom payment is deleted successfully.']);
    }

    public function shiftsShow($company_id)
    {
        $company_name = Company::find($company_id)->name;
        $start = Common::formatTimeFromSQL(Company::find($company_id)->shift_day_start);
        $end = Common::formatTimeFromSQL(Company::find($company_id)->shift_night_start);

        return view('companies.shifts')->with(['company_name' => $company_name, 'shift_day_start' => $start, 'shift_night_start' => $end]);
    }

    public function shiftsUpdate($company_id, Request $request)
    {
        $this->validate($request, [
            'shift_day_start' => ['regex:/[0-9]{2}:[0-9]{2}\s(AM|PM)/'],
            'shift_night_start' => ['regex:/[0-9]{2}:[0-9]{2}\s(AM|PM)/']
        ]);

        $shift = Company::find($company_id);
        $shift->shift_day_start = Common::formatTimeForSQL($request->input('shift_day_start'));
        $shift->shift_night_start = Common::formatTimeForSQL($request->input('shift_night_start'));
        $shift->save();

        return redirect('/companies/'.$company_id.'/shifts#')->with(['message' => 'Shifts time is updated successfully.']);
    }
}

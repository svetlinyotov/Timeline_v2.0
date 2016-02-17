<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\ImageResize;
use App\Messages;
use App\Notification;
use App\PersonalInfo;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('auth.notWorker', ['except' => ['show', 'edit', 'update']]);

        Event::listen('auth.login', function($user) {
            $user->last_login = new \DateTime('now');

            $user->save();
        });
    }

    public function index()
    {
        $company_id = $this->company_id;

        if(Auth::user()->role == "supadmin")
            $data = User::with(['company' => function($query)
            {
                $query->select('companies.id', 'name');

            }])->get();
        else
            $data = User::with(['company' => function($query)
            {
                $query->select('companies.id', 'name');

            }])->whereHas('company', function ($q) use ($company_id) {
                $q->where('companies.id', '=', $company_id);
            })->get();
        return view('users.list')->with(['data' => $data]);
    }

    public function create()
    {
        $companies = Company::listAll();
        return view('users.new')->with(['companies' => $companies]);
    }

    public function store(Request $request)
    {
        if(Auth::user()->role != "supadmin" && $request->input('type') == "supadmin") abort(401);

        $rules = [
            'email' => 'required|email|unique:users',
            'type' => 'required',
            'names' => 'required',
            'coordinates' => 'required',
            'address' => 'required',
            'mobile' => 'required',
            'birth_date' => 'regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/',
            'avatar' => 'image|mimes:jpg,jpeg,bmp,png,gif,tiff',
            'cv' => 'mimes:doc,docx,ppt,pps,pptx,ppsx,xls,xlsx',
        ];

        if(Auth::user()->role == "supadmin") {$rules['company'] = 'required';}

        $this->validate($request, $rules);

        $avatar_file_name = null;
        $cv_file_name = null;

        if($request->file('avatar')) {
            $avatar_file_name = Common::randString(6).".".$request->file('avatar')->getClientOriginalExtension();
            Storage::put('avatars/' . $avatar_file_name, file_get_contents($request->file('avatar')->getRealPath()));

            ImageResize::load(storage_path("app") . '/avatars/' . $avatar_file_name);
            ImageResize::resizeToWidth(300);
            ImageResize::save();
        }
        if($request->file('cv')) {
            $cv_file_name = Common::randString(6).".".$request->file('cv')->getClientOriginalExtension();
            Storage::put('cv/' . $cv_file_name, file_get_contents($request->file('cv')->getRealPath()));
        }

        $password = Common::generateStrongPassword();

        if(Auth::user()->role == "supadmin")
            $company_id = $request->input('company');
        else
            $company_id = $this->company_id[0];

        $user = new User();
        $user->role = $request->input('type');
        $user->password = Hash::make($password);
        $user->email = $request->input('email');
        $user->save();

        $info = new PersonalInfo();
        $info->names = $request->input('names');
        $info->address = $request->input('address');
        $info->coordinates = $request->input('coordinates');
        $info->mobile = $request->input('mobile');
        $info->gender = $request->input('gender');
        $info->birth_date = $request->input('birth_date');
        $info->home_phone = $request->input('home_phone');
        $info->work_phone = $request->input('work_phone');
        $info->fax = $request->input('fax');
        $info->other = $request->input('other');
        $info->avatar = $avatar_file_name;
        $info->cv = $cv_file_name;

        $user->info()->save($info);

        Company::find($company_id)->users()->attach($user->id);

        Notification::add($user->id, 'USER_ADD_BY_ADMIN', ['admin_id'=>Auth::user()->id]);

        Mail::send('emails.newUserPassword', ['user' => $user, 'info' => $info, 'password' => $password, 'company' => Company::find($company_id)->pluck('name')], function ($m) use ($user) {
            $m->from('support@timeline.snsdevelop.com', 'TIMELINE');

            $m->to($user->email, $user->names)->subject('You have been added to TIMELINE');
        });

        return redirect('/users')->with(['message'=>"User added successfully. Generated password with further login information is sent to his/her email ({$request->input('email')})."]);

    }

    public function show($id = null)
    {
        if($id == null) $id = Auth::user()->id;
        $user = User::find($id);
        $notification_count = Notification::count($id);
        $notification_obj = Notification::read($id, true, 6);
        $notification_list = Notification::format($notification_obj);

        return view('users.view')->with(['user' => $user, 'notification_count' => $notification_count, 'notification_list' => $notification_list]);
    }

    public function showAllNotifications($id = null)
    {
        if($id == null) $id = Auth::user()->id;

        $notification_count = Notification::count($id);
        $notification_obj = Notification::read($id, false, 1000, 12);
        $notifications = Notification::format($notification_obj);
        $user = User::with('info')->where('id', $id)->first();

        return view('users.notifications')->with(['notifications' => $notifications, 'notifications_obj'=>$notification_obj, 'notification_count' => $notification_count, 'user'=>$user]);
    }

    public function edit($id = null)
    {
        if($id == null) $id = Auth::user()->id;
        $companies = Company::listAll();
        $user = User::find($id);
        return view('users.edit')->with(['companies' => $companies, 'user' => $user]);
    }

    public function update(Request $request, $id = null)
    {
        if($id == null) $id = Auth::user()->id;

        $rules = [
            'names' => 'required',
            'password' => 'min:5',
            'coordinates' => 'required',
            'address' => 'required',
            'mobile' => 'required',
            'birth_date' => 'regex:/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/',
            'avatar' => 'image|mimes:jpg,jpeg,bmp,png,gif,tiff',
            'cv' => 'mimes:doc,docx,ppt,pps,pptx,ppsx,xls,xlsx',
        ];

        $this->validate($request, $rules);

        $avatar_file_name = null;
        $cv_file_name = null;

        if($request->file('avatar')) {
            $avatar_file_name = Common::randString(6).".".$request->file('avatar')->getClientOriginalExtension();
            Storage::put('avatars/' . $avatar_file_name, file_get_contents($request->file('avatar')->getRealPath()));

            ImageResize::load(storage_path("app") . '/avatars/' . $avatar_file_name);
            ImageResize::resizeToWidth(300);
            ImageResize::save();
        }
        if($request->file('cv')) {
            $cv_file_name = Common::randString(6).".".$request->file('cv')->getClientOriginalExtension();
            Storage::put('cv/' . $cv_file_name, file_get_contents($request->file('cv')->getRealPath()));
        }

        $user = User::find($id);

        if($request->input('password') != null)
            $user->password = Hash::make($request->input('password'));
        $user->save();

        $info = PersonalInfo::where('user_id', $id)->first();
        $info->names = $request->input('names');
        $info->address = $request->input('address');
        $info->coordinates = $request->input('coordinates');
        $info->mobile = $request->input('mobile');
        $info->gender = $request->input('gender');
        $info->birth_date = $request->input('birth_date');
        $info->home_phone = $request->input('home_phone');
        $info->work_phone = $request->input('work_phone');
        $info->fax = $request->input('fax');
        $info->other = $request->input('other');
        if($avatar_file_name != null)
            $info->avatar = $avatar_file_name;
        if($cv_file_name != null)
            $info->cv = $cv_file_name;

        $info->save();

        if($id != Auth::user()->id)
            Notification::add($id, 'USER_UPDATE_BY_ADMIN', ['admin_id'=>Auth::user()->id]);

        if($request->input('password') != null) {
            Mail::send('emails.editUserPassword', [
                'user' => $user,
                'info' => $info,
                'password' => $request->input('password'),
                'company' => implode(', ', User::find($id)->company->pluck('name')->toArray())
            ], function ($m) use ($user) {
                $m->from('support@timeline.snsdevelop.com', 'TIMELINE');

                $m->to($user->email, $user->names)->subject('Your account on TIMELINE platform was edited');
            });

            return redirect('/users/' . $id)->with(['message' => "User was updated successfully. The new password was sent to his/her email ({$user->email})."]);
        }

        if(Auth::user()->role == "worker" || $id == Auth::user()->id)
            return redirect('/profile/')->with(['message' => "Data was updated successfully."]);

        return redirect('/users/' . $id)->with(['message' => "User was updated successfully."]);

    }

    public function destroy($id)
    {
        if(Auth::user()->id == $id) abort(200);
        $user = null;

        if(Auth::user()->role == "supadmin"){
            $user = User::find($id);
        }else if(Auth::user()->role == "admin"){
            $user = User::find($id);
            if(!in_array($this->company_id[0], $user->with(['company' => function($query) {$query->select('companies.id');}])->first()->company->pluck("id")->toArray())){
                abort(400);
            }
        }else{
            abort(401);
        }

        if(file_exists(storage_path("app") . 'avatar/'.$user->info->avatar))
            Storage::delete('avatar/'.$user->info->avatar);
        if(file_exists(storage_path("app") . 'cv/'.$user->info->cv))
            Storage::delete('cv/'.$user->info->cv);

        $user->delete();

        abort(200, "User is deleted successfully.");
    }

    public function unlinkCompany($user_id, $company_id){

        if(Auth::user()->role == "supadmin"){
            $user = User::find($user_id);
        }else if(Auth::user()->role == "admin"){
            $user = User::find($user_id);
            if($this->company_id[0] != $company_id){
                abort(400);
            }
        }else{
            abort(401);
        }

        if($user->role != "worker") abort(400, "Not allowed");

        $user->company()->detach($company_id);

        abort(200, "All done");
    }

    public function unlinkAllCompanies($user_id){

        if(Auth::user()->role == "supadmin"){
            $user = User::find($user_id);
        }else{
            abort(401);
        }
        if($user->role != "worker") abort(400, "Not allowed");

        $user->company()->detach();

        abort(200, "All done");
    }

    public function linkCompanyFrom($id)
    {
        $companies = User::notLinkedCompanies($id);
        $user = User::find($id);
        if($user->role != "worker") abort(400, "Not allowed");

        return view('users.link')->with(['user' => $user, 'companies' => $companies]);
    }

    public function linkCompany($id, Request $request)
    {
        if($request->input('company') != null) {
            if(is_array($request->input('company'))) {
                $company_ids = array_keys($request->input('company'));
            }else{
                $company_ids = $request->input('company');
            }
            $user = User::find($id);

            if($user->role != "worker") abort(400, "Not allowed");

            $user->company()->attach($company_ids);

            return redirect('/users')->with(['message' => "User is linked successfully."]);
        }
        return redirect('/users');
    }

    public function linkUser($company_id, Requests\LinkUserToCompanyRequest $request)
    {
        $email = $request->input("email");
        $user = User::where('email', $email)->first();
        $if_user_already_linked = (bool)User::with(['company' => function($query)
        {
            $query->select('companies.id');

        }])->whereHas('company', function ($q) use ($company_id) {
            $q->where('companies.id', '=', $company_id);
        })->where('users.email', '=', $email)->count();
        $if_admin = (bool)User::where('users.role', '!=', 'worker')->where('users.email', '=', $email)->count();

        if($email == Auth::user()->email) return redirect('/users#link_user')->withErrors(["You cannot link yourself."])->withInput(['email'=>$email]);
        if($if_user_already_linked) return redirect('/users#link_user')->withErrors(["$email is already linked to your company"])->withInput(['email'=>$email]);
        if($if_admin) return redirect('/users#link_user')->withErrors(["You are not allowed to link $email"])->withInput(['email'=>$email]);

        if(count($user) != 1){
            return redirect('/users/create?email='.$email)->with(['info' => "User $email does not exists in our records. You have to create new one."]);
        }

        $user->company()->attach($company_id);
        return redirect('/users')->with(['message' => "User $email is linked successfully."]);
    }

    public function showMessages($id = null)
    {
        if($id == null) $id = Auth::user()->id;
        $messages = Messages::getWithUser($id);

        return view('users.messages')->with(['messages' => $messages]);
    }

    public function readMessage($id)
    {
        //TODO: Return view with detailed info about message
    }

}

<?php

namespace App\Http\Controllers;

use App\Common;
use App\Company;
use App\ImageResize;
use App\Notification;
use App\PersonalInfo;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        if(Auth::user()->role == "supadmin")
            $data = User::all();
        else
            $data = User::where('company_id', Auth::user()->company_id)->get();
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

        $user = new User();
        if(Auth::user()->role == "supadmin")
            $user->company_id = $request->input('company');
        else
            $user->company_id = Auth::user()->company_id;
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

        Notification::add($user->id, 'USER_ADD_BY_ADMIN', ['admin_id'=>Auth::user()->id]);

        Mail::send('emails.newUserPassword', ['user' => $user, 'info' => $info, 'password' => $password, 'company' => Company::find($user->company_id)->pluck('name')], function ($m) use ($user) {
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
        $notification_list = Notification::read($id, false);

        return view('users.view')->with(['user' => $user, 'notification_count' => $notification_count, 'notification_list' => $notification_list]);
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
        if(Auth::user()->role != "supadmin" && $request->input('type') == "supadmin") abort(401);

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

        if(Auth::user()->role == "supadmin") $rules['company'] = 'required';
        if( $request->input('type') == "supadmin") $rules['company'] = '';
        if(Auth::user()->role != "worker") $rules['type'] = 'required';

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
        if(Auth::user()->role == "supadmin")
            $user->company_id = $request->has('company') ? $request->input('company') : null;
        if(Auth::user()->role != "worker")
            $user->role = $request->input('type');
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
            Mail::send('emails.editUserPassword', ['user' => $user, 'info' => $info, 'password' => $request->input('password'), 'company' => Company::find($user->company_id)->pluck('name')], function ($m) use ($user) {
                $m->from('support@timeline.snsdevelop.com', 'TIMELINE');

                $m->to($user->email, $user->names)->subject('Your account on TIMELINE platform was edited');
            });

            return redirect('/users/' . $id)->with(['message' => "User was updated successfully. The new password was sent to his/her email ({$user->email})."]);
        }

        if(Auth::user()->role == "worker")
            return redirect('/profile/')->with(['message' => "User was updated successfully."]);

        return redirect('/users/' . $id)->with(['message' => "User was updated successfully."]);

    }

    public function destroy($id)
    {
        if(Auth::user()->id == $id) return redirect()->back();
        $user = null;

        if(Auth::user()->role == "supadmin"){
            $user = User::find($id);
        }else if(Auth::user()->role == "admin"){
            $user = User::where('company_id', Auth::user()->comapny_id);
        }else{
            abort(401);
        }

        if(file_exists(storage_path("app") . 'avatar/'.$user->info->avatar))
            Storage::delete('avatar/'.$user->info->avatar);
        if(file_exists(storage_path("app") . 'cv/'.$user->info->cv))
            Storage::delete('cv/'.$user->info->cv);

        $user->delete();

        return redirect()->back()->with(['message' => 'User is deleted successfully.']);
    }
}

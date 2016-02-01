<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $company_id = null;

    public function __construct(Request $request)
    {
        $timezone = 'UTC';

        if(Auth::user()->company != null) $timezone = "";//Auth::user()->company->timezone;

        if(Auth::check()) {
            Config::set('app.timezone', $timezone);

            $this->company_id = Auth::user()->with(['company' => function($query)
            {
                $query->select('companies.id');

            }])->first()->company->pluck("id")->toArray();

        }

        if($request->get('noti')) {
            Notification::markAsRead($request->get('noti'));
        }
    }
}

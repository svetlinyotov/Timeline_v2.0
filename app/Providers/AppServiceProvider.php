<?php

namespace App\Providers;

use App\Notification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts.master', function($view)
        {
            $notification_obj = Notification::read(Auth::user()->id, true, 3);
            $notification_list = Notification::format($notification_obj);

            $view->with('user_notification_count', Notification::count(Auth::user()->id));
            $view->with('user_notification_list', $notification_list);
        });

        view()->composer('users.list', function($view){
            $view->with('company_id', User::where('id', Auth::user()->id)->with(['company' => function($q){$q->select('companies.id', 'companies.name');}])->first()->company->pluck('id')->toArray()[0]??"");
            $view->with('company_name', User::where('id', Auth::user()->id)->with(['company' => function($q){$q->select('companies.id', 'companies.name');}])->first()->company->pluck('name')->toArray()[0]??"");
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

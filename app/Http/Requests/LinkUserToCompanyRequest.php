<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class LinkUserToCompanyRequest extends Request
{
    protected $redirect = '/users#link_user';

    public function authorize(\Illuminate\Http\Request $request)
    {
        list($comp, $company_id, $link) = explode('/', $request->path());
        if (User::where('id', Auth::user()->id)->with(['company' => function($q){$q->select('companies.id', 'companies.name');}])->first()->company->pluck('id')->toArray()[0] != $company_id)
            return false;
        else
            return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email'
        ];
    }
}

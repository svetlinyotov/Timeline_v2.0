<?php

namespace App\Http\Controllers;



class AvailabilityController extends Controller
{
    public function index()
    {
        return view("availability.list");
    }

    public function googleList()
    {
        return view("availability.googleList");
    }


}
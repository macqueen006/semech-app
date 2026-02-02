<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index()
    {
        return view('admin.subscribers.index');
    }
    public function show()
    {
        return view('admin.subscribers.show');
    }
    public function destory()
    {

    }
}

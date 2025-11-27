<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class HomeController extends Controller
{
    protected $userService;


    public function __construct(UserService $userService) {

        $this->userService = $userService;

    }

    public function index()
    {
        return view('home');
    }

    public function news()
    {
        return view('news');
    }

    public function messages()
    {
        return view('messages');
    }

    public function phpinfo()
    {
        phpinfo();
    }

}

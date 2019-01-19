<?php

namespace App\Controllers;

use App\Kernel\Controller;

class HomeController extends Controller
{
	public function index()
	{
		return response()->create('Welcome...');
	}
}
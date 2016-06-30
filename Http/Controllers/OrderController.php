<?php namespace App\Http\Controllers;
use Session,Request,View,Redirect;
use DB;
use App\Restaurent,App\helpers;
use App\Area,App\Product;
class OrderController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Order Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "Order Product" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */		
	public function createOrder(){
		$inputs = Request::all();
		$cart = Session::get('cart');	
		pr($cart);
			echo 'Hello'.__FILE__;
	}

}

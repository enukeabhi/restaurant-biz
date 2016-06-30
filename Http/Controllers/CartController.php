<?php namespace App\Http\Controllers;
use Session,Request,View,Redirect;
use DB;
use App\Restaurent,App\helpers;
use App\Area,App\Product;
class CartController extends Controller {

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
	protected function output()
	{
		$this->data = empty($this->data) ? null : $this->data;
		return array(
			'status' => $this->status, 			
			'data' => $this->data
		);
	}
	public function addtocart(){		
		try{
			$inputs = Request::all();		
			$restaurantproduct = Product::lang()->where('id',$inputs['prodid'])->first();	
			if($restaurantproduct){
				Session::put('cart.'.$inputs['prodid'].'.name',$restaurantproduct['name']);				
				Session::put('cart.'.$inputs['prodid'].'.quantity',$inputs['quantity']);				
				Session::put('cart.'.$inputs['prodid'].'.product_type',$restaurantproduct['product_type']);				
				Session::put('cart.'.$inputs['prodid'].'.restaurant_id',$restaurantproduct['restaurant_id']);				
				Session::put('cart.'.$inputs['prodid'].'.description',$restaurantproduct['description']);				
				Session::put('cart.'.$inputs['prodid'].'.cost',$restaurantproduct['cost']);
				Session::put('cart.'.$inputs['prodid'].'.field',$inputs['field']);
				if($inputs['quantity'] < 1){					
					Session::pull('cart.'.$inputs['prodid']);			
				}
				$this->status = 'success';	
				$this->data = Session::get('cart.'.$inputs['prodid']);							
			}else{
				$this->status = 'fail';
			}						
		}catch(\Exception $e){
			$this->status =$e->getMessage();
		}
		return $this->output();
	}
}

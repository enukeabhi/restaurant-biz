<?php namespace App;
use Illuminate\Database\Eloquent\Model;


class CartDb extends AppModel
{
	protected $table = 'cart_db';
	public $fillable = ['userid','cart'];
	
	public function write($cart, $conditioninput) {			
		$cart_token	=	empty($conditioninput['cart_token'])?"":$conditioninput['cart_token'];
		if($cart_token == '')
		{	$cart_token 		= 	str_random(30);
			$this->user_id		=	empty($inputs['userid'])?"":$userid;
			$this->cart_token	=	$cart_token;
			$this->cart			=	serialize($cart);
			$this->save();
		}
		else
		{
			$conditions	=	$this->makeCondition($conditioninput);
			self::where($conditions)->update(['cart' => serialize($cart)]);
		}
		return array("cart_token"=>$cart_token);
	}
	public function read($input) {
		$conditions	=	$this->makeCondition($input);
		$CartDb	=	self::where($conditions)->get()->toArray();
		return	empty($CartDb['0']['cart'])?array():unserialize($CartDb['0']['cart']);
	}
	
	public function clearCart($input){
		$conditions	=	$this->makeCondition($input);
		self::where($conditions)->delete();
	}
	private function makeCondition($inputs)
	{
		$conditions=array();
		if(!(empty($inputs['userid']) ||	$inputs['userid']==""))
			$conditions['user_id']=$inputs['userid'];
		if(!(empty($inputs['cart_token']) ||	$inputs['cart_token']==""))
			$conditions['cart_token']=$inputs['cart_token'];
		return $conditions; 
	}
	
}

<?php namespace App;
use DB;
use Session;
class Cart{
	protected $handler;
	protected $status;
	
	
	
	
	
	public function __construct($handler = 'App\CartSession') { //App\SessionCart and App\DbCart	
		$this->handler = new $handler();
	}

	public function addtocart($inputs){					
		try{			
			$optionItemsname ='';
			$restaurantproduct = \App\Product::lang()->where('id',$inputs['prodid'])->first();					
			$itemaddond=array();
			if(isset($inputs['itemaddond'])){
				$itemaddond=$inputs['itemaddond'];
				$optionItems = OptionItem::lang()->whereIn('id', $inputs['itemaddond'])->get();						
				foreach($optionItems as $optionItem){
					$optionItemsname .= $optionItem->item_name."***";
				}
			}
			if($restaurantproduct){						
				//write data to handler										
				$this->status = $this->handler->write([
					'prodid' => $inputs['prodid'],
					'name' => $restaurantproduct['name'],
					'quantity' => $inputs['quantity'],
					'product_type' => $restaurantproduct['product_type'],
					'restaurant_id' => $restaurantproduct['restaurant_id'],
					'description' => $restaurantproduct['description'],
					'cost' => $restaurantproduct['cost'],
					'totalCost' => $this->getTotalItemPrice($restaurantproduct,$inputs['quantity'],$itemaddond),
					'field' => $inputs['field'],							
					'optionItem' => $optionItemsname,
					'itemaddond'=>$itemaddond,
				]);				
			}else{				
				$this->status = 'fail';
			}
			return $this->status === true ? 'success' : 'fail';
		}catch(\Exception $e){
			echo $e->getMessage();die;
			$this->status = $e->getMessage();
		}
	}
	
	public function getData() {			
		$data = $this->handler->read();		
		return $data ? $data : [];
	}
	public function getSubtotal() {	
		$subtotal = 0;		
		$datas =  $this->getData();
		foreach($datas as $data){
			//$subtotal = $subtotal+($data['cost']*$data['quantity']);
			$subtotal += $data['totalCost'];
		}
		return $subtotal;
	}
	public function getTotal() {	
		$total = 0;			
		$deliverycharge = $this->deliveryCharges();
		return ($total+$this->getSubtotal())+$deliverycharge;
	}
	public function getTotalItemPrice($restaurantproduct,$quantity,$itemaddond=array()) {						
		$ProductPrice = DB::table('product_options')->select('price')->whereIn('option_item_id',$itemaddond)->get();
		$total=0;
		foreach($ProductPrice as $key=>$val)
			$total +=	$val->price;
		return ($restaurantproduct['cost']+$total)*$quantity;	
	}
	public function deliveryCharges() {				
		return 0;		
	}
	public function clearcart(){
		$this->handler->clearcart();	
		}	
}
?>

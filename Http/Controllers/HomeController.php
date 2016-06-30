<?php namespace App\Http\Controllers;
use Session;
use Illuminate\Http\Request;
use App\User;
use Validator;
use Auth;
use DB;
use App\Country,App\UserAddress;;
use Illuminate\Contracts\Auth\Registrar;
use Input,App\City;
use App\Area,App\State;
class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
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
	public function __construct(Registrar $registrar)
	{					
		$this->registrar = $registrar;		
	}
	public function index()
	{	
		return view('home.home');
	}
	public function registrationSuccessful()
	{
		return view('auth/registration_successful');
	}
	public function updateProfile(Request $request)
	{	
		if ( !Auth::check()){
			return back();		    
		}						
		$rules = User::rulesUpdateWeb($request->input('id'));						
		$this->validate($request, $rules);		
		$currentUser = Auth::user();
		$currentUser->fill([$request->except(array(
				'contact_number','newsletter','first_name','last_name','countrycode','email'
				)),
				'contact_number' =>$request->input('contact_number'),
				'first_name' =>$request->input('first_name'),
				'last_name' =>$request->input('last_name'),
				'countrycode' =>$request->input('countrycode'),
				'email' =>$request->input('email'),
				'newsletter' => ($request->input('newsletter') ? 1 : 0)])->save();				
		return redirect('editprofile')->with(['status'=>'Profile edited successfully.' ]);
	}
	public function editProfile(){	
		if ( !Auth::check()){
			return back();		    
		}		
		$currentUser = Auth::user();		
		return view('editprofile')->with(['currentUser'=>$currentUser]);
	}
	public function changePassword(){		
		if ( !Auth::check()){
			return back();		    
		}
		$currentUser = Auth::user();
		return view('changePassword')->with(['currentUser'=>$currentUser]);
	}
	public function updatepassword(Request $request){				
		if ( !Auth::check()){
			return back();		    
		}		
		$currentUser = Auth::user();		
		$rules = User::rulesUpdatePassword($currentUser->id);							
		$this->validate($request, $rules);							
		$currentUser->update(['password' => bcrypt($request->input('new_password'))]);				
		return redirect('editprofile')->with(['status'=>'Password edited successfully.' ]);	    	   
	}
	public function addressBook(){
		if ( !Auth::check()){
			return back();		    
		}
		$detail = array();			
		$userid = Auth::user()->id;
		$userAddress = DB::table('user_address')->where('user_id',$userid)->get();			
		$detail['areas']	=Area::select('*')->get();
		$detail['states']	=State::select('*')->get();
		$detail['cities']	=City::select('*')->get();	
		$detail['useraddress']	=$userAddress;			
		return view('addressBook')->with($detail);				
	}
	public function updateAddressBook(Request $request){
		if ( !Auth::check()){
			return back();		    
		}		
		$detail 			= array();	
		$addressid 			= $request->input('id');		
		$userAddress 		= DB::table('user_address')->where('id',$addressid)->first();	
		$Areas 				= Area::select('name')->where('city_id',$userAddress->city)->first();					
		$states 			= State::select('state_name')->where('state_id',$userAddress->state)->first();
		$cities 			= City::select('name')->where('id',$userAddress->city)->first();					
		$detail['addressid']=$userAddress->id;
		$detail['user_id'] 	=$userAddress->user_id;
		$detail['first_address'] =$request->input('first_address');
		$detail['second_address'] =$request->input('second_address');
		$detail['country'] 	=$request->input('country');
		$detail['stateId'] 	=$request->input('state');
		$detail['cityId'] 	=$request->input('city');
		$detail['areaId'] 	=$request->input('area');
		$detail['zip'] 		=$request->input('zip');
		$detail['countries']=Country::select('*')->get();
		$detail['areas']	=Area::select('*')->get();
		$detail['states']	=State::select('*')->get();
		$detail['cities']	=City::select('*')->get();	
		$validator 			= $this->registrar->addnewaddressvalidator($request->all());	
		if ($validator->fails()){	
			$detail['errors'] = $validator->errors();				
			return view('cart.updateaddress')->with($detail);				
		}				
		$country = Country::select('country_id')->where('country_code',$request->input('country'))->first();							
		if(!is_numeric($request->input('state'))){				
			$stateId = State::insertGetId(['state_name'=>$request->input('state'),'country_id'=>$country->country_id]);			
			$cityId = City::insertGetId(['name'=>$request->input('city'),'state_id'=>$stateId]);			
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$cityId]);										
			$userAddressId = $request->input('id');				
			UserAddress::where('id', $userAddressId)->update($request->except(['_token' , 'state','city','area']));	   						
			UserAddress::where('id', $userAddressId)->update(['state'=>$stateId,'city'=>$cityId,'area'=>$AreaId]);			
			return view('cart.updateaddrsuccess')->with('status','Address edited Successfully.');	    
			die;
		}		
		if(!is_numeric($request->input('city'))){												
			$cityId = City::insertGetId(['name'=>$request->input('city'),'state_id'=>$request->input('state')]);			
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$cityId]);			
			$userAddressId = $request->input('id');	
			UserAddress::where('id', $userAddressId)->update($request->except(['_token','city','area']));	   					
			UserAddress::where('id', $userAddressId)->update(['city'=>$cityId,'area'=>$AreaId]);  
			return view('cart.updateaddrsuccess')->with('status','Address edited Successfully.');	    
			die;
		}
		if(!is_numeric($request->input('area'))){															
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$request->input('city')]);			
			$userAddressId = $request->input('id');	
			UserAddress::where('id', $userAddressId)->update($request->except(['_token','area']));	   						
			UserAddress::where('id', $userAddressId)->update(['area'=>$AreaId]);
			return view('cart.updateaddrsuccess')->with('status','Address edited Successfully.');	    
			die;
		}
		//========
		DB::table('user_address')->where('id', $request->input('id'))->where('user_id', $request->input('user_id'))->update($request->except('_token'));		
		return view('cart.updateaddrsuccess')->with('status','Address edited Successfully.');	    			    	   
	}
	public function deleteAddress(){
		if ( !Auth::check()){
			return back();	    	   
		}
		$input = \Request::all();			
		$deleteMessage = DB::table('user_address')->where('id', $input['addrid'])->where('user_id', $input['uid'])->delete();		
		if($deleteMessage){
			//return back()->with(['status'=>'Address deleted successfully.' ]);	    	   	
			return 1;
		}else{
			return 0;
		}				
	}
	public function loadNewAddressBook(Request $request){
		$countries = Country::select('*')->get();
		return view('cart.addnewaddress',['countries'=>$countries]);				
	}
	public function loaduUpdatedAddressBook(Request $request){
		$addressid = $request->input('addr');		
		$userAddress = DB::table('user_address')->where('id',$addressid)->first();
		$Area = Area::select('name')->where('city_id',$userAddress->city)->first();					
		$state = State::select('state_name')->where('state_id',$userAddress->state)->first();
		$city = City::select('name')->where('id',$userAddress->city)->first();		
		$detail =array();		
		$detail['addressid'] =$userAddress->id;
		$detail['user_id'] =$userAddress->user_id;
		$detail['first_address'] =$userAddress->first_address;
		$detail['second_address'] =$userAddress->second_address;
		$detail['country'] =$userAddress->country;
		$detail['stateId'] =$userAddress->state;
		$detail['cityId'] =$userAddress->city;
		$detail['areaId'] =$userAddress->area;
		$detail['state'] =$state['state_name'];
		$detail['city'] =$city['name'];
		$detail['area'] =$Area['name'];
		$detail['zip'] =$userAddress->zip;
		$country = Country::select('country_id')->where('country_code',$userAddress->country)->first();							
		$detail['areas']	=Area::select('*')->where('city_id',$userAddress->city)->get();
		$detail['states']	=State::select('*')->where('country_id',$country->country_id)->get();
		$detail['cities']	=City::select('*')->where('state_id',$userAddress->state)->get();	
		$detail['countries'] =Country::select('*')->get();		
		return view('cart.updateaddress',$detail);				
	}
					
	public function addNewAddressBook(Request $request){

		if ( !Auth::check()){
			return back();		    
		}			
		$validator = $this->registrar->addnewaddressvalidator($request->all());				
		if ($validator->fails()){
			
			$countries = Country::select('*')->get();
			return view('cart.addnewaddress')->with(['countries'=>$countries, 'errors'=> $validator->errors()]);				
		}	
		//die;	

		$country = Country::select('country_id')->where('country_code',$request->input('country'))->first();				
		if(!is_numeric($request->input('state'))){

			$stateId = State::insertGetId(['state_name'=>$request->input('state'),'country_id'=>$country->country_id]);			
			$cityId = City::insertGetId(['name'=>$request->input('city'),'state_id'=>$stateId]);			
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$cityId]);											
			UserAddress::insert([$request->except(['_token' , 'state','city','area'])]);	   			
			$userAddressId = DB::getPdo()->lastInsertId();
			UserAddress::where('id', $userAddressId)->update(['state'=>$stateId,'city'=>$cityId,'area'=>$AreaId]);			
			return view('cart.addaddrsuccess')->with('status','Address Added Successfully.');	    
			die;
		}		
		if(!is_numeric($request->input('city'))){												
			$cityId = City::insertGetId(['name'=>$request->input('city'),'state_id'=>$request->input('state')]);			
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$cityId]);			
			UserAddress::insert([$request->except(['_token','city','area'])]);	   			
			$userAddressId = DB::getPdo()->lastInsertId();
			UserAddress::where('id', $userAddressId)->update(['city'=>$cityId,'area'=>$AreaId]);  
			return view('cart.addaddrsuccess')->with('status','Address Added Successfully.');	    
			die;
		}
		if(!is_numeric($request->input('area'))){															
			$AreaId = Area::insertGetId(['name'=>$request->input('area'),'city_id'=>$request->input('city')]);			
			UserAddress::insert([$request->except(['_token','area'])]);	   			
			$userAddressId = DB::getPdo()->lastInsertId();
			UserAddress::where('id', $userAddressId)->update(['area'=>$AreaId]);
			/*
			DB::table('user_address')->insert([
				'user_id'=>$request->input('user_id'),
				'first_address'=>$request->input('first_address'),
				'second_address'=>$request->input('second_address'),
				'country'=>$request->input('country'),
				'state'=>$request->input('state'),
				'city'=>$request->input('city'),				
				'area'=>$AreaId,
				'zip'=>$request->input('zip'),
				]);	 */ 
			return view('cart.addaddrsuccess')->with('status','Address Added Successfully.');	    
			die;
		}									  	
	    DB::table('user_address')->insert([$request->except('_token')]);	    
	    return view('cart.addaddrsuccess')->with('status','Address Added Successfully.');	    
	}
	public function setLang()
	{
		  session(['lang' => $_GET['lang']]);
		  return view('home.home');		  
	}
	public function help()
	{		
		return view('home.help');
	}
}

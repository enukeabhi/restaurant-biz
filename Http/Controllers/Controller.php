<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;
	public function apiResponse($data=[],$error = false){
		$defaultResponseArr = [];
		switch($error){
			case true:
			case '1':
			case 'true':
				$defaultResponseArr['success'] = false;				
				break;
			default:
				$defaultResponseArr['success'] = true;

		}
		return \Response::json(array_merge($defaultResponseArr,$data));
		
	}
	

}

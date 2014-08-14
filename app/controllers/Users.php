<?php

namespace apibvw\Controller;

use \BaseController;
use \Input;
use \Redirect;
use \Validator;
use \View;
use \Lang;
use \Response;
use \URL;


/*
 * Macro for the BioVoiceWeb Api responses
 */
Response::macro('v1apijson', function ($http_code, $error, $data, $message = null) {
	$response_defaults = array('http_code' => $http_code, 'error' => $error);
	if ($message) {
		$response_defaults['message'] = $message;
	}
	$json_data = array_merge($response_defaults,(Array)$data);
	return Response::json($json_data, $http_code);
});

use \apibvw\Model\User as ModelUser;

class Users extends BaseController {
	
	public function index() {
		$user = ModelUser::get();
		return Response::v1apijson(200, false, array('users'=>$user->toArray()));
	}
	
	public function show($username) {
		$user = ModelUser::find($username);
		if (!$user) {
			return Response::v1apijson(404,true,null,"User not foud");
		}
		return Response::v1apijson(200,false,array('user'=>$user->toArray()));
	}
	
	public function store() {
		$data = Input::all();
		
		$rules = array (
			'first_name' => 'required|max:30',
			'surname' => 'max:100',
			'username' => 'required|unique:user,username|max:6|min:2',
			'email' => 'required|email|unique:user,email|max:50',
		);
		
		$validator = Validator::make($data, $rules);
		
		if ($validator->passes()) {
			$usuario = new ModelUser;
			$usuario->first_name = $data['first_name'];
			$usuario->surname = $data['surname'];
			$usuario->username = $data['username'];
			$usuario->email = $data['email'];
			$usuario->save();
			$usuario = ModelUser::find($data['username']);
			return Response::v1apijson(201,false,array('users'=>$usuario->toArray()))
					->header('Location',URL::action('apibvw\Controller\Users@show',$data['username']));
		}

		return Response::v1apijson(400,true,array('messages' => $validator->messages()->toArray()),
							"Unable to create user with provided data. Check errors and try again.");
	}
	
	public function destroy($username) {
		$user = ModelUser::find($username);
		if (!$user) {
			return Response::v1apijson(404,true,null,"User not foud");
		}
		$user->delete();
		
		return Response::v1apijson(200,false,null,"User deleted");
	}
	
	public function missingMethod($paramenters = array()) {
		return Response::json(array(),405);
	}
	
}
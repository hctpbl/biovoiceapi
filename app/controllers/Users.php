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

use \apibvw\Model\User as ModelUser;

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


/**
 * 
 * User controller to handle CRUD actions over users in database using
 * HTTP verbs.
 * 
 * For a clearer documentation of the API, client-oriented, please visit 
 * http://docs.biovoiceapi.apiary.io/
 * 
 * The base URL for this actions is v1/users
 * 
 * @author Héctor Pablos
 * @see http://docs.biovoiceapi.apiary.io/
 */
class Users extends BaseController {
	
	/**
	 * 
	 * Returns a JSON with every user in the database.
	 * 
	 * URL: GET v1/users
	 * 
	 */
	public function index() {
		$user = ModelUser::get();
		return Response::v1apijson(200, false, array('users'=>$user->toArray()));
	}
	
	/**
	 * 
	 * Returns JSON ith de data of the user identified by $username
	 * 
	 * URL: GET v1/users/{username}
	 * 
	 * @param string $username
	 */
	public function show($username) {
		$user = ModelUser::find($username);
		if (!$user) {
			return Response::v1apijson(404,true,null,"User not foud");
		}
		return Response::v1apijson(200,false,array('user'=>$user->toArray()));
	}
	
	/**
	 * 
	 * Registers a user in the database
	 * 
	 * URL: POST v1/users
	 * 
	 */
	public function store() {
		$data = Input::all();
		
		$rules = array (
			'first_name' => 'required|alpha_spaces|max:30',
			'surname' => 'required|alpha_spaces|max:100',
			'username' => 'required|alpha_dash|unique:user,username|max:6|min:2',
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
			return Response::v1apijson(201,false,array('user'=>$usuario->toArray(), 'messages'=>null))
					->header('Location',URL::action('apibvw\Controller\Users@show',$data['username']),
							"User successfully created.");
		}

		return Response::v1apijson(400,true,array('messages' => $validator->messages()->toArray(), 'user'=>null),
							"Unable to create user with provided data. Check errors and try again.");
	}
	
	/**
	 * 
	 * Deletes the user identified by $username from de database
	 * 
	 * @param string $username
	 */
	public function destroy($username) {
		$user = ModelUser::find($username);
		if (!$user) {
			return Response::v1apijson(404,true,null,"User not foud");
		}
		$user->delete();
		
		return Response::v1apijson(200,false,null,"User deleted");
	}
	
	/**
	 * 
	 * Makes every other HTTP request to this base address return a 405 response
	 * 
	 * @param unknown $paramenters
	 */
	public function missingMethod($paramenters = array()) {
		return Response::json(array(),405);
	}
	
}
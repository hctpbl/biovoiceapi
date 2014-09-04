<?php

namespace apibvw\Model;

use \Eloquent;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * 
 * Model class for a User. It stores its username, first name, surname, email and
 * time and date of his registry
 * 
 * @author Héctor Pablos
 *
 */
class User extends Eloquent {

	/**
	 * The database table used by the model. In this case, 'user'.
	 *
	 * @var string
	 */
	protected $table = 'user';
	
	/**
	 * The primary key for the table, 'username' in our case
	 * 
	 * @var string
	 */
	protected $primaryKey = 'username';
	
	/**
	 * For BVW, we use our own timestamp, not the ones
	 * provided by the eloquent class
	 * 
	 * @var boolean
	 */
	public $timestamps = false;
	
	/**
	 * Get the sessions of a user
	 */
	public function sessions() {
		return $this->hasMany('Session');
	}

}
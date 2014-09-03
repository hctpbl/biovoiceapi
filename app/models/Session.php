<?php

namespace apibvw\Model;

use \Eloquent;

class Session extends Eloquent {

	/**
	 * The database table used by the model. In this case, 'session'.
	 *
	 * @var string
	 */
	protected $table = 'session';
	
	/**
	 * For BVW, we use our own timestamp, not the ones
	 * provided by the eloquent class
	 * 
	 * @var boolean
	 */
	public $timestamps = false;
}
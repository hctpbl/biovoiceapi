<?php

use \apibvw\Model\User as User;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('UserTableSeeder');
	}

}

class UserTableSeeder extends Seeder {
	
	private $field_username = 'username';
	private $field_first_name = 'first_name';
	private $field_surname = 'surname';
	private $field_email = 'email';
	
	private $username = 'testus';
	private $first_name = 'Test';
	private $surname = 'User';
	private $email = 'test@user.com';
	
	public function run() {
		DB::table('user')->delete();
		
		User::create(array(
			$this->field_username=>$this->username,
			$this->field_first_name=>$this->first_name,
			$this->field_surname=>$this->surname,
			$this->field_email=>$this->email
		));
	}
}

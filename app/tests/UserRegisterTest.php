<?php

class UserRegisterTest extends TestCase {
	
	private $field_username = 'username';
	private $field_first_name = 'first_name';
	private $field_surname = 'surname';
	private $field_email = 'email';
	
	private $username = 'testus';
	private $first_name = 'Test';
	private $surname = 'User';
	private $email = 'test@user.com';
	
	private function getUserData($username, $first_name, $surname, $email) {
		return array(
			$this->field_username=>$username,
			$this->field_first_name=>$first_name,
			$this->field_surname=>$surname,
			$this->field_email=>$email
		);
	}
	
	/**
	 * T-API01
	 * Tests a successful registration in the system
	 */
	public function testRegisterSucessful() {
		$data = $this->getUserData(
			$this->username,
			$this->first_name,
			$this->surname,
			$this->email
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(201);
		$this->assertEquals($resp_data->user->username, $this->username);
		$this->assertEquals($resp_data->user->first_name, $this->first_name);
		$this->assertEquals($resp_data->user->surname, $this->surname);
		$this->assertEquals($resp_data->user->email, $this->email);
	}
	
	/**
	 * T-API02
	 * Tests a registration in the system with a blank username
	 */
	public function testRegisterBlankUsername() {
		$data = $this->getUserData(
			'    ',
			$this->first_name,
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		// Code returned when error is always 400
		$this->assertResponseStatus(400);
		// Variable error should be set to true
		$this->assertTrue($resp_data->error);
		// The test should cover only one partition, so only
		// one error message should exist in the response
		$this->assertEquals(count($resp_data->messages), 1);
	}
	
	/**
	 * T-API03
	 * Tests a registration in the system with a username of less than two
	 * characters
	 */
	public function testRegisterSmallUsername() {
		$data = $this->getUserData(
			't',						// 1 char
			$this->first_name,
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
		$this->assertEquals(count($resp_data->messages), 1);
	}
	
	/**
	 * T-API04
	 * Tests a registration in the system with a username of more than six
	 * characters
	 */
	public function testRegisterLongUsername() {
		$data = $this->getUserData(
			'testusr',					// 7 chars
			$this->first_name,
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
		$this->assertEquals(count($resp_data->messages), 1);
	}
	
	/**
	 * T-API05
	 * Tests a registration in the system with a username already
	 * registered
	 */
	public function testRegisterUsedUsername() {
		$data = $this->getUserData(
			$this->username,			// Already registered
			$this->first_name,
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();

		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
		$this->assertEquals(count($resp_data->messages), 1);
	}
	
	/**
	 * T-API06
	 * Tests a registration in the system with a username string
	 * with different characters than numbers, letters, dashes
	 * or underscors
	 */
	public function testRegisterInvalidUsername() {
		$data = $this->getUserData(
			'12a-_#',
			$this->first_name,
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API07
	 * Tests a registration in the system with a blank first name
	 */
	public function testRegisterBlankFirstName() {
		$data = $this->getUserData(
			'othrus',
			'     ',
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API08
	 * Tests a registration in the system with a more than
	 * 30 chars first name
	 */
	public function testRegisterLongFirstName() {
		$data = $this->getUserData(
			'othrus',
			str_repeat('abcde', 6)+ 'a', // 31 chars
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API09
	 * Tests a registration in the system with an invalid
	 * name string (should only have letters and spaces)
	 */
	public function testRegisterInvalidFirstName() {
		$data = $this->getUserData(
			'othrus',
			'aasd asdf fsd 5', // 31 chars
			$this->surname,
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API10
	 * Tests a registration in the system with a blank surname
	 */
	public function testRegisterBlankSurname() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			'    ',
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API11
	 * Tests a registration in the system with a surname of
	 * more than 100 characters
	 */
	public function testRegisterLongSurname() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			str_repeat('abcde',20)+'a',
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API12
	 * Tests a registration in the system with an invalid surname
	 */
	public function testRegisterInvalidSurname() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			'abcd efgh 3',
			'another@test.com'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API13
	 * Tests a registration in the system with a blank email
	 */
	public function testRegisterBlankEmail() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			$this->surname,
			'    '
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API14
	 * Tests a registration in the system with an email
	 * invalid email
	 */
	public function testRegisterWrongEmail() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			$this->surname,
			'emailsinarroba.es'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API15
	 * Tests a registration in the system with an email
	 * of more than 50 chars
	 */
	public function testRegisterLongEmail() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			$this->surname,
			str_repeat('abc45', 6) + '@' + str_repeat('def90',4) + '.es'
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * T-API16
	 * Tests a registration in the system with an email
	 * already registered
	 */
	public function testRegisterRepeatedEmail() {
		$data = $this->getUserData(
			'othrus',
			$this->first_name,
			$this->surname,
			$this->email
		);
		
		$response = $this->call('POST','v1/users', $data);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	/**
	 * NOT DOCUMENTED
	 * Tests de deletion of a user
	 */
	public function testDeleteSuccessful() {
		$response = $this->call('DELETE','v1/users/testus');
		$this->assertResponseOk();
	}
}
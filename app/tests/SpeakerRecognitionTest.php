<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;
use \apibvw\Model\User as ModelUser;
use \Illuminate\Database\Eloquent;
use \apibvw\speakerrec\AlizePHPFacade;

class SpeakerRecognitionTest extends TestCase {
	
	private $field_username = 'username';
	private $field_first_name = 'first_name';
	private $field_surname = 'surname';
	private $field_email = 'email';
	
	private $username = 'testus';
	private $first_name = 'Test';
	private $surname = 'User';
	private $email = 'test@user.com';
	
	private $filename_path = '/files/';
	private $original_filename_name = 'cortazar.mp3';
	private $filename_name = 'test.mp3';
	private $filename_type = 'audio/mpeg';
	

	private $unregistered_username = 'testus';
	
	private function getWorkingPath() {
		return __DIR__;
	}
	
	private function prepareAudioFile() {
		$command = "cp ".$this->getWorkingPath().$this->filename_path.$this->original_filename_name
					." ".$this->getWorkingPath().$this->filename_path.$this->filename_name;
		exec($command);
	}
	
	public function setUp() {
		parent::setUp();
		$this->seed();
		$this->prepareAudioFile();
	}
	
	private function deleteAlizePHPUser() {
		$alizephp = new AlizePHPFacade($this->username);
		$alizephp->deleteUser();
	}
	
	public function testStatus() {
		$response = $this->call('GET','v1/voiceaccess/status/'.$this->username);
		$resp_data = $response->getData();
		
		$this->assertResponseOk();
		$this->assertFalse($resp_data->error);
		$this->assertTrue($resp_data->registered);
		$this->assertFalse($resp_data->enrolled);
	}
	
	public function testEnrollment() {
		$file = new UploadedFile(
			$this->getWorkingPath() . $this->filename_path . $this->filename_name,
			$this->filename_name,
			$this->filename_type
			
		);

		$response = $this->call(
				'POST',
				'v1/voiceaccess/enroll/'.$this->username,
				array(),
				array('audiofile'=>$file)
		);
		$resp_data = $response->getData();

		$this->assertResponseOk();
		$this->assertFalse($resp_data->error);
	}
	
	public function testEnrollmentNotRegistered() {
		$file = new UploadedFile(
			$this->getWorkingPath() . $this->filename_path . $this->filename_name,
			$this->filename_name,
			$this->filename_type
			
		);

		$response = $this->call(
				'POST',
				'v1/voiceaccess/enroll/otrusr', // Not registered username
				array(),
				array('audiofile'=>$file)
		);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
	public function testVerification() {
		$file = new UploadedFile(
			$this->getWorkingPath() . $this->filename_path . $this->filename_name,
			$this->filename_name,
			$this->filename_type
			
		);

		$response = $this->call(
				'POST',
				'v1/voiceaccess/test/'.$this->username, // Not enrolled username
				array(),
				array('audiofile'=>$file)
		);
		$resp_data = $response->getData();
		
		$this->assertResponseOk();
		$this->assertFalse($resp_data->error);
		
		$this->deleteAlizePHPUser();
	}
	
	public function testVerificationNotEnrolled() {
		$file = new UploadedFile(
			$this->getWorkingPath() . $this->filename_path . $this->filename_name,
			$this->filename_name,
			$this->filename_type
			
		);

		$response = $this->call(
				'POST',
				'v1/voiceaccess/test/otrusr',
				array(),
				array('audiofile'=>$file)
		);
		$resp_data = $response->getData();
		
		$this->assertResponseStatus(400);
		$this->assertTrue($resp_data->error);
	}
	
}
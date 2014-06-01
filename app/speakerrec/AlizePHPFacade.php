<?php
namespace apibvw\SpeakerRec;

use alizephp\AlizePHP;

class AlizePHPFacade implements SpeakerRecognitionPackage {
	
	protected $alizephp_user;
	
	public function __construct($user) {
		$this->alizephp_user = $user;
	}
	
	public function getUser() {
		return $this->alizephp_user;
	}
	
	public function isEnrolled() {
		return AlizePHP::hasVector($user);
	}
	
	private function enroll_alizephp_user(AlizePHP $alizephp_user) {
		$alizephp_user->extractFeatures();
		$alizephp_user->normaliseEnergy();
		$alizephp_user->detectEnergy();
		$alizephp_user->normaliseFeatures();
		$alizephp_user->ivExtractor();
	}
	
	public function enroll($audio_file_path) {
		$alizephp_user = new AlizePHP($this->getUser(), $audio_file_path);
		$this->enroll_alizephp_user($alizephp_user);
		return true;
	}
	
	public function testSpeakerIdentity($audio_file_path) {
		// A "test" user is created, to test him against the enrolled user
		$alizephp_user = new AlizePHP("test_".$this->getUser(), $audio_file_path);
		$this->enroll_alizephp_user($alizephp_user);
		$test_result = $alizephp_user->ivTest($user);
		$alizephp_user->cleanUserFiles();
		
		return $test_result;
	}
}
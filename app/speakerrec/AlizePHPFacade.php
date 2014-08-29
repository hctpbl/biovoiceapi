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
		return AlizePHP::hasModel($this->alizephp_user);
	}
	
	private function enroll_alizephp_user(AlizePHP $alizephp_user, $audio_file_path) {
		$alizephp_user->extractFeatures($audio_file_path);
		$alizephp_user->normaliseEnergy();
		$alizephp_user->detectEnergy();
		$alizephp_user->normaliseFeatures();
		//$alizephp_user->ivExtractor();
		$alizephp_user->trainTarget();
	}
	
	public function enroll($audio_file_path) {
		$alizephp_user = new AlizePHP($this->getUser());
		$this->enroll_alizephp_user($alizephp_user, $audio_file_path);
		return true;
	}
	
	public function testSpeakerIdentity($audio_file_path) {
		// A "test" user is created, to test him against the enrolled user
		$alizephp_user = new AlizePHP("test_".$this->getUser());
		$this->enroll_alizephp_user($alizephp_user, $audio_file_path);
		//$test_result = $alizephp_user->ivTest($this->getUser());
		$test_result = $alizephp_user->computeTest($this->getUser());
		$alizephp_user->cleanUserFiles();
		
		return $test_result;
	}
	
	public function deleteUser() {
		$alizephp_user = new AlizePHP($this->getUser(), null);
		$alizephp_user->cleanUserFiles();
		
		return true;
	}
}
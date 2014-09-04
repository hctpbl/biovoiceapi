<?php
namespace apibvw\SpeakerRec;

use alizephp\AlizePHP;

/**
 * Facade for the alizephp wrapper. Performs high-level biometric operations,
 * such as enrollment or verification
 * 
 * @author Héctor Pablos
 *
 */
class AlizePHPFacade implements SpeakerRecognitionPackage {
	
	/**
	 * AlizePHP user represented in the facade
	 * 
	 * @var AlizePHP
	 * @see AlizePHP
	 */
	protected $alizephp_user;
	
	/**
	 * 
	 * Constructs an AlizePHPFacade for the user identified by $username
	 * 
	 * @param string $user
	 */
	public function __construct($user) {
		$this->alizephp_user = $user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \apibvw\SpeakerRec\SpeakerRecognitionPackage::getUser()
	 */
	public function getUser() {
		return $this->alizephp_user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \apibvw\SpeakerRec\SpeakerRecognitionPackage::isEnrolled()
	 */
	public function isEnrolled() {
		return AlizePHP::hasModel($this->alizephp_user);
	}
	
	/**
	 * Performs an enrollment of an AlizePHP user with the audio file in the path
	 * indicated by $audio_file_path
	 * 
	 * @param AlizePHP $alizephp_user
	 * @param string $audio_file_path
	 */
	private function enroll_alizephp_user(AlizePHP $alizephp_user, $audio_file_path) {
		$alizephp_user->extractFeatures($audio_file_path);
		$alizephp_user->normaliseEnergy();
		$alizephp_user->detectEnergy();
		$alizephp_user->normaliseFeatures();
		//$alizephp_user->ivExtractor();
		$alizephp_user->trainTarget();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \apibvw\SpeakerRec\SpeakerRecognitionPackage::enroll()
	 */
	public function enroll($audio_file_path) {
		$alizephp_user = new AlizePHP($this->getUser());
		$this->enroll_alizephp_user($alizephp_user, $audio_file_path);
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \apibvw\SpeakerRec\SpeakerRecognitionPackage::testSpeakerIdentity()
	 */
	public function testSpeakerIdentity($audio_file_path) {
		// A "test" user is created, to test him against the enrolled user
		$alizephp_user = new AlizePHP("test_".$this->getUser());
		$this->enroll_alizephp_user($alizephp_user, $audio_file_path);
		//$test_result = $alizephp_user->ivTest($this->getUser());
		$test_result = $alizephp_user->computeTest($this->getUser());
		$alizephp_user->cleanUserFiles();
		
		return $test_result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \apibvw\SpeakerRec\SpeakerRecognitionPackage::deleteUser()
	 */
	public function deleteUser() {
		$alizephp_user = new AlizePHP($this->getUser(), null);
		$alizephp_user->cleanUserFiles();
		
		return true;
	}
}
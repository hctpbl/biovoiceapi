<?php
namespace apibvw\SpeakerRec;

/**
 * Factory class to create SpeakerRecognitionPackages of differente biometric engines
 * that can later be used to perform biometric authentication by voice.
 * @author Héctor Pablos
 *
 */
class SpeakerRecognitionManager {
	
	/**
	 * Constant to set AlizePHP as the engine to perform speaker recognition
	 * @var number
	 */
	const ENGINE_ALIZEPHP = 1;
	
	/**
	 * 
	 * @param string $user User id for the Speaker recognition system
	 * @param number $engine Speaker recognition engine. Defaults to ENGINE_ALIZEPHP
	 * @return \apibvw\SpeakerRec\SpeakerRecognitionPackage
	 */
	public static function getSpeakerRecognitionSystem($user, $engine=ENGINE_ALIZEPHP) {
		return new AlizePHPFacade($user);
	}
}
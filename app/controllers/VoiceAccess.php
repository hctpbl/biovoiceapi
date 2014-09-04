<?php
namespace apibvw\Controller;

use \BaseController;
use \Config;
use \Exception;
use \Input;
use \Log;
use \Request;
use \Response;
use \apibvw\Model\User as ModelUser;
use \apibvw\Model\Session as ModelSession;
use \apibvw\SpeakerRec\AudioTools;
use \apibvw\SpeakerRec\SpeakerRecognitionManager;
use \apibvw\SpeakerRec\SpeakerRecognitionException;

/**
 * 
 * Controller to handle API request to perform voice access operations.
 * 
 * For a clearer documentation of the API, client-oriented, please visit 
 * http://docs.biovoiceapi.apiary.io/
 * 
 * The base URL for this actions is v1/voiceaccess
 * 
 * @author Héctor Pablos
 *
 */
class VoiceAccess extends BaseController {
	
	/**
	 * Depending on a Content-Type like string, returns the right
	 * extension of a file.
	 * 
	 * @param string $filetype Content-Type like string
	 * @return string
	 */
	private function getCorrectExtension($filetype) {
			$extension = ".wav";
			
			if ($filetype == "audio/ogg") {
				$extension = ".ogg";
			} if ($filetype == "audio/x-m4a") {
				$extension = ".m4a";
			} if ($filetype == "audio/mpeg" || $filetype == "audio/mp3") {
				$extension = ".mp3";
			}
			
			return $extension;
	}
	
	/**
	 * 
	 * Receives a username whose audio sample has been uploaded and processes
	 * that file, creating a PCM audio file that can be passed to a speaker
	 * verification engine.
	 * 
	 * @param string $usuario username for a user whose audio sample has been uploaded
	 * @throws SpeakerRecognitionException
	 * @return string
	 */
	private function handleAudio($usuario) {
		$filetype = Request::header('Content-Type');
		
		if (Input::hasFile('audiofile')) {
			$file = Input::file("audiofile");
			$destination = storage_path("tmp_voices/");
			if ($filetype == "application/x-www-form-urlencoded") {
				$filetype = $file->getMimeType();
			} else {
				$filetype = $file->getClientMimeType();
			}
			$filename = $usuario.$this->getCorrectExtension($filetype);
			$file->move($destination, $filename);
		} else {
			$audio_raw = Request::getContent();
			
			$extension = $this->getCorrectExtension($filetype);
			
			$filename = $usuario.$this->getCorrectExtension($filetype);
			$audio_file_path = storage_path("tmp_voices/").$filename;
			
			$f = fopen($audio_file_path, "w");
			fwrite($f, $audio_raw);
		}
		
		$raw_audio_file_path = "";

		Log::error($filetype);
		
		if ($filetype == "audio/wav") {
			$raw_audio_file_path = AudioTools::WavToRaw($usuario);
		} else if ($filetype == "audio/ogg") {
			$raw_audio_file_path = AudioTools::OggToRaw($usuario);
		} else if ($filetype == "audio/x-m4a") {
			$raw_audio_file_path = AudioTools::AacToRaw($usuario);
		} else if ($filetype == "audio/mpeg" || $filetype == "audio/mp3") {
			$raw_audio_file_path = AudioTools::Mp3ToRaw($usuario);
		} else {
			throw new SpeakerRecognitionException("Audio Content-Type not supported: ".$filetype);
		}
		
		return $raw_audio_file_path;
	}

	/**
	 * 
	 * Enrolls a user identified by $username in the syste, creating his model.
	 * 
	 * URL: POST v1/voiceaccess/enroll/{username}
	 * 
	 * @param string $usuario username of the user to enroll
	 */
	public function postEnroll($usuario) {
		$threshold = Config::get('speakerrec.threshold');
		$user = ModelUser::find($usuario);
		if (!$user) {
			return Response::json(array(
					'error'=>true,
					'message'=>'User not registered. You must register first in order to enroll.',
					'threshold'=>$threshold,
					'result' => '0'
			),400);
		}
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);
		
		$error_message = "";
		
		try {
			$raw_audio_file_path = $this->handleAudio($usuario);
			$result = $speaker_rec_package->enroll($raw_audio_file_path);
		} catch (FileNotFoundException $fne) {
			$error_message = 'No audio file received.';
		} catch (SpeakerRecognitionException $sre) {
			$error_message = $sre->getMessage();
		} catch (Exception $e) {
			Log::error($e->getMessage());
			$error_message = "An error has occured. Contact the administrator.";
		}
		
		if($error_message != "") {
			return Response::json(array(
					'error'=>true,
					'action'=>'enroll',
					'message'=>$error_message,
					'threshold'=>$threshold,
					'result' => '0'
			),400);
		}
		
		$ses = new ModelSession();
		$ses->user_username = $usuario;
		$ses->type = 0;
		$ses->save();

		return Response::json(array(
				'error'=>false,
				'action'=>'enroll',
				'threshold'=>$threshold,
				'result' => '0'
		),200);
	}
	
	/**
	 * Performs a speaker verification for $username, using the audio file POSTed
	 * via HTTP.
	 * 
	 * URL: POST v1/voiceaccess/test/{username}
	 * 
	 * @param string $usuario username of the user whose identity verify
	 */
	public function postTest($usuario) {
		$threshold = Config::get('speakerrec.threshold');
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);
		
		if (!$speaker_rec_package->isEnrolled()) {
			return Response::json(array(
					'error'=>true,
					'message'=>'User not enrolled. You must enroll first in order to perform the verification.',
					'threshold'=>$threshold,
					'result' => '0'
			),400);
		}
		
		$error_message = "";
		$result = 0;

		try {
			$raw_audio_file_path = $this->handleAudio($usuario);
			$result = $speaker_rec_package->testSpeakerIdentity($raw_audio_file_path);
		} catch (FileNotFoundException $fne) {
			$error_message = 'No audio file received.';
		} catch (SpeakerRecognitionException $sre) {
			$error_message = $sre->getMessage();
		} catch (Exception $e) {
			Log::error($e->getMessage());
			$error_message = "An error has occured. Contact the administrator.";
		}
		
		if($error_message != "") {
			return Response::json(array(
					'error'=>true,
					'action'=>'verify',
					'message'=>$error_message,
					'threshold'=>$threshold,
			),400);
		}
		
		$ses = new ModelSession();
		$ses->user_username = $usuario;
		$ses->type = 1;
		$ses->result = $result;
		$ses->save();

		return Response::json(array(
				'error'=>false,
				'action'=>'verify',
				'threshold'=>$threshold,
				'result'=>$result,
		),200);
	}
	
	/**
	 * 
	 * Checks the status of $username user in the system, returning information
	 * of wether he is registered and enrolled or not.
	 * 
	 * URL: v1/voiceaccess/test/{username}
	 * 
	 * @param string $usuario
	 */
	public function getStatus($usuario) {
		$registered = false;
		$enrolled = false;
		
		$user = ModelUser::find($usuario);
		if ($user) {
			$registered = true;
			$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario);
			$enrolled = $speaker_rec_package->isEnrolled();
		}
		
		if (!$registered) {
			return Response::json(array('error'=>false, 'registered'=>false,
					'enrolled'=>false, 'message'=>'User not registered.'),200);
		} else if (!$enrolled) {
			return Response::json(array('error'=>false, 'registered'=>true,
					'enrolled'=>false, 'message' => 'User not enrolled.'),200);
		} else {
			return Response::json(array('error'=>false, 'registered'=>true,
					'enrolled'=>true, 'message' => 'User is valid.'),200);
		}
		
	}
}
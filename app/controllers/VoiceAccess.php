<?php
namespace apibvw\Controller;

use \BaseController;
use \Config;
use \Exception;
use \Input;
use \Request;
use \Response;
use \apibvw\Model\User as ModelUser;
use \apibvw\SpeakerRec\AudioTools;
use \apibvw\SpeakerRec\SpeakerRecognitionManager;

class VoiceAccess extends BaseController {
	
	private function getCorrectExtension($filetype) {
			$extension = ".wav";
			
			if ($filetype == "audio/ogg") {
				$extension = ".ogg";
			} if ($filetype == "audio/x-m4a") {
				$extension = ".m4a";
			} if ($filetype == "audio/mpeg") {
				$extension = ".mp3";
			}
			
			return $extension;
	}
	
	private function handleAudio($usuario) {
		$filetype = Request::header('Content-Type');
		
		if (Input::hasFile('audiofile')) {
			$file = Input::file("audiofile");
			$destination = storage_path("tmp_voices/");
			if ($filetype == "application/x-www-form-urlencoded") {
				$filetype = $file->getMimeType();
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
		
		if ($filetype == "audio/wav") {
			$raw_audio_file_path = AudioTools::WavToRaw($usuario);
		} else if ($filetype == "audio/ogg") {
			$raw_audio_file_path = AudioTools::OggToRaw($usuario);
		} else if ($filetype == "audio/x-m4a") {
			$raw_audio_file_path = AudioTools::AacToRaw($usuario);
		} else if ($filetype == "audio/mpeg") {
			$raw_audio_file_path = AudioTools::Mp3ToRaw($usuario);
		} else {
			throw new Exception("Audio Content-Type not supported.");
		}
		
		return $raw_audio_file_path;
	}

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
		
		$raw_audio_file_path = $this->handleAudio($usuario);
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);

		$result = $speaker_rec_package->enroll($raw_audio_file_path);

		return Response::json(array('error'=>false, 'threshold'=>$threshold, 'result' => '0'),200);
	}
	
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
		
		$raw_audio_file_path = $this->handleAudio($usuario);

		$result = $speaker_rec_package->testSpeakerIdentity($raw_audio_file_path);

		return Response::json(array('error'=>false, 'threshold'=>$threshold, 'result' => $result),200);
	}
	
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
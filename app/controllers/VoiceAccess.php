<?php
namespace apibvw\Controller;

use \BaseController;
use \Input;
use \Request;
use \Response;
use \apibvw\Model\User as ModelUser;
use apibvw\SpeakerRec\AudioTools;
use apibvw\SpeakerRec\SpeakerRecognitionManager;

class VoiceAccess extends BaseController {
	
	private function handleAudio($usuario) {
		$filetype = Request::header('Content-Type');
		
		if (Input::hasFile('audiofile')) {
			$file = Input::file("audiofile");
			$destination = storage_path("tmp_voices/").$file->getClientOriginalName();
			$file->move($destination);
		} else {
			$audio_raw = Request::getContent();
			
			$extension = ".wav";
			if ($filetype == "audio/ogg")
				$extension = ".ogg";
			if ($filetype == "audio/aac")
				$extension = ".m4a";
			
			$filename = $usuario.$extension;
			$audio_file_path = storage_path("tmp_voices/").$filename;
			
			$f = fopen($audio_file_path, "w");
			fwrite($f, $audio_raw);
		}
		
		$raw_audio_file_path = "";
		
		if ($filetype == "audio/wav") {
			$raw_audio_file_path = AudioTools::WavToRaw($usuario);
		} else if ($filetype == "audio/ogg") {
			$raw_audio_file_path = AudioTools::OggToRaw($usuario);
		} else {
			$raw_audio_file_path = AudioTools::AacToRaw($usuario);
		}
		
		return $raw_audio_file_path;
	}

	public function postEnroll($usuario) {
		
		$raw_audio_file_path = $this->handleAudio($usuario);
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);
		
		$result = $speaker_rec_package->enroll($raw_audio_file_path);

		return Response::json(array('error'=>false, 'result' => $result),200);
	}
	
	public function postTest($usuario) {
		
		$raw_audio_file_path = $this->handleAudio($usuario);
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);
		
		$result = $speaker_rec_package->testSpeakerIdentity($raw_audio_file_path);

		return Response::json(array('error'=>false, 'result' => $result),200);
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
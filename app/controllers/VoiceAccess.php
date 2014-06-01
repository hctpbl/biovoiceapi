<?php
namespace apibvw\Controller;

use \BaseController;
use \Request;
use \Response;
use apibvw\SpeakerRec\AudioTools;
use apibvw\SpeakerRec\SpeakerRecognitionManager;

class VoiceAccess extends BaseController {
	
	public function postEnroll($usuario) {
		$filetype = Request::header('Content-Type');
		$audio_raw = Request::getContent();
		
		$extension = ".wav";
		
		if ($filetype == "audio/ogg")
			$extension = ".ogg";
		
		$filename = $usuario.$extension;
		$audio_file_path = storage_path("tmp_voices/").$filename;
		
		$f = fopen($audio_file_path, "w");
		fwrite($f, $audio_raw);
		
		$raw_audio_file_path = "";
		
		if ($filetype == "audio/wav") {
			$raw_audio_file_path = AudioTools::WavToRaw($usuario);
		} else {
			$raw_audio_file_path = AudioTools::OggToRaw($usuario);
		}
		
		$speaker_rec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario,
				SpeakerRecognitionManager::ENGINE_ALIZEPHP);
		
		$result = $speaker_rec_package->enroll($raw_audio_file_path);

		return Response::json(array('error'=>false, 'result' => $result),200);
		
		/*$speakerrec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario);
		$speakerrec_package->enroll($audio_file_path);*/
	}
}
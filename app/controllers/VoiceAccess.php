<?php
namespace apibvw\Controller;

use \BaseController;
use \Request;
use apibvw\SpeakerRec\SpeakerRecognitionManager;
use \Response;

class VoiceAccess extends BaseController {
	
	public function postEnroll($usuario) {
		$audio_raw = Request::getContent();
		
		$extension = ".wav";
		
		if ($filetype == "audio/ogg")
			$extension = ".ogg";
		
		$filename = $usuario.$extension;
		$audio_file_path = storage_path("tmp_voices").$filename;
		
		$f = fopen($audio_file_path, "w");
		fwrite($f, $audio_raw);

		return Response::json(array('error'=>false),200);
		
		/*$speakerrec_package = SpeakerRecognitionManager::getSpeakerRecognitionSystem($usuario);
		$speakerrec_package->enroll($audio_file_path);*/
	}
}
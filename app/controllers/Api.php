<?php

namespace bvw\Controller;

use \BaseController;
use \Input;
use \Response;
use \Request;

class Api extends BaseController {
	
	public function postUploadAudio() {
		//$file = Input::file('audio-blob');
		
		$destinationPath = 'uploads';
		$filename = str_random(12);
		$filetype = Request::header('Content-Type');
		//$filename = $file->getClientOriginalName();
		//$extension =$file->getClientOriginalExtension();
		//$upload_success = $file->move($destinationPath, $filename.'.wav');
		
		$extension = ".wav";
		
		if ($filetype == "audio/ogg")
			$extension = ".ogg";
		
		$filename = str_random(12).$extension;
		
		$raw_wav = Request::getContent();
		
		$f = fopen("uploads/".$filename, "w");
		fwrite($f, $raw_wav);
		
		
		echo $filename;
		/*if( $upload_success ) {
			return Response::json(array('status'=>'success', 'file'=>$filename, 'code'=>200));
		} else {
			return Response::json(array('status'=>'error', 'code'=>400));
		}*/
	}

}
<?php
namespace apibvw\SpeakerRec;

class AudioTools {
	public static function WavToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".wav";
		$output_file_path = storage_path("tmp_voices/").$user.".pcm";
		$command = "sox $input_file_path -e signed-integer -b 16 -t raw $output_file_path remix 1,2";
		exec($command);
		return $output_file_path;
	}
	
	public static function OggToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".ogg";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "opusdec $input_file_path $output_file_path";
		exec($command);
		return AudioTools::WavToRaw($user);
	}
}
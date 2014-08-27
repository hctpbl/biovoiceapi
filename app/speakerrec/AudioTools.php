<?php
namespace apibvw\SpeakerRec;

class AudioTools {
	public static function WavToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".wav";
		$output_file_path = storage_path("tmp_voices/").$user.".pcm";
		$command = "sox $input_file_path -e signed-integer -b 16 -t raw -L -r 8000 $output_file_path remix 1,2";
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
	
	public static function AacToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".m4a";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "faad $input_file_path $output_file_path";
		exec($command);
		return AudioTools::WavToRaw($user);
	}
	
	public static function Mp3ToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".mp3";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "avconv -y -i $input_file_path $output_file_path 2> /dev/null";
		exec($command);
		return AudioTools::WavToRaw($user);
	}
}
<?php
namespace apibvw\SpeakerRec;

/**
 * Class with helper methods to convert audio between different formats
 * 
 * @author Hector
 *
 */
class AudioTools {
	
	/**
	 * Converts the audio for the user $username from wav to raw (PCM),
	 * returning the file path of the converted file as a string
	 * 
	 * @param string $user
	 * @return string
	 */
	public static function WavToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".wav";
		$output_file_path = storage_path("tmp_voices/").$user.".pcm";
		$command = "sox $input_file_path -e signed-integer -b 16 -t raw -L -r 8000 $output_file_path remix 1";
		exec($command);
		return $output_file_path;
	}

	/**
	 * Converts the audio for the user $username from ogg to raw (PCM),
	 * returning the file path of the converted file as a string
	 *
	 * @param string $user
	 * @return string
	 */
	public static function OggToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".ogg";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "opusdec $input_file_path $output_file_path";
		exec($command);
		return AudioTools::WavToRaw($user);
	}

	/**
	 * Converts the audio for the user $username from aac to raw (PCM),
	 * returning the file path of the converted file as a string
	 *
	 * @param string $user
	 * @return string
	 */
	public static function AacToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".m4a";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "faad $input_file_path $output_file_path";
		exec($command);
		return AudioTools::WavToRaw($user);
	}

	/**
	 * Converts the audio for the user $username from mp3 to raw (PCM),
	 * returning the file path of the converted file as a string
	 *
	 * @param string $user
	 * @return string
	 */
	public static function Mp3ToRaw($user) {
		$input_file_path = storage_path("tmp_voices/").$user.".mp3";
		$output_file_path = storage_path("tmp_voices/").$user.".wav";
		$command = "avconv -y -i $input_file_path $output_file_path 2> /dev/null";
		exec($command);
		return AudioTools::WavToRaw($user);
	}
}
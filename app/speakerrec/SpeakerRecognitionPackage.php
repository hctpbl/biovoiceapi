<?php
namespace apibvw\SpeakerRec;

/**
 * 
 * Speaker recognition independant package interface. BVW API will call this methods
 * to provice third parties with a simple way to enroll and test user's identity.
 * 
 * @author Hector
 *
 */
interface SpeakerRecognitionPackage {
	
	/**
	 * 
	 * Test if a user is enrolled in a speaker recognition system.
	 * A user being enrolled means there is information about him (data structures, files,
	 * database entries, ...) that can be used to compare him to another user.
	 * 
	 * @param string $user User id. If it's a number, it will be converted to a string.
	 * @return bool true if $user is enrolled, false if not.
	 */
	public static function isEnrolled($user);
	
	/**
	 * 
	 * Create necessary initial information (data structures, files, database entries, ...)
	 * to allow testing the identity of a user against another based on the audio features
	 * of his voice.
	 * 
	 * @param string $user User id. If it's a number, it will be converted to a string.
	 * @param string $audio_file_path Path to audio file with a sample voice of the subject $user.
	 * @return bool true if enrollment was succesful, false if not.
	 */
	public static function enroll($user, $audio_file_path);
	
	/**
	 * 
	 * Checks if the audio file located in $audio_file_path belongs to $user's voice.
	 * $user should be already erolled in the speaker recognition system in order to test
	 * his identity
	 * 
	 * @param string $user User id. If it's a number, it will be converted to a string.
	 * 						This user must be enrolled in order to test his identity.
	 * @param string $audio_file_path Path to audio file with a sample voice to test
	 * 						against existing $user data to check identity matching.
	 * @return bool true if $audio_file_path belongs to $user, false if not.
	 */
	public static function testSpeakerIdentity($user, $audio_file_path);
}
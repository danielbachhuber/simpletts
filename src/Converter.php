<?php
/**
 * Converts text to speech using the Amazon Polly API
 *
 * @package Simpletts
 */

namespace Simpletts;

use WP_Error;

/**
 * Converts text to speech using the Amazon Polly API
 */
class Converter {

	/**
	 * Convert text to speech
	 *
	 * @param string $text      Text to convert.
	 * @param string $voice     Voice to use.
	 * @return integer|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	public static function create_audio_attachment_from_text( $text, $voice = false ) {

		$access_key = Settings::get_option( 'simpletts_access_key' );
		$secret_key = Settings::get_option( 'simpletts_secret_key' );
		if ( empty( $access_key ) || empty( $secret_key ) ) {
			return new WP_Error( 'missing-config', 'Both simpletts_access_key and simpletts_secret_key must be set.', 'simpletts' );
		}

		$default_voice = Settings::get_option( 'simpletts_default_voice' );
		if ( ! $voice ) {
			$voice = $default_voice;
		}
		$voices = self::get_available_voices();
		$voices = wp_list_pluck( $voices, 'name' );
		if ( ! in_array( $voice, $voices, true ) ) {
			// translators: Placeholder is the voice passed through.
			return new WP_Error( 'invalid-voice', sprintf( __( "'%s' is not available as a voice", 'simpletts' ), $voice ) );
		}

		$request_body = array(
			'Text'         => $text,
			'TextType'     => 'text',
			'OutputFormat' => 'mp3',
			'VoiceId'      => $voice,
		);
		$region = Settings::get_option( 'simpletts_aws_region' );
		$service = 'polly';
		$host = $service . '.' . $region . '.amazonaws.com';
		$datetime_stamp = date( 'Ymd\THis\Z' );
		$date_stamp = date( 'Ymd' );
		$request_body = json_encode( $request_body );

		$method = 'POST';
		$uri = '/v1/speech';
		$payload_hash = hash( 'sha256', $request_body );
		$query_params = '';
		$canonical_headers = implode( PHP_EOL, array(
			'content-type:application/json',
			'host:' . $host,
			'x-amz-date:' . $datetime_stamp,
		) );
		$signed_headers = 'content-type;host;x-amz-date';

		$canonical_request = implode( PHP_EOL, array(
			$method,
			$uri,
			$query_params,
			$canonical_headers . PHP_EOL,
			$signed_headers,
			$payload_hash,
		) );

		$algorithm = 'AWS4-HMAC-SHA256';
		$credential_scope = implode( '/', array(
			$date_stamp,
			$region,
			$service,
			'aws4_request',
		) );
		$string_to_sign = implode( PHP_EOL, array(
			$algorithm,
			$datetime_stamp,
			$credential_scope,
			hash( 'sha256', $canonical_request ),
		) );

		$signature_key = self::get_signature_key( $secret_key, $date_stamp, $region, $service );
		$signature = hash_hmac( 'sha256', $string_to_sign, $signature_key );

		$request_url = 'https://' . $host . $uri;
		$headers = array(
			'Content-Type'  => 'application/json',
			'X-Amz-Date'    => $datetime_stamp,
			'Host'          => $host,
			'Authorization' => $algorithm
				. ' Credential=' . $access_key . '/' . $credential_scope
				. ', SignedHeaders=' . $signed_headers
				. ', Signature=' . $signature,
		);
		$response = wp_remote_post( $request_url, array(
			'headers'      => $headers,
			'body'         => $request_body,
		) );
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		if ( 200 !== $response_code ) {
			$response_data = json_decode( $response_body, true );
			if ( ! empty( $response_data['message'] ) ) {
				// translators: Returns AWS error message.
				$message = sprintf( __( '%1$s (HTTP code %2$d)', 'simpletts' ), $response_data['message'], $response_code );
			} else {
				// translators: Returns unknown error message.
				$message = sprintf( __( 'Unknown conversion error (HTTP code %d)', 'simpletts' ), $response_code );
			}
			return new WP_Error( 'convert-failure', $message );
		} elseif ( is_wp_error( $response ) ) {
			return $response;
		}

		$ramtext  = substr( $signature, 0, 7 );
		$filename = 'simpletts-' . $ramtext;
		$fname    = apply_filters( 'simpletts_file_name', $filename, $ramtext );
		$tmp      = wp_tempnam( $fname );
		$ret      = file_put_contents( $tmp, $response_body );
		if ( ! $ret ) {
			return new WP_Error( 'convert-failure', __( 'Could not write audio file to tmp directory.', 'simpletts' ) );
		}
		$file_array = array(
			'name'     => $fname . '.mp3',
			'tmp_name' => $tmp,
		);
		$id = media_handle_sideload( $file_array, 0 );
		if ( is_wp_error( $id ) ) {
			unlink( $tmp );
			return $id;
		}
		return $id;
	}

	/**
	 * Create an AWS4 signature key from various input.
	 *
	 * @param string $secret_key AWS secret key.
	 * @param string $date_stamp Date stamp.
	 * @param string $region     AWS region.
	 * @param string $service    AWS service.
	 * @return string
	 */
	private static function get_signature_key( $secret_key, $date_stamp, $region, $service ) {
		$kdate = hash_hmac( 'sha256', $date_stamp, 'AWS4' . $secret_key, true );
		$kregion = hash_hmac( 'sha256', $region, $kdate, true );
		$kservice = hash_hmac( 'sha256', $service, $kregion, true );
		return hash_hmac( 'sha256', 'aws4_request', $kservice, true );
	}

	/**
	 * Get available voices
	 *
	 * @return array
	 */
	public static function get_available_voices() {
		return array(
			// Danish (da-DK).
			array(
				'name'    => 'Mads',
				'gender'  => 'Male',
				'lang'    => 'Danish (da-DK)',
			),
			array(
				'name'    => 'Naja',
				'gender'  => 'Female',
				'lang'    => 'Danish (da-DK)',
			),
			// Dutch (nl-NL).
			array(
				'name'    => 'Ruben',
				'gender'  => 'Male',
				'lang'    => 'Dutch (nl-NL)',
			),
			array(
				'name'    => 'Lotte',
				'gender'  => 'Female',
				'lang'    => 'Dutch (nl-NL)',
			),
			// English (Australian) (en-AU).
			array(
				'name'    => 'Russell',
				'gender'  => 'Male',
				'lang'    => 'English (Australian) (en-AU)',
			),
			array(
				'name'    => 'Nicole',
				'gender'  => 'Female',
				'lang'    => 'English (Australian) (en-AU)',
			),
			// English (British) (en-GB).
			array(
				'name'    => 'Brian',
				'gender'  => 'Male',
				'lang'    => 'English (British) (en-GB)',
			),
			array(
				'name'    => 'Amy',
				'gender'  => 'Female',
				'lang'    => 'English (British) (en-GB)',
			),
			array(
				'name'    => 'Emma',
				'gender'  => 'Female',
				'lang'    => 'English (British) (en-GB)',
			),
			// English (Indian) (en-IN).
			array(
				'name'    => 'Raveena',
				'gender'  => 'Female',
				'lang'    => 'English (Indian) (en-IN)',
			),
			// English (US) (en-US).
			array(
				'name'    => 'Ivy',
				'gender'  => 'Female',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Joanna',
				'gender'  => 'Female',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Joey',
				'gender'  => 'Male',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Justin',
				'gender'  => 'Male',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Kendra',
				'gender'  => 'Female',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Kimberly',
				'gender'  => 'Female',
				'lang'    => 'English (US) (en-US)',
			),
			array(
				'name'    => 'Salli',
				'gender'  => 'Female',
				'lang'    => 'English (US) (en-US)',
			),
			// English (Welsh) (en-GB-WLS).
			array(
				'name'    => 'Geraint',
				'gender'  => 'Male',
				'lang'    => 'English (Welsh) (en-GB-WLS)',
			),
			// French (fr-FR).
			array(
				'name'    => 'Celine',
				'gender'  => 'Female',
				'lang'    => 'French (fr-FR)',
			),
			array(
				'name'    => 'Mathieu',
				'gender'  => 'Male',
				'lang'    => 'French (fr-FR)',
			),
			// French (Canadian) (fr-CA).
			array(
				'name'    => 'Chantal',
				'gender'  => 'Female',
				'lang'    => 'French (Canadian) (fr-CA)',
			),
			// German (de-DE).
			array(
				'name'    => 'Hans',
				'gender'  => 'Male',
				'lang'    => 'German (de-DE)',
			),
			array(
				'name'    => 'Marlene',
				'gender'  => 'Female',
				'lang'    => 'German (de-DE)',
			),
			array(
				'name'    => 'Vicki',
				'gender'  => 'Female',
				'lang'    => 'German (de-DE)',
			),
			// Icelandic (is-IS).
			array(
				'name'    => 'Dora',
				'gender'  => 'Female',
				'lang'    => 'Icelandic (is-IS)',
			),
			array(
				'name'    => 'Karl',
				'gender'  => 'Male',
				'lang'    => 'Icelandic (is-IS)',
			),
			// Italian (it-IT).
			array(
				'name'    => 'Carla',
				'gender'  => 'Female',
				'lang'    => 'Italian (it-IT)',
			),
			array(
				'name'    => 'Giorgio',
				'gender'  => 'Male',
				'lang'    => 'Italian (it-IT)',
			),
			// Japanese (ja-JP).
			array(
				'name'    => 'Mizuki',
				'gender'  => 'Female',
				'lang'    => 'Japanese (ja-JP)',
			),
			// Norwegian (nb-NO).
			array(
				'name'    => 'Liv',
				'gender'  => 'Female',
				'lang'    => 'Norwegian (nb-NO)',
			),
			// Polish (pl-PL).
			array(
				'name'    => 'Jacek',
				'gender'  => 'Male',
				'lang'    => 'Polish (pl-PL)',
			),
			array(
				'name'    => 'Jan',
				'gender'  => 'Male',
				'lang'    => 'Polish (pl-PL)',
			),
			array(
				'name'    => 'Ewa',
				'gender'  => 'Female',
				'lang'    => 'Polish (pl-PL)',
			),
			array(
				'name'    => 'Maja',
				'gender'  => 'Female',
				'lang'    => 'Polish (pl-PL)',
			),
			// Portuguese (Brazilian) (pt-BR).
			array(
				'name'    => 'Ricardo',
				'gender'  => 'Male',
				'lang'    => 'Portuguese (Brazilian) (pt-BR)',
			),
			array(
				'name'    => 'Vitoria',
				'gender'  => 'Female',
				'lang'    => 'Portuguese (Brazilian) (pt-BR)',
			),
			// Portuguese (European) (pt-PT).
			array(
				'name'    => 'Cristiano',
				'gender'  => 'Male',
				'lang'    => 'Portuguese (European) (pt-PT)',
			),
			array(
				'name'    => 'Ines',
				'gender'  => 'Female',
				'lang'    => 'Portuguese (European) (pt-PT)',
			),
			// Romanian (ro-RO).
			array(
				'name'    => 'Carmen',
				'gender'  => 'Female',
				'lang'    => 'Romanian (ro-RO)',
			),
			// Russian (ru-RU).
			array(
				'name'    => 'Maxim',
				'gender'  => 'Male',
				'lang'    => 'Russian (ru-RU)',
			),
			array(
				'name'    => 'Tatyana',
				'gender'  => 'Female',
				'lang'    => 'Russian (ru-RU)',
			),
			// Spanish (Castilian) (es-ES).
			array(
				'name'    => 'Conchita',
				'gender'  => 'Female',
				'lang'    => 'Spanish (Castilian) (es-ES)',
			),
			array(
				'name'    => 'Enrique',
				'gender'  => 'Male',
				'lang'    => 'Spanish (Castilian) (es-ES)',
			),
			// Spanish (Latin American) (es-US).
			array(
				'name'    => 'Miguel',
				'gender'  => 'Male',
				'lang'    => 'Spanish (Latin American) (es-US)',
			),
			array(
				'name'    => 'Penelope',
				'gender'  => 'Female',
				'lang'    => 'Spanish (Latin American) (es-US)',
			),
			// Swedish (sv-SE).
			array(
				'name'    => 'Astrid',
				'gender'  => 'Female',
				'lang'    => 'Swedish (sv-SE)',
			),
			// Turkish (tr-TR).
			array(
				'name'    => 'Filiz',
				'gender'  => 'Female',
				'lang'    => 'Turkish (tr-TR)',
			),
			// Welsh (cy-GB).
			array(
				'name'    => 'Gwyneth',
				'gender'  => 'Female',
				'lang'    => 'Welsh (cy-GB)',
			),
		);
	}

}

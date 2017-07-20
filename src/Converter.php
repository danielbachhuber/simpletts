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
	 * @return integer|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	public static function create_audio_attachment_from_text( $text ) {

		$access_key = 'AKIAIAI3DYAPFXZEKQNA';
		$secret_key = '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';

		$request_body = array(
			'Text'         => $text,
			'TextType'     => 'text',
			'OutputFormat' => 'mp3',
			'VoiceId'      => 'Joanna',
		);
		$region = 'us-west-2';
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

		$fname = 'simpletts-' . substr( $signature, 0, 7 );
		$tmp = wp_tempnam( $fname );
		$ret = file_put_contents( $tmp, $response_body );
		if ( ! $ret ) {
			return new WP_Error( 'convert-failure', __( 'Could not write audio file to tmp directory.', 'simpletts' ) );
		}
		$file_array = array(
			'name'     => $fname . '.mp3',
			'tmp_name' => $tmp,
		);
		$id = media_handle_sideload( $file_array, 0 );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
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

}

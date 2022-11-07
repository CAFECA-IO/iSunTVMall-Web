<?php
namespace PayPalCheckoutSdk\Core;

/**
 * Class PaypalApi
 *
 * @package PayPalCheckoutSdk\Core
 */
class PaypalApi
{
    
    /**
     * Verifies a webhook from PayPal.
     *
     * @param string $cert_url The URL of the certificate that corresponds to the
     *                         private key that was used to sign the certificate.
     *                         When the webhook is posted to you, PayPal provides
     *                         this in the PAYPAL-CERT-URL HTTP header.
     * @param string $transmission_id The transmission ID for the webhook event.
     *                                When the webhook is posted to you, PayPal
     *                                provides this in the PAYPAL-TRANSMISSION-ID
     *                                HTTP header.
     * @param string $timestamp The timestamp of when the webhook was sent. When
     *                          the webhook is posted to you, PayPal provides
     *                          this in the PAYPAL-TRANSMISSION-TIME HTTP header.
     * @param string $webhook_id The webhook ID assigned to your webhook, as
     *                           defined in your developer.paypal.com dashboard.
     *                           If you used the Create Webhook API to create your
     *                           webhook, this ID was returned in the response to
     *                           that call.
     * @param string $signature_algorithm The signature algorithm that was used to
     *                                    generate the signature for the webhook.
     *                                    When the webhook is posted to you, PayPal
     *                                    provides this in the PAYPAL-AUTH-ALGO
     *                                    HTTP header.
     * @param string $webhook_body The byte-for-byte body of the request that
     *                             PayPal posted to you.
     *
     * @return bool Returns true if the webhook could be successfully verified, or
     *              false if it was not.
     *
     * @throws Exception if an error occurred while attempting to verify the
     *     webhook.
     */
    function verify_webhook( $cert_url, $transmission_id, $timestamp, $webhook_id, $signature_algorithm, $signature, $webhook_body ) {
      // This is used to translate the hash methods provided by PayPal into ones that
      // are known by OpenSSL...right now the only one we've seen PayPal use is 'SHA256withRSA'
      $known_hash_methods = [
        'SHA256withRSA' => 'sha256WithRSAEncryption'
      ];
      $algo = "";
      if( array_key_exists( $signature_algorithm, $known_hash_methods ) ) {
        $algo = $known_hash_methods[ $signature_algorithm ];
      } else {
        $algo = $signature_algorithm;
      }

      // Make sure OpenSSL knows how to handle this hash method
      $openssl_algos = openssl_get_md_methods( true );
      if( !in_array( $algo, $openssl_algos ) ) {
        throw new Exception( "OpenSSL doesn't know how to handle message digest algorithm ".$algo);
      }

      // Fetch the cert -- we have to use cURL for this because PHP's built-in
      // capability for opening http/https URLs uses HTTP 1.0, which PayPal doesn't
      // support
      $curl = curl_init( $cert_url );
      curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
      $cert = curl_exec( $curl );

      if( false === $cert ) {
        $error = curl_error( $curl );
        curl_close( $curl );
        throw new Exception( "Failed to fetch certificate from server: $error" );
      }

      curl_close( $curl );

      // Parse the certificate
      $x509 = openssl_x509_read( $cert );
      if( false === $x509 ) {
        throw new Exception( "OpenSSL was unable to parse the certificate from PayPal\n" );
      }

      // Calculate the CRC32 of the webhook body
      $crc = crc32( $webhook_body );

      // Assemble the string that PayPal actually signed
      $sig_string = sprintf( '%s|%s|%s|%u', $transmission_id, $timestamp, $webhook_id, $crc );

      // Base64-decode PayPal's signature
      $decoded_signature = base64_decode( $signature );

      // Fetch the public key from the certificate
      $pkey = openssl_pkey_get_public( $cert );
      if( false === $pkey ) {
        throw new Exception( "Failed to get public key from PayPal certificate\n" );
      }

      // Verify the signature
      $verify_status = openssl_verify( $sig_string, $decoded_signature, $pkey, $algo );

      openssl_x509_free( $x509 );

      // Check the status of the verification
      if( $verify_status == 1 ) {
        return true;
      } else if( $verify_status == -1 ) {
        throw new Exception( "Error occurred while trying to verify webhook signature" );
      } else {
        return false;
      }
    }
}

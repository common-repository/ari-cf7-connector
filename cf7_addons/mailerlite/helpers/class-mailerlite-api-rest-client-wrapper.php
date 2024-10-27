<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Helpers;

require_once ARICF7CONNECTOR_MAILERLITE_3RDPARTY_LOADER;

class Mailerlite_Api_Rest_Client_Wrapper {
    public $apiKey;

    public $baseUrl;

    private $last_response;

    public function __construct($baseUrl, $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    public function get($endpointUri, $queryString = [])
    {
        return $this->send('GET', $endpointUri . '?' . http_build_query($queryString));
    }

    public function post($endpointUri, $data = [])
    {
        return $this->send('POST', $endpointUri, $data);
    }

    public function put($endpointUri, $putData = [])
    {
        return $this->send('PUT', $endpointUri, $putData);
    }

    public function delete($endpointUri)
    {
        return $this->send('DELETE', $endpointUri);
    }

    protected function isNewApi() {
        if (!$this->apiKey) {
            return false;
        }

        return strlen( $this->apiKey ) > 32;
    }

    protected function send( $method, $endpointUri, $body = null, array $headers = [] ) {
        $endpointUrl = $this->baseUrl . $endpointUri;
        $headers = array_merge($headers, $this->getDefaultHeaders());

        $httpClient = new \WP_Http();
        $response = $httpClient->request(
            $endpointUrl,
            array(
                'method' => $method,
                'headers' => $headers,
                'body' => $body ? json_encode($body) : null,
            )
        );
        $res = null;

        if ( is_wp_error($response) ) {
            $res = ['status_code' => 500, 'body' => null];
        } else {
            $data = (string) $response['body'];
            $jsonResponseData = json_decode($data, false);
            $body = $data && $jsonResponseData === null ? $data : $jsonResponseData;
            $responseCode = $response['response']['code'];

            $res = ['status_code' => $responseCode, 'body' => $body];
        }
        $this->last_response = $res;

        return $res;
    }

    protected function getDefaultHeaders() {
        $headers = [
            'Content-Type'        => 'application/json',
            'Accept'              => 'application/json',
            'User-Agent'          => 'mailersend-php/1.0.0', 
        ];

        // Only adding it when provided. Not required for RestClientTest
        if ($this->apiKey) {
            if ($this->isNewApi()) {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            } else {
                $headers['X-MailerLite-ApiKey'] = $this->apiKey;
            }
        }

        return $headers;
    }

    public function is_error() {
        if ( empty( $this->last_response ) )
            return false;

        return $this->last_response['status_code'] >= 400;
    }

    public function get_last_error() {
        if ( empty( $this->last_response['body'] ) )
            return '';

        $error = '';
        $body = $this->last_response['body'];
        $status_code = $this->last_response['status_code'];

        if ( isset( $body->message ) ) {
            $error = sprintf(
                'Error code: %s. %s. %s.',
                $status_code,
                $body->message,
                isset( $body->errors) ? json_encode( $body->errors ) : ''
            );
        }

        return $error;
    }
}

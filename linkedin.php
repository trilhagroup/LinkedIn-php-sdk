<?php

/*
* Pedro Góes (contato@estudiotrilha.com.br) http://estudiotrilha.com.br
*
* LinkedIn PHP SDK
*
* License: MIT
*/

class LinkedIn  {

    /* Official API url */
    const API_BASE_URI = 'https://api.linkedin.com/v1/';

    /* Official Oauth2 url */
    const OAUTH_BASE_URI = 'https://www.linkedin.com/uas/oauth2/';

    /* API Key */
	protected $_apiKey = '';

    /* API Secret */
	protected $_apiSecret = '';

    /* Access Token */
	protected $_accessToken = null;

    /* Callback url */
	protected $_callbackUrl = '';

    /* Set timeout default. */
    public $_timeout = 30;

    /* Set connect timeout. */
    public $_connectTimeout = 30;

    /* Contains the last HTTP status code returned. */
    public $_httpCode;

    /* Contains the last HTTP headers returned. */
    public $_httpInfo;

    /* Set the userAgent. */
    public $_userAgent = 'Trilha LinkedIn PHP SDK v0.1';

    /**
    * Construct Linkedin object
    */
	public function __construct($config) {
		$this->_apiKey = $config['apiKey'];
		$this->_apiSecret = $config['apiSecret'];
		$this->_callbackUrl = $config['callbackUrl'];
	}
	
    /**
    * Setters
    */
    public function setAccessToken($token) {
        $this->_accessToken = $token;
    }

    /**
    * Login
    */
	public function getLoginUrl($scope="") {
		$params = array('response_type' => 'code',
    		'client_id' => $this->_apiKey,
    		'scope' => $scope,
    		'state' => uniqid('', true), 
    		'redirect_uri' => $this->_callbackUrl
		);

		$uri = self::OAUTH_BASE_URI . 'authorization?' . http_build_query($params);
		$_SESSION['state'] = $params['state'];
		
		return $uri;
	}
	
	public function getAccessToken($code)  {

		if ($this->_accessToken !== null) {
			return $this->_accessToken;
		}
		
		$params = array('grant_type' => 'authorization_code',
			'client_id' => $this->_apiKey,
			'client_secret' => $this->_apiSecret,
			'code' => $code,
			'redirect_uri' => $this->_callbackUrl
		);
		
		$uri = self::OAUTH_BASE_URI . 'accessToken?' . http_build_query($params);
		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'POST'
					)
				)
		);

		$response = file_get_contents($uri, false, $context);
		$token = json_decode($response);

		$this->_accessToken = $token->access_token; 
		$_SESSION['expires_in'] = $token->expires_in; 
		$_SESSION['expires_at'] = time() + $_SESSION['expires_in']; 
		
		return $this->_accessToken;
	}
	
    /**
    * Requests
    */
	public function request($segment, $segmentID, $fieldSelectors, $attributes = array(), $format = 'json')  {

		$essentialParams = array(
			'oauth2_access_token' => $this->_accessToken,
			'format' => $format
		);

        // Encode properties
        $getProperties = (!empty($attributes["GET"])) ? http_build_query($attributes["GET"]) : "";
        $postProperties = (!empty($attributes["POST"])) ? http_build_query($attributes["POST"]) : "";

        // Complete URL
		$completeURL = self::API_BASE_URI . $segment . '/' . $segmentID . ':' . $this->encodeSelectors($fieldSelectors) . '?' . http_build_query($essentialParams);
        if (strlen($getProperties) > 0) $completeURL .= '&' . http_build_query($getProperties);

        // Clean up old variables
        $this->httpInfo = array();

        // Curl settings
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        curl_setopt($ci, CURLOPT_POST, FALSE);
        curl_setopt($ci, CURLOPT_URL, $completeURL);

        if (!empty($postProperties)) curl_setopt($ci, CURLOPT_POSTFIELDS, $postProperties);

        // Display its output
		$response = curl_exec($ci);
        $this->httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->httpInfo = array_merge($this->httpInfo, curl_getinfo($ci));
        curl_close($ci);

        // Display its output
        return json_decode($response, true);
	}

    /**
    * Encode all selectors into linkedin's field format
    */
    private function encodeSelectors($array) {
        $text = "";
        $keys = array_keys($array);
        for ($i = 0; $i < count($keys); $i++) {
            if (is_array($array[$keys[$i]])) {
                $text .= $keys[$i] . ":" . encodeSelectors($array[$keys[$i]]);
            } else {
                $text .= $keys[$i];
            }
            if ($i < count($keys) - 1 && count($keys) > 1) $text .= ",";
        }
        return "(" . $text . ")";
    }

    /**
    * Get the header info to store.
    */
    private function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        
        return strlen($header);
    }
		  
}
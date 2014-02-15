<?php

/*
* Pedro Góes (contato@estudiotrilha.com.br) http://estudiotrilha.com.br
*
* LinkedIn PHP SDK
*
* License: MIT
*/

// Include our main class
require_once('linkedin.php');

// Define access token
$linkedin->setAccessToken($acessToken);

// Make a request
$response = $linkedin->request("people", "~", array(
    "id" => '',
    "first-name" => '',
    "last-name" => '',
    "email-address" => '',
    "picture-url" => ''
));

?>
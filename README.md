LinkedIn
========

This is a PHP SDK for LinkedIn. All its documentation can be accessed at [https://developer.linkedin.com/rest](https://developer.linkedin.com/rest).

Installation
--------
0. Upload all the files to your host.
1. Include the file 'linkedin.php'.
2. You are set to go!

How-to
--------
Access the file 'example.php' for a SDK How-to.

	// Create the object
	$linkedin = new LinkedIn(array(
	    'apiKey' => '<API KEY>',
	    'apiSecret' => '<API SECRET>',
	    'callbackUrl' => '<CALLBACK>'
	));

	// Define access token
    $linkedin->setAccessToken($accessToken);

    // Make a request
    $response = $linkedin->request("people", "~", array(
        "id" => '',
        "first-name" => '',
        "last-name" => '',
        "email-address" => '',
        "picture-url" => ''
    ));

Support
--------
Just open an issue on Github and I'll get to it as soon as possible.

About
--------
LinkedIn for PHP is brought to you by Estúdio Trilha.
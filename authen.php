<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
//error_reporting(0);

if(session_id() == '') {
    session_start();
}

require_once realpath(dirname(__FILE__) . '/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
$test = false;

if($test){
	$client_id 			= '197165612997-9vkntk8lsosl65uq91l7krkcotl9a07o.apps.googleusercontent.com';
	$client_secret 	= 'LBZaUhiFYMnLJ32g-v09U13O';
	$redirect_uri 	= 'http://localhost/sentiment/index.php';
}else {
	$client_id 			= '197165612997-307r62e1pkmllkb61qm5pv366f6up3kj.apps.googleusercontent.com';
	$client_secret 	= '-_9SZpSEPIKekFvHQrnqOUo9';
	$redirect_uri 	= 'http://wdginteractive.com/sentimeant/index.php';	
}
	$devKey = 'AIzaSyDozCGBFFyzbK663jbbIYieUXu0C07iCt8';
/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = new Google_Client();
$client->setApplicationName('Sentimeant'); // Set your app name here
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setDeveloperKey($devKey);


$client->addScope("https://www.googleapis.com/auth/gmail.labels");
$client->addScope("https://www.googleapis.com/auth/gmail.readonly");
$client->addScope("https://www.googleapis.com/auth/gmail.labels");
$client->addScope("https://www.googleapis.com/auth/gmail.modify");

/************************************************
  We are going to create both YouTube and Drive
  services, and query both.
 ************************************************/
$mail_service = new Google_Service_Gmail($client);

/************************************************
  Boilerplate auth management - see
  user-example.php for details.
 ************************************************/

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}
if (isset($_GET['code'])) {

	// Exchange the authentication code with the Google Client.
	$client->authenticate($_GET['code']); 
	
	// Retrieve the access token from the Google Client.
	// In this example, we are storing the access token in
	// the session storage - you may want to use a database instead.
	$_SESSION['access_token'] = $client->getAccessToken(); 
	
	$results['redirect_url'] = filter_var($redirect_uri, FILTER_SANITIZE_URL);
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && !empty($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
} 

// If the Google Client does not have an authenticated access token,
// have the user go through the OAuth2 authentication flow.
if (!$client->getAccessToken()) {
    // Get the OAuth2 authentication URL.
    $_SESSION['authUrl'] = $client->createAuthUrl();
    $results['authUrl'] = $_SESSION['authUrl'];
		
}

echo json_encode($results);



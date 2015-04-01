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
//error_reporting(1);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
include_once "templates/base.php";
require_once 'alchemyapi.php';
require_once 'class/GmailUtil.php';

$alchemyapi = new AlchemyAPI();
if(session_id() == '') {
    session_start();
}

require_once realpath(dirname(__FILE__) . '/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
$test=false;

if($test){
	$client_id = '197165612997-vmlqk6jpof78lk6vpdpc22vobqg3i8cc.apps.googleusercontent.com';
	$client_secret = 'cz-iFAwfuTUVknChhY11OKNq';
	$redirect_uri = 'http://localhost/sentiment/index1.php';	
}else{
	$client_id = '197165612997-d2on8jbbc2ppqvuulg7udvvkanu90r14.apps.googleusercontent.com';
	$client_secret = 'T8gxhuHTss6KkahSTCL_GoXJ';
	$redirect_uri = 'http://sentimeant.wdginteractive.com/index.php';	
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
$yt_service = new Google_Service_YouTube($client);
$dr_service = new Google_Service_Drive($client);
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
	
	// Once the access token is retrieved, you no longer need the
	// authorization code in the URL. Redirect the user to a clean URL.
	header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && !empty($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
} 

// If the Google Client does not have an authenticated access token,
// have the user go through the OAuth2 authentication flow.
if (!$client->getAccessToken()) {
    // Get the OAuth2 authentication URL.
    $authUrl = $client->createAuthUrl();
}

echo pageHeader("");

?>
<div class="container">
<div class="header">

</div>
<div class="clear"></div>
<div class="box">
  <div class="request">
<?php 
if (isset($authUrl)) {
  echo "<a class='login' href='" . $authUrl . "'>Start</a>";
}else {
	
	//echo "<script>alert('test');</script>";
	$messages   	= array();
	$opt_param  	= array();
	$client_id  	= 'me';
	$target_label = 'Sentimeant';
	$gmail_util 	= new GmailUtil($mail_service,$client_id);
	$label     	  = $gmail_util->listLabels($mail_service,$client_id,$target_label);
	//echo($label);
//	if(!isset($label)){
//		echo "No emails with label 'Sentimeant'";
//	}
//
	$opt_param['labelIds'] = $label;
//	$opt_param['maxResults'] = 2;
//	
	$messages   = $gmail_util->listMessages($mail_service,$client_id,$opt_param);

	try {

		$responses = array();
		$total_score = 0;
		$total_num   = 0;
		$percent 		 = 0;
		foreach ($messages as $message) {
		
		 $a = $mail_service->users_messages->get('me', $message->getId(),array('full'));
		 $msg_part = $a->getSnippet();
		 

			 if(isset($msg_part)){
					$response = $alchemyapi->sentiment('html',$msg_part, null);
					array_push($responses,$response);
					$responses[$total_num]['word'] = $msg_part;
					$score = 0;
					switch ($response['docSentiment']['type']) {
						case 'natural':
						$score = 0;
						break;
						case 'positive':
						case 'negative':
						$score = $response['docSentiment']['score'];
						break;
						default:
						break;
					}
					
					$total_score += floatval($score);
					$total_num++;
			 }
		 
		}
		
		$percent 	= $total_score * 100 / $total_num;
		$status 	= "";
		$image 		= "";
		if ($total_score > 0.15) {
				$status = "Positive";
				$image  = "images/green.png";
		}else if($total_score < 0.15){
				$status = "Negative";
				$image  = "images/red.png";
		}else if($total_score = 0.15){
				$status = "Netural";
				$image  = "images/yellow.png";
		}
		$percent_text = number_format($percent, 2, '.', ',');
		echo "<div class='result-box'><div class='status'><img src='".$image."' /></div>";
		echo "<div class='status-text'>Your inbox is ".$percent_text."%  ".$status."</div></div>";
		echo "<div class='clear'></div>";
		$i=0;
		foreach ($responses as $response) {
			echo '<br/>';
		
			if ($response['status'] == 'OK') {
				echo "<div class='score'>";
				if (array_key_exists('score', $response['docSentiment'])) {
					$word = $responses[$i]['word'];
					$msgscore = $response['docSentiment']['score']; 
					$font = 'green';
					if($msgscore < 0){
						$font = 'red';
					}else if($msgscore == 0 ){
						$font = 'yellow';
					}
					if(!empty($word)){
						echo 'Score <span class="'.$font.'">'.number_format($msgscore, 2, '.', ',').'</span>';
						echo ' : " ' . $word .' " </div>';
					}
				}				
				

			}	   
			$i++;
		}	
			
			
			
	} catch (Exception $e) {
	  print 'An error occurred: ' . $e->getMessage();
	}
	
} 
?>
  </div>
</div>
</div>
<?php echo pageFooter();

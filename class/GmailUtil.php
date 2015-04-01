<?php 

class GmailUtil
{
	public $user_id;
	public $service;
	
  public function __construct($service,$user_id)
  {
  	$this->user_id = $user_id;
  	$this->service  = $service;
  }
  
	/**
	 * Get all the Labels in the user's mailbox.
	 *
	 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
	 * @param  string $userId User's email address. The special value 'me'
	 * can be used to indicate the authenticated user.
	 * @return array Array of Labels.
	 */
	public function listLabels($service,$user_id,$targetWord) {
	  $labels = array();
	
	  try {
	    $labelsResponse = $service->users_labels->listUsersLabels($user_id);
	
	    if ($labelsResponse->getLabels()) {
	      $labels = array_merge($labels, $labelsResponse->getLabels());
	    }
	
	    foreach ($labels as $label) {
	     if($label->getName() == $targetWord){
	     	return $label->getId();	
	 			break;
	     }
	    }
	  } catch (Excetion $e) {
	    print 'An error occurred: ' . $e->getMessage();
	  }
	
	  return null;
	}
	
	/**
	 * Get list of Messages in user's mailbox.
	 *
	 * @param  Google_Service_Gmail $service Authorized Gmail API instance.
	 * @param  string $userId User's email address. The special value 'me'
	 * can be used to indicate the authenticated user.
	 * @return array Array of Messages.
	 */
	function listMessages($service,$user_id,$opt_param = null) {
	  $pageToken = NULL;
	  $messages = array();
	  do {
	    try {
	      if ($pageToken) {
	        $opt_param['pageToken'] = $pageToken;
	      }
	      $messagesResponse = $service->users_messages->listUsersMessages($user_id, $opt_param);
	      if ($messagesResponse->getMessages()) {
	        $messages = array_merge($messages, $messagesResponse->getMessages());
	        $pageToken = $messagesResponse->getNextPageToken();
	      }
	    } catch (Exception $e) {
	      print 'An error occurred: ' . $e->getMessage();
	    }
	  } while ($pageToken);
	
	  return $messages;
	}
	
}
?>
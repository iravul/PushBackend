<?php

/*
 * 		Device Helper
 * 		Insert/update Device infos
 *
 * 		Ergin - 16.06.2016
 */
require(APPPATH.'libraries/AmazonSNS.php');


function deviceStatus($user_id,$device)
{
	$ci = get_instance();
	$ci -> load -> model('device_model','Device');

	if($device['osType']=='ios' || $device['osType']=='iOS')
	{
		//is device exist?
		$deviceState = $ci->Device->checkDevice($device['token']);
			
		//same device exist
		if($deviceState!=false)
		{
			//match user with device
			$ci->Device->loginIosDevice($user_id,$device['token'],$device['app_version']);

			if($device['token']!=0 || !empty($device['token'])) {
				if($device["token_type"]=="development"){
					$type= "ios-dev";
				} else {
					$type= "ios-prod";
				}
				//token state may be false in AWS SNS, so should subscribe it again
				createSubscription($device['token'],$type);
			}
			return true;
		}
		else
		{
			//no device exist, so subscribe it to AWS and add to database
			$device['amazon_arn'] = "";
			if($device['token']!=0 || !empty($device['token'])) {
				if($device["token_type"]=="development") {
					$type= "ios-dev";
				} else {
					$type= "ios-prod";
				}
				$devicearn = createSubscription($device['token'],$type);
				$device['amazon_arn'] = $devicearn;
			}
			//insert all info to device_token table
			$ci->Device->insertDevice($user_id,$device);
			return true;
		}
	}
	else if($device['osType']=='android')
	{
		//device id is unique on Android so we check the device_id first.
		$deviceState = $ci->Device->checkAndroidDevice($device['deviceId']);
		if($deviceState!=false)
		{
			//token may be changed on Android so weh should check it
			if($deviceState->token!=$device['token'])
			{
				//if token is not same, we remove it from AWS(deletepoint) and our database
				//after that, we create subscription and add it to database
				$ci = get_instance();
				$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));
				if($deviceState->amazon_arn=="0" || $deviceState->amazon_arn=="") {

				} else {
					$AmazonSNS->deleteEndpoint($deviceState->amazon_arn);
				}
				$delete_Array= array('id'=> $deviceState->id);
				$ci->db->delete('device_token',$delete_Array);
				$device['amazon_arn'] = "";
				if($device['token']!="0" && $device['token']!="") {
					$devicearn = createSubscription($device['token'], $device['osType']);
					$device['amazon_arn'] = $devicearn;
				}
				$ci->Device->insertDevice($user_id,$device);
			}
			else
			{
				//if token not changed then check if it it matched with user or not
				if($user_id!=$deviceState->user_id)
				{
					//if token is not matched with user, then update it with user
					$ci->Device->loginIosDevice($user_id,$device['token'],$device['app_version']);
					if($device['token']!="0" && $device['token']!="") {
						createSubscription($device['token'], $device['osType']);
					}
					return true;
				}
			}
		}
		else
		{
			//no device exist, so subscribe it to AWS and add to database
			$device['amazon_arn'] = "";
			if($device['token']!="0" && $device['token']!="") {
				$devicearn = createSubscription($device['token'], $device['osType']);
				$device['amazon_arn'] = $devicearn;
			}
			$ci->Device->insertDevice($user_id,$device);
			return true;
		}
	}

}



///create AWS SNS arn from device token
function createSubscription($token,$type)
{
	$ci = get_instance();
	$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

	if($type=="android")
	{
		$appArn = $ci->config->item('push_arn_android');
	}
	else if($type=="ios-dev")
	{
		$appArn = $ci->config->item('push_arn_ios_dev');
	}else if($type=="ios-prod")
	{
		$appArn = $ci->config->item('push_arn_ios_pro');
	}

	try {
		$devicearn = $AmazonSNS->createEndpoint($appArn, $token);
	}
	catch(SNSException $e) {
		// Amazon SNS returned an error
		return false;
		//echo 'SNS returned the error "' . $e->getMessage() . '" and code ' . $e->getCode();
	}
	catch(APIException $e) {
		// Problem with the API
		return false;
		//echo 'There was an unknown problem with the API, returned code ' . $e->getCode();
	}
	return $devicearn;
}
	

	//subscribe device to topic
	function subsToTopic($devicearn,$topicArn)
	{
		$ci = get_instance();
		$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

		try {
			$subscriber_id = $AmazonSNS->subscribe($topicArn,"application",$devicearn);
		}
		catch(SNSException $e) {
				// Amazon SNS returned an error
			}
		catch(APIException $e) {
				// Problem with the API
			}
		return $subscriber_id;


	}

	//unsubscribe device from topic and remove it from database
	function unSubsLogoutTopic($user_id,$device)
	{
		$ci = get_instance();
		$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));
		$ci->load->model('device_model','Device');
		$topicList = $ci->Device->getAnrByDevice($user_id,$device["token"]);
		
		try {
			if($topicList)
			{
				foreach($topicList as $tp)
				{
					$AmazonSNS->unsubscribe($tp->subscriber_id,$tp->topicarn);
					$ci->Device->removeDevice($tp->id);
				}
			}
			$ci->Device->loginIosDevice("0",$device['token'],$device['app_version']);
			
		}
		catch(SNSException $e) {
			// Amazon SNS returned an error
		}
		catch(APIException $e) {
			// Problem with the API
		}
		return true;


	}
	
	//unsubscribe device from topic
	function unsubsToTopic($subscriber_id,$topic_id)
	{
		$ci = get_instance();
		$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

		try {
			$AmazonSNS->unsubscribe($subscriber_id,$topic_id);
		}
		catch(SNSException $e) {
			// Amazon SNS returned an error
		}
		catch(APIException $e) {
			// Problem with the API
		}
		return $subscriber_id;


	}

	//create topic as given title
	function createTopic($title)
	{
		$ci = get_instance();
		$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

		try {
			$topicarn = $AmazonSNS->createTopic($title);
		}
		catch(SNSException $e) {
			// Amazon SNS returned an error
		}
		catch(APIException $e) {
			// Problem with the API
		}
		return $topicarn;


	}
	
	//remove token from AWS SNS
	function deleteArn($token)
	{
		$ci = get_instance();
		$deviceState = $ci->Device->checkDevice($token);
		if($deviceState!=false)
		{
			$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

			if($deviceState->amazon_arn=="0" || $deviceState->amazon_arn=="")
			{
			} else {
				$AmazonSNS->deleteEndpoint($deviceState->amazon_arn);
			}
	
			$delete_Array= array('id'=> $deviceState->id);
			$ci->db->delete('device_token',$delete_Array);
		}
		return true;
	}

	//send push message
	function sendPushMessage($user_id,$message,$type,$type_id)
	{
		$ci = get_instance();
		$ci->load->model('device_model','Device');
		
		$AmazonSNS = new AmazonSNS($ci->config->item('aws_accesskey'),$ci->config->item('aws_secretkey'),$ci->config->item('aws_region'));

		$deviceInfo = $ci->Device->getOnlineDevice($user_id);

		if($deviceInfo)
		{
			$message = array(
				"default" =>  $message,
				"APNS_SANDBOX" => json_encode(array("aps" => array(
					'alert' => $message,'sound' => "default",
					'type'  => $type,
					'type_id' =>$type_id

				)
				)),
				"APNS" => json_encode(array("aps" => array(
					'alert' => $message,'sound' => "default",
					'type'  => $type,
					'type_id' =>$type_id
				)
				)),
				"GCM" => json_encode(array(
					"data" => array('message' => $message,'sound' => "default",
						'type'  =>$type,
						'type_id' =>$type_id )))
			);

			$content = json_encode($message);
			foreach($deviceInfo as $d)
			{
				try {
					$AmazonSNS->publishToOne($d->amazon_arn, $content, "");
				}
				catch(SNSException $e) {
					print_r($e);
					// Amazon SNS returned an error
				}
				catch(APIException $e) {
					print_r($e);
					// Problem with the API
				}
			}
			return true;
		}
	}
?>
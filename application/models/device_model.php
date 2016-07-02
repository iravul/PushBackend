<?php if ( !defined('BASEPATH') ) exit('No direct script access allowed');

class Device_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function checkDevice($token)
	{
		$this->db->select('*')
			->from('device_token')
			->where('token', $token);

		$query = $this->db->get();
		if ( $query->num_rows > 0 )
		{
			$result = $query->row();
			$query->free_result();
			return $result;
		}
		else
		{
			return false;
		}
	}
	//added the app version
	function loginIosDevice($user_id,$token,$app_version)
	{
		//update event_user state
		$query = $this->db->set('user_id', $user_id)
			->set('app_version', $app_version)
			->where('token',  $token)
			->update('device_token');
		if ($query==TRUE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function insertDevice($user_id = 0,$device)
	{
		//userid push to device, state 1 push to device
		//insert comment
		$this->logoutAllDevice($device['token']);

		$insert = array(
			"user_id" => $user_id,
			"osType"=> $device['osType'],
			"osVersion"=> $device['osVersion'],
			"device"=> $device['device'],
			"deviceId"=> $device['deviceId'],
			"token"=> $device['token'],
			"token_type"=> $device['token_type'],
			"app_version"=> $device['app_version'],
			"amazon_arn"=> $device['amazon_arn'],
			"state"=> 'active');

		$query = $this->db->insert('device_token',$insert);
		if ($query == TRUE)
		{

			return true;
		}
		else
		{
			return false;
		}
	}

	function logoutAllDevice($token)
	{
		$query = $this->db->set('state', 'passive')
			->where('token',  $token)
			->update('device_token');
		if ($query==TRUE) {
			return true;
		}
		else {
			return false;
		}
	}



	function logoutDevice($user_id,$token)
	{
		$query = $this->db->set('state', 'passive')
			->where('user_id', $user_id)
			->where('token',  $token)
			->update('device_token');
		if ($query==TRUE) {
			return true;
		}
		else {
			return false;
		}
	}

	/*
	 *
	 *
	 */
	function checkAndroidDevice($deviceId)
	{
		//userid push to device, state 1 push to device
		//insert comment
		$this->db->select('id, user_id, token, amazon_arn')
			->from('device_token')
			->where('deviceId', $deviceId);
		$query = $this->db->get();
		if ( $query->num_rows > 0 )
		{
			$result = $query->row();
			$query->free_result();
			return $result;
		}
		else
		{
			return false;
		}
	}


	function loginAndroidDevice($user_id,$deviceId,$app_version)
	{
		$query = $this->db->set('state', 'active')
			->set('app_version', $app_version)
			->where('user_id', $user_id)
			->where('deviceId',  $deviceId)
			->update('device_token');
		if ($query==TRUE)
		{
			return true;
		} else {
			return false;
		}
	}

	function updateAndroidDeviceToken($user_id,$deviceId,$deviceToken,$app_version)
	{
		$query = $this->db->set('token', $deviceToken)
			->set('app_version', $app_version)
			->where('user_id', $user_id)
			->where('deviceId', $deviceId)
			->update('device_token');

		if ( $query==TRUE)
		{
			return true;
		} else {
			return false;
		}
	}


	function getOnlineDevice($user_id)
	{
		//userid push to device, state 1 push to device
		//insert comment
		$this->db->select('*')
			->from('device_token')
			->where('user_id', $user_id)
			->where('token !=','')
			->where('amazon_arn !=','')
			->where('state', 'active');
		$query = $this->db->get();
		if ( $query->num_rows > 0 )
		{
			$result = $query->result();
			$query->free_result();
			return $result;
		}
		else
		{
			return false;
		}
	}

}

/* End of file device_model.php */
/* Location: ./application/models/device_model.php */
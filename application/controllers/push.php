<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');

####################################################################################################
#	Push Class
#	Last Modified: Ergin - 26.06.2016
####################################################################################################

class Push extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('device_model','Device');
    }

    ####################################################################################################
    #	sendPush function is used for sending push messages.
    #
    #   $message: should be max 200 char because of push message packet size limit
    #   $user_id: can equal to your system's user identifier. It is varchar on MySQL. Also you can edit it.
    #	$type   : will be used for differentiate push type. you can send general message, image, sound or pool push messages. it will be differed by $type attribute.
    #   $type_id: you can get push detail with type_id and type values. then you make a request and get data.
    #
    ####################################################################################################


    public function sendPush_post()
    {
        $user_id = $this->input->post('user_id');
        $message = $this->input->post('message');

        //optional parameters
        $type_id = $this->input->post('type_id')?$this->input->post('type_id'):"0";
        $type = $this->input->post('type')?$this->input->post('type'):"general";

        sendPushMessage($user_id,$message,$type,$type_id);

    }

    public function setToken_post()
    {

        $user_id = $this->input->post('user_id');

        $device = array("deviceId"	=> ($this->input->post('deviceId'))?$this->input->post('deviceId'):"",
            "device"		=> ($this->input->post('device'))?$this->input->post('device'):"",
            "token_type" 	=> ($this->input->post('token_type'))?$this->input->post('token_type'):"",
            "osType" 		=> ($this->input->post('osType'))?$this->input->post('osType'):"",
            "osVersion" 	=> ($this->input->post('osVersion'))?$this->input->post('osVersion'):"",
            "token" 		=> ($this->input->post('token'))?$this->input->post('token'):"",
            "app_version"   => ($this->input->post('app_version'))?$this->input->post('app_version'):""
        );


        deviceStatus($user_id, $device);

        $this->response(array('status' => true, 'code' => 200, 'message' => 'User and device matched'), 200);

    }


    function removeToken_post()
    {
        $user_id = $this->input->post('user_id');
        $device_token = $this->input->post('token');

        $state = deleteArn($device_token);
        if($state==true) {
            $this->response(array('status' => true, 'code' => 200, 'message' => 'Device deleted from user info'), 200);
        } else {
            $this->response(array('status' => false, 'code' => 201, 'message' => 'Error!!! Device not deleted from user info'), 200);
        }
    }



}//eo class

/* End of file push.php */
/* Location: ./application/controllers/push.php */
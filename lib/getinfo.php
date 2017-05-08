<?php
namespace OCA\SingleSignOn1;

class GetInfo implements IUserInfoRequest {
    private $soapClient;
    private $setupParams = array();
    private $userId;
    private $email;
    private $groups = array();
    private $userGroup;
    private $displayName;
    private $token;
    private $errorMsg;

    public function __construct($soapClient){
        $this->soapClient = $soapClient;
    }


    public function setup($params)
    {
        foreach ($params as $key => $value) {
            $this->setupParams[$key] = $value;
        }
    }


    public function name() {
        return ISingleSignOnRequest::INFO;
    }

    public function send($data = null) {
        $result = $this->soapClient->getConnection()->__soapCall("getToken2", array(array("TokenId" => $data["token1"], "UserIP" => $data["userIp"])));

        if($result->return->ActXML->StatusCode != 200) {
            $this->errorMsg = $result->return->ActXML->Message;
            return false;
        }

        $info = $result->return->ActXML->RsInfo->User;

        $this->userId = $info->UserAccount;
        $this->email = $info->UserEmail;
        $this->displayName = $info->CName;
        $this->userSid = $info->UserSid;
        $this->userGroup = $info->UserGroup;
        $this->token = $data["token1"];

        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getGroups() {
        return $this->groups;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Get user auth token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }
    

    /**
     * Getter for userSid
     *
     * @return string userSid
     */
    public function getRegion() {
        return (int)substr($this->userSid,0,2);
    }

    /**
     * Check user have permassion to use the service or not
     *
     * @return bool
     */
    public function hasPermission(){

        return true;
    }
    
    /**
     * Check has error massage or not
     *
     * @return true|false
     */
    public function hasErrorMsg()
    {
        return $this->errorMsg ? true : false;
    }
}

<?php

namespace OCA\SingleSignOn1;

class GetToken1 implements ISingleSignOnRequest {

    private $soapClient;
    private $errorMsg;
    
    public function __construct($soapClient){
        $this->soapClient = $soapClient;
    }

    public function name() {
        return ISingleSignOnRequest::GETTOKEN;    
    }

    public function send($data = null) {
        $result = $this->soapClient->getConnection()->__soapCall("getToken1", array(array("UserId" => $data["userId"],"Password" => $data["password"],  "UserIP" => $data["userIp"])));

        if($result->return->ActXML->StatusCode != 200) {
            $this->errorMsg = $result->return->ActXML->Message;
            return false;
        }

        return $result->return->ActXML->RsInfo->TokenId;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }
}

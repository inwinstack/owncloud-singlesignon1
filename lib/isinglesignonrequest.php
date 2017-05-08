<?php

namespace OCA\SingleSignOn1;

interface ISingleSignOnRequest {
    const VALIDTOKEN = "validtoken";
    const INFO = "info";
    const USERPASSWORDGENERATOR = "userpasswordgenerator";
    const INVALIDTOKEN = "invalidtoken";
    const GETTOKEN = "gettoken";

    public function name();
    public function send($data);
    public function getErrorMsg();
}


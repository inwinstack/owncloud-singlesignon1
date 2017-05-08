<?php
namespace OCA\SingleSignOn1;

use Exception;

class Util {
    public static function login($userInfo, $authInfo) {
        $userID = $userInfo->getUserId();
        $userToken = $userInfo->getToken();
        $manager = \OC::$server->getUserManager();

        //$manager->emit('\OC\User', 'preLogin', array($userID, $userToken));

        $user = $manager->get($userID);
        \OC::$server->getUserSession()->setUser($user);
        \OC::$server->getUserSession()->setLoginName($user);
        \OC_Util::setupFS($userID);
        \OC::$server->getUserFolder($userID);

        if (class_exists('\\OCA\\SingleSignOn1\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo);
        }

        $manager->emit('\OC\User', 'postLogin', array($user, $userToken));

        self::wirteAuthInfoToSession($authInfo);

        return true;
    }

    public static function firstLogin($userInfo, $authInfo) {
        $userID = $userInfo->getUserId();
        $password = RequestManager::getRequest(ISingleSignOnRequest::USERPASSWORDGENERATOR) ? RequestManager::send(ISingleSignOnRequest::USERPASSWORDGENERATOR) : $userID;

        $user = \OC_User::createUser($userID, $password);

        if (class_exists('\\OCA\\SingleSignOn1\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo);
        }

        self::wirteAuthInfoToSession($authInfo);
        return \OC_User::login($userID, $password);
    }

    public static function webDavLogin($userID, $password) {
        $config = \OC::$server->getSystemConfig();

        RequestManager::init($config->getValue("sso_portal_url1"), $config->getValue("sso_requests1"));

        $authInfo = WebDavAuthInfo::get($userID, $password);

        $userInfo = RequestManager::getRequest(ISingleSignOnRequest::INFO);

        $userInfo->setup(array("action" => "webDavLogin"));

        if(!$userInfo->send($authInfo)) {
            return ;
        }

        if($config->getValue("sso_multiple_region1")) {
            self::redirectRegion($userInfo, $config->getValue("sso_regions1"), $config->getValue("sso_owncloud_url1"));
        }
        
        if(!\OC_User::userExists($userInfo->getUserId())) {
            return self::firstLogin($userInfo, $authInfo);
        }

        if($authInfo){
            return self::login($userInfo, $authInfo);
        }

        return false;
    }

    public static function redirect($url) {
        if(!$url) {
            \OC_Util::redirectToDefaultPage();
        }
        else {
            header("location: " . $url);
            exit();
        }
    }

    /**
     * Check user region and redirect to correct region.
     *
     * @return void
     */
    public static function redirectRegion($userInfo, $regions, $serverUrls) {
        $region = $userInfo->getRegion();
        $request = \OC::$server->getRequest();

        if($request->getServerHost() === $serverUrls[$regions[$region]]) {
            return ;
        }

        $redirectUrl = RedirectRegion::getRegionUrl($region);

        self::redirect($redirectUrl);
    }

    /**
     * Write auth info to session
     *
     * @param array $authInfo
     * @return void
     */
    public static function wirteAuthInfoToSession($authInfo)
    {
        foreach ($authInfo as $key => $value) {
            \OC::$server->getSession()->set("sso_" . $key, $value);
        }
    }
}

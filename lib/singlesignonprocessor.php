<?php
namespace OCA\SingleSignOn1;

use Exception;

class SingleSignOnProcessor {

    /**
     * required keys in config/config.php
     */
    private static $requiredKeys = array("sso_login_url1",
                                         "sso_return_url_key1",
                                         "sso_requests1",
                                         "sso_portal_url1",
                                         "sso_global_logout1",
                                         "sso_multiple_region",
                                         "sso_admin_login_port1",
                                         "sso_admin_login_uri1",
                                         "sso_one_time_password1");

    /**
     * uri which unnecessary authenticate with Single Sign-On
     */
    private static $unnecessaryAuthUri = array("(.*\/webdav.*)",
                                                "(.*\/cloud.*)",
                                                "(.*\/s\/.*)",
                                                "(\/admin)",
                                                "(.*\/ocs\/.*)",
                                                "(\/core\/js\/oc\.js)",
                                                "(\/apps\/gallery\/config\.public)",
                                                "(.*\/files_sharing\/ajax\/.*)",
                                                "(.*\/files_sharing\/shareinfo.*)",
                                                "(\/apps\/files_pdfviewer\/)",
                                                "(\/apps\/gallery\/.*)");

    /**
     * Necessary class
     *
     * @var array
     */
    private static $necessaryImplementationClass = array("\\OCA\\SingleSignOn1\\AuthInfo",
                                                  "\\OCA\\SingleSignOn1\\APIServerConnection",
                                                  "\\OCA\\SingleSignOn1\\WebDavAuthInfo");

    /**
     * \OC\SystemConfig
     */
    private $config;

    /**
     * \OC\Appframework\Http\Request
     */
    private $request; 

    /**
     * user token
     */
    private $token;

    /**
     * url where to redirect after SSO login
     */
    private $redirectUrl;

    /**
     * user visit port on server
     *
     * @var int
     */
    private $visitPort;

    public function run() {
        try {
            $this->process();
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function __construct() {
        $this->request = \OC::$server->getRequest();
        $this->config = \OC::$server->getSystemConfig();
        $this->redirectUrl = $this->request->getRequestUri();
        $this->defaultPageUrl = \OC_Util::getDefaultPageUrl();
        $this->visitPort = (int)$_SERVER["SERVER_PORT"];

        if($this->config->getValue("sso_multiple_region")) {
            array_push(self::$requiredKeys, "sso_owncloud_url");
            array_push(self::$requiredKeys, "sso_regions");
            array_push(self::$necessaryImplementationClass, "\\OCA\\SingleSignOn1\\RedirectRegion");
        }

        foreach(self::$necessaryImplementationClass as $class) {
            if(!class_exists($class)) {
                throw new Exception("The class " . $class . " did't exist.");
            }
        }

        self::checkKeyExist(self::$requiredKeys);

        RequestManager::init($this->config->getValue("sso_portal_url1"), $this->config->getValue("sso_requests1"));
    }

    public function process() {
        $ssoUrl = $this->config->getValue("sso_login_url1");
        $userInfo = RequestManager::getRequest(ISingleSignOnRequest::INFO);
        $authInfo = AuthInfo::get();

        $userInfo->setup(array("action" => "webLogin"));

        if($this->unnecessaryAuth($this->request->getRequestUri())){
            $uri = substr($this->request->getRequestUri(), (-1)*strlen($this->config->getValue("sso_admin_login_uri1")));
            if ($uri === $this->config->getValue("sso_admin_login_uri1") && $this->visitPort != $this->config->getValue("sso_admin_login_port1")) {
                Util::redirect($this->defaultPageUrl);
            }
            return;
        }

        if(isset($_GET["logout"]) && $_GET["logout"] == "true") {
            if($this->config->getValue("sso_global_logout1")) {
                RequestManager::send(ISingleSignOnRequest::INVALIDTOKEN, $authInfo);
            }
            \OC_User::logout();
            $template = new \OC_Template("singlesignon1", "logout", "guest");
            $template->printPage();
            die();
        }

        if(\OC_User::isLoggedIn() && $this->config->getValue("sso_one_time_password1")) {
            return;
        }

        if(\OC_User::isLoggedIn() && !$authInfo) {
            header("HTTP/1.1 " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("Status: " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("WWW-Authenticate: ");
            header("Retry-After: 120");

            $template = new \OC_Template("singlesignon1", "unauthorizedActions", "guest");
            $template->printPage();
            die();
        }

        if(\OC_User::isLoggedIn() && (!RequestManager::send(ISingleSignOnRequest::VALIDTOKEN, $authInfo) && !$this->config->getValue("sso_one_time_password1"))) {
            header("HTTP/1.1 " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("Status: " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED); 
            header("WWW-Authenticate: "); 
            header("Retry-After: 120");

            $template = new \OC_Template("singlesignon1", "tokenExpired", "guest");
            $template->printPage();
            die();
        }

        if(!$authInfo || (!RequestManager::send(ISingleSignOnRequest::VALIDTOKEN, $authInfo) && !$this->config->getValue("sso_one_time_password1"))) {
            $url = $this->redirectUrl ? $ssoUrl . $this->config->getValue("sso_return_url_key1") . $this->redirectUrl : $ssoUrl;
            Util::redirect($url);
        }

        if(\OC_User::isLoggedIn()) {
            return ;
        }

        if(empty($ssoUrl) || !$userInfo->send($authInfo) || !$userInfo->hasPermission()) {
            header("HTTP/1.1 " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("Status: " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("WWW-Authenticate: ");
            header("Retry-After: 120");

            $template = new \OC_Template("singlesignon1", "verificationFailure", "guest");
            $template->printPage();
            if($userInfo->hasErrorMsg()) {
                \OCP\Util::writeLog("Single Sign-On", $userInfo->getErrorMsg(), \OCP\Util::ERROR);
            }
            die();
        }

        if($this->config->getValue("sso_multiple_region")) {
            Util::redirectRegion($userInfo, $this->config->getValue("sso_regions"), $this->config->getValue("sso_owncloud_url"));
        }

        if(!\OC_User::userExists($userInfo->getUserId())) {
            \OC::$server->getSession()->set("LOGIN_ASUS",true);
            Util::firstLogin($userInfo, $authInfo);
            if($this->request->getHeader("ORIGIN")) {
                return;
            }
            Util::redirect($this->defaultPageUrl);
        }
        else {
            \OC::$server->getSession()->set("LOGIN_ASUS",true);
            Util::login($userInfo, $authInfo);
        
            if($this->request->getHeader("ORIGIN")) {
                return;
            }

            Util::redirect($this->defaultPageUrl);
        }
    }

    /**
     * Check key is exist or not in config/config.php
     *
     * @param array reqiured keys
     * @return void
     */
    public static function checkKeyExist($requiredKeys) {
        $configKeys = \OC::$server->getSystemConfig()->getKeys();

        foreach ($requiredKeys as $key) {
            if (!in_array($key, $configKeys)) {
                throw new Exception("The config key " . $key . " did't exist.");
            }
        }
    }

    /**
     * unnecessaryAuth
     * @param array url path
     * @param array uri
     * @return bool
     **/
    private function unnecessaryAuth($uri) {
        for ($i = 0; $i < count(self::$unnecessaryAuthUri); $i++) {
            if ($i == 0) {
                $NAUri = self::$unnecessaryAuthUri[$i];
            }
            else {
                $NAUri = $NAUri . "|" . self::$unnecessaryAuthUri[$i];
            }
        }

        $NAUri = "/" . $NAUri . "/";

        preg_match($NAUri, $uri, $matches);

        if(count($matches) || \OC_User::isAdminUser(\OC_User::getUser())){
            return true;
        }

        return false;
    }
    
    /**
     * Get SingleSignOnProcessor.
     *
     * @return Object \OCA\SingleSigoOnProcessor
     */
    public static function getInstance() {
        return new static();
    }

    /**
     * Get the user token
     *
     * @return string user token
     */
    public function getToken() {
        return $this->token;
    }
}

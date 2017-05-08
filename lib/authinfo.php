<?php

namespace OCA\SingleSignOn1;

/**
 * Class AuthInfo
 * @author Dauba
 */
class AuthInfo implements IAuthInfo
{
    /**
     * requeir keys for auth info
     *
     * @var array
     */
    public static $requireKeys = array("token1");

    /**
     * auth info
     *
     * @var array
     */
    private static $info = array();

    /**
     * Getter for Info
     *
     * @return array
     */
    public static function get()
    {
        $request = \OC::$server->getRequest();
        $session = \OC::$server->getSession();
        foreach (self::$requireKeys as $key) {
            if($request->offsetGet($key)) {
                self::$info[$key] = $request->offsetGet($key);
            }
            else if($request->getHeader($key)) {
                self::$info[$key] = $request->getHeader($key);
            }
            else if($session->get("sso_" . $key)) {
                self::$info[$key] = $session->get("sso_" . $key);
            }
        }

        self::$info["userIp"] = $request->getRemoteAddress();
        foreach (self::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                if($request->getHeader('SSO_TOKEN')){
                    self::$info["token1"] = $request->getHeader("SSO_TOKEN");
                }
                else{                                                                               return null;                                                                }
               
            }
        }

        return self::$info;
    }
    
}

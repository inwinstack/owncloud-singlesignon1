<?php

namespace OCA\SingleSignOn1;

/**
 * Class UserInfoSetter
 * @author Dauba
 */
class UserInfoSetter
{
    /**
     * Set ownCloud user info
     *
     * @return void
     */
    public static function setInfo($user, $userInfo)
    {
        $config = \OC::$server->getConfig();
        $userID = $userInfo->getUserId();
        
        $data = ['region' => $userInfo->getRegion(),
                 'schoolCode' => 'undefined',
        ];
        
        $config->setUserValue($userID, "settings", "regionData", json_encode($data));
        
  
    }

}


<?php

namespace OCA\SingleSignOn1;

/**
 * Interface IWebDavAuthInfo
 * @author Dauba
 */
interface IWebDavAuthInfo
{
    /**
     * get auth info
     *
     * @return array
     * @author Dauba
     */
    public static function get($userID, $password);
}

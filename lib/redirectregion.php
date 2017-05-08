<?php
namespace OCA\SingleSignOn1;

class RedirectRegion implements IRedirectRegion{
    public static function getRegionUrl($region) {
        $request = \OC::$server->getRequest();
        $requestUri = $request->getRequestUri();
        $config = \OC::$server->getSystemConfig();
        $regions = $config->getValue("sso_regions");
        $authInfo = AuthInfo::get();
        $authInfo["asus"] = true;

        $url = $request->getServerProtocol() . "://" . $config->getValue("sso_owncloud_url")[$regions[$region]] . "?" . http_build_query($authInfo);

        return $url;
    }
}

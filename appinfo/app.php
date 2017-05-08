<?php
/**
 * ownCloud - singlesignon1
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author dauba <dauba.k@inwinstack.com>
 * @copyright dauba 2015
 */

namespace OCA\SingleSignOn1\AppInfo;

use OCP\AppFramework\App;

$app = new App('singlesignon1');
$application = new Application();
$container = $app->getContainer();

$container->registerService("L10N", function($c) {
    return $c->getServerContainer()->getL10N("singlesignon1");
});

$request = \OC::$server->getRequest();

if($request->offsetGet("asus") or $request->getHeader("SSO_TOKEN")) {
    $processor = new \OCA\SingleSignOn1\SingleSignOnProcessor();
    $processor->run();
}

\OCP\Util::addScript("singlesignon1", "script");

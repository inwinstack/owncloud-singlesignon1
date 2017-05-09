<?php
if(!empty($_POST["account"]) || !empty($_POST["password"])) {
    $redirectUrl = "https://172.20.3.18/owncloud/index.php";
    $userid = $_POST["account"];
    $password = $_POST["password"];
    $ip = $_SERVER["REMOTE_ADDR"];
    $url = "http://sso.cloud.edu.tw/ORG/service/EduCloud/auth/tokens";
    $data = ["UserId" => $userid, "Password" => $password, "UserIP" => $_SERVER["REMOTE_ADDR"]];
    $ch = curl_init(); 

    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // restore result to var
    $head = curl_exec($ch); 
    $head = json_decode($head);
    if ($head->actXML->statusCode == 200) {
        $params["token1"] = $head->actXML->rsInfo->tokenId;
        $params["userip"] = $ip;
        $params["asus"] = true; 
        $queryStr = "?" . http_build_query($params);
        
        header('location:' . $redirectUrl . $queryStr);
        exit();
    }
    else {
	$msg = "帳號不存在或密碼錯誤";
    }
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>
        雲端儲存服務        </title>
    <link rel="shortcut icon" type="image/png" href="core/img/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="core/img/favicon-touch.png">
    <link rel="stylesheet" href="core/css/styles.css" media="screen">
    <link rel="stylesheet" href="core/css/header.css" media="screen">
    <link rel="stylesheet" href="core/css/mobile.css" media="screen">
    <link rel="stylesheet" href="core/css/icons.css" media="screen">
    <link rel="stylesheet" href="core/css/fonts.css" media="screen">
    
    <link rel="stylesheet" href="core/css/apps.css" media="screen">
    <link rel="stylesheet" href="core/css/fixes.css" media="screen">
    <link rel="stylesheet" href="core/css/multiselect.css" media="screen">
    <link rel="stylesheet" href="core/vendor/jquery-ui/themes/base/jquery-ui.css" media="screen">
    <link rel="stylesheet" href="core/css/jquery-ui-fixes.css" media="screen">
    <link rel="stylesheet" href="core/css/tooltip.css" media="screen">
    <link rel="stylesheet" href="core/css/share.css" media="screen">
    <link rel="stylesheet" href="core/css/jquery.ocdialog.css" media="screen">
    
    <link rel="stylesheet" href="themes/MOE/core/css/styles.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/header.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/icons.css" media="screen">
    <link rel="stylesheet" href="themes/MOE/core/css/apps.css" media="screen">
    <!--
    <link rel="stylesheet" href="login/styles/vendor/bootstrap.css" />
    <link rel="stylesheet" href="login/styles/vendor/font-awesome.css" />
    -->
</head>
<body id="body-login">
    <div class="wrapper">
        <div class="v-align">
            <header role="banner">
                <div id="header">
                    <div class="logo svg">
                        <h1 class="hidden-visually">
                            雲端儲存服務                                </h1>
                    </div>
                    <div id="logo-claim" style="display:none;"></div>
                </div>
            </header>
                                
            <form method="post" name="login">
                <fieldset>
                    <div id="message" class="hidden">
                        <img class="float-spinner" alt="" src="/core/img/loading-dark.gif">
                        <span id="messageText"></span>
                        <div style="clear: both;"></div>
                    </div>
                    <p class="grouptop">
                        <input type="text" name="account" id="user" placeholder="使用者名稱" value="" autofocus="" autocomplete="on" autocapitalize="off" autocorrect="off" required="">
                        <label for="user" class="infield">使用者名稱</label>
                    </p>

                    <p class="groupbottom">
                        <input type="password" name="password" id="password" value="" placeholder="密碼" autocomplete="on" autocapitalize="off" autocorrect="off" required="">
                        <label for="password" class="infield">密碼</label>
                        <input type="submit" id="submit" class="login primary icon-confirm svg" title="登入" value="">
                    </p>

                    <div class="remember-login-container">
                        <input type="checkbox" name="remember_login" value="1" id="remember_login" class="checkbox checkbox--white">
                        <label for="remember_login">remember</label>
                    </div>
                    <input type="hidden" name="timezone-offset" id="timezone-offset" value="8">
                    <input type="hidden" name="timezone" id="timezone" value="Asia/Shanghai">
                    <input type="hidden" name="requesttoken" value="aUsFDmwGEgcCbkMqFScfJC05JUgIfitlCkEGKDNg:02ITZ5TJC89dBkhrCts0b2q1fxLZY0">
                </fieldset>
            </form>
            <div class="push">
		<?php if(isset($msg)) echo "<p style='color:red;'>$msg</p>"; ?>
	    </div>
        </div>
    </div>
    <footer role="contentinfo">
        <div class="footer-img"></div>
        <div style="display: inline-block">
            請使用教育體系 OpenID 帳號進行登入<br>
            Copyright © Ministry of Education. All rigths reserved.
        </div>
    </footer>
    <!--<div class="container">
        <div class="row">
            <div class="col-sm-4 col-sm-offset-4">
                <form name="login" class="panel panel-primary" method="POST">
                    <input name="return_url" type="hidden" value="<?php //echo isset($_GET["returnUrl"]) ? $_GET["returnUrl"] : "";?>">
                    
                    <div class="panel-heading">
                        <header class="panel-title">Sign In</header>
                    </div>
                    
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="account">Account</label>
                            <input id="account" name="account" type="text" class="form-control" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" class="form-control" required />
                        </div>
                    </div>
                    
                    <div class="panel-footer">
                        <button class="btn btn-default btn-block" type="submit">LogIn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>--!>
</body>
</html>

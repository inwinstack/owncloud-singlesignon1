<?php
style("singlesignon1", "styles");
?>
<ul>
    <li class='update'>
        <?php p($l->t('Logout success.')); ?><br/><br/>
        <a class="button" href="<?php echo \OC_Config::getValue("sso_login_url1") . \OC_Config::getValue("sso_return_url_key1") . "/" ?>"><?php p($l->t("Login again.")) ?></a>
    </li>
</ul>

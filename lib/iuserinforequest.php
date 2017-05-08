<?php
namespace OCA\SingleSignOn1;

interface IUserInfoRequest extends ISingleSignOnRequest {
    /**
     * setup userinfo
     * @param array params
     * @return void
     * @author Dauba
     **/
    public function setup($params);

    /**
     * Getter for UserId
     *
     * @return string
     * @author Dauba
     */
    public function getUserId();

    /**
     * Getter for Email
     *
     * @return string
     * @author Dauba
     */
    public function getEmail();

    /**
     * Getter for group
     *
     * @return void
     * @author Dauba
     */
    public function getGroups();

    /**
     * Getter for display name
     *
     * @return string
     * @author Dauba
     */
    public function getDisplayName();

    /**
     * Getter for region
     *
     * @return string
     * @author Dauba
     */
    public function getRegion();

    /**
     * Check user permission
     *
     * @return bool
     * @author Dauba
     */
    public function hasPermission();

    /**
     * Get user auth token
     *
     * @return void
     */
    public function getToken();
    
    /**
     * Check has error message or not
     *
     * @return true|false
     * @author Dauba
     **/
    public function hasErrorMsg();
}

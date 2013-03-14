<?php

Import::php("OpenM-SSO.api.Impl.OpenM_SSOImpl");
Import::php("OpenM-SSO.api.Impl.OpenM_SSOSessionImpl");
Import::php("OpenM-SSO.client.OpenM_SSOSessionLocalManager");

/**
 * Description of OpenM_SSOSessionManager
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOSessionManager {

    public function getID() {
        if (OpenM_SSOSessionLocalManager::isAPILocal())
            return OpenM_SSOSessionLocalManager::getID();
        else
            return $this->getSSO()->getID();
    }

    public function getSSOSession($api) {
        return OpenM_SSOSessionImpl::getSession($api);
    }

    public function isAPI() {
        if (OpenM_SSOSessionLocalManager::isAPILocal())
            return true;
        else
            return OpenM_ID_Tool::isTokenValid($this->getSSO()->getSession()->get(OpenM_SSO_SessionDAO::IP_HASH));
    }

    private function getSSO() {
        return OpenM_SSOImpl::getInstance();
    }

    private function getSession() {
        return $this->getSSO()->getSession();
    }

    public function isUser() {
        if (OpenM_SSOSessionLocalManager::isAPILocal())
            return true;
        else
            return !OpenM_ID_Tool::isTokenValid($this->getSSO()->getSession()->get(OpenM_SSO_SessionDAO::IP_HASH));
    }

}

?>
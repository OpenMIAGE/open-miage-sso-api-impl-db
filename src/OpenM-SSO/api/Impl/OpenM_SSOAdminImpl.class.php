<?php

Import::php("OpenM-SSO.api.Impl.OpenM_SSOCommonImpl");
Import::php("OpenM-SSO.api.OpenM_SSOAdmin");
Import::php("OpenM-SSO.api.Impl.OpenM_SSOSessionManager");
Import::php("OpenM-SSO.api.OpenM_SSOAdmin_Tool");

/**
 * Description of OpenM_SSOAdminImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOAdminImpl extends OpenM_SSOCommonImpl implements OpenM_SSOAdmin {

    private static $sso;
    private $adminDAO;
    private $clientDAO;
    private $clientRightsDAO;

    public function __construct() {
        parent::__construct();
        $this->adminDAO = self::$daoFactory->get("OpenM_SSO_AdminDAO");
        $this->clientDAO = self::$daoFactory->get("OpenM_SSO_ClientDAO");
        $this->clientRightsDAO = self::$daoFactory->get("OpenM_SSO_ClientRightsDAO");
    }

    private function checkRights($needToBeeAdmin = true) {
        OpenM_Log::debug("check SSO Session", __CLASS__, __METHOD__, __LINE__);
        $manager = new OpenM_SSOSessionManager();
        $id = $manager->getID();
        if ($id == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_NO_SSO_SESSION_ACTIVE_VALUE);

        OpenM_Log::debug("SSO Session active", __CLASS__, __METHOD__, __LINE__);

        if (!$needToBeeAdmin)
            return $manager;

        OpenM_Log::debug("check if SSO Admin Session", __CLASS__, __METHOD__, __LINE__);

        $admin = $this->adminDAO->get($id);
        if ($admin == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_ENOUGH_RIGHTS_VALUE);
        OpenM_Log::debug("It's SSO admin session", __CLASS__, __METHOD__, __LINE__);
        return $admin;
    }

    private function checkClientId($clientId) {
        if (!String::isString($clientId))
            return $this->error("clientId must be a string");
        if (!RegExp::preg("/^[0-9]+$/", $clientId))
            return $this->error("clientId must be a number");
        return null;
    }

    public function getClientList($notValidOnly = null) {
        if (!String::isStringOrNull($notValidOnly))
            return $this->error("notValidOnly must be a string");
        if ($notValidOnly instanceof String)
            $notValidOnly = $notValidOnly . "";
        if ($notValidOnly == null)
            $notValidOnly == self::TRUE_PARAMETER_VALUE;
        if ($notValidOnly != self::TRUE_PARAMETER_VALUE && $notValidOnly != self::FALSE_PARAMETER_VALUE)
            return $this->error("notValidOnly must be equal to '" . self::TRUE_PARAMETER_VALUE . "' or '" . self::FALSE_PARAMETER_VALUE . "'");
        $notValidOnly = ($notValidOnly == self::TRUE_PARAMETER_VALUE) ? true : false;

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("get all client validation request", __CLASS__, __METHOD__, __LINE__);
        $return = $this->clientDAO->getALL($notValidOnly);
        if ($return == null)
            return new HashtableString ();

        OpenM_Log::debug("return all client validation request", __CLASS__, __METHOD__, __LINE__);
        return $return;
    }

    public function removeClient($clientId) {
        $check = $this->checkClientId($clientId);
        if ($check != null)
            return $check;

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("remove client validation request ($clientId)", __CLASS__, __METHOD__, __LINE__);
        $this->clientDAO->remove($clientId);
        return $this->ok();
    }

    public function validateClient($clientId) {
        $check = $this->checkClientId($clientId);
        if ($check != null)
            return $check;

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("validate client validation request ($clientId)", __CLASS__, __METHOD__, __LINE__);
        $this->clientDAO->validate($clientId);
        return $this->ok();
    }

    public function addClientRights($clientId, $rights = null) {
        $check = $this->checkClientId($clientId);
        if ($check != null)
            return $check;
        if ($rights == null)
            $rights = self::DEFAULT_CLIENT_RIGHTS;
        if (!OpenM_SSOAdmin_Tool::isValidRights($rights))
            return $this->error("rights must be valid");

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("add client Rights in DAO", __CLASS__, __METHOD__, __LINE__);
        $this->clientRightsDAO->create($clientId, $rights);
        return $this->ok();
    }

    public function getClientRights($clientId = null) {
        if ($clientId != null) {
            $check = $this->checkClientId($clientId);
            if ($check != null)
                return $check;
        }

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("get client Rights in DAO", __CLASS__, __METHOD__, __LINE__);
        $results = $this->clientRightsDAO->get($clientId);
        return $this->ok()->put(self::RETURN_CLIENT_RIGHTS_LIST_PARAMETER, $results);
    }

    public function removeClientRights($rightsId) {
        if (!String::isString($rightsId))
            return $this->error("rightsId must be a string");
        if (!RegExp::preg("/^[0-9]+$/", $rightsId))
            return $this->error("rightsId must be a number");

        $admin = $this->checkRights();
        if ($admin->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $admin;

        OpenM_Log::debug("remove client Rights in DAO", __CLASS__, __METHOD__, __LINE__);
        $this->clientRightsDAO->remove($rightsId);
        return $this->ok();
    }

    public function isValid($clientIp, $api, $method) {
        if (!OpenM_SSOAdmin_Tool::isValidMethodOrAPI($api) || !OpenM_SSOAdmin_Tool::isValidMethodOrAPI($method))
            return false;

        OpenM_Log::debug("load client rights from DAO", __CLASS__, __METHOD__, __LINE__);
        $rights = $this->clientRightsDAO->getFromClientIp($clientIp);
        $e = $rights->enum();
        while ($e->hasNext()) {
            $line = $e->next();
            OpenM_Log::debug("a new rights found in DAO (" . $line->get(OpenM_SSO_ClientRightsDAO::RIGHTS) . ")", __CLASS__, __METHOD__, __LINE__);
            if (OpenM_SSOAdmin_Tool::isValid($api, $method, $line->get(OpenM_SSO_ClientRightsDAO::RIGHTS)))
                return true;
        }
        return false;
    }

    public static function getInstance() {
        if (self::$sso == null)
            self::$sso = new OpenM_SSOAdminImpl();
        return self::$sso;
    }

}

?>
<?php

Import::php("OpenM-SSO.api.Impl.OpenM_SSOImpl");
Import::php("OpenM-SSO.client.OpenM_SSOSession");
Import::php("OpenM-SSO.client.OpenM_SSOSessionLocalManager");
Import::php("util.HashtableString");
Import::php("util.OpenM_Log");
Import::php("util.http.OpenM_URL");

/**
 * Description of OpenM_SSOSessionImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOSessionImpl extends OpenM_SSOCommonImpl implements OpenM_SSOSession {

    private $api;
    private $ssid;
    private static $instances;
    private $sessionDAO;

    /**
     * @return OpenM_SSOSession
     */
    public function __construct($url) {
        parent::__construct();
        if (OpenM_URL::isValid($url))
            $this->api = $url;
        else
            OpenM_Log::debug("url not valid", __CLASS__, __METHOD__, __LINE__);
        $this->sessionDAO = self::$daoFactory->get("OpenM_SSO_SessionDAO");
    }

    public static function getSession($url) {
        OpenM_Log::debug("load SSOSession", __CLASS__, __METHOD__, __LINE__);
        if (self::$instances == null)
            self::$instances = new HashtableString();
        if (!self::$instances->containsKey($url))
            self::$instances->put($url, new OpenM_SSOSessionImpl($url));

        return self::$instances->get($url);
    }

    public function getSSID() {
        return $this->ssid . "";
    }

    public function isSSOapiConnectionOK($optimisticMode = true) {
        if ($this->api == null) {
            OpenM_Log::debug("not initialized", __CLASS__, __METHOD__, __LINE__);
            return false;
        }

        if ($this->ssid != null) {
            OpenM_Log::debug("SSID found, api connection OK", __CLASS__, __METHOD__, __LINE__);
            return true;
        }

        $session = null;
        if (OpenM_SSOSessionLocalManager::isAPILocal()) {
            OpenM_Log::debug("load local session", __CLASS__, __METHOD__, __LINE__);
            $session = $this->sessionDAO->get(OpenM_SSOSessionLocalManager::getSSID());
        } else {
            OpenM_Log::debug("load SSOImpl session", __CLASS__, __METHOD__, __LINE__);
            $ssoImpl = OpenM_SSOImpl::getInstance();
            $session = $ssoImpl->getSession();
        }

        if ($session == null) {
            OpenM_Log::debug("SSOImpl session KO", __CLASS__, __METHOD__, __LINE__);
            return false;
        }

        OpenM_Log::debug("load APISession from DAO", __CLASS__, __METHOD__, __LINE__);
        $s = $this->sessionDAO->get($session->get(OpenM_SSO_SessionDAO::SSID), $this->api);
        if ($s != null) {
            OpenM_Log::debug("APISession from DAO found", __CLASS__, __METHOD__, __LINE__);
            $end_time = $s->get(OpenM_SSO_APISessionDAO::END_TIME)->toInt();
            $time = time();
            OpenM_Log::debug("check end time ($end_time>$time)", __CLASS__, __METHOD__, __LINE__);
            if ($end_time > $time) {
                OpenM_Log::debug("APISession OK", __CLASS__, __METHOD__, __LINE__);
                $this->ssid = $s->get(OpenM_SSO_APISessionDAO::API_SSID);
                return true;
            }
            OpenM_Log::debug("APISession outOfDate", __CLASS__, __METHOD__, __LINE__);
        }

        OpenM_Log::debug("Open remote SSO session", __CLASS__, __METHOD__, __LINE__);
        $result = OpenM_RESTControllerClient::call($this->api, "OpenM_SSO", "openSession", array(
                    $session->get(OpenM_SSO_SessionDAO::OID),
                    $session->get(OpenM_SSO_SessionDAO::API_SSO_TOKEN
        )));

        if ($result->containsKey(OpenM_Service::RETURN_ERROR_PARAMETER)) {
            OpenM_Log::debug("Remote SSO session error", __CLASS__, __METHOD__, __LINE__);
            return false;
        }

        OpenM_Log::debug("remove all outOfDate APISession", __CLASS__, __METHOD__, __LINE__);
        $this->sessionDAO->removeOutOfDate();
        $this->ssid = $result->get(OpenM_SSO::RETURN_SSID_PARAMETER);
        $validity = $result->get(OpenM_SSO::RETURN_SSID_TIMER_PARAMETER);
        OpenM_Log::debug("Create new APISession (" . $this->api . ", " . $this->ssid . ", $validity)", __CLASS__, __METHOD__, __LINE__);
        $this->sessionDAO->create($session->get(OpenM_SSO_SessionDAO::SSID), $this->api, $this->ssid, $validity);
        return true;
    }

    public function getAPIpath() {
        return $this->api;
    }

    public function getID() {
        return "";
    }

}

?>
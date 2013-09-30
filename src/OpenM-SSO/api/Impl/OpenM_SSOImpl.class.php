<?php

Import::php("OpenM-SSO.api.OpenM_SSO");
Import::php("util.Properties");
Import::php("OpenM-ID.api.OpenM_ID");
Import::php("OpenM-ID.api.OpenM_ID_Tool");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceImpl");
Import::php("OpenM-Services.client.OpenM_ServiceClientImpl");
Import::php("util.http.OpenM_Server");
Import::php("util.http.OpenM_URL");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_SessionDAO");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_AdminDAO");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_ClientDAO");
Import::php("OpenM-SSO.api.Impl.OpenM_SSOAdminImpl");
Import::php("util.OpenM_Log");
Import::php("util.time.Date");
Import::php("util.time.Delay");
if (!Import::php("Auth/OpenID/CryptUtil.php"))
    throw new ImportException("Auth/OpenID/CryptUtil");

/**
 * Description of OpenM_SSOImpl
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOImpl extends OpenM_ServiceImpl implements OpenM_SSO {

    const SPECIFIC_CONFIG_FILE_NAME = "OpenM_SSO.config.file.path";
    const API_PATH = "OpenM_ID.api.path";
    const SESSION_VALIDITY = "OpenM_SSO.api.session.validity";
    const SERVICE_ID = "OpenM_SSO.service.id";
    const HASH_SECRET = "OpenM_SSO.hash.secret";
    const HASH_ALGO = "OpenM_SSO.hash.algo";
    const IP_HASH_ALGO = "OpenM_SSO.ip.hash.algo";
    const MAINTENANCE_MODE = "OpenM_SSO.maintenance.mode";
    const MAINTENANCE_MODE_ON = "ON";
    const MAINTENANCE_MESSAGE = "OpenM_SSO.maintenance.message";

    private static $apiPath;
    private static $validityTime;
    private static $serviceId;
    private static $secret;
    private static $hashAlgo;
    private static $ipHashAlgo;
    private static $sso;
    private $OpenM_IDClient;
    private $session;

    public function __construct() {
        self::$sso = $this;
        $this->init();
        $this->OpenM_IDClient = new OpenM_ServiceClientImpl(self::$apiPath, "OpenM_ID", false);
    }

    public function addClient($oid, $token) {
        if (!String::isString($oid))
            return $this->error("oid must be a string");
        if (!$this->isOIDValid($oid))
            return $this->error("oid must be a valid oid");
        if (!String::isString($token))
            return $this->error("token must be a string");
        if (!OpenM_ID_Tool::isTokenValid($token))
            return $this->error("token must be in a valid format");
        if (OpenM_ID_Tool::isTokenApi($token))
            return $this->error("token must be a user token");

        OpenM_Log::debug("add API client ($oid / $token)", __CLASS__, __METHOD__, __LINE__);
        $clientIp = OpenM_ID_Tool::getClientIp(self::$ipHashAlgo);
        OpenM_Log::debug("add service client", __CLASS__, __METHOD__, __LINE__);
        $status = $this->OpenM_IDClient->addServiceClient(self::$serviceId, $oid, $token, $clientIp);

        if ($status->containskey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $status;

        $id = OpenM_ID_Tool::getId($oid);
        $adminDAO = new OpenM_SSO_AdminDAO();
        OpenM_Log::debug("check admin SSO rights", __CLASS__, __METHOD__, __LINE__);
        $admin = $adminDAO->get($id);

        $clientDAO = new OpenM_SSO_ClientDAO();
        OpenM_Log::debug("add client validation request", __CLASS__, __METHOD__, __LINE__);
        $client = $clientDAO->create($this->getClientIp(), $id, ($admin != null));
        if ($admin != null) {
            OpenM_Log::debug("admin calling", __CLASS__, __METHOD__, __LINE__);
            $clientRightsDAO = new OpenM_SSO_ClientRightsDAO();
            OpenM_Log::debug("add rights on OpenM_SSOAdmin methods", __CLASS__, __METHOD__, __LINE__);
            $clientRightsDAO->create($client->get(OpenM_SSO_ClientDAO::ID), OpenM_SSOAdmin . "::*");
        }
        OpenM_Log::debug("return client ID : " . $client->get(OpenM_SSO_ClientDAO::ID), __CLASS__, __METHOD__, __LINE__);
        return $this->ok()->put(self::RETURN_CLIENT_ID_PARAMETER, $client->get(OpenM_SSO_ClientDAO::ID));
    }

    public function openSession($oid, $token) {
        if (!String::isString($oid))
            return $this->error("oid must be a string");
        if (!$this->isOIDValid($oid))
            return $this->error("oid must be a valid oid");
        if (!String::isString($token))
            return $this->error("token must be a string");
        if (!OpenM_ID_Tool::isTokenValid($token))
            return $this->error("token must be in a valid format");
        
        if (self::$serviceId == null)
            throw new OpenM_ServiceImplException(self::SERVICE_ID . " not defined");

        OpenM_Log::debug("open session with $oid ($token)", __CLASS__, __METHOD__, __LINE__);

        $ip_hash = null;
        $OpenM_ID_ip_hash = null;
        if (OpenM_ID_Tool::isTokenApi($token)) {
            OpenM_Log::debug("It's an API token", __CLASS__, __METHOD__, __LINE__);
            $clientDAO = new OpenM_SSO_ClientDAO();
            OpenM_Log::debug("check if client is valid", __CLASS__, __METHOD__, __LINE__);
            $ip_hash = $this->getClientIp();
            if ($clientDAO->getValidated($ip_hash) == null)
                $this->error(self::RETURN_ERROR_MESSAGE_CLIENT_NOT_VALID_VALUE);
            OpenM_Log::debug("Client is valid", __CLASS__, __METHOD__, __LINE__);
            $OpenM_ID_ip_hash = OpenM_ID_Tool::getClientIp(self::$ipHashAlgo);
        }

        OpenM_Log::debug("Check user/API rights", __CLASS__, __METHOD__, __LINE__);
        $status = $this->OpenM_IDClient->checkUserRights(self::$serviceId, $oid, $token, $OpenM_ID_ip_hash);

        if ($status->containskey(OpenM_Service::RETURN_ERROR_PARAMETER))
            return $status;

        if ($status->get(OpenM_ID::RETURN_STATUS_PARAMETER) == OpenM_ID::RETURN_STATUS_OK_VALUE) {
            OpenM_Log::debug("Rights OK", __CLASS__, __METHOD__, __LINE__);
            $ssoApiToken = $status->get(OpenM_ID::RETURN_TOKEN_PARAMETER);
            $ssid = OpenM_Crypto::hash(self::$hashAlgo, OpenM_URL::encode(self::$secret . $oid . Auth_OpenID_CryptUtil::randomString(200) . $ssoApiToken . self::$secret));
            $ssid .= "_" . microtime(true);

            $sessionDAO = new OpenM_SSO_SessionDAO();

            //add new session
            OpenM_Log::debug("Create session $ssid on $oid ($ssoApiToken)", __CLASS__, __METHOD__, __LINE__);
            $this->session = $sessionDAO->create($ssid, $oid, $ip_hash, $ssoApiToken);

            $validityTime = self::$validityTime;
            OpenM_Log::debug("validity time : " . $validityTime, __CLASS__, __METHOD__, __LINE__);

            //delete all outOfDate session.
            $validity = new Delay($validityTime);
            OpenM_Log::debug("Remove out of date session", __CLASS__, __METHOD__, __LINE__);
            $sessionDAO->removeOutOfDate($validity);

            return $this->ok()->put(self::RETURN_SSID_PARAMETER, $ssid)->put(self::RETURN_SSID_TIMER_PARAMETER, intval($validityTime));
        } else
            return $this->error(self::RETURN_ERROR_MESSAGE_NO_AUTHORIZATION_VALUE);
    }

    public function isSessionOK($SSID) {
        if (!String::isString($SSID))
            return $this->error("SSID must be a string");
        if (!OpenM_ID_Tool::isTokenValid($SSID))
            return $this->error("SSID is not in a valid format");

        OpenM_Log::debug("load session if exist", __CLASS__, __METHOD__, __LINE__);
        $sessionDAO = new OpenM_SSO_SessionDAO();
        $session = $sessionDAO->get($SSID);

        if ($session == null) {
            return $this->error(self::RETURN_ERROR_MESSAGE_NO_SESSION_ACTIVE_VALUE);
        } else {
            if (OpenM_ID_Tool::isTokenApi($SSID) && $session->get(OpenM_SSO_SessionDAO::IP_HASH) != $this->getClientIp()) {
                return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_SSID_VALUE);
            } else {
                OpenM_Log::debug("Session load OK, check if not expired", __CLASS__, __METHOD__, __LINE__);
                $begin_time = new Date($session->get(OpenM_SSO_SessionDAO::BEGIN_TIME)->toInt());
                OpenM_Log::debug("begin time : " . $begin_time->toString(), __CLASS__, __METHOD__, __LINE__);
                $validity = new Delay(self::$validityTime);
                OpenM_Log::debug("validity : " . $validity->getSeconds(), __CLASS__, __METHOD__, __LINE__);
                $now = new Date();
                if ($begin_time->plus($validity)->compareTo($now) < 0)
                    return $this->error(self::RETURN_ERROR_MESSAGE_EXPIRED_SESSION_VALUE);
                $this->session = $session;
                OpenM_Log::debug("Session OK", __CLASS__, __METHOD__, __LINE__);
                return $this->ok();
            }
        }
    }

    public function closeSession($SSID) {
        if (!String::isString($SSID))
            return $this->error("SSID must be a string");
        if (!OpenM_ID_Tool::isTokenValid($SSID))
            return $this->error("SSID is not in a valid format");

        $sessionDAO = new OpenM_SSO_SessionDAO();
        OpenM_Log::debug("Search session $SSID in DAO", __CLASS__, __METHOD__, __LINE__);
        $session = $sessionDAO->get($SSID);

        if ($session == null)
            return $this->error(self::RETURN_ERROR_MESSAGE_NO_SESSION_ACTIVE_VALUE);
        if ($session->get(OpenM_SSO_SessionDAO::IP_HASH) != $this->getClientIp())
            return $this->error(self::RETURN_ERROR_MESSAGE_NOT_YOUR_SSID_VALUE);

        OpenM_Log::debug("remove session $SSID from DAO", __CLASS__, __METHOD__, __LINE__);
        $sessionDAO->remove($SSID);

        return $this->ok();
    }

    public function getOpenM_ID_URL() {
        $return = new HashtableString();
        return $return->put(self::RETURN_OpenM_ID_PROVIDER_PARAMETER, self::$apiPath);
    }

    public function install($serviceName, $oid, $token) {
        if (!String::isString($serviceName))
            throw new InvalidArgumentException("serviceName must be a string");
        if (!String::isString($oid))
            throw new InvalidArgumentException("oid must be a string");
        if (!String::isString($token))
            throw new InvalidArgumentException("token must be a string");

        OpenM_Log::debug("install API '$serviceName' from $oid ($token)", __CLASS__, __METHOD__, __LINE__);
        if (self::$serviceId != null && self::$serviceId != "")
            throw new OpenM_ServiceImplException("Service already installed");

        $adminDAO = new OpenM_SSO_AdminDAO();
        $admins = $adminDAO->get();
        if ($admins->size() > 0)
            throw new OpenM_ServiceImplException("Admin already exist in database. first installation must be launch without any Admin in database");

        OpenM_Log::debug("API not already installed", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("Install API in OpenM-ID provider", __CLASS__, __METHOD__, __LINE__);
        $return = $this->OpenM_IDClient->installService($serviceName, $oid, $token);
        if ($return->containsKey(self::RETURN_ERROR_PARAMETER))
            throw new OpenM_ServiceImplException($return->get(self::RETURN_ERROR_MESSAGE_PARAMETER));

        OpenM_Log::debug("add $oid is now the first SUPER ADMIN of this API", __CLASS__, __METHOD__, __LINE__);
        $adminDAO->create(OpenM_ID_Tool::getId($oid), OpenM_SSO_AdminDAO::LEVEL_SUPER_ADMIN);

        OpenM_Log::debug("API installed in OpenM-ID provider", __CLASS__, __METHOD__, __LINE__);
        $serviceId = $return->get(OpenM_ID::RETURN_SERVICE_ID_PARAMETER);
        OpenM_Log::debug("ServiceId=$serviceId", __CLASS__, __METHOD__, __LINE__);
        self::$serviceId = $serviceId;
        $p = Properties::fromFile(self::CONFIG_FILE_NAME);
        $path = $p->get(self::SPECIFIC_CONFIG_FILE_NAME);
        $p2 = Properties::fromFile($path);
        $p2->set(self::SERVICE_ID, $serviceId);
        $p2->save();
        return true;
    }

    private function init() {
        if ($this->secret != null)
            return;
        $p = Properties::fromFile(self::CONFIG_FILE_NAME);
        $path = $p->get(self::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceImplException(self::SPECIFIC_CONFIG_FILE_NAME . " not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile($path);
        self::$secret = $p2->get(self::HASH_SECRET);
        if (self::$secret == null)
            throw new OpenM_ServiceImplException(self::HASH_SECRET . " not defined in " . $path);
        self::$apiPath = $p2->get(self::API_PATH);
        if (self::$apiPath == null)
            throw new OpenM_ServiceImplException(self::API_PATH . " not defined in " . $path);
        self::$serviceId = $p2->get(self::SERVICE_ID);
        self::$validityTime = $p2->get(self::SESSION_VALIDITY);
        if (self::$validityTime == null)
            throw new OpenM_ServiceImplException(self::SESSION_VALIDITY . " not defined in " . $path);
        if ($p2->get(self::HASH_ALGO) == null)
            throw new OpenM_ServiceImplException(self::HASH_ALGO . " property is not defined in $path");
        self::$hashAlgo = $p2->get(self::HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid(self::$hashAlgo))
            throw new OpenM_ServiceImplException(self::HASH_ALGO . " property is not a valid crypto algo in $path");
        self::$ipHashAlgo = $p2->get(self::IP_HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid(self::$ipHashAlgo))
            throw new OpenM_ServiceImplException(self::IP_HASH_ALGO . " property is not a valid crypto algo in $path");
        if ($p2->get(self::MAINTENANCE_MODE) == self::MAINTENANCE_MODE_ON)
            die($p2->get(self::MAINTENANCE_MESSAGE));
    }

    private function isOIDValid($oid) {
        return OpenM_URL::isValid($oid);
    }

    /**
     * 
     * @return OpenM_SSOImpl
     */
    public static function getInstance() {
        if (self::$sso == null)
            self::$sso = new OpenM_SSOImpl();
        return self::$sso;
    }

    /**
     * 
     * @return HashtableString
     */
    public function getSession() {
        if ($this->session == null)
            return null;
        else
            return $this->session;
    }

    public function isValid($api, $method) {
        return OpenM_SSOAdminImpl::getInstance()->isValid($this->getClientIp(), $api, $method);
    }

    private function getClientIp() {
        return OpenM_Server::getClientIpCrypted(self::$hashAlgo, self::$secret . $_SERVER['HTTP_USER_AGENT']);
    }

    public function getID() {
        if ($this->session == null)
            return null;
        else
            return OpenM_ID_Tool::getId($this->session->get(OpenM_SSO_SessionDAO::OID));
    }

}

?>

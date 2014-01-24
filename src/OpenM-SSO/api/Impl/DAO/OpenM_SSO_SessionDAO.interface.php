<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_SessionDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
interface OpenM_SSO_SessionDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_SESSION";
    const SSID = "SSID";
    const OID = "oid";
    const IP_HASH = "ip_hash";
    const BEGIN_TIME = "begin_time";
    const API_SSO_TOKEN = "api_sso_token";

    public function create($ssid, $oid, $ip_hash, $ssoApiToken);

    public function removeOutOfDate(Delay $validity);

    public function remove($ssid);

    public function get($ssid);
}

?>
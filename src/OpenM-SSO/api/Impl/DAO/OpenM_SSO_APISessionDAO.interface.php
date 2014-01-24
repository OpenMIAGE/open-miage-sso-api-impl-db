<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_APISessionDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO
 * @author Gaël Saunier
 */
interface OpenM_SSO_APISessionDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_API_SESSION";
    const SSID = "SSID";
    const API_PATH = "api_url";
    const API_SSID = "api_SSID";
    const END_TIME = "end_time";

    public function create($ssid, $api_url, $api_ssid, $validity);

    public function removeOutOfDate();
}

?>
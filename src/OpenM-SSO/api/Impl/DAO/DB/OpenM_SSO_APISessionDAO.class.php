<?php

Import::php("OpenM-SSO.api.Impl.DAO.DB.OpenM_SSO_DAO_DB");

/**
 * Description of OpenM_SSO_APISessionDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
class OpenM_SSO_APISessionDAO extends OpenM_SSO_DAO_DB {

    const SSO_TABLE_NAME = "OpenM_SSO_API_SESSION";
    const SSID = "SSID";
    const API_PATH = "api_url";
    const API_SSID = "api_SSID";
    const END_TIME = "end_time";

    public function create($ssid, $api_url, $api_ssid, $validity) {
        $time = time() + $validity;
        self::$db->request(OpenM_DB::insert(self::SSO_TABLE_NAME, array(
                    self::SSID => $ssid,
                    self::API_PATH => $api_url,
                    self::API_SSID => $api_ssid,
                    self::END_TIME => $time
                )));

        $return = new HashtableString();
        return $return->put(self::API_SSID, $api_ssid)->put(self::END_TIME, $time)
                        ->put(self::API_PATH, $api_url)
                        ->put(self::SSID, $ssid);
    }

    public function removeOutOfDate() {
        $now = new Date();
        self::$db->request("DELETE FROM " . self::SSO_TABLE_NAME . " WHERE " . self::END_TIME . "<" . $now->getTime());
    }

    public function remove($ssid, $api_url = null) {
        $array = array(self::API_PATH, $api_url);
        if ($api_url != null)
            $array[self::SSID] = $ssid;
        self::$db->request(OpenM_DB::delete(self::SSO_TABLE_NAME, $array));
    }

    public function get($ssid, $api_url) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, array(
                            self::API_PATH => $api_url,
                            self::SSID => $ssid
                        )));
    }

}

?>
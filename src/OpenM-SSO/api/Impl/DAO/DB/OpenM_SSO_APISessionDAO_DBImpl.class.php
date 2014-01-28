<?php

Import::php("OpenM-SSO.api.Impl.DAO.DB.OpenM_SSO_DAO_DBImpl");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_APISessionDAO");

/**
 * Description of OpenM_SSO_APISessionDAO_DBImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO\DB
 * @author Gaël Saunier
 */
class OpenM_SSO_APISessionDAO_DBImpl extends OpenM_SSO_DAO_DBImpl implements OpenM_SSO_APISessionDAO {

    public function create($ssid, $api_url, $api_ssid, $validity) {
        $time = time() + $validity;
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::SSO_TABLE_NAME), array(
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
        self::$db->request("DELETE FROM " . $this->getTABLE(self::SSO_TABLE_NAME) . " WHERE " . self::END_TIME . "<" . $now->getTime());
    }

    public function get($ssid, $api_url) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::SSO_TABLE_NAME), array(
                            self::API_PATH => $api_url,
                            self::SSID => $ssid
        )));
    }

}

?>
<?php

Import::php("OpenM-SSO.api.Impl.DAO.DB.OpenM_SSO_DAO_DBImpl");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_SessionDAO");

/**
 * Description of OpenM_SSO_SessionDAO_DBImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO\DB
 * @author Gaël Saunier
 */
class OpenM_SSO_SessionDAO_DBImpl extends OpenM_SSO_DAO_DBImpl implements OpenM_SSO_SessionDAO {

    public function create($ssid, $oid, $ip_hash, $ssoApiToken) {
        $time = time();
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::SSO_TABLE_NAME), array(
                    self::SSID => $ssid,
                    self::BEGIN_TIME => $time,
                    self::OID => $oid,
                    self::IP_HASH => $ip_hash,
                    self::API_SSO_TOKEN => $ssoApiToken)));

        $return = new HashtableString();
        return $return->put(self::SSID, $ssid)->put(self::BEGIN_TIME, $time)
                        ->put(self::OID, $oid)->put(self::IP_HASH, $ip_hash)
                        ->put(self::API_SSO_TOKEN, $ssoApiToken);
    }

    public function removeOutOfDate(Delay $validity) {
        $now = new Date();
        $outOfDate = $now->less($validity);
        self::$db->request("DELETE FROM " . $this->getTABLE(self::SSO_TABLE_NAME) . " WHERE " . self::BEGIN_TIME . "<" . $outOfDate->getTime());
    }

    public function remove($ssid) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::SSO_TABLE_NAME), array(self::SSID, $ssid)));
    }

    public function get($ssid) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select($this->getTABLE(self::SSO_TABLE_NAME), array(self::SSID => $ssid)));
    }

}

?>
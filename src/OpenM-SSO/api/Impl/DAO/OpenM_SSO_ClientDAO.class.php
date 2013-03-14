<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_ClientDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
class OpenM_SSO_ClientDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_CLIENT";
    const ID = "client_id";
    const IP_HASH = "ip_hash";
    const IS_VALID = "is_valid";
    const INSTALLER_USER_ID = "install_user_id";
    const TIME = "time";

    public function create($ip_hash, $install_user_id, $is_valid = false) {
        $time = time();
        self::$db->request(OpenM_DB::insert(self::SSO_TABLE_NAME, array(
                    self::IP_HASH => $ip_hash,
                    self::IS_VALID => ($is_valid) ? 1 : 0,
                    self::INSTALLER_USER_ID => $install_user_id,
                    self::TIME => $time
                )));

        return self::$db->request_fetch_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, array(
                    self::IP_HASH => $ip_hash,
                    self::IS_VALID => ($is_valid) ? 1 : 0,
                    self::INSTALLER_USER_ID => $install_user_id,
                    self::TIME => $time
                )));
    }

    public function removeOutOfDate(Delay $validity) {
        $now = new Date();
        $outOfDate = $now->less($validity);
        self::$db->request("DELETE FROM " . self::SSO_TABLE_NAME . " WHERE " . self::TIME . "<" . $outOfDate->getTime());
    }

    public function remove($clientId) {
        self::$db->request(OpenM_DB::delete(self::SSO_TABLE_NAME, array(
                    self::ID => $clientId
                )));
    }

    public function getALL($notValidOnly = true) {
        $array = array();
        if ($notValidOnly)
            $array[self::IS_VALID] = 0;
        return self::$db->request_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, $array), self::ID);
    }

    public function get($clientIp) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, array(
                            self::IP_HASH => $clientIp
                        )), self::ID);
    }

    public function getValidated($ip_hash) {
        return self::$db->request_fetch_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, array(
                            self::IP_HASH => $ip_hash,
                            self::IS_VALID => 1
                        )), self::ID);
    }

    public function validate($clientId) {
        self::$db->request("UPDATE " . self::SSO_TABLE_NAME . " SET " . self::IS_VALID . "=1 WHERE " . self::ID . "=$clientId");
    }

}

?>
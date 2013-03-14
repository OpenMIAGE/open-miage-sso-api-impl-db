<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_AdminDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
class OpenM_SSO_AdminDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_ADMIN";
    const USER_ID = "user_id";
    const USER_LEVEL = "user_level";
    const LEVEL_ADMIN = "1";
    const LEVEL_SUPER_ADMIN = "2";

    public function create($user_id, $user_level) {
        self::$db->request(OpenM_DB::insert(self::SSO_TABLE_NAME, array(
                    self::USER_ID => $user_id,
                    self::USER_LEVEL => $user_level
                )));

        $return = new HashtableString();
        return $return->put(self::USER_ID, $user_id)->put(self::USER_LEVEL, $user_level);
    }

    public function remove($userId) {
        self::$db->request(OpenM_DB::delete(self::SSO_TABLE_NAME, array(self::USER_ID, $userId)));
    }

    /**
     * 
     * @param type $userId
     * @return HashtableString
     */
    public function get($userId = null) {
        if ($userId != null) {
            $array = array(self::USER_ID => $userId);
            return self::$db->request_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, $array),self::USER_ID);
        }
        else
            return self::$db->request_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME),self::USER_ID);
    }

}

?>
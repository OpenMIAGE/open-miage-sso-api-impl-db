<?php

Import::php("OpenM-SSO.api.Impl.DAO.DB.OpenM_SSO_DAO_DBImpl");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_AdminDAO");

/**
 * Description of OpenM_SSO_AdminDAO_DBImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO\DB
 * @author Gaël Saunier
 */
class OpenM_SSO_AdminDAO_DBImpl extends OpenM_SSO_DAO_DBImpl implements OpenM_SSO_AdminDAO {

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
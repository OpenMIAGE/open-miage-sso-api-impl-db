<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_AdminDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO
 * @author Gaël Saunier
 */
interface OpenM_SSO_AdminDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_ADMIN";
    const USER_ID = "user_id";
    const USER_LEVEL = "user_level";
    const LEVEL_ADMIN = "1";
    const LEVEL_SUPER_ADMIN = "2";

    public function create($user_id, $user_level);

    public function remove($userId);

    public function get($userId = null);

}

?>
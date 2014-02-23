<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_ClientDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
interface OpenM_SSO_ClientDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_CLIENT";
    const ID = "client_id";
    const IP_HASH = "ip_hash";
    const IS_VALID = "is_valid";
    const INSTALLER_USER_ID = "install_user_id";
    const TIME = "time";

    public function create($ip_hash, $install_user_id, $is_valid = false);

    public function removeOutOfDate(Delay $validity);

    public function getALL($notValidOnly = true);

    public function get($clientId);

    public function getValidated($ip_hash);

    public function validate($clientId);
}

?>
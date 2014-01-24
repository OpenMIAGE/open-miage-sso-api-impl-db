<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_ClientRightsDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
interface OpenM_SSO_ClientRightsDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_CLIENT_RIGHTS";
    const ID = "rights_id";
    const CLIENT_ID = "client_id";
    const RIGHTS = "rights_pattern";

    public function create($clientId, $rights);

    public function remove($rightId);

    public function get($clientId = null);

    public function getFromClientIp($clientIp);
}

?>
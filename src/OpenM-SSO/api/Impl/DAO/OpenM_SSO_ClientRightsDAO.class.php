<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_ClientRightsDAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO 
 * @author Gaël Saunier
 */
class OpenM_SSO_ClientRightsDAO extends OpenM_SSO_DAO {

    const SSO_TABLE_NAME = "OpenM_SSO_CLIENT_RIGHTS";
    const ID = "rights_id";
    const CLIENT_ID = "client_id";
    const RIGHTS = "rights_pattern";

    public function create($clientId, $rights) {
        self::$db->request(OpenM_DB::insert(self::SSO_TABLE_NAME, array(
                    self::CLIENT_ID => $clientId,
                    self::RIGHTS => $rights
                )));

        $return = new HashtableString();
        return $return->put(self::CLIENT_ID, $clientId)->put(self::RIGHTS, $rights);
    }

    public function remove($rightId) {
        self::$db->request(OpenM_DB::delete(self::SSO_TABLE_NAME, array(self::ID, $rightId)));
    }

    /**
     * 
     * @param type $clientId
     * @return HashtableString
     */
    public function get($clientId = null) {
        if ($clientId != null)
            return self::$db->request_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME, array(self::CLIENT_ID => $clientId)), self::ID);
        else
            return self::$db->request_HashtableString(OpenM_DB::select(self::SSO_TABLE_NAME), self::ID);
    }

    /**
     * 
     * @param type $clientId
     * @return HashtableString
     */
    public function getFromClientIp($clientIp) {
        return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::SSO_TABLE_NAME))
                        . " WHERE " . self::CLIENT_ID
                        . " IN ("
                        . OpenM_DB::select($this->getTABLE(OpenM_SSO_ClientDAO::SSO_TABLE_NAME), array(
                            OpenM_SSO_ClientDAO::IP_HASH => $clientIp
                                ), array(
                            OpenM_SSO_ClientDAO::ID
                        ))
                        . ") GROUP BY " . self::RIGHTS
                        , self::ID);
    }

}

?>
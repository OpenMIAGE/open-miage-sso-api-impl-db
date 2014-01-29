<?php

Import::php("OpenM-SSO.api.Impl.DAO.DB.OpenM_SSO_DAO_DBImpl");
Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_ClientRightsDAO");

/**
 * Description of OpenM_SSO_ClientRightsDAO_DBImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO\DB
 * @author Gaël Saunier
 */
class OpenM_SSO_ClientRightsDAO_DBImpl extends OpenM_SSO_DAO_DBImpl implements OpenM_SSO_ClientRightsDAO {

    public function create($clientId, $rights) {
        self::$db->request(OpenM_DB::insert($this->getTABLE(self::SSO_TABLE_NAME), array(
                    self::CLIENT_ID => $clientId,
                    self::RIGHTS => $rights
        )));

        $return = new HashtableString();
        return $return->put(self::CLIENT_ID, $clientId)->put(self::RIGHTS, $rights);
    }

    public function remove($rightId) {
        self::$db->request(OpenM_DB::delete($this->getTABLE(self::SSO_TABLE_NAME), array(self::ID, $rightId)));
    }

    public function get($clientId = null) {
        if ($clientId != null)
            return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::SSO_TABLE_NAME), array(self::CLIENT_ID => $clientId)), self::ID);
        else
            return self::$db->request_HashtableString(OpenM_DB::select($this->getTABLE(self::SSO_TABLE_NAME)), self::ID);
    }

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
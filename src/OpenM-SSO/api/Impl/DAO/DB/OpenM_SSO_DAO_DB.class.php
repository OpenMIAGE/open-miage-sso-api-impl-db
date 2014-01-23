<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAO");

/**
 * Description of OpenM_SSO_DAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO\DB 
 * @author Gaël Saunier
 */
class OpenM_SSO_DAO_DB extends OpenM_DAO {
    
    const DAO_CONFIG_FILE_NAME = "OpenM_SSO.DAO.config.file.path";
    const PREFIX = "OpenM_SSO.DAO.prefix";
    
    public function getDaoConfigFileName() {
        return self::DAO_CONFIG_FILE_NAME;
    }

    public function getPrefixPropertyName() {
        return self::PREFIX;
    }
}

?>
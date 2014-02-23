<?php

Import::php("OpenM-SSO.api.Impl.DAO.OpenM_SSO_DAOFactory");
Import::php("util.Properties");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceImpl");
Import::php("util.OpenM_Log");
Import::php("OpenM-SSO.api.Impl.OpenM_SSOImpl");

/**
 * Description of OpenM_SSOCommonImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOCommonImpl extends OpenM_ServiceImpl {

    protected static $daoFactory = null;

    const DAO_IMPL_PACKAGE = "OpenM_SSO.DAO.Impl.Package";

    public function __construct() {
        if (self::$daoFactory !== null)
            return;
        $p = Properties::fromFile(self::CONFIG_FILE_NAME);
        $path = $p->get(OpenM_SSOImpl::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceImplException(OpenM_SSOImpl::SPECIFIC_CONFIG_FILE_NAME . " not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile($path);
        $impl = $p2->get(self::DAO_IMPL_PACKAGE);
        if ($impl == null)
            throw new OpenM_ServiceImplException(self::DAO_IMPL_PACKAGE . " not defined in " . $path);
        self::$daoFactory = new OpenM_SSO_DAOFactory($impl);
    }

}

?>

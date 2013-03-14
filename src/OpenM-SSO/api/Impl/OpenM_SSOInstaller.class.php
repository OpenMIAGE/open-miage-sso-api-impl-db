<?php

Import::php("OpenM-SSO.api.Impl.OpenM_SSOImpl");
Import::php("OpenM-SSO.client.OpenM_SSOClientSessionManager");
Import::php("util.Properties");
Import::php("util.file.OpenM_Dir");
Import::php("util.OpenM_Log");

/**
 * Description of OpenM_SSOInstaller
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_SSOInstaller {

    public static function main($log=true) {
        if($log)
            OpenM_Log::init(".", OpenM_Log::DEBUG, "installer.log");
        
        OpenM_Log::info("check if installer.properties file exist");
        if (!is_file("installer.properties"))
            throw new Exception("./installer.properties not found");
        OpenM_Log::info("load installer.properties file");
        $p = new Properties("installer.properties");
        $api_name = $p->get("OpenM_SSO.install.name");
        if ($api_name == null)
            throw new Exception("OpenM_SSO.install.name not found in ./installer.properties");
        OpenM_Log::info("load manager");
        $manager = new OpenM_SSOClientSessionManager("OpenM_SSO_API_INSTALLER");
        $api = OpenM_SSOImpl::getInstance()->getOpenM_ID_URL()->get(OpenM_SSO::RETURN_OpenM_ID_PROVIDER_PARAMETER);
        OpenM_Log::info("generate sso instance ($api)");
        $store = "./installer.store";
        if (!is_dir($store)) {
            OpenM_Log::info("generate store path ($store)");
            mkdir($store);
        }
        $sso = $manager->get($api, $store);
        OpenM_Log::info("login (force)");
        $sso->login(array(OpenM_ID::TOKEN_PARAMETER), true);
        OpenM_Log::info("check if connected");
        if (!$sso->isConnected())
            throw new Exception("Installer bad parameterized... connection not possible");
        OpenM_Log::info("install API");
        $return = OpenM_SSOImpl::getInstance()->install($api_name, $sso->getOID(), $sso->getToken());
        if ($return)
            OpenM_Dir::rm($p->get($store));
        else
            throw new Exception("Error during API Installation...");
        OpenM_Log::info("installation successfully finished");
        ?>
        <h1>
            API successfully installed !
        </h1>
        <?
    }

}
?>
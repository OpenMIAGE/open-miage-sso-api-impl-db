<?php

Import::php("OpenM-Services.api.Impl.OpenM_ServiceImpl");
Import::php("OpenM-SSO.api.OpenM_SSOSessionManager");

/**
 * Description of OpenM_ServiceSSOImpl
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Services\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_ServiceSSOImpl extends OpenM_ServiceImpl {

    private $manager;

    public function __construct() {
        $this->manager = new OpenM_SSOSessionManager();
    }

    /**
     * 
     * @return OpenM_SSOSessionManager
     */
    protected function getManager() {
        return $this->manager;
    }

}

?>
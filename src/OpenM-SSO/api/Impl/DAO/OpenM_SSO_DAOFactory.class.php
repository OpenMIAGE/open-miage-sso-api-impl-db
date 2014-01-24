<?php

/**
 * Description of OpenM_SSO_DAOFactory
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-SSO\api\Impl\DAO
 * @author Gaël Saunier
 */
class OpenM_SSO_DAOFactory {

    private $impl = null;

    public function __construct($impl = "OpenM-Services.api.Impl.DAO.DB") {
        if (!String::isString($impl))
            throw new InvalidArgumentException("impl must be a string");
        $this->impl = $impl;
    }

    public function get($dao) {
        $class = $dao . "_" . substr(strrchr($this->impl, "."), 1) . "Impl";
        Import::php($this->impl . "." . $class);
        if (!class_exists($class))
            throw new InvalidArgumentException("dao must have an existing implementation");
        return new $class();
    }

}

?>
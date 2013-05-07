<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    public function _initAutoload()
    {
        $modelLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH
                ));
        return $modelLoader;
    }

}


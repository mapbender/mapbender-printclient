<?php

namespace Mapbender\PrintClientBundle;

use Mapbender\CoreBundle\Component\MapbenderBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mapbender\CoreBundle\DependencyInjection\Compiler\MapbenderYamlCompilerPass;

class MapbenderPrintClientBundle extends MapbenderBundle
{   
    
        /**
     * @inheritdoc
     */
    public function getTemplates()
    {
        return array();
    }
    
    /**
     * @inheritdoc
     */
    public function getElements()
    {
        return array(
            'Mapbender\PrintClientBundle\Element\PrintClient'
        );
    }
    
}

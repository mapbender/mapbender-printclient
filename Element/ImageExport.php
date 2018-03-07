<?php

namespace Mapbender\PrintBundle\Element;

use Mapbender\CoreBundle\Component\Element;
use Mapbender\PrintBundle\Component\ImageExportService;

/**
 * Class ImageExport
 * @package Mapbender\PrintBundle\Element
 */
class ImageExport extends Element
{

    /**
     * @inheritdoc
     */
    public static function getClassTitle()
    {
        return "mb.print.imageexport.class.title";
    }

    /**
     * @inheritdoc
     */
    public static function getClassDescription()
    {
        return "mb.print.imageexport.class.description";
    }

    /**
     * @inheritdoc
     */
    public static function getTags()
    {
        return array(
            "mb.print.imageexport.tag.image",
            "mb.print.imageexport.tag.export",
            "mb.print.imageexport.tag.jpeg",
            "mb.print.imageexport.tag.png"
        );
    }

    /**
     * @inheritdoc
     */
    public function getWidgetName()
    {
        return 'mapbender.mbImageExport';
    }

    /**
     * @inheritdoc
     */
    public static function listAssets()
    {
        return array(
            'js'    => array(
                'mapbender.element.imageExport.js',
                '@FOMCoreBundle/Resources/public/js/widgets/popup.js',
                '@FOMCoreBundle/Resources/public/js/widgets/dropdown.js'
            ),
            'css'   => array(
                'sass/element/imageexport.scss'
            ),
            'trans' => array('MapbenderPrintBundle:Element:imageexport.json.twig')
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultConfiguration()
    {
        return array(
            "target" => null
        );
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration()
    {
        return parent::getConfiguration();
    }

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return 'Mapbender\PrintBundle\Element\Type\ImageExportAdminType';
    }

    /**
     * @inheritdoc
     */
    public static function getFormTemplate()
    {
        return 'MapbenderPrintBundle:ElementAdmin:imageexport.html.twig';
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return $this->container->get('templating')
            ->render('MapbenderPrintBundle:Element:imageexport.html.twig', array(
                'id'            => $this->getId(),
                'title'         => $this->getTitle(),
                'configuration' => $this->getConfiguration()
            ));
    }

    /**
     * @inheritdoc
     */
    public function httpAction($action)
    {
        switch ($action) {
            case 'export':
                $request = $this->container->get('request');
                $data = $request->get('data');
                $exportservice = new ImageExportService($this->container);
                $exportservice->export($data);
        }
    }
}

<?php
namespace Mapbender\WmsBundle\Tests\Entity;

require_once dirname(__DIR__).'/../../../../../app/AppKernel.php';

use Doctrine\ORM\Tools\SchemaTool;

use Mapbender\CoreBundle\Component\BoundingBox;
use Mapbender\CoreBundle\Entity\Contact;
use Mapbender\WmsBundle\Component\Attribution;
use Mapbender\WmsBundle\Component\Authority;
use Mapbender\WmsBundle\Component\Identifier;
use Mapbender\WmsBundle\Component\MetadataUrl;
use Mapbender\WmsBundle\Component\OnlineResource;
use Mapbender\WmsBundle\Component\RequestInformation;
use Mapbender\WmsBundle\Entity\WmsLayerSource;
use Mapbender\WmsBundle\Entity\WmsSource;


/*
 * @package bkg_testing
 * @author Karim Malhas <karim@malhas.de>
 */


/**
 *   Tests the CapabilitiesParser. Note that te tests are coupled to the testdata somewhaty tightly. This is on purpose
 *   to keep the tests simple
 */
class WmsSourceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected $kernel;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    public function setUp() {
        // Boot the AppKernel in the test environment and with the debug.
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();

        // Store the container and the entity manager in test case properties
        $this->container = $this->kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getEntityManager();

        // Build the schema for sqlite
        $this->generateSchema();


        parent::setUp();
    }

    public function tearDown() {
        // Shutdown the kernel.
        $this->kernel->shutdown();

        parent::tearDown();
    }

    protected function generateSchema() {
        // Get the metadatas of the application to create the schema.
        $metadatas = $this->getMetadatas();

        if (!empty($metadatas)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->entityManager);
            $tool->createSchema($metadatas);
        } else {
            throw new Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }

    /**
     * Overwrite this method to get specific metadatas.
     *
     * @return Array
     */
    protected function getMetadatas() {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    public function testInheritance() {
        
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getName(), $wms->getName());
        $this->assertEquals($wms_new->getTitle(), $wms->getTitle());
        
    }
    
    public function testContact() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        $contact = new Contact();
        $contact->setPerson("person")->setAddressCity("bonn");
        $wms->setContact($contact);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getContact()->getPerson(), $wms->getContact()->getPerson());
    }
    
    public function testRequestInformation() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        $reqinf = new RequestInformation();
        $reqinf->setHttpGet("http:www.google.de")
                ->setHttpPost("http:www.google.de")
                ->setFormats(array("text/xml", "image/png"));
        $wms->setGetCapabilities($reqinf);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getGetCapabilities()->getHttpGet(), $wms->getGetCapabilities()->getHttpGet());
        $this->assertEquals($wms_new->getGetCapabilities()->getHttpPost(), $wms->getGetCapabilities()->getHttpPost());
        $this->assertEquals($wms_new->getGetCapabilities()->getFormats(), $wms->getGetCapabilities()->getFormats());
    }
    
    public function testWmsLayerSource() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $layer2 = new WmsLayerSource();
        $layer2->setName("1_1");
        $layer2->setParent($layer);
        
        $wms->addLayer($layer);
        $wms->addLayer($layer2);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getLayers(), $wms->getLayers());
    }
    
    public function testWmsLayerSourceAttribution() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $attribution = new Attribution();
        $attribution->setTitle("ATTR");
        $layer->setAttribution($attribution);

        $wms->addLayer($layer);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getRootLayer()->getAttribution()->getTitle(),
                $wms->getRootLayer()->getAttribution()->getTitle());
    }
    
    public function testWmsLayerSourceLatlonBoundingBox() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $bbox = new BoundingBox();
        $bbox->setSrs("EPSG:4326")
                ->setMinx(0)
                ->setMiny(0)
                ->setMaxx(90)
                ->setMaxy(90);
        
        $layer->setLatlonBounds($bbox);

        $wms->addLayer($layer);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        $this->assertEquals($wms_new->getRootLayer()->getLatlonBounds(),
                $wms->getRootLayer()->getLatlonBounds());
    }
    
    public function testWmsLayerSourceBoundingBoxes() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $bbox = new BoundingBox();
        $bbox->setSrs("EPSG:4326")
                ->setMinx(0)
                ->setMiny(0)
                ->setMaxx(90)
                ->setMaxy(90);
        
        $bbox2 = new BoundingBox();
        $bbox2->setSrs("EPSG:25832")
                ->setMinx(99999)
                ->setMiny(5200000)
                ->setMaxx(999999)
                ->setMaxy(5900000);
        
        $layer->addBoundingBox($bbox);
        $layer->addBoundingBox($bbox2);

        $wms->addLayer($layer);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        
        $this->assertEquals($wms_new->getRootLayer()->getBoundingBoxes(),
                $wms->getRootLayer()->getBoundingBoxes());
        $bboxes_new = $wms_new->getRootLayer()->getBoundingBoxes();
        $bboxes = $wms->getRootLayer()->getBoundingBoxes();
        $this->assertEquals($bboxes_new[0]->getSrs(), $bboxes[0]->getSrs());
        $this->assertEquals($bboxes_new[1]->getSrs(), $bboxes[1]->getSrs());
    }
    
    
    public function testWmsLayerSourceMetadataUrl() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $mdurl = new MetadataUrl();
        $onlineResource = new OnlineResource();
        $onlineResource->setFormat("text/xml")->setHref("http://www.google.de");   
        $mdurl->setType("type");
        $mdurl->setOnlineResource($onlineResource);
        
        $mdurl2 = new MetadataUrl();
        $onlineResource2 = new OnlineResource();
        $onlineResource2->setFormat("text/xml")->setHref("http://www.google.de");   
        $mdurl2->setType("type");
        $mdurl2->setOnlineResource($onlineResource2);
        
        $layer->addMetadataUrl($mdurl);
        $layer->addMetadataUrl($mdurl2);

        $wms->addLayer($layer);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        
        $md_new = $wms_new->getRootLayer()->getMetadataUrl();
        $md = $wms->getRootLayer()->getMetadataUrl();
        $this->assertEquals($md_new[0]->getType(), $md[0]->getType());
        $this->assertEquals($md_new[0]->getOnlineResource()->getHref(),
               $md[0]->getOnlineResource()->getHref());
        $this->assertEquals($md_new[1]->getOnlineResource()->getHref(),
                $md[1]->getOnlineResource()->getHref());
    }
    
    public function testWmsLayerSourceIdentfier() {
        $wms = new WmsSource();
        $wms->setName("NAME")->setTitle("title");
        
        $layer = new WmsLayerSource();
        $layer->setName("1");
        
        $identifier = new Identifier();
        $authority = new Authority();
        $authority->setName("NAME")
                ->setUrl("http://www.google.de");
        $identifier->setAuthority($authority)
                ->setValue("BLA BLA");
        
        $layer->setIdentifier($identifier);

        $wms->addLayer($layer);
        
        $this->entityManager->persist($wms);
        $this->entityManager->flush();
        $repo = $this->container->get("doctrine")->getRepository("MapbenderWmsBundle:WmsSource");
        $wms_new = $repo->findOneByName("NAME");
        
        $this->assertEquals($wms_new->getRootLayer()->getIdentifier(),
                $wms->getRootLayer()->getIdentifier());
        $this->assertEquals($wms_new->getRootLayer()->getIdentifier()->getValue(),
                $wms->getRootLayer()->getIdentifier()->getValue());
        $this->assertEquals($wms_new->getRootLayer()->getIdentifier()->getAuthority()->getUrl(),
                $wms->getRootLayer()->getIdentifier()->getAuthority()->getUrl());
    }
    

}
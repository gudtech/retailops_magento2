<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 5.12.16
 * Time: 14.59
 */

namespace RetailOps\Api\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;

class RoRicsLinkUpcRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea('adminhtml')
            ->load(\Magento\Framework\App\Area::PART_CONFIG);
    }

    /**
     *@magentoDataFixture ../../../../app/code/RetailOps/Api/Test/Integration/_files/add_ro_link.php
     */
    public function testGetAllROUpcsByUpcs()
    {
        $objectManager = Bootstrap::getObjectManager();
        /**
         * @var \RetailOps\Api\Model\RoRicsLinkUpcRepository $repository
         */
        $repository = $objectManager->create('RetailOps\Api\Model\RoRicsLinkUpcRepository');
        $upcs = [
            '91209558430',
            '91209558433',
            '022859473118',
            '22859473118'
        ];
        $newUpcs = $repository->getAllROUpcsByUpcs($upcs);
        $this->assertEquals(4, $newUpcs->count());

    }
}
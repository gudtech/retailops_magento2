<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/**
 * @var RetailOps\Api\Model\RoRicsLinkUpcRepository $repository
 */
$repository = $objectManager->create('RetailOps\Api\Model\RoRicsLinkUpcRepository');
/**
 * @var RetailOps\Api\Model\RoRicsLinkUpcFactory $factoryLink
 */
$factoryLink = $objectManager->create('RetailOps\Api\Model\RoRicsLinkUpcFactory');
$data = [
    [
    'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e3',
    'upc' => '91209558430',
    'retail_opcs_upc' =>1
    ],
    [
        'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e3',
        'upc' => '91209558431',
        'retail_ops_upc' =>1
    ],
    [
        'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e3',
        'upc' => '91209558432',
        'retail_ops_upc' =>1
    ],
    [
        'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e3',
        'upc' => '91209558435',
    ],
    [
        'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e4',
        'upc' => '91209558433',
        'retail_ops_upc' =>1
    ],
    [
        'rics_integration_id' => '73ffaff9-03a0-40c4-8f3f-c0f5145f23e4',
        'upc' => '91209558434',
    ],
    [
        'rics_integration_id' => 'ec087881-b1de-48f2-b7f7-466c7cbbe67d',
        'upc' => '022859473118',
        'retail_ops_upc' =>1
    ],

];
foreach ($data as $t) {
    /**
     * @var RetailOps\Api\Model\RoRicsLinkUpc $link
     */
    $link = $factoryLink->create();
    $link->addData($t);
    $repository->save($link);
}

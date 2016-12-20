<?php

namespace RetailOps\Api\Controller\Frontend;

use Magento\Framework\App\ObjectManager;
use RetailOps\Api\Controller\RetailOps;

class Inventory extends RetailOps
{
    const PARAM = 'inventory_updates';
    const SKU = 'sku';
    const QUANTITY = 'calc_inventory';
    const SERVICENAME = 'inventory';
    const ENABLE = 'retailops/RetailOps_feed/inventory_push';
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    protected $events = [];
    protected $response = [];
    protected $statusRetOps = 'success';
    /**
     * @var \RetailOps\Api\Service\CalculateInventory
     */
    protected $inventory;
    protected $association = [];
    /**
     * @var \RetailOps\Api\Model\RoRicsLinkUpcRepository
     */
    protected $upcRepository;

    public function execute()
    {
        try {
            $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            if(!$scopeConfig->getValue(self::ENABLE)) {
                throw new \LogicException('This feed disable');
            }
            $inventories = $this->getRequest()->getParam(self::PARAM);
            $inventoryObjects = [];
            if (is_array($inventories) && count($inventories)) {
                $inventory = [];
                $inventories = $this->inventory->calculateInventory($inventories);
                foreach ($inventories as $invent) {
                    $upcs = $this->upcRepository->getProductUpcByRoUpc($invent[self::SKU]);
                    //if for one rics_integration Id can be many products
                    foreach ($upcs as $upc) {
                        $object = ObjectManager::getInstance()->create('\RetailOps\Api\Model\Inventory');
                        $object->setUPC($upc);
                        $object->setCount($invent[self::QUANTITY]);
                        $inventoryObjects[] = $object;
                    }
                    $upcs = [];
                }
                $this->inventory->addInventoiesFromNotSendedOrderYet($inventoryObjects);
                $inventoryApi = ObjectManager::getInstance()->create('\RetailOps\Api\Model\Inventory\Inventory');
                foreach ($inventoryObjects as $inventory){
                    $this->association[] = ['identifier_type' => 'sku_number', 'identifier'=>$inventory->getUPC()];
                }
                $state = ObjectManager::getInstance()->get('\Magento\Framework\App\State');
                $state->emulateAreaCode(\Magento\Framework\App\Area::AREA_WEBAPI_REST, [$inventoryApi, 'setInventory'], [$inventoryObjects]);
            }
        }catch (\Exception $e) {
            $event = [
                'event_type' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'diagnostic_data' => 'string',
                'associations'=>$this->association,
            ];
            $this->events[] = $event;
            $this->statusRetOps = 'error';

        }finally {
            $this->response['events'] = [];
            foreach ($this->events as $event)
            {
                $this->response['events'][] = $event;
            }
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode('200');
            parent::execute();
            return $this->getResponse();
        }


    }

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \RetailOps\Api\Model\RoRicsLinkUpcRepository $linkUpcRepository,
                                \RetailOps\Api\Service\CalculateInventory $inventory)
    {
        $this->upcRepository = $linkUpcRepository;
        $this->inventory = $inventory;
        parent::__construct($context);
    }

}
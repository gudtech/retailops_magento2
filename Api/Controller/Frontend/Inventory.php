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
    /**
     * @var string
     */
    protected $areaName = self::BEFOREPULL.self::SERVICENAME;
    protected $events = [];
    protected $response = [];
    protected $statusRetOps = 'success';
    protected $association = [];

    public function execute()
    {
        try {

            $inventories = $this->getRequest()->getParam(self::PARAM);
            if (count($inventories)) {
                $inventory = [];
                $object = ObjectManager::getInstance()->create('\RetailOps\Api\Model\Inventory\Inventory');
                $inventories = $object->calculateInventory($inventories);
                foreach ($inventories as $invent) {
                    $object = ObjectManager::getInstance()->create('\RetailOps\Api\Model\Inventory');
                    $object->setUPC($invent[self::SKU]);
                    $object->setCount($invent[self::QUANTITY]);
                    $inventoryObject[] = $object;
                }
                $inventoryApi = ObjectManager::getInstance()->create('\RetailOps\Api\Model\Inventory\Inventory');
                foreach ($inventoryObject as $inventory){
                    $this->association[] = ['identifier_type' => 'sku_number', 'identifier'=>$inventory->getUPC()];
                }
                $state = ObjectManager::getInstance()->get('\Magento\Framework\App\State');
                $state->emulateAreaCode(\Magento\Framework\App\Area::AREA_WEBAPI_REST, [$inventoryApi, 'setInventory'], [$inventoryObject]);
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

}
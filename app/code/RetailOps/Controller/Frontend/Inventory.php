<?php

namespace Shiekhdev\RetailOps\Controller\Frontend;

use Magento\Framework\App\ObjectManager;
use \Shiekhdev\RetailOps\Controller\RetailOps;

class Inventory extends RetailOps
{
    const PARAM = 'inventory_updates';
    const SKU = 'sku';
    const QUANTITY = 'quantity_available';
    const SERVICENAME = 'inventory';

    protected $events = [];
    protected $response = [];
    protected $status = 'success';
    protected $association = [];

    public function execute()
    {
        try {

            $inventories = $this->getRequest()->getParam(self::PARAM);
            if (count($inventories)) {
                $inventory = [];
                foreach ($inventories as $invent) {
                    $object = ObjectManager::getInstance()->create('Shiekhdev\RetailOps\Model\Inventory');
                    $object->setSKU($invent[self::SKU]);
                    $object->setCount($invent[self::QUANTITY]);
                    $inventoryObject[] = $object;
                }
                $inventoryApi = ObjectManager::getInstance()->create('Shiekhdev\RetailOps\Model\Inventory\Inventory');
                $serviceName = self::SERVICENAME;
                $areaName = "retailops_before_push_{$serviceName}";
                $this->_eventManager->dispatch($areaName, [
                    'inventory' => $inventory,
                    'request' => $this->getRequest(),
                    'events' => $this->events
                ]);
                foreach ($inventoryObject as $inventory){
                    $this->association[] = ['identifier_type' => 'sku_number', 'identifier'=>$inventory->getSKU()];
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
            $areaName = "retailops_error_event_{$serviceName}";
            $this->_eventManager->dispatch($areaName, [
                'event' => $event,
                'request' => $this->getRequest(),
                'events' => $this->events
            ]);
            $this->events[] = $event;
            $this->status = 'error';

        }finally {
            $this->response['events'] = [];
            foreach ($this->events as $event)
            {
                $this->response['events'][] = $event;
            }
            $areaName = "retailops_before_response_{$serviceName}";
            $this->_eventManager->dispatch($areaName, [
                'response' => $this->response,
                'request' => $this->getRequest(),
                'events' => $this->events
            ]);
            $this->getResponse()->representJson(json_encode($this->response));
            $this->getResponse()->setStatusCode('200');
            return $this->getResponse();
        }


    }

}
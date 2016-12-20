<?php
namespace RetailOps\Api\Ui\Component\Listing\DataProviders\Retailops\Api;

class Log extends \Magento\Ui\DataProvider\AbstractDataProvider
{    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \RetailOps\Api\Model\Resource\Collection\InventoryHistory\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}

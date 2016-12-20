<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.10.16
 * Time: 9.33
 */

namespace RetailOps\Api\Model;


use RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcRepositoryInterface;
use \RetailOps\Api\Model\Resource\Collection\RoRicsLinkUpc\CollectionFactory;
use \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface as RoRiLink;


class RoRicsLinkUpcRepository implements RetailOpsRicsLinkByUpcRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var Resource\RoRicsLinkUpc
     */
    protected $resource;

    /**
     * @var RoRicsLinkFactory
     */
    protected $roRicsLinkFactory;

    /**
     * @var \RetailOps\Api\Logger\Logger
     */
    protected $logger;

    /**
     * RoRicsLinkUpcRepository constructor.
     * @param CollectionFactory $collection
     */
    public function __construct(CollectionFactory $collection,
                                \RetailOps\Api\Model\Resource\RoRicsLinkUpc $resource,
                                \RetailOps\Api\Model\RoRicsLinkUpcFactory $roRicsLinkFactory,
                                \RetailOps\Api\Model\Product\CollectionFactory $productCollectionFactory,
                                \RetailOps\Api\Logger\Logger $logger)
    {
        $this->collectionFactory = $collection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
        $this->roRicsLinkFactory = $roRicsLinkFactory;
        $this->logger = $logger;
    }

    public function save(\RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface $link)
    {
        return $this->resource->save($link);
    }

    public function load($id)
    {
        $model = $this->roRicsLinkFactory->create();
        return $this->resource->load($model,$id);
    }

    /**
     * @param $upc
     * @return \Magento\Framework\DataObject
     */
    public function getRoUpc($upc)
    {
        $collection = $this->getAllUpcs($upc);
        $collection->addFieldToFilter('rrrlu2.'.RoRiLink::RO_UPC,1);
        return $collection->getFirstItem();

    }

    /**
     * @param $upc
     * @return array|mixed|null
     */
    public function getProductUpcByRoUpc($upc)
    {
        $this->setROUpc($upc);
        $upcItems = $this->getAllUpcs($upc);
        $upcs = [];
        $upcsInStore = [];
        foreach ($upcItems as $upcLink)
        {
            $upcs[] = $upcLink->getUpc();
        }
        if(!in_array($upc, $upcs)) {
            $upcs[] = $upc;
        }
        if(!count($upcs)) {
            return [];
        }
        /**
         * @var Product\Collection $productCollection
         */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToFilter('upc', ['in' => $upcs]);
        /**
         * set default store
         */
        $productCollection->setStoreId(0);
        $productCollection->load();
        $firstProduct = $productCollection->getFirstItem();
        if(!$firstProduct->getId()) {
            return [];
        }
        //check that only one product for all upc
        foreach ($productCollection as $product) {
            if ($product->getId() !== $firstProduct->getId()) {
                $this->logger->addError('More than one product for upc: '.$upc);
            }
            $upcsInStore[] = (string)$product->getUpc();

        }
        return $upcsInStore;

    }


    /**
     * @param string $upcValue
     */
    public function getByUpc($upcValue)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RoRiLink::UPC,$upcValue);
        return $collection->getFirstItem();
    }

    /**
     * @param array $upcs
     * @return Resource\Collection\RoRicsLinkUpc\Collection
     */
    public function getAllROUpcsByUpcs($upcs)
    {
        $collection = $this->collectionFactory->create();
        /**
         * @var \RetailOps\Api\Model\Resource\Collection\RoRicsLinkUpc\Collection $collection
         */
        $collection->getSelect()
            ->joinLeft(['rrrlu2' => $this->resource->getMainTable()],'rrrlu2.'.
                RoRiLink::RICS_ID.'=main_table.'.RoRiLink::RICS_ID, ['rrrlu2.*']);
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns('*', 'rrrlu2');
        $collection->addFieldToFilter('main_table.'.RoRiLink::UPC, [ 'in' => $upcs]);
        $collection->addFieldToFilter('rrrlu2.'.RoRiLink::RO_UPC,1);
        $collection->setOrder('update_at');
        $collection->getSelect()->group('rrrlu2.entity_id');
        return $collection;
    }

    /**
     * @param $upc
     * @return Resource\Collection\RoRicsLinkUpc\Collection
     */
    public function getAllUpcs($upc)
    {
        $collection = $this->collectionFactory->create();
        /**
         * @var \RetailOps\Api\Model\Resource\Collection\RoRicsLinkUpc\Collection $collection
         */
        $collection->getSelect()
            ->joinLeft(['rrrlu2' => $this->resource->getMainTable()],'rrrlu2.'.
                RoRiLink::RICS_ID.'=main_table.'.RoRiLink::RICS_ID, ['rrrlu2.*']);
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns('*', 'rrrlu2');
        $collection->addFieldToFilter('main_table.'.RoRiLink::UPC, $upc);
        $collection->setOrder('rrrlu2.update_at');
        return $collection;
    }

    public function setRoUpc($upcValue)
    {
        //see if for this upc already has upc
        $upc = $this->getRoUpc($upcValue);
        if($upc->getId()) {
            return;
        }
        $upc = $this->getByUpc($upcValue);
        if (!$upc->getId()) {
            return;
        }
        $upc->setRoUpc(true);
        $this->save( $upc );
    }

    public function resetROUpc( $upcValue )
    {
        $upc = $this->getRoUpc( $upcValue );
        if( $upc->getUpc() === $upcValue ) {
            return;
        }
        if ($upc->getId()) {

            $upc->setRoUpc(false);
            $this->save( $upc );
        }
        $newRoUpc = $this->getByUpc( $upcValue );
        $newRoUpc->setRoUpc(true);
        $this->save( $newRoUpc );
    }


}
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

    public function getProductUpcByRoUpc($upc)
    {
        $upcItems = $this->getAllUpcs($upc);
        $upcs = [];
        foreach ($upcItems as $upcLink)
        {
            $upcs[] = $upcLink->getUpc();
        }
        if(!count($upcs)) {
            return null;
        }
        /**
         * @var Product\Collection $productCollection
         */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToFilter('upc', ['in' => $upcs]);
        $productCollection->load();
        $firstProduct = $productCollection->getFirstItem();
        if(!$firstProduct->getId()) {
            return null;
        }
        //check that only one product for all upc
        foreach ($productCollection as $product) {
            if ($product->getId() !== $firstProduct->getId()) {
                $this->logger->addError('More than one product for upc:'.$upc);
                throw new \LogicException(__('For upc'.$upc .'more than one product'));
            }
        }
        return $firstProduct->getUpc();

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
        return $collection;
    }




}
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
     * @var Resource\RoRicsLinkUpc
     */
    protected $resource;

    /**
     * @var RoRicsLinkFactory
     */
    protected $roRicsLinkFactory;

    /**
     * RoRicsLinkUpcRepository constructor.
     * @param CollectionFactory $collection
     */
    public function __construct(CollectionFactory $collection,
                                \RetailOps\Api\Model\Resource\RoRicsLinkUpc $resource,
                                \RetailOps\Api\Model\RoRicsLinkUpcFactory $roRicsLinkFactory)
    {
        $this->collectionFactory = $collection;
        $this->resource = $resource;
        $this->roRicsLinkFactory = $roRicsLinkFactory;
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
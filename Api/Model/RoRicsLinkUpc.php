<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.10.16
 * Time: 9.22
 */

namespace RetailOps\Api\Model;


use \Magento\Framework\Model\AbstractModel;
use RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface;

class RoRicsLinkUpc extends AbstractModel implements RetailOpsRicsLinkByUpcInterface
{
    public function getRicsIntegrationId()
    {
        return parent::getData(self::RICS_ID);
    }

    public function getUpc()
    {
        return parent::getData(self::UPC);
    }

    public function getRoUpc()
    {
        return parent::getData(self::RO_UPC);
    }

    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * @param  string $upc
     */
    public function setRoUpc($upc)
    {
       return parent::setData(self::RO_UPC, $upc);
    }

}
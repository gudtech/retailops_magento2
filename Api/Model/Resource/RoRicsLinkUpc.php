<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.10.16
 * Time: 9.55
 */

namespace RetailOps\Api\Model\Resource;


class RoRicsLinkUpc extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('retailops_rics_retailops_link_upc', 'entity_id');
    }
}
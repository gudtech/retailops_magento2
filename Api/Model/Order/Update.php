<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.56
 */

namespace \RetailOps\Api\Model\Order;


class Update
{
    protected $updateOrder;

    public function __construct(\RetailOps\Api\Model\Api\Order\Update $updateOrder)
    {
        $this->updateOrder = $updateOrder;
    }

    public function updateOrder($postData)
    {
        if($postData['rmas'] === null && $postData['order'] === null ) {
            throw new \LogicException( __('Don\'t have rmas or order for updates') );
        }
        $this->updateOrder->updateOrder($postData);

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 26.9.16
 * Time: 11.56
 */

namespace \RetailOps\Api\Model\Order;


class Complete
{
    protected $completeOrder;

    public function __construct(\RetailOps\Api\Model\Api\Order\Complete $completeOrder)
    {
        $this->completeOrder = $completeOrder;
    }

    public function updateOrder($postData)
    {
        if( !isset($postData['order']) || !isset($postData['order']['shipments']) ) {
            throw new \LogicException( __('Don\'t have valid data') );
        }
        $this->completeOrder->completeOrder($postData['order']);

    }
}
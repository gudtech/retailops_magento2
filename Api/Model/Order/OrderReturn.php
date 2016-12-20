<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 29.11.16
 * Time: 10.15
 */

namespace RetailOps\Api\Model\Order;


class OrderReturn
{
    /**
     * @var \RetailOps\Api\Model\Api\Order\OrderReturn
     */
    protected $orderReturn;

    public function __construct(\RetailOps\Api\Model\Api\Order\OrderReturn $orderReturn)
    {
        $this->orderReturn = $orderReturn;
    }

    public function returnOrder($data)
    {
        $this->orderReturn->returnData($data);
    }
}
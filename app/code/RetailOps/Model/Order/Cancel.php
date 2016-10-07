<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 12.12
 */

namespace Shiekhdev\RetailOps\Model\Order;


class Cancel
{
    /**
     * @var \Shiekhdev\RetailOps\Model\Api\Order\Cancel
     */
    protected $cancelOrder;

    public function cancelOrder( $postData )
    {
        if ($postData['order']) {
            $response = $this->cancelOrder->cancel($postData['order']);
            return $response;
        }
        return [];
    }

    /**
     * Cancel constructor.
     * @param \Shiekhdev\RetailOps\Model\Api\Order\Cancel $cancelOrder
     */
    public function __construct(\Shiekhdev\RetailOps\Model\Api\Order\Cancel $cancelOrder)
    {
        $this->cancelOrder = $cancelOrder;
    }
}
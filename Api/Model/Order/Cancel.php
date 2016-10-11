<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 23.9.16
 * Time: 12.12
 */

namespace \RetailOps\Api\Model\Order;


class Cancel
{
    /**
     * @var \RetailOps\Api\Model\Api\Order\Cancel
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
     * @param \\RetailOps\Api\Model\Api\Order\Cancel $cancelOrder
     */
    public function __construct(\RetailOps\Api\Model\Api\Order\Cancel $cancelOrder)
    {
        $this->cancelOrder = $cancelOrder;
    }
}
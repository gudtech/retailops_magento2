<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 11.23
 */

namespace RetailOps\Api\Api\Queue;


interface QueueInterface
{
    /**
     * @return mixed
     */
    public function setToQueue($message, \Magento\Sales\Api\Data\OrderInterface $order, $type);

    /**
     * @param $id
     * @return mixed
     */
    public function getFromQueue($id);

}
<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 22.9.16
 * Time: 14.11
 */

namespace Shiekhdev\RetailOps\Model;


class Acknowledge
{
    /**
     * @var Api\Acknowledge
     */
    protected $acknowledge;
    /**
     * @param array $postData
     * @return array
     */
    public function setOrderRefs($postData)
    {
        if ($postData['orders']) {
            $events = $this->acknowledge->setOrderNumbers($postData['orders']);
            return $events;
        }
        return [];
    }

    public function __construct(\Shiekhdev\RetailOps\Model\Api\Acknowledge $acknowledge)
    {
        $this->acknowledge = $acknowledge;
    }

}
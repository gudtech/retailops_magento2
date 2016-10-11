<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 27.9.16
 * Time: 10.26
 */

namespace RetailOps\Api\Config;


class InventoryType implements \Magento\Framework\Option\ArrayInterface
{
    protected $statuses = [
        'internal' => 'internal',
        'empty' => 'empty',
        'external' => 'external'
    ];
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
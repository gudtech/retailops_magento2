<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 7.9.16
 * Time: 15.36
 */

namespace RetailOps\Api\Api;


interface ApiInterface
{
    /**
     * @api
     * @param   string[] $data
     * @return string
     */
    public function pushInventory($data);
}
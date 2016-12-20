<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 21.10.16
 * Time: 9.34
 */

namespace RetailOps\Api\Api\Data;


interface RetailOpsRicsLinkByUpcRepositoryInterface
{
    /**
     * Save model.
     *
     * @param \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface $link
     * @return \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface $link);

    /**
     * @param $id
     * @return $this
     */
    public function load($id);

    /**
     * @param $upc
     * @return \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface
     */
    public function getRoUpc($upc);

    /**
     * @param $upc
     * @return \RetailOps\Api\Api\Data\RetailOpsRicsLinkByUpcInterface
     */
    public function getAllUpcs($upc);

    /**
     * @param string $upc
     * @return void
     */
    public function setRoUpc($upc);
}
<?php
namespace RetailOps\Api\Api;

use RetailOps\Api\Model\QueueInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QueueRepositoryInterface 
{
    public function save(QueueInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(QueueInterface $page);

    public function deleteById($id);
}

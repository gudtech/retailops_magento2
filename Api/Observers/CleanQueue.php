<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 6.12.16
 * Time: 18.32
 */

namespace RetailOps\Api\Observers;

use Magento\Framework\App\ObjectManager;
use \RetailOps\Api\Model\Order\Cancel;

class CleanQueue   implements \Magento\Framework\Event\ObserverInterface
{
    const ORDER_STATUS = [
        'canceled',
        'closed'
    ];
    /**
     * @var \RetailOps\Api\Model\QueueRepository
     */
    protected $queueRepository;

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getOrder();
        $scopeConfig = ObjectManager::getInstance()->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        if($scopeConfig->getValue(Cancel::QUEUE)) {
            if (in_array($order->getStatus(), self::ORDER_STATUS) && !$order->isObjectNew() && $order->hasDataChanges()) {
                $this->queueRepository->deleteByOrderInc($order->getIncrementId());
            }
        }
    }

    public function __construct(\RetailOps\Api\Model\QueueRepository  $queueRepository)
    {
      $this->queueRepository = $queueRepository;
    }
}
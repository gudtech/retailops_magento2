<?php
namespace RetailOps\Api\Model\Pull;

use Magento\Framework\Exception\AuthenticationException;
use \Magento\Framework\ObjectManagerInterface;
class Order
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $OrderRepository;

    /**
     * @var int
     */
    protected $currentPage;
    /**
     * @var \Magento\Framework\Api\SearchCriteria
     */
    protected $searchCriteria;
    /**
     * @var ObjectManagerInterface
     */
    protected $ObjectManager;

    /**
     * @var int|null
     */
    protected $countPages=1;

    /**
     * @var Magento\Framework\Api\Filter[]
     */
    private $filters=[];

    public function getOrders($pageToken, $maxcount = 1, $data)
    {
        $this->setFilters($pageToken, $maxcount, $data);
        // Create the order repo and get a list of orders matching our criteria
        $result = $this->OrderRepository->getList($this->searchCriteria);
        $this->countPages = $result->getLastPageNumber();
        $orderItems = $this->RetailOrderMaps->getOrders($result->getItems());
        if ($this->getNextPageToken()) {
            $orders['next_page_token'] = $this->getNextPageToken();
        }

        $orders['orders'] = $orderItems;
        return $orders;
    }


    protected function getNextPageToken()
    {
        if($this->countPages > $this->currentPage)
        {
            $service = $this->ObjectManager->get('\\RetailOps\Api\Service\NumberPageToken');
            $pageNumberToken = $service->encode($this->currentPage+1);
            return $pageNumberToken;
        }
        return null;
    }


    protected function setFilters($pageToken, $maxcount,$data)
    {
        $this->setData($pageToken, $maxcount, $data);
        $this->setSpecificOrders($data);
        $this->addFilterGroups();
    }

    private function setData($pageToken, $maxcount, $data)
    {
            $filter = $this->createFilter('retailops_send_status', 'eq', 0);
            $this->addFilter('retail_status',$filter);
            $page = $this->getCurrentPage($pageToken);
            $this->searchCriteria->setPageSize($maxcount);
            $this->searchCriteria->setCurrentPage($page);
            $this->currentPage = $page;
    }

    private function addFilter( $name, \Magento\Framework\Api\Filter $filter)
    {
        $this->filters[$name] = $this->createFilterGroup([$filter]);
    }

    private function getFilters()
    {
        return $this->filters;
    }


    private function addFilterGroups()
    {
        $groups = [];

        if (($filters = $this->getFilters()) && count($filters)) {
                foreach($filters as $key=>$filter){
                    $groups[] = $filter;
                }
        }
            $this->searchCriteria->setFilterGroups($groups)
                ->setSortOrders($this->createSortOrder('created_at', 'asc'));
    }

    private function setSpecificOrders($data)
    {
        if (isset($data['specific_orders'])) {
            $orders_id =  $this->getIdOrders($data['specific_orders']);
            if ($orders_id) {
                $this->resetFilters();
                $filter = $this->createFilter('entity_id','in', $orders_id);
                $this->addFilter('specificOrder', $filter);
            }
        }
    }

    private function resetFilters()
    {
        $this->filters = [];
    }

    private function getIdOrders($orders)
    {
        $orders_id = [];
        if (count($orders)) {
            foreach ($orders as $order_id) {
                $val = $order_id['channel_refnum'];
                if (is_numeric($val)) {
                    $orders_id[] = $val;
                }
            }
        }
        return $orders_id;
    }

    protected function getCurrentPage($pageToken=null)
    {
        $page = 1;
        if($pageToken) {
            if ($pageToken === 'string') {
                return 1;
            }
            $service = $this->ObjectManager->get('\\RetailOps\Api\Service\NumberPageToken');
            $pageNumber = $service->decode($pageToken);
            if (is_numeric($pageNumber)){
                $page = (int)$pageNumber;
            }else {
                $logger = $this->ObjectManager->get('Psr\Log\LoggerInterface');
                $logger->addCritical($pageToken. ' is invalid');
                throw new \Magento\Framework\Exception\AuthenticationException(__('Page token are invalid'));
            }
        }
        return $page;
    }

    /**
     * create a sort order with the given field and direction
     *
     * @param  \string $field
     * @param  \string $direction
     * @return \Magento\Framework\Api\SortOrder
     */
    private function createSortOrder($field, $direction)
    {
        // Create a sort order
        $sortOrder = $this->ObjectManager->create('\Magento\Framework\Api\SortOrder');
        $sortOrder->setField($field)
            ->setDirection($direction);

        return array($sortOrder);
    }

    /**
     * create a filter group using the given filters
     *
     * @param  \Magento\Framework\Api\Filter[] $filters
     * @return \Magento\Framework\Api\Search\FilterGroup
     */
    private function createFilterGroup($filters)
    {
        // Add the filters to a filter group
        $filterGroup = $this->ObjectManager->create('\Magento\Framework\Api\Search\FilterGroup');
        $filterGroup->setFilters($filters);

        return $filterGroup;
    }

    /**
     * create a filter using the given field, operator and value
     *
     * @param  \string $field
     * @param  \string $operator
     * @param  \string $value
     * @return \Magento\Framework\Api\Filter
     */
    private function createFilter($field, $operator, $value)
    {
        // Create a filter
        $filter = $this->ObjectManager->create('\Magento\Framework\Api\Filter');
        $filter->setField($field)
            ->setConditionType($operator)
            ->setValue($value);

        return $filter;
    }

    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $OrderRepository,
                                   ObjectManagerInterface $objectManager,
                                   \RetailOps\Api\Model\Api\Map\Order $RetailOrderMaps)
    {
        $this->OrderRepository = $OrderRepository;
        $this->ObjectManager = $objectManager;
        $this->RetailOrderMaps = $RetailOrderMaps;
        $this->searchCriteria = $this->ObjectManager->create('\Magento\Framework\Api\SearchCriteria');

    }

}
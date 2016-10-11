<?php
/**
 * Created by PhpStorm.
 * User: galillei
 * Date: 8.9.16
 * Time: 11.11
 */

namespace Shiekhdev\RetailOps\CustomRouter;



use Magento\Framework\App\ObjectManager;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected static $map =
        [
            'inventory_push_v1' => 'Inventory',
            'order_pull_v1' => 'Order\\Pull',
            'order_acknowledge_v1' => 'Order\Acknowledge',
            'order_cancel_v1' => 'Order\Cancel',
            'order_complete_v1' => 'Order\Complete'

        ];
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if(!$request->isPost())
            return null;
        $identifier = trim($request->getPathInfo(), '/');
        $path = explode('/', $identifier);
        if (count($path) !== 2)
            return null;

        if ($path[0] !== 'retailops')
            return null;
        if (isset(self::$map[$path[1]])) {
            $controller = self::$map[$path[1]];
            $content = file_get_contents('php://input');
            $paremeters = ObjectManager::getInstance()->create('Zend\Stdlib\Parameters',
                [ 'values'=>json_decode($content, true)]);
            //fix error with empty content
            $request->setPost($paremeters);
            return $this->actionFactory->create(
                "Shiekhdev\\RetailOps\\Controller\\Frontend\\{$controller}",
                ['request' => $request]
            );
        }
        return null;

    }
}
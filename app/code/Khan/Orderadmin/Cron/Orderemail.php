<?php

namespace Khan\Orderadmin\Cron;

class Orderemail {

    protected $logger;

    const XML_PATH_EMAIL_TIME = 'test/settings/notifytime';
    const XML_PATH_EMAIL_EMAIL = 'test/settings/custom_email';

    public function __construct(
    \Psr\Log\LoggerInterface $loggerInterface, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Framework\Api\SortOrderBuilder $sortBuilder, \Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, array $data = []
    ) {
        $this->logger = $loggerInterface;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $data);
    }

    public function execute() {

        $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('status', 'pending', 'eq')
                        ->addSortOrder($this->sortBuilder->setField('entity_id')
                                ->setDescendingDirection()->create())
                        ->setPageSize(100)->setCurrentPage(1)->create();

        $to = now(); // current date
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $to = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TIME, $storeScope);

        $from = strtotime('-2 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from); // 2 days before

        $ordersList = $this->orderRepository->getList($searchCriteria);
        $ordersList->addFieldToFilter('created_at', array('from' => $from, 'to' => $to));
        $report = array(
            'total_orders' => count($ordersList)
            
        );

        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($report);
        $useremail=$this->scopeConfig->getValue(self::XML_PATH_EMAIL_EMAIL, $storeScope);
        $transport = $this->transportBuilder
                ->setTemplateIdentifier('productapproval_status_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(['name' => 'order notification', 'email' => 'khanlucky2016@gmail.com'])
                ->addTo([trim($useremail)])
                ->getTransport();
        $transport->sendMessage();
    }

}

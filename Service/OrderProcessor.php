<?php

namespace MageSuite\AutoOrderCompletion\Service;

class OrderProcessor
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var InvoiceCreator
     */
    protected $invoiceCreator;

    /**
     * @var ShipmentCreator
     */
    protected $shipmentCreator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $greaterThanDaysFilter = 0;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param InvoiceCreator $invoiceCreator
     * @param ShipmentCreator $shipmentCreator
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        InvoiceCreator $invoiceCreator,
        ShipmentCreator $shipmentCreator,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceCreator = $invoiceCreator;
        $this->shipmentCreator = $shipmentCreator;
        $this->logger = $logger;
    }

    public function completeOrders()
    {
        $collection = $this->getCollection();
        $lastPage = $collection->getSize();
        $page = 1;

        while ($page <= $lastPage) {
            $collection->setCurPage($page)->load();

            foreach ($collection as $order) {
                try {
                    $this->invoiceCreator->execute($order);
                    $this->shipmentCreator->execute($order);
                } catch (\Exception $e) {
                    $this->logger->error($e);
                }
            }

            $collection->clear();
            $page++;
        }

        return $this;
    }

    /**
     * Add order creation date filter
     *
     * @param int $days
     * @return $this
     */
    public function addGreaterThanDaysFilter(int $days)
    {
        $this->greaterThanDaysFilter = $days;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function getCollection()
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addAttributeToFilter(
            'main_table.state',
            ['nin' => [
                \Magento\Sales\Model\Order::STATE_CLOSED,
                \Magento\Sales\Model\Order::STATE_CANCELED
            ]]
        )->setPageSize(500);
        $conditions = ['si.entity_id IS NULL', 'ssh.entity_id IS NULL'];
        $collection->getSelect()
            ->joinLeft(
                ['si' => $collection->getTable('sales_invoice')],
                'si.order_id = main_table.entity_id',
                []
            )->joinLeft(
                ['ssh' => $collection->getTable('sales_shipment')],
                'ssh.order_id = main_table.entity_id',
                []
            )->where(implode(' OR ', $conditions));

        if ($this->greaterThanDaysFilter > 0) {
            $modify = sprintf('-%d days', $this->greaterThanDaysFilter);
            $dateTime = new \DateTime();
            $dateTime->modify($modify);
            $collection->addAttributeToFilter(
                'main_table.created_at',
                ['gt' => $dateTime->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)]
            );
        }

        return $collection;
    }
}

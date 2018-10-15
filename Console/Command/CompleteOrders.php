<?php

namespace MageSuite\AutoOrderCompletion\Console\Command;

class CompleteOrders extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $ordersCollectionFactory;
    /**
     * @var \MageSuite\AutoOrderCompletion\Service\InvoiceCreatorFactory
     */
    private $invoiceCreatorFactory;
    /**
     * @var \MageSuite\AutoOrderCompletion\Service\ShipmentCreatorFactory
     */
    private $shipmentCreatorFactory;

    /**
     * ImportFile constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $ordersCollectionFactory,
        \MageSuite\AutoOrderCompletion\Service\InvoiceCreatorFactory $invoiceCreatorFactory,
        \MageSuite\AutoOrderCompletion\Service\ShipmentCreatorFactory $shipmentCreatorFactory
    )
    {
        parent::__construct();

        $this->state = $state;
        $this->ordersCollectionFactory = $ordersCollectionFactory;

        $this->invoiceCreatorFactory = $invoiceCreatorFactory;
        $this->shipmentCreatorFactory = $shipmentCreatorFactory;
    }

    protected function configure()
    {
        $this
            ->setName('order:complete')
            ->setDescription('Automatically complete all orders by creating invoices and shipments');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    )
    {
        $ordersCollection = $this->ordersCollectionFactory->create();
        $invoiceCreator = $this->invoiceCreatorFactory->create();
        $shipmentCreator = $this->shipmentCreatorFactory->create();

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($ordersCollection as $order) {
            if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_COMPLETE) {
                continue;
            }

            $invoiceCreator->createInvoice($order);
            $shipmentCreator->createShipment($order);
        }
    }
}

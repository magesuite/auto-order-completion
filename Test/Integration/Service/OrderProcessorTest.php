<?php

namespace MageSuite\AutoOrderCompletion\Test\Integration\Observer;

class OrderProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\AutoOrderCompletion\Service\OrderProcessor
     */
    protected $orderProcessor;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderFactory = $this->objectManager->get(\Magento\Sales\Model\OrderFactory::class);
        $this->orderProcessor = $this->objectManager->get(\MageSuite\AutoOrderCompletion\Service\OrderProcessor::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 1
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testItCompletesOrderAutomaticallyOnOrderSave()
    {
        $this->orderProcessor->completeOrders();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(1, $order->hasInvoices());
        $this->assertEquals(1, $order->hasShipments());
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_COMPLETE, $order->getStatus());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAdminConfigFixture autocompletion/settings/auto_invoicing_enabled 0
     * @magentoAdminConfigFixture autocompletion/settings/auto_shipment_enabled 0
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderIsNotCompletedAutomaticallyWhenAutoInvoicingAndShippmentAreDisabled()
    {
        $this->orderProcessor->completeOrders();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId('100000001');

        $this->assertEquals(0, $order->hasInvoices());
        $this->assertEquals(0, $order->hasShipments());
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_PROCESSING, $order->getStatus());
    }
}

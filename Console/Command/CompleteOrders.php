<?php

namespace MageSuite\AutoOrderCompletion\Console\Command;

class CompleteOrders extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \MageSuite\AutoOrderCompletion\Service\OrderProcessorFactory
     */
    protected $orderProcessorFactory;

    /**
     * @param \MageSuite\AutoOrderCompletion\Service\OrderProcessorFactory $orderProcessorFactory
     */
    public function __construct(\MageSuite\AutoOrderCompletion\Service\OrderProcessorFactory $orderProcessorFactory)
    {
        parent::__construct();
        $this->orderProcessorFactory = $orderProcessorFactory;
    }

    protected function configure()
    {
        $this->setName('order:complete')
            ->setDescription('Automatically complete all orders by creating invoices and shipments');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->orderProcessorFactory->create()->completeOrders();
    }
}

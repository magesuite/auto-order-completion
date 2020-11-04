<?php

namespace MageSuite\AutoOrderCompletion\Console\Command;

class CompleteOrders extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \MageSuite\AutoOrderCompletion\Service\OrderProcessor
     */
    protected $orderProcessor;

    /**
     * @param \MageSuite\AutoOrderCompletion\Service\OrderProcessor $orderProcessor
     */
    public function __construct(\MageSuite\AutoOrderCompletion\Service\OrderProcessor $orderProcessor)
    {
        parent::__construct();
        $this->orderProcessor = $orderProcessor;
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
        $this->orderProcessor->execute();
    }
}

<?php

namespace MageSuite\AutoOrderCompletion\Cron;

class ProcessPendingOrders
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
        $this->orderProcessor = $orderProcessor;
    }

    public function execute()
    {
        $this->orderProcessor->addGreaterThanDaysFilter(7)->execute();
    }
}

<?php

namespace Icare\Sales\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * command to aggregate sales order
 * @author Nam Pham
 *
 */
class AggregateCommand extends \Icare\NetSuite\Console\Command\AbstractCommand
{
    protected function configure()
    {
        $this->setName('sales:aggregate')
        ->addOption("--fromDate", "-f", InputOption::VALUE_OPTIONAL, "date in YYYY-MM-DD from which aggregation starts", "now")
        ->addOption("--toDate", "-t", InputOption::VALUE_OPTIONAL, "date in YYYY-MM-DD at which aggregation stops", "now");
    
        parent::configure();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Icare\NetSuite\Console\Command\AbstractCommand::doExecute()
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        /**
         * 
         * @var \Magento\Framework\Locale\ResolverInterface $localeResolver
         */
        $localeResolver = $this->objectManager->get('Magento\Framework\Locale\ResolverInterface');  
        $localeResolver->emulate(0);
        
        /**
         * 
         * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
         */
        $localeDate = $this->objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $from = $input->getParameterOption(['--fromDate', '-f'], 'now');
        $to = $input->getParameterOption(['--toDate', '-t'], 'now');
        
        $dateCurrent = $localeDate->date(new \DateTime($from));
        $dateTo = $localeDate->date(new \DateTime($to));
        
        /**
         * 
         * @var \Magento\Sales\Model\ResourceModel\Report\OrderFactory $orderFactory
         */
        $orderFactory = $this->objectManager->get('Magento\Sales\Model\ResourceModel\Report\OrderFactory');
        /**
         *
         * @var \Icare\Sales\Model\ResourceModel\Report\OrderFactory $orderFactory
         */
        $icareOrderFactory = $this->objectManager->get('Icare\Sales\Model\ResourceModel\Report\OrderFactory');
        
        while ($dateCurrent->getTimestamp() <= $dateTo->getTimestamp()) {
            $this->logger->info(\sprintf('Aggregate sales order report data at %s', $dateCurrent->format('Y-m-d')));
            // aggregate magento's sales order report data
            $orderFactory->create()->aggregate($dateCurrent);
            // and i care report data (with user_id)
            $icareOrderFactory->create()->aggregate($dateCurrent);
            $dateCurrent = $dateCurrent->add(new \DateInterval('PT24H'));
        }
        
        $localeResolver->revert();
    }
}
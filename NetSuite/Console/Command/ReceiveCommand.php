<?php

namespace Icare\NetSuite\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implement console command to read command from SQS for different websites
 * 
 * @author Nam Pham
 * @since 2.0.5 
 */
class ReceiveCommand extends AbstractCommand
{
   
    public static $QUEUE_NAMES = ['item_fulfillment', 'inventory_item'];
    
    /**
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('netsuite:receive')
            ->setDescription('Recieve and process message from SQS queue configured in "aws_sqs_netsuite_recv" variable for messages and post to Magento')
            ->addOption("--queue", "-Q", InputOption::VALUE_OPTIONAL, "queue name(s) (comma-separated) to read from, if not specified, all queues (inventory_item, item_fulfillment) will be read", "all")
            ->addOption("--website", "-w", InputOption::VALUE_OPTIONAL, "website code(s) (comma-separated) to read from, if not specified, all websites will be consumed", "info");
            
        parent::configure();
    }
 
    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {   
        // verify website codes
        $websiteCodes = \explode(',', $input->getParameterOption(["--website", "-w"], ""));
        $websites = $this->retrieveWebsites($websiteCodes);
        
        // verify queue names
        $queues = \explode(',', $input->getParameterOption(["--queue", "-Q"], "all"));
        if (\in_array('all', $queues)) {
            $queues = self::$QUEUE_NAMES;
        }
        foreach ($queues as $queue) {
            if (!\in_array($queue, self::$QUEUE_NAMES)) {
                throw new LocalizedException(__('Invalid queue name: %1', $queue));
            }
        }
        
        // initialize sqsClient
        $variable = $this->objectManager->get('Magento\Variable\Model\Variable');
        $sqsClient = new \Icare\NetSuite\Helper\Client\NetSuiteSQS($variable, $this->logger);
        
        // read message from site
        foreach ($websites as $website) {
            foreach ($queues as $queue) {
                $this->receiveWebsiteMessages($sqsClient, $website, $queue);
            }
        }
    }
    
    /**
     * 
     * @param string[] $websiteCodes
     *  array of website codes
     * @return \Magento\Store\Model\Website[]
     *  associative of code of \Magento\Store\Model\Website
     */
    private function retrieveWebsites($websiteCodes)
    {
        /**
         * 
         * @var \Magento\Store\Model\ResourceModel\Website\Collection $allSites
         */
        $colSites = $this->objectManager->get('Magento\Store\Model\ResourceModel\Website\Collection');
        $retVal = [];
        
        foreach ($colSites as $website) {
            if (in_array($website->getCode(), $websiteCodes) || empty($websiteCodes)) {
                $retVal[$website->getCode()] = $website;
            }
        }
        
        $missingCodes = \array_diff($websiteCodes, \array_keys($retVal));
        if (count($missingCodes)) {
            throw new LocalizedException(__('Invalid website code(s): %1', [implode(', ', $missingCodes)]));
        }
        
        return $retVal;
    }
    
    /**
     * consume message from SQS's queue of a website
     * @param \Icare\NetSuite\Helper\Client\NetSuiteSQS $sqsClient
     * @param \Magento\Store\Model\Website $website
     * @param string $queue name of the queue
     */
    protected function receiveWebsiteMessages($sqsClient, $website, $queue)
    {   
        $this->logger->info(\sprintf('Start retrieving messages for %s website\'s %s', $website->getName(), $queue));
        
        /**
         * @var \Magento\Framework\Event\ManagerInterface $eventManager
         */
        $eventManager = $this->objectManager->get('Magento\Framework\Event\ManagerInterface');
        
        $messages = $sqsClient->dequeue($website->getCode().'-'.$queue, 10, 0);
        $toDelete = [];
        while (count($messages) > 0) {
            foreach ($messages as $message) {
                $type = empty($message['_MessageType'])?'text':$message['_MessageType'];
                $payload = isset($message['_Payload']) && is_array($message['_Payload']) ? (object) $message['_Payload'] : (object) $message;
                $this->logger->debug(sprintf('dispatching message %s', json_encode($message, JSON_PRETTY_PRINT))); 
                try {
                    $eventManager->dispatch('netsuite_message_' . $type, ['payload' => $payload, 'logger' => $this->logger]);
                    $toDelete[] = $message;
                }
                catch (\Exception $ex) {
                    $this->logError($ex);
                }
            }
            if (count($toDelete) > 0) {
                $sqsClient->purge($toDelete);
                $toDelete = [];
            }
            $messages = $sqsClient->dequeue($website->getCode(), 10, 0);
        }
        
        $this->logger->info(\sprintf('Dispatched %u messages for %s website\'s %s', count($toDelete), $website->getName(), $queue));
    }
}

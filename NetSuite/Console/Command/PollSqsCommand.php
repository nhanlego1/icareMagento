<?php

namespace Icare\NetSuite\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implement console command to allow immediate invocation from OS cron
 * @author Nam Pham
 * @deprecated since 2.0.5 use {@link ConsumeSqsCommand} to read command from SQS for different websites
 */
class PollSqsCommand extends \Symfony\Component\Console\Command\Command
{
    private $_objectManager;
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
 
    /**
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('netsuite:poll_sqs')
            ->setDescription('Poll SQS queue configured in "aws_sqs_netsuite" variable for messages and post to NetSuite')
            ->addOption("--log-level", "-l", InputOption::VALUE_OPTIONAL, "log level which can be debug, info, notice, waring, error, critical, alert, emergency", "info");
            
        parent::configure();
    }
 
    /**
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logLevel = $input->getParameterOption("--log-level", "info");
        
        /**
         *
         * @var \Magento\Framework\ObjectManager\ObjectManager $om
         */
        $om = ObjectManager::getInstance();
        
        $netsuiteClient = $om->get('Icare\NetSuite\Helper\Client\NetSuiteClient');
        $variable = $om->get('Magento\Variable\Model\Variable');
        $logger = new \Icare\NetSuite\Console\Log\OutputInterfaceLogger($output, $logLevel);
        $sqsClient = new \Icare\NetSuite\Helper\Client\NetSuiteSQS($variable, $logger);
        $sqsConsumer = new \Icare\NetSuite\Cron\SqsConsumer($netsuiteClient, $sqsClient, $logger);
        
        $sqsConsumer->receiveAndSend();
    }
}

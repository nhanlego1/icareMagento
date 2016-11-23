<?php

namespace Icare\NetSuite\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author nampham
 *
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @var ObjectManager
     */
    protected $objectManager;
    
    /**
     * 
     * @var InputInterface
     */
    protected $input;
    
    /**
     * 
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * 
     * @var Application
     */
    protected $consoleApp;
    
    /**
     * implementation of command execution protected members have been assigned
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected abstract function doExecute(InputInterface $input, OutputInterface $output);
    
    /**
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->addOption("--log-level", "-l", InputOption::VALUE_OPTIONAL, "log level which can be debug, info, notice, waring, error, critical, alert, emergency", "info");
    
        parent::configure();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->objectManager = ObjectManager::getInstance();
        
        // initialize log
        $logLevel = $input->getParameterOption(["--log-level", "-l"], "info");
        $this->logger = new \Icare\NetSuite\Console\Log\OutputInterfaceLogger($output, $logLevel);
        
        // assign app state
        /**
         * @var \Magento\Framework\App\State $appState
         */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode('adminhtml');
        
        // retrieve consoleApp
        $this->consoleApp = $this->objectManager->get('Symfony\Component\Console\Application');
        
        $this->doExecute($input, $output);
    }
    
    /**
     * log exception to console output
     * @param \Exception $ex
     */
    function logError($ex)
    {
        $errors = [$ex];
        if ($ex instanceof \Icare\Exception\Model\IcareWebApiException) {
            $errors = null;
            if (empty($ex->getErrors())) {
                               
            }
            else {
                $errors = $ex->getErrors();
            }
        }
        foreach ($errors as $index => $ex) {
            $this->consoleApp->renderException($ex, $this->output instanceof ConsoleOutputInterface ? $this->output->getErrorOutput() : $this->output);
            $this->logger->error(sprintf("[%u] Unexpected error occured [%s] %s\n%s", $index + 1, $ex->getCode(), $ex->getMessage(), $ex->getTraceAsString()));
            if ($ex->getPrevious()) {
                $ex = $ex->getPrevious();
                $this->output->write("\t", false);
                $this->logger->error(sprintf("\tCaused by [%s] %s\n%s", $ex->getCode(), $ex->getMessage(), $ex->getTraceAsString()));
            }
        }
    }
}
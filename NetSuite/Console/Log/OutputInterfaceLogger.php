<?php

namespace Icare\NetSuite\Console\Log;

use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\json_encode;

/**
 *
 * @author Nam Pham
 *
 */
class OutputInterfaceLogger implements \Psr\Log\LoggerInterface
{
    private $_output;
    
    private $_minLevel;
    
    const LEVELS = array('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency');
    
    /**
     * 
     * @param OutputInterface $outputInterface
     * @param string $level
     */
    public function __construct(OutputInterface $outputInterface, $level = 'info')
    {
        $this->resetOutputInterface($outputInterface);
        $this->_minLevel = \array_search($level, self::LEVELS);
        if ($this->_minLevel < 0) {
            throw new \InvalidArgumentException('level is invalid');
        }
    }
    
    public function resetOutputInterface(OutputInterface $outputInterface)
    {
        $this->_output = $outputInterface;
    }
    
    /**
     * Interpolates context values into the message placeholders.
     */
    private function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
    
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
    
  
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $method = strtolower($level);
        
        if (\array_search($method, self::LEVELS) < $this->_minLevel) return;
        
        $output =  $this->_output;
        switch ($method) {
            case 'emergency':
            case 'critical':
            case 'alert':
            case 'error':
                if ($output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();   
                }   
            case 'warning':
            case 'notice':
            case 'info':
            case 'debug':
                break;
            default:
                throw new \Exception('Unknown log level: '.$level);
        }
        $dt = new \DateTime();
        $message = $dt->format('Y-m-d H:m:i [') . strtoupper($level) . '] ' .  $this->interpolate($message, $context);
        $output->writeln($message, OutputInterface::OUTPUT_NORMAL);
    }
    
    
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log('emergency', $message, $context);
    }
    
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log('alert', $message, $context);
    }
    
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log('critical', $message, $context);
    }
    
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log('error', $message, $context);
    }
    
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log('warning', $message, $context);
    }
    
    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log('notice', $message, $context);
    }
    
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log('info', $message, $context);
    }
    
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log('debug', $message, $context);
    }
    
    /**
     * to conform with ICareLogger
     *
     * @param string $message
     * @param array $context
     * @return null
     * @see \Icare\Custom\Model\ICareLogger
     */
    public function track($message, array $context = array())
    {
        $this->info(\is_string($message)?$message:\json_encode($message, JSON_PRETTY_PRINT), $context);
    }
}

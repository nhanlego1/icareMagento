<?php 
namespace Icare\NetSuite\Helper\Client;

/**
 * API for exchanging messages asynchronously with NetSuite
 * @author Nam Pham
 *
 */
interface NetSuiteQueueInterface 
{
    /**
     * queue message to NetSuite
     * @param string $queue name of the queue
     * @param mixed $payload
     */
    public function enqueue(string $queue, $payload);
    
    /**
     * dequeue message from NetSuite
     * @param string $queue name of the queue
     * @param mixed $payload
     * @return array
     *   array of messages
     */
    public function dequeue(string $queue, $maxMessages = 10, $timeoutSeconds = 0);
    
    /**
     * delete the received messages from <code>dequeue()</code> after process
     * @param array $messages
     * @see NetSuiteQueueInterface::dequeue()
     */
    public function purge($messages);
}
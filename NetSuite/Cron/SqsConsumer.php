<?php

namespace Icare\NetSuite\Cron;

/**
 * cron job implementation which read messages from SQS and post it to SQS
 * @author Nam Pham
 *
 */
class SqsConsumer
{
    private $_netsuiteClient;
    
    private $_sqsClient;
    
    private $_logger;
    
    /**
     *
     * @param \Icare\NetSuite\Helper\Client\NetSuiteClient $netsuiteClient
     * @param \Icare\NetSuite\Helper\Client\NetSuiteSQS $sqsClient
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Icare\NetSuite\Helper\Client\NetSuiteClient $netsuiteClient,
        \Icare\NetSuite\Helper\Client\NetSuiteSQS $sqsClient,
        \Psr\Log\LoggerInterface $logger)
    {
        $this->_netsuiteClient = $netsuiteClient;
        $this->_sqsClient = $sqsClient;
        $this->_logger = $logger;
    }
    
    /**
     * callback function for cron job: read from SQS and send to NetSuite
     * @deprecated since 2.0.5 this function is no longer required because each website has 
     *   a different queue to send to and receive from
     */
    public function receiveAndSend()
    {
        $begin = time();
        do {
            $messages = $this->_sqsClient->receiveFromSQS();
            $this->dispatchMessages($messages);
        } while (count($messages) > 0 && time() - $begin < 30);        // TODO make execution time configurable
    }
        
    /**
     * process message
     * @param array $messages
     */
    private function dispatchMessages($messages)
    {
        $toDelete = array();
        $errors = array();
        foreach ($messages as $message) {
            if (empty($message['url']) || empty($message['payload'])) {
                $this->_logger->error('either "url" or "payload" is missing from message: ' . json_encode($message, JSON_PRETTY_PRINT));
            } else {
                try {
                    $this->_logger->track($message, ['netsuite post request']);
                    $result = $this->_netsuiteClient->postToNetSuite($message['url'], $message['payload']);
                    $this->_logger->track($result, ['netsuite post response']);
                } catch (\Exception $ex) {
                    $errors []= array(
                      'message' => $message,
                      'exception' => $ex,
                    );
                }
            }
            $toDelete []= array(
                'Id' => $message['_MessageId'],             // can be different but no need
                'ReceiptHandle' => $message['_ReceiptHandle'],
            );
        }
        
        // retry errors
        if (count($errors) > 0) {
            $this->handleNetSuiteErrors($errors);
        }
        
        // delete the messages
        if (count($toDelete) > 0) {
            // remove processed messages
            $result = $this->_sqsClient->getSQSClient()->deleteMessageBatch(array(
                'QueueUrl' => $this->_sqsClient->getNetSuiteQueueURL(),
                'Entries' => $toDelete,
            ));
        }
    }
    
    /**
     * handle message exception
     * @param array $errors associative array which has "message" and "error"
     */
    private function handleNetSuiteErrors(&$errors)
    {
        $toSend = array();
        foreach ($errors as $error) {
            $message = &$error['message'];
            $payload = &$message['payload'];
            // decide whether to retry to send the message
            $ex = $error['exception'];
            if (isset($payload['_retry']) && is_numeric($payload['_retry'])) {
                if ($payload['_retry'] < 3) {
                    $payload['_retry'] ++;
                } else {
                    $this->_logger->error("Failed to dispatch message to NetSuite at {$message['url']}: "
                        . $ex->getMessage() . "\n" . json_encode($payload, JSON_PRETTY_PRINT));
                    continue;
                }
            } else {
                $payload['_retry'] = 1;
            }
            // retry to send the message back to SQS
            $this->_logger->warning("Retrying({$payload['_retry']}) to send message to NetSuite at {$message['url']}: " . $ex->getMessage());
            $toSend []= array(
              'Id' => $message['_MessageId'],                   // can be different but no need
              'MessageBody' => json_encode($message),
              'DelaySeconds' => 900/(4 - $payload['_retry']),   // maximum values 900 450 300
            );
        }
        // resend message
        if (count($toSend) > 0) {
            $result = $this->_sqsClient->getSQSClient()->sendMessageBatch(array(
                'QueueUrl' => $this->_sqsClient->getNetSuiteQueueURL(),
                'Entries' => $toSend,
            ));
        }
    }
}

<?php
namespace Icare\NetSuite\Helper\Client;

use Aws\Sqs\SqsClient;
use Aws\Sqs\Exception\SqsException;
use Aws\Common\Credentials\Credentials;
use Icare\NetSuite\Helper\Payload;
use Magento\Framework\Exception\LocalizedException;

/**
 * This code read the following system variables
 *
 * <ul>
 * <li><strong>aws_access_key</strong> AWS access key</li>
 * <li><strong>aws_access_secret</strong> AWS access secret</li>
 * <li><strong>aws_sqs_netsuite</strong> URL of the queue which receives messages sent to NetSuite
 * <br/>ie. <i>https://sqs.ap-southeast-1.amazonaws.com/&lt;account_id&gt;/netsuite</i></li>
 * </ul>
 *
 * @author Nam Pham
 *
 */
class NetSuiteSQS implements NetSuiteClientInterface, NetSuiteQueueInterface
{
    private $_sqsClient;

    private $_sqsNetSuiteQueue;
    
    private $_sqsMagentoQueue;

    private $_logger;
    
    private $_deleteAfterReceive = false;
    
    /**
     *
     * @param \Magento\Variable\Model\Variable $variables
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Variable\Model\Variable $variables,
        \Psr\Log\LoggerInterface $logger)
    {
        // read sqs queue url
        $this->_sqsNetSuiteQueue = $variables->loadByCode('aws_sqs_netsuite')->getPlainValue();
        
        $this->_sqsMagentoQueue = $variables->loadByCode('aws_sqs_magento')->getPlainValue();
        
        // read aws credentials
        $credentials = new Credentials($variables->loadByCode('aws_access_key')->getPlainValue(), $variables->loadByCode('aws_secret_key')->getPlainValue());
        
        // Instantiate the S3 client with your AWS credentials
        $this->_sqsClient = SqsClient::factory(array(
            'credentials' => $credentials,
            'region' => 'ap-southeast-1'
        ));
        
        $this->_logger = $logger;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Icare\NetSuite\Helper\Client\NetSuiteClientInterface::postToNetSuite()
     */
    public function postToNetSuite(string $api, $payload)
    {
        if (strpos($api, 'http://') !== 0 && strpos($api, 'https://') !== 0) {
            /**
             * @var \Icare\NetSuite\Helper\Entity\NetSuiteConfiguration $netsuiteConf
             */
            $netsuiteConf = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Icare\NetSuite\Helper\Entity\NetSuiteConfiguration');
            $url = call_user_func_array(array($netsuiteConf, 'getUrl'.$api), array());
            if ($url == false) {
                throw new \Magento\Framework\Exception\NotFoundException(__('No api found for ' . $api));
            }
        } else {
            $url = $api;
        }
        
        // use any primitive data as tag
        $tags = array();
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $tags[$key] = $value;
                }
            }
        }
        
        // prepare message and send to SQS
        $message = array(
            'url' => $url,
            'payload' => $payload,
            'tags' => array(
                'table' => $api,
                'tags' => $tags
            )
        );
        $this->_sqsClient->sendMessage(array(
            'QueueUrl' => $this->_sqsNetSuiteQueue,
            'MessageBody' => json_encode($message, JSON_PRETTY_PRINT),
            'DelaySeconds' => 1, // wait for transaction commit
        ));
    }

    /**
     * receive messages from SQS
     * @param number $maxNumberOfMessages
     * @param number $timeoutInSecs
     * @return array an array of associative array
     * @deprecated since 2.0.5 Magento does not send message directly to NetSuite
     */
    public function receiveFromSQS($maxNumberOfMessages = 5, $timeoutInSecs = 10)
    {
       return $this->dequeue(null, $maxNumberOfMessages, $timeoutInSecs);
    }
    
    /**
     *
     * @return \Aws\Sqs\SqsClient
     */
    public function getSQSClient()
    {
        return $this->_sqsClient;
    }
    
    /**
     * get a normalized URL, which will avoid some certain config errors.
     * <p>input: <code>http://magento/-test--here--and-there</code>  
     * <br/>will return: <code>http://magento/test-here-and-there</code>
     * 
     * @param string $url
     * @return string
     */
    public static function normalizeURL($url) {
        return preg_replace(["/\\/-/", "/--/"], ["/", "-"], $url);
    }
    
    /**
     * URL of the queue to send message to NetSuite
     * @param string website code
     * @return string URL of the queue
     */
    public function getNetSuiteQueueURL($websiteCode = null)
    {
        return self::normalizeURL($this->_sqsNetSuiteQueue . ($websiteCode ? '-'.$websiteCode : ''));
    }
    
    /**
     * URL of the queue to receive message from NetSuite
     * @param string website code
     * @return string URL of the queue
     */
    public function getMagentoQueueURL($websiteCode = null)
    {
        return self::normalizeURL($this->_sqsMagentoQueue . ($websiteCode ? '-'.$websiteCode : ''));
    }
    
    public function setDeleteAfterReceive($delete) 
    {
        $this->_deleteAfterReceive = $delete;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface::enqueue()
     */
    public function enqueue(string $queue, $payload)
    {
        try {
            $this->_sqsClient->sendMessage(array(
                'QueueUrl' => $this->getNetSuiteQueueURL($queue),
                'MessageBody' => json_encode($payload, JSON_PRETTY_PRINT),
            ));
        }
        catch (SqsException $ex) {
            $this->_logger->critical(sprintf('Failed to enqueue message to %s: %s', $this->getNetSuiteQueueURL($queue), $ex->getMessage()));
            throw new LocalizedException(__('Unexpected exception from SQS: %1', $ex->getMessage()), $ex);;
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface::dequeue()
     */
    public function dequeue(string $queue, $maxMessages = 10, $timeoutSeconds = 0)
    {
        $queueURL = $this->getMagentoQueueURL($queue);
        try {
            $result = $this->_sqsClient->receiveMessage(array(
                'QueueUrl' => $queueURL,
                'MaxNumberOfMessages' => $maxMessages,
                'WaitTimeSeconds' => $timeoutSeconds,
            ));
        } catch (SqsException $ex) {
            $this->_logger->critical(sprintf('Failed to receive message from %s: %s', $queueURL, $ex->getMessage()));
            throw new LocalizedException(__('Unexpected exception from SQS: %1', $ex->getMessage()), $ex);
        }
        
        $toDelete = [];
        $messages = [];
        $sqsMsgs = $result->getPath('Messages');
        if ($sqsMsgs) {
            foreach ($sqsMsgs as $sqsMsg) {
                $message = json_decode($sqsMsg['Body'], true);
        
                if ($message === null || $this->_deleteAfterReceive) {
                    // queue message to be deleted
                    $toDelete[] = array(
                        'Id' => $sqsMsg['MessageId'].'-DECODE_ERROR',
                        'ReceiptHandle' => $sqsMsg['ReceiptHandle'],
                    );
                    if ($message === null) {
                        $this->_logger->error('Failed to decode JSON message from SQS: ' . $sqsMsg['Body']);
                    }
                } else {
                    $message['_QueueURL'] = $queueURL;
                    $message['_MessageId'] = $sqsMsg['MessageId'];
                    $message['_ReceiptHandle'] = $sqsMsg['ReceiptHandle'];
                    $messages []= $message;
                }
            }
        }
        $this->_logger->debug(empty($messages) ? 'No message left in SQS to receive.' : ('Received ' . count($messages) . ' message(s) from SQS.'));
        
        // remove messages which cannot be decoded
        if (!empty($toDelete)) {
            try {
                $this->_sqsClient->deleteMessageBatch(array(
                    'QueueUrl' => $queueURL,
                    'Entries' => $toDelete,
                ));
            }
            catch (\Exception $ex) {
                $this->_logger->warning('Failed to delete messages after receive '. $ex->getMessage());
            }
        }
        
        return $messages;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Icare\NetSuite\Helper\Client\NetSuiteQueueInterface::purge()
     */
    public function purge($messages) 
    {
        $toDelete = array();
        $failed = [];
        // aggregate message to delete per queue
        $map = [];
        foreach ($messages as $message) {
            $toDelete[$message['_QueueURL']][] = array(
                'Id' => $message['_MessageId'],
                'ReceiptHandle' => $message['_ReceiptHandle'],
            );
            $map[$message['_MessageId']] = $message;
        }
        
        foreach ($toDelete as $queueURL => $entries) {
            try {
                $this->_sqsClient->deleteMessageBatch(array(
                    'QueueUrl' => $queueURL,
                    'Entries' => $entries,
                ));
                $this->_logger->debug(sprintf('Deleted %u message(s) from %s', \count($entries), $queueURL));
            }
            catch (\Exception $ex) {
                $this->_logger->warning('Failed to delete messages from ' . $queueURL . ' ' . $ex->getMessage());
                foreach ($entries as $entry) {
                    $failed = $map[$entry['Id']];
                }
            }
        }
        
        return $failed;
    }
}

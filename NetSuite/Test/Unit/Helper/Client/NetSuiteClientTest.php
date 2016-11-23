<?php 

namespace Icare\NetSuite\Test\Unit\Helper\Client;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Date;

/**
 * Unit Test for NetSuite testing
 * @author Nam Pham
 *
 */
class NetSuiteClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Icare\NetSuite\Helper\Client\NetSuiteClient $netsuiteClient */
    private $netsuiteClient;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $netsuiteConfiguration;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $date;
    
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        
        /** @var \Icare\NetSuite\Helper\Entity\NetSuiteConfiguration $netsuiteConfiguration */
        $this->netsuiteConfiguration = $this->getMock('Icare\NetSuite\Helper\Entity\NetSuiteConfiguration', [], [], '', false);
        $csv = new \Magento\Framework\File\Csv($objectManager->getObject('Magento\Framework\Filesystem\Driver\File'));
        $config = $csv->getData(__DIR__ . '/NetSuiteConfig.csv');
        foreach ($config as $line) {
            if (count($line) !== 2) {
                continue;
            }
            $this->netsuiteConfiguration->expects($this->any())->method('get'.$line[0])->willReturn($line[1]);
        }
        
        /** @var \Magento\Framework\Oauth\Helper\Oauth $oauthHelper */
        $oauthHelper = $objectManager->getObject('Magento\Framework\Oauth\Helper\Oauth', [
            'mathRandom' => $objectManager->getObject('Magento\Framework\Math\Random'),
        ]);
        
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $date */
        $this->date = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        $this->date->expects($this->any())->method('timestamp')->willReturn(time());
        
        $this->netsuiteClient = $objectManager->getObject('Icare\NetSuite\Helper\Client\NetSuiteClient', [
            'netsuiteConf' => $this->netsuiteConfiguration,
            'oauthHelper' => $oauthHelper,
            'date' => $this->date,
            'httpUtility' => null,
        ]);
    }
    
    /**
     * authentication test
     */
    public function testAuthentication()
    {
        $this->netsuiteClient->postToNetSuite('HelloApi', array(
            'client' => 'Magento UnitTest',
            'message' => 'Message sent at '.(new \DateTime())->format('medium'),
        ));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 25/10/2016
 * Time: 11:29
 */

namespace Icare\Test\testsuite\Deposit\Api;

class DepositApiTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const RESOURCE_PATH = '/V1/icare/deposit/search_by_user';

    const USER_ID = 8;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetListByUser()
    {
        /** @var \Icare\Deposit\Model\Deposit $deposit */
        $deposit = $this->objectManager->create('Icare\Deposit\Model\Deposit');
        $data = $deposit->loadByUser(self::USER_ID);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, ['user_id' => self::USER_ID]);
        $this->assertEquals(count($data), count($result[0]['items']));
    }
}
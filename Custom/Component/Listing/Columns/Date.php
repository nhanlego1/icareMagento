<?php
/**
 * Created by PhpStorm.
 * User: nhan_nguyen
 * Date: 13/10/2016
 * Time: 16:43
 */

namespace Icare\Custom\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Date
 */
class Date extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var StoreManagerInterface
     */
    protected $store;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param StoreManagerInterface $store
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        StoreManagerInterface $store,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->store = $store;
        $this->_authSession = $authSession;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $user = $this->_authSession->getUser();
            $storeId = $user->getStoreId();
            $store = $this->store->getStore($storeId);
            $storeCode = $store->getCode();
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $date = $this->timezone->date(new \DateTime($item[$this->getData('name')]));
                    $timezone = $this->timezone->getConfigTimezone(
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeCode
                    );
                    $date = $this->timezone->formatDateTime(
                        $date,
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM,
                        null,
                        $timezone
                    );
                    $item[$this->getData('name')] = $date;
                }
            }
        }

        return $dataSource;
    }
}

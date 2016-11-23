<?php

namespace Icare\Sales\Block\Adminhtml\Report\Helper\Form;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Sales agent field helper
 * 
 * @author Nam Pham
 *
 */
class SalesAgent extends \Magento\Framework\Data\Form\Element\Text
{
    protected $_jsonEncoder;
    
    protected $_userCollection;
    
    protected $_userOptions;
    
    /**
     * 
     * @param \Magento\User\Model\ResourceModel\User\Collection $userCollection
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\Collection $userCollection,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->_userCollection = $userCollection;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }
    
    /**
     * Attach category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $htmlId = $this->getHtmlId();
        $selectorOptions = $this->_jsonEncoder->encode($this->_getSelectorOptions());
        $suggestPlaceholder = __('start typing to search user');
        $value = $this->getValue();
        $return = <<<HTML
    <script>
        require(["jquery", "mage/mage"], function($){
            var element = $('#{$htmlId}');
            element.attr('placeholder', '$suggestPlaceholder').mage('suggest', {$selectorOptions}).closest('.admin__field-control').addClass('admin__scope-old');
            window.setTimeout(function() {
                var value = element.val();
                element.val('');
                element.val(value);
                $('input[name="sales_agent"]').val('$value');
            }, 1000);   // because default delay is 500 at https://github.com/magento/magento2/blob/develop/lib/web/mage/backend/suggest.js#L48
        });
    </script>
HTML;
        return $return;
    }
    
    /**
     * Get selector options
     *
     * @return array
     */
    protected function _getSelectorOptions()
    {
        //@todo declare a URL to search for admin_user
        if ($this->_userOptions == NULL) {
            $options = [];
            
            /** var \Magento\User\Model\ResourceModel\User $user **/
            foreach ($this->_userCollection as $user) {
                $options[] = [
                  'id' => $user->getId(),
                  'label' => __("%1 %2 - %3 - Tel: %4", $user->getFirstName(), $user->getLastName(), $user->getEmail(), $user->getTelephone())->__toString(),
                ];
            }
            $this->_userOptions = $options;
        }
        
        return [
            'source' => $this->_userOptions,
            'className' => 'sales-agent-select'
        ];
    }
    
    /**
     * Get the HTML
     *
     * @return mixed
     */
    public function getHtml()
    {
        $this->addClass('icare_sale_agent');
        return parent::getHtml();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Magento\Framework\Data\Form\Element\AbstractElement::getEscapedValue()
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);
        
        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        
        if ($index == null) {
            foreach ($this->_userOptions as $option) {
                if ($option['id'] == $value) {
                    $value = $option['label'];
                    break;
                }
            }
        }
        
        return $this->_escape($value); 
    }
}
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiElement',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Magento_Ui/js/grid/provider'
], function ($, _, utils, Element, alert, $t, provider) {
    'use strict';

    return provider.extend({
	    initialize: function () {
	    	var self = this;
	    	$('select[data-id="select_location"]').live('change',function(){
	            	self.params.filters.icare_address_id = this.value;
	            	self.reload();
	        });
	        return this._super();
	    },
    	
    });
});

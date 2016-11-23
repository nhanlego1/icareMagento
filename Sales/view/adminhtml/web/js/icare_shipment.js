/*
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by baonguyen on 10/5/16.
 */
require([
    'jquery',
    'jquery/ui',
    'jquery/jquery-storageapi',
    'mage/adminhtml/events'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @returns {Object}
     */
    function getForm(url) {
        var shipment_ids = $('div.admin__data-grid-wrap input:checked[id^="check"]').map(function() {
            return this.value;
        }).get().join();
        var form =  $('<form>', {
            'action': url,
            'method': 'POST'
        });

        form.append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
        form.append($('<input>', {
            'name':'shipment_ids',
            'value': shipment_ids,
            'type':'hidden'
        }));
        return form;
    }

    function getFormWithLocation(url, location_id) {
        var form =  $('<form>', {
            'action': url,
            'method': 'POST'
        });

        form.append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
        form.append($('<input>', {
            'name':'icare_address_id',
            'value': location_id,
            'type':'hidden'
        }));
        return form;
    }

    function triggerButtons() {
        if($('.data-grid-checkbox-cell-inner input:checked').length ==0){
            $('#print_button').attr('disabled',true);
            $('#ship_button').attr('disabled',true);
        }
        else{
            $('#print_button').removeAttr('disabled',true);
            $('#ship_button').removeAttr('disabled',true);
        }
    }

    $('#ship_button').click(function () {
        var url = $('#ship_button').data('url');
        getForm(url).appendTo('body').submit();
        return false;
    });

    $('#print_button').click(function () {
        var url = $('#print_button').data('url');
        getForm(url).appendTo('body').submit();
        return false;
    });
    
    triggerButtons();
    $(document).on('change','.data-grid-checkbox-cell-inner input[type="checkbox"],#mass-select-checkbox',function(e){
        triggerButtons();
    });


});
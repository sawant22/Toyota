/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
 var filterFormSubmit,exportdealerrank; 
 require([
     'jquery',
     'mage/backend/validation',
     'prototype'
 ], function($){
        jQuery('#dealerrank_filter_form').mage('validation', {errorClass: 'mage-error'});
        filterFormSubmit = function () {
            var filters = $$('#dealerrank_filter_form input', '#dealerrank_filter_form select'),
            elements = [];
            var actionUrl = jQuery('#dealerrank_filter_form [name="url"]').val();
            for (var i in filters) {
                if (filters[i].value && filters[i].value.length && !filters[i].disabled) {
                    elements.push(filters[i]);
                }
            }
            if (jQuery('#dealerrank_filter_form').valid()) {
                    setLocation(actionUrl+'filter/'+Base64.encode(Form.serializeElements(elements))+'/'
                );
            }
        }
        exportdealerrank = function () {
            setLocation(jQuery("#dealerrank_export").val());
        }
 });
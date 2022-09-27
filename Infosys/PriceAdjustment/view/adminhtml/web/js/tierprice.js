/**
 * @package   Infosys/PriceAdjustment
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
require(
    [
        'jquery',
        'mage/translate',
    ],
    function ($) {
    $(document).ready(function() {
        setTimeout(function () {
            ($)(".admin__dynamic-rows th").each(function() {
            var id = ($)(this).attr("data-repeat-index");
            if(id == 1 ) {
                ($)(this).find('span').html("Adjustment Type </br> Cost+Percentage: <span style='font-weight:normal;'> This rule will applied on COST and increased as per mentioned percentage up to the List/MSRP price.</span> </br>List-Percentage: <span style='font-weight:normal;'> This rule will apply on List and will be decreased by mentioned percentage.</span>");
                }
            if(id == 4 ) {
                ($)(this).find('span').html("Percentage </br> <strong style='font-weight:normal;'> Percentage should be in number format eg: 20</strong>");
                }
            });
            $(".admin__control-table th").css("vertical-align", "top");
            }, 5000);
        });
    }
);
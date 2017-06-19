{**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    guisardo
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*}
<script type="text/template" id="shippingCalculator">
<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
<div>
    <div style="width: 220px; margin: 0 auto;">
        <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/shipper.png" style="width: 30px; float: left; margin-left: 7px;">
        <ul style="text-align: right; margin-right: 7px;">
            <li style="margin-right: -2px;">Costo total de envío a:
                <input type="number" min="1000" max="9999" style="width: 40px; text-align: center;" placeholder="CP" title="Código Postal"></input>
            </li>
        </ul>
    </div>
</div>
</script>
<script type="text/template" id="shippingCPA">
<li>
Consultá tu Código Postal <u><a target="_blank" href="http://wxw.oca.com.ar/Contenidos_Dinamicos/cpa.asp" onclick="window.open(this.href,'','toolbar=0,status=0,width=380,height=286');return false;">acá</a></u>
</li>
</script>
<script type="text/template" id="shippingOption">
<li style="text-align: right;">[[name]]: [[list_cost]]</li>
</script>
<script type="text/javascript">
$(document).ready(function(){
    var shippingOptionsTpl = $('#shippingCalculator').text();
    var shippingCPATpl = $('#shippingCPA').text();
    var shippingOptionTpl = $('#shippingOption').text();
    var $shippingOptions = false;
    var updateShippingCost = function(_shippingOptions) {
        var _oldCP = '';
        if ($shippingOptions) {
            _oldCP = $shippingOptions.find('input').val();
            $shippingOptions.remove();
        }
        $shippingOptions = $(shippingOptionsTpl);
        $('.box-info-product').append($shippingOptions);
        var $input = $shippingOptions.find('input');
        if (!('ontouchstart' in window)) {
            $input.attr('type', 'text');
            $input.attr('pattern', '\\d{4}');
        }
        var $optionList = $shippingOptions.find('ul');
        if (_shippingOptions.destination) {
            $input.val(_shippingOptions.destination.zip_code);
            for (var i = 0; i < _shippingOptions.options.length; i++) {
                $optionList.append(shippingOptionTpl.replace('[[name]]', _shippingOptions.options[i].name).replace('[[list_cost]]', _shippingOptions.options[i].list_cost));
            }
            if (_oldCP != '') {
                $input.focus();
            }
        } else {
            $input.val(_oldCP);
            $optionList.append(shippingCPATpl);
        }
        var _keyUpTimeout = false;
        $input.on('click', function () {
            this.setSelectionRange(0, this.value.length);
        });
        $input.on('keypress', function(e) {
            if(e.keyCode == 13)
            {
                event.preventDefault();
                return false;
            }
        });
        $input.on('change keyup', function() {
            clearTimeout(_keyUpTimeout);
            if (this.value == '' || (1000 <= this.value && this.value <= 9999)) {
                _keyUpTimeout = setTimeout(delayedCheck, 1000);
            }
        });
    }
    var delayedCheck = function() {
        var _data = {};
        if ($shippingOptions) {
            _data.z = $shippingOptions.find('input').val();
        }
        $.getJSON('/modules/mercadopago/shipping.php', _data).success(function(resp) {
            updateShippingCost(resp);
        });
    }
    delayedCheck();
});
</script>
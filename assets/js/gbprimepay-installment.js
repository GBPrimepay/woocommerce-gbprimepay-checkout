jQuery(function($) {
    'use strict';



        const NAME = 'ToSelect';
        const VERSION = '1.3.1';

        var ToSelect = function (element, options) {
            this.source = $(element)
            this.selects = {
                bankcode: this.getParentSelect(),
                term: this.getChildSelect()
            }
            this.options = options
            this.name = NAME
            this.version = VERSION

            var that = this;

            var e = $.Event('toselect.init', { relatedTarget: this.source});
            this.init($.proxy(function () {
                setTimeout(function () {
                    that.source.trigger(e)
                }, 0)
            }, this))
        }

        ToSelect.DEFAULTS = {

        };

        ToSelect.prototype = {

            constructor: ToSelect,

            init: function (callback) {
                this.hideSource()
                this.bindParentSelect()
                this.bindChildSelect()
                this.selects.bankcode.on('change', $.proxy(function () {
                    this.bankcodeOnChange()
                }, this))
                this.selects.term.on('change', $.proxy(function () {
                    this.termOnChange()
                }, this))

                callback && callback()
            },

            hideSource: function () {
                this.source.hide()
            },

            getParentSelect: function () {
                var bankcode, $s = this.source
                bankcode = $s.attr('data-bankcode')
                return $(bankcode)
            },
            getChildSelect: function () {
                var term, $s = this.source
                term = $s.attr('data-term')
                return $(term)
            },

            bindParentSelect: function () {
                var bankcode = this.selects.bankcode, $s = this.source, $m = this.getParentSelectMetadata, $c = this.createSelectOption

                var optgroups = $s.children('optgroup')
                if (!optgroups) {
                    return false;
                }

                $.each(optgroups, function () {
                    var metadata = $m($(this))
                    if (!metadata) return
                    var option = $c(metadata)
                    option.data('toselect.optgroup', $(this));
                    bankcode.append(option)
                })

                this.source.trigger($.Event('toselect.bankcode.updated', {
                    relatedTarget: this.selects.bankcode
                }))
            },
            getParentSelectMetadata: function (optgroup) {
                if (!optgroup) {
                    return false
                }
                if (!optgroup.attr('label')) {
                    return false
                }

                var label = optgroup.attr('label');
                var isSelected = optgroup.find('option:selected').length > 0

                if (/^TextValue\[.*\]+$/.test(label)) {

                    var data = eval(label.replace('TextValue', ''))
                    return {
                        text: data[0],
                        value: data.length === 2 ? data[1] : data[0],
                        selected: isSelected
                    }
                }

                return {
                    text: label,
                    value: label,
                    selected: isSelected
                }
            },

            bindChildSelect: function () {
                var bankcode = this.selects.bankcode, term = this.selects.term, $m = this.getChildSelectMetadata,
                    $c = this.createSelectOption
                var optgroup = bankcode.children('option:selected');
                if (!optgroup) {
                    return
                }

                var data = $(optgroup).data('toselect.optgroup')
                if (!data) {
                    return
                }
                var options = data.find('option');

                $.each(options, function () {
                    var metadata = $m($(this))
                    if (!metadata) return

                    var option = $c(metadata)
                    term.append(option)
                })

                this.source.trigger($.Event('toselect.term.updated', {
                    relatedTarget: this.selects.term
                }))
            },
            getChildSelectMetadata: function (option) {
                if (!option) {
                    return false
                }

                return {
                    text: option.text(),
                    value: option.val(),
                    disabled: option.is(':disabled'),
                    selected: option.is(':selected')
                }
            },

            createSelectOption: function (metadata) {
                if (metadata === null) {
                    return false;
                }

                var option = document.createElement('option')
                option.innerText = metadata.text
                option.setAttribute('value', metadata.value)

                if (metadata.selected) option.setAttribute('selected', 'selected')
                if (metadata.disabled) option.setAttribute('disabled', 'disabled')
                return $(option);
            },

            bankcodeOnChange: function () {


                var selected = this.selects.bankcode.find('option:selected')

                this.source.trigger($.Event('toselect.bankcode.changed', {
                    relatedTarget: this.selects.bankcode,
                    text: selected.text(),
                    value: selected.val()
                }))

                this.selects.term.find('option:not([data-keep="true"])').remove()
                this.bindChildSelect();
                this.termOnChange();
            },
            termOnChange: function () {



            var get_amount = $("div#gbprimepay-payment-installment-data").data().amount;
            var get_bankimg = $("div#gbprimepay-payment-installment-data").data().bankimg;

            var varBank = $('select[id=gbprimepay_installment-bankcode] option').filter(':selected').val();
            var varSelected = $('select[id=gbprimepay_installment-term] option').filter(':selected').val();
            var varDivision = (parseFloat(get_amount) / varSelected);
            var isDecimal = (varDivision - Math.floor(varDivision)) !== 0;
            var varDivisionFix;
            if (isDecimal){
              varDivisionFix = varDivision.toFixed(2);
            }else{
              varDivisionFix = varDivision;
            };
            var varAamount = (parseFloat(get_amount));
            var isDecimal = (varAamount - Math.floor(varAamount)) !== 0;
            var varAamountFix;
            if (isDecimal){
              varAamountFix = varAamount.toFixed(2);
            }else{
              varAamountFix = varAamount;
            };
            // var infotxt = varDivisionFix+' THB/month in '+varSelected+' payments';
var infotxt = '';
infotxt += '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0 0 0.625em 0;">';

infotxt += '<div style="width:auto; height:5.625emem;display: flex;flex-direction: column;align-items: center;justify-content: center; "><img style="margin: 0px;max-height: 5.000em;" src="';
infotxt += ''+get_bankimg+'issuerBank'+varBank+'.png';
infotxt += '" alt=""></div>';
infotxt += '</td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="2px;" style="border: none !important;padding: 0;"></td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';

infotxt += '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;margin:0;padding: 0;">';
infotxt += '<td colspan="2" rowspan="2" cellpadding="0" cellspacing="0" height="100%" style="border: none !important;width:70%;border: none !important;text-align: right; vertical-align: middle;padding: 0;">';
infotxt += '<h1 style="border: none !important;width:100%;margin:0;padding: 0 5px 0 0;line-height: 1;"><strong>'+currency_format(varDivisionFix)+'</strong></h1>';
infotxt += '</td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="50%" width="100%" style="border: none !important;text-align: left; vertical-align: bottom;padding: 0;"><span style="border: none !important;margin:0;padding:0;line-height: 1 !important;"><strong>x'+varSelected+'</strong></span></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td cellpadding="0" cellspacing="0" height="50%" width="100%" style="border: none !important;width:25%;min-width170px;text-align: left; vertical-align: top;padding: 0 0 5px 0;"><span style="border: none !important;margin:0;padding:0;line-height: 1 !important;">payments</span></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 10px 0 0 0;"><span style="border: none !important;margin:0;padding:0 15px 0 0;line-height: 1 !important;">Total Amount </span><span style="border: none !important;margin:0;padding:0;line-height: 1 !important;"><strong>'+currency_format(varAamountFix)+'</strong></span></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';

infotxt += '</td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';


            if(varSelected){
              $('#gbprimepay_installment-info').html(infotxt);
            }else{
              $('#gbprimepay_installment-info').html('');
            }





                var selected = this.selects.term.find('option:selected')
                this.source.val(selected.val())
                this.source.trigger($.Event('toselect.term.changed', {
                    relatedTarget: this.selects.term,
                    text: selected.text(),
                    value: selected.val()
                }))



            }
        }

        // ToSelect Plugin Definition
        // ================================

        function Plugin(option) {
            return this.each(function () {
                var $this = $(this)
                var data = $this.data('toselect')
                var options = $.extend({}, ToSelect.DEFAULTS, $this.data(), typeof option === 'object' && option)

                if (!data) $this.data('toselect', (data = new ToSelect(this, options)))
                if (typeof option === 'string') data[option]()
            })
        }

        var old = $.fn.toSelect

        $.fn.toSelect = Plugin
        $.fn.toSelect.Constructor = ToSelect


        // ToSelect No Conflict
        // ====================

        $.fn.toSelect.noConflict = function () {
            $.fn.toSelect = old
            return this
        }

        // ToSelect DATA-API
        // =================


        window.addEventListener('load', function(){
            $('[data-role="toselect"]').each(function () {
                var $toSelect = $(this)
                Plugin.call($toSelect, $toSelect.data())
            })
        });


        function currency_format(num) {
          return '' + num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        }
    function genIssuers(){
      setTimeout(function(){
        $('select[id=gbprimepay_installment-CCInstallmentToSelect]').toSelect();
      }, 1000);
    }

    var bankcode = $('#gbprimepay_installment-publicKey').val();
    var term = $('#gbprimepay_installment-term').val();
    // onject to handle GB Prime Pay
    var se_gbprimepay_installment_form = {
        init: function() {
            if ($('form.woocommerce-checkout').length) {
                this.form = $('form.woocommerce-checkout');
            }
            $('form.woocommerce-checkout').on('submit', this.onSubmit);
        },

        isGbprimepayDefault: function() {

setTimeout(function(){
  genIssuers();
}, 700);

        },

        block: function() {
            se_gbprimepay_installment_form.form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        isGbprimepayChosen: function() {
            return $( '#payment_method_gbprimepay_installment' ).is( ':checked' );
        },

        unblock: function() {
            se_gbprimepay_installment_form.form.unblock();
        },

        onSubmit: function(e) {
            if (se_gbprimepay_installment_form.isGbprimepayChosen()) {
                e.preventDefault();
                // se_gbprimepay_installment_form.block(); // block it !!!!!!

                var bankcode = $('#gbprimepay_installment-publicKey').val().replace(/ /g,'');
                var term = $('#gbprimepay_installment-term').val();

            }
        }
    };





    window.addEventListener('load', function(){
      se_gbprimepay_installment_form.isGbprimepayDefault();
    });

});

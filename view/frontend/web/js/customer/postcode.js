
define([
    'jquery',
    'Magento_Checkout/js/model/postcode-validator',
], function ($, postcodeValidator) {

    $.widget('ridders.postcode', {
        options: {
            template: '#postcode-check-template',
            postcodeFields: {
                fieldset: 'fieldset.postcode-check',
                postcode: 'input[name="ridders_postcode_postcode"]',
                houseNumber: 'input[name="ridders_postcode_housenumber"]',
                additional: 'input[name="ridders_postcode_addition"]'
            },
            addressFields: {
                street: 'fieldset.street div.field.street',
                houseNumber: 'fieldset.street div.field.housenumber',
                additional: 'fieldset.street div.field.additional',
                postcode: 'div.field.zip',
            },
            country: '',
            checkDelay: 750
        },

        _create: function () {
            this._setTemplate();
            this._setCountry();
            this._initObservers();
        },

        _initObservers: function () {
            this._observeCountry();
            this._observeFields();
            this._hideFields(this.country);
        },

        _setTemplate: function () {
            var template = $(this.options.template).html();
            $('fieldset.street').before(template);
        },

        _setCountry: function() {
            this.country = $('select[name="country_id"] option:selected').val();
        },

        _hideFields: function (country) {
            if (country == 'NL') {
                $(this.options.postcodeFields.fieldset).show();
                $(this.options.addressFields.houseNumber).hide();
                $(this.options.addressFields.additional).hide();
                $(this.options.addressFields.postcode).hide();
            } else {
                $(this.options.postcodeFields.fieldset).hide();
                $(this.options.addressFields.houseNumber).show();
                $(this.options.addressFields.additional).show();
                $(this.options.addressFields.postcode).show();
            }
        },

        _observeCountry: function () {
            var self = this;
            $('select[name="country_id"]').on('change', function() {
                self.country = $('select[name="country_id"] option:selected').val();

                self._hideFields(self.country);
            });
        },

        _observeFields: function () {
            var self = this;
            var postcodeInput = this.options.postcodeFields.postcode;
            var housenumberInput = this.options.postcodeFields.houseNumber;
            var additionalInput = this.options.postcodeFields.additional;

            $(`${postcodeInput}, ${housenumberInput}`).on('input', function() {

                clearTimeout(this.postcodeCheckTimeout);
                this.postcodeCheckTimeout = setTimeout(function () {
                    if ($(`${postcodeInput}`).val() && $(`${housenumberInput}`).val()) {
                        var validationResult = postcodeValidator.validate($(`${postcodeInput}`).val(), 'NL', self.options.postCodes);
                        var houseIntCheck = !isNaN($(`${housenumberInput}`).val());

                        if (validationResult && houseIntCheck) {
                            clearTimeout(this.postcodeCheck);
                            this.postcodeCheck = setTimeout(function () {
                                $("body").loader("show");
                                self._checkPostcode($(`${postcodeInput}`).val(), $(`${housenumberInput}`).val());
                            }, 500);
                        }
                    }
                }, 750);
            });

            $(`${additionalInput}`).on('input', function(){
                $('#street_3').val($(this).val());
            });
        },

        _checkPostcode: function(postcode, houseNumber) {
            let settings = {
                "url": "/rest/V1/address-api/lookup",
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Content-Type": "application/json"
                },
                "data": JSON.stringify({
                    "address": {
                        "postcode": postcode,
                        "house_number": parseInt(houseNumber),
                        "country": "NL" // TODO: support multiple countries
                    }
                })
            };

            this.currentRequest = $.ajax(settings)
                .done(function (data, status, xhr) {
                    $('#street_1').val(data.street);
                    $('#street_2').val(data.house_number);

                    $('input[name="postcode"]').val(data.postcode);
                    $('input[name="city"]').val(data.city);
                })
                .always(function () {
                    $("body").loader("hide");
                });
        }
    });

    return $.ridders.postcode;
});

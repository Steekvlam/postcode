define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/postcode-validator',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'mage/validation'
], function ($, Component, ko, postcodeValidator, checkoutData, registry, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            isLoading: false,
            addressType: 'shipping',
            postcode: null,
            houseNumber: null,
            houseAddition: null,
            lastRequestResult: null,
            validationMessage: null,
            notFoundMessage: null,
            isVisible: false,
            visible: true,
            isLoader: false,
            imports: {
                observeCountry: '${ $.parentName }.country_id:value',
                observePostcodeField: '${ $.parentName }.ridders_postcode_fieldset.ridders_postcode_postcode:value',
                observeHousenumberField: '${ $.parentName }.ridders_postcode_fieldset.ridders_postcode_housenumber:value',
                observeAdditionField: '${ $.parentName }.ridders_postcode_fieldset.ridders_postcode_addition:value',
            },
            listens: {
                '${ $.provider }:${ $.customScope ? $.customScope + "." : ""}data.validate': 'validate',
            }
        },

        checkDelay: 500,
        postcodeCheckTimeout: 0,
        currentRequest: null,

        initObservable: function () {
            this._super().observe('isLoading isLoader validate isVisible validationMessage notFoundMessage');

            return this;
        },
        initConfig: function () {
            this._super();
            this.isVisible = this.resolveFieldsetVisibility();

            return this;
        },
        resolveFieldsetVisibility: function () {
            var address = this.getAddressData();
            if (!!address && address.country_id == 'NL') {
                return true;
            }

            return false;
        },
        getAddressData: function () {
            if (this.addressType === 'shipping' && typeof checkoutData.getShippingAddressFromData() !== 'undefined' && checkoutData.getShippingAddressFromData()) {
                return checkoutData.getShippingAddressFromData();
            } else if (this.addressType === 'billing' && typeof checkoutData.getBillingAddressFromData() !== 'undefined' && checkoutData.getBillingAddressFromData()) {
                return checkoutData.getBillingAddressFromData();
            } else if (this.source) {
                return this.source.get(this.customScope);
            }
        },
        observePostcodeField: function (value) {
            if (value) {
                this.postcode = value;
                this.updatePostcode();
            }
        },
        observeHousenumberField: function (value) {
            if (value) {
                this.houseNumber = value;
                this.updatePostcode();
            }
        },
        observeAdditionField: function (value) {
            if (value) {
                if (value == this.houseNumber || value == this.postcode) {
                    registry.get(this.parentName + '.ridders_postcode_fieldset.ridders_postcode_addition').set('value', '');
                }
                this.updatePostcode();
            }
        },
        observeManualOverwrite: function (value) {
            if (value == true) {
                this.isVisible(false);
                this.toggleFields(['street', 'street.1', 'street.2', 'postcode'], true);
            } else {
                this.isVisible(true);
                this.toggleFields(['street', 'street.0', 'street.1', 'street.2', 'city', 'postcode'], false);
            }
        },
        observeCountry: function (value) {
            if (value) {
                var address = this.getAddressData();
                if (address && address.country_id === 'NL') {
                    this.isVisible(true);
                    this.toggleFields(['street', 'street.1', 'street.2', 'postcode'], false);
                    $('body').addClass('checkout-postcode-check');

                    this.toggleFields([
                        'ridders_postcode_fieldset.ridders_postcode_postcode',
                        'ridders_postcode_fieldset.ridders_postcode_housenumber',
                        'ridders_postcode_fieldset.ridders_postcode_addition'
                    ], true);

                    this.updatePostcode();
                } else {
                    this.toggleFields(['street', 'street.0', 'street.1', 'street.2', 'city', 'postcode'], true);
                    this.toggleFields([
                        'ridders_postcode_fieldset.ridders_postcode_postcode',
                        'ridders_postcode_fieldset.ridders_postcode_housenumber',
                        'ridders_postcode_fieldset.ridders_postcode_addition'
                    ], false);
                    this.isVisible(false);
                    $('body').removeClass('checkout-postcode-check');
                }
            }
        },
        getValidationMessage: function () {
            var warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

            if (postcodeValidator.validatedPostCodeExample.length) {
                warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
            }
            warnMessage += $t('If you think it is correct you can ignore this notice.');

            return warnMessage;
        },
        toggleFields: function (fields, isOn) {
            var self = this;
            $.each(fields, function (key, fieldName) {
                registry.async(self.parentName + '.' + fieldName)(function () {
                    var element = registry.get(self.parentName + '.' + fieldName);
                    if (element) {
                        if (isOn) {
                            element.set('visible', true).set('labelVisible', true);
                        } else {
                            element.set('visible', false).set('labelVisible', false);
                        }
                    }
                });
            });
        },
        updatePostcode: function () {
            var address = this.getAddressData();
            var formData = this.source.get(this.customScope);
            this.validationMessage(false);
            this.notFoundMessage(false);

            if (!!address) {
                var self = this;

                clearTimeout(this.postcodeCheckTimeout);
                this.postcodeCheckTimeout = setTimeout(function () {
                    if (address.country_id == 'NL' && formData.ridders_postcode_postcode && formData.ridders_postcode_housenumber) {
                        var validationResult = postcodeValidator.validate(formData.ridders_postcode_postcode, address.country_id);
                        var houseIntCheck = !isNaN(formData.ridders_postcode_housenumber);

                        if (validationResult && houseIntCheck) {
                            self.isLoading(true);
                            setTimeout(function () {
                                self.getPostcodeInformation();
                                self.validate();
                            }, self.checkDelay);
                        } else if (!validationResult && formData.ridders_postcode_postcode) {
                            self.validationMessage(self.getValidationMessage());
                            self.apiError();
                        }
                    }
                }, self.checkDelay);
            }
        },
        getPostcodeInformation: function () {
            var formData = this.source.get(this.customScope);
            var self = this;

            if (this.currentRequest !== null) {
                this.currentRequest.abort();
                this.currentRequest = null;
            }

            let settings = {
                "url": "/rest/V1/address-api/lookup",
                "method": "POST",
                "timeout": 8000,
                "headers": {
                    "Content-Type": "application/json"
                },
                "data": JSON.stringify({
                    "address": {
                        "postcode": formData.ridders_postcode_postcode,
                        "house_number": parseInt(formData.ridders_postcode_housenumber),
                        "country": "NL" // TODO: support multiple countries
                    }
                }),
            };

            // possible http code: 200, 400, 404, 422, 500, 503
            this.currentRequest = $.ajax(settings)
                .done(function (data, status, xhr) {
                    registry.get(self.parentName + '.street.0').set('value', data.street).set('error', false);
                    registry.get(self.parentName + '.street.1').set('value', formData.ridders_postcode_housenumber).set('error', false);
                    if (formData.ridders_postcode_addition) {
                        registry.get(self.parentName + '.street.2').set('value', formData.ridders_postcode_addition).set('error', false);
                    }
                    registry.get(self.parentName + '.postcode').set('value', formData.ridders_postcode_postcode).set('error', false);
                    registry.get(self.parentName + '.city').set('value', data.city).set('error', false);

                    self.toggleFields(['street', 'street.0', 'city'], true);
                }).fail(function (xhr) {
                    switch (xhr.status) {
                        case 404:
                            self.validationMessage(xhr.responseJSON.message);
                            break;
                        case 400:
                        case 422:
                        case 500:
                            self.notFoundMessage($t('The specified combination of postal code and house number does not exist. Check the input!'));
                            break;
                        case 503:
                        default:
                            if (xhr.responseJSON) {
                                // console.log(xhr.status, xhr.responseJSON.message)
                            }
                            break;
                    }

                    self.apiError();
                }).always(function () {
                    self.isLoading(false);
                    $("input[name*='street[0]']").trigger('change');
                    $("input[name*='city']").trigger('change');
                    this.currentRequest = null;
                });
        },
        apiError: function (callback) {
            var formData = this.source.get(this.customScope);
            this.toggleFields(['street', 'street.0', 'city'], true);
            registry.get(this.parentName + '.postcode').set('value', formData.ridders_postcode_postcode).set('error', false);
            registry.get(this.parentName + '.street.1').set('value', formData.ridders_postcode_housenumber).set('error', false);
            if (formData.ridders_postcode_addition) {
                registry.get(this.parentName + '.street.2').set('value', formData.ridders_postcode_addition).set('error', false);
            }
        }
    });
});

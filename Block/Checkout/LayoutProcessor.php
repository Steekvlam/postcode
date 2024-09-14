<?php

namespace Ridders\Postcode\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Ridders\Postcode\Model\AddressService;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor extends AbstractBlock implements LayoutProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    public function process($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset'])) {
            $shippingFields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $shippingFields = array_merge($shippingFields, $this->getPostcodeFieldSet('shippingAddress', 'shipping'));

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $shippingFields;

            $jsLayout = $this->getBillingFormFields($jsLayout);
        }

        return $jsLayout;
    }

    public function getBillingFormFields($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list'])) {
            $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
                if (!isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }
                $billingFields = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingPostcodeFields = $this->getPostcodeFieldSet('billingAddress' . $paymentMethodCode, 'billing');
                $billingFields = array_merge($billingFields, $billingPostcodeFields);

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'] = $billingFields;
            }
        }
        return $jsLayout;
    }

    public function getPostcodeFieldSet($scope, $addressType)
    {
        if ($this->scopeConfig->getValue(AddressService::XML_PATH_GENERAL_IS_ACTIVE, ScopeInterface::SCOPE_STORE)) {
            return [
                'ridders_postcode_fieldset' => [
                    'component' => 'Ridders_Postcode/js/view/form/postcode',
                    'type' => 'group',
                    'config' => [
                        "customScope" => $scope,
                        "template" => 'Ridders_Postcode/form/group',
                        "additionalClasses" => "ridders_postcode_fieldset",
                        "loaderImageHref" => $this->getViewFileUrl('images/loader-1.gif'),
                    ],
                    'sortOrder' => '50',
                    'children' => $this->getPostcodeFields($scope, $addressType),
                    'provider' => 'checkoutProvider',
                    'addressType' => $addressType,
                ],
            ];
        } else {
            return [];
        }
    }

    public function getPostcodeFields($scope, $addressType)
    {
        return [
            'ridders_postcode_postcode' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    "customScope" => $scope,
                    "template" => 'ui/form/field',
                    "elementTmpl" => 'Ridders_Postcode/form/element/input',
                ],
                'provider' => 'checkoutProvider',
                'dataScope' => $scope . '.ridders_postcode_postcode',
                'label' => __('ZIP Code'),
                'sortOrder' => '51',
                'validation' => [
                    'required-entry' => true,
                    'pattern' => '^[1-9][0-9]{3} ?(?!sa|sd|ss)[a-zA-Z]{2}$',
                ],
            ],
            'ridders_postcode_housenumber' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    "customScope" => $scope,
                    "template" => 'ui/form/field',
                    "elementTmpl" => 'Ridders_Postcode/form/element/input',
                ],
                'provider' => 'checkoutProvider',
                'dataScope' => $scope . '.ridders_postcode_housenumber',
                'label' => __('Housenumber'),
                'sortOrder' => '52',
                'validation' => [
                    'required-entry' => true,
                ],
            ],
            'ridders_postcode_addition' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    "customScope" => $scope,
                    "template" => 'ui/form/field',
                    "elementTmpl" => 'Ridders_Postcode/form/element/addition',
                ],
                'provider' => 'checkoutProvider',
                'dataScope' => $scope . '.ridders_postcode_addition',
                'label' => __('Additional'),
                'sortOrder' => '53',
                'validation' => [
                    'required-entry' => false,
                ],
                'options' => [],
                'visible' => true,
            ],
        ];
    }
}

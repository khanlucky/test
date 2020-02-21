var config = {
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default': 'Khan_Curotec/js/model/shipping-save-processor/default'
        },
         '*': {
            configurable: 'Khan_Curotec/js/configurable'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Khan_Curotec/js/mixin/shipping-mixin': true
            }
        }
    }
};
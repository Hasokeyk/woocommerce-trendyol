jQuery(function ($){

    //TRENDYOL SELECT2
    $('.trendyol_select_2').select2();
    //TRENDYOL SELECT2

    $('.trendyol_select_2_search').select2({
        minimumInputLength: 3,
        ajax              : {
            url           : ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType      : 'json',
            type          : 'post',
            delay         : 250, // delay in ms while typing when to perform a AJAX search
            data          : function (params){
                return {
                    q     : params.term, // search query
                    action: 'wc_trendyol_search_brand' // AJAX action for admin-ajax.php
                };
            },
            processResults: function (data){
                var options = [];
                if (data){

                    // data is the array of arrays, and each of them contains ID and the Label of the option
                    $.each(data, function (index, text){ // do not forget that "index" is just auto incremented value
                        options.push({
                            id  : text[0],
                            text: text[1]
                        });
                    });

                }
                return {
                    results: options
                };
            },
            cache         : true
        },
    });

    //CALC TRENDYOL COMMISSION
    function calc_trendyol_cat_commission(){

        var wc_trendyol_price          = $('.wc_trendyol_product_sales_price_input').val();
        var wc_trendyol_cat_commission = $('.wc_trendyol_cat_commission_input').data('trendyol_cat_commission');

        if (wc_trendyol_cat_commission > 0){
            $('.wc_trendyol_cat_commission_input').val(((wc_trendyol_price / 100) * wc_trendyol_cat_commission).toFixed(2));
        }
    }

    $('.wc_trendyol_product_sales_price_input').on('keyup', function (){
        calc_trendyol_cat_commission();
    })

    if ($('.wc_trendyol_settings_tab').length > 0){
        calc_trendyol_cat_commission();
    }
    //CALC TRENDYOL COMMISSION

    //SEARCH BRAND

    //SEARCH BRAND

    //AJAX ADD PRODUCT

    //AJAX ADD PRODUCT
});
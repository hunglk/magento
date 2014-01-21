/**
 * Created by HungLK on 1/21/14.
 */
jQuery.noConflict();

jQuery(document).ready(function(){
    jQuery('#slider5').tinycarousel({ axis: 'y'});
});

jQuery(document).ready(function(){
    jQuery(".btn-up").click(function(){
        var cart_id = '#' + jQuery(this).attr('id').replace('cart', '');
        var qty = jQuery(cart_id).val();
        qty = parseInt(qty)  +1;
        jQuery(cart_id).attr("value",qty);
    });
    jQuery(".btn-down").click(function(){
        var cart_id = '#' + jQuery(this).attr('id').replace('cart', '');
        var qty = jQuery(cart_id).val();
        qty = parseInt(qty)  -1;
        if (qty < 0) {
            return false;
        }
        jQuery(cart_id).attr("value",qty);
    });
});
//JS
var mergado_pack = {
    'add' : function(product_id, quantity) {
        $.ajax({
			url: 'index.php?route=checkout/cart/mergado_add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'html',
			success: function(html) {
                $('body').append(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
    },
    'pd_add' : function() { //product detail
        $.ajax({
			url: 'index.php?route=checkout/cart/mergado_add',
			type: 'post',
			data: $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea'),
			dataType: 'html',
			success: function(html) {
                $('body').append(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
    },
    'parseFuncArgs' : function(func) {
        var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/)|\')/mg;
        var ARGUMENT_NAMES = /([^\s,]+)/g;

        var fnStr = func.toString().replace(STRIP_COMMENTS, '');
        var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
        if(result === null)
            result = [];
        return result;
    }

}

$(document).ready(function(){

    $("body button").each(function() {
        var attr = $(this).attr('onclick');
        if(typeof attr !== typeof undefined && attr !== false && attr.includes('cart.')) { //hp, categories, search
            $(this).on('click', function(){
                mergado_pack.add(mergado_pack.parseFuncArgs(attr))
            });
        }else if($('#button-cart').length > 0) { //product detail
            $product_id = $('input[name="product_id"]').val();
            $(this).on('click', function(){
                mergado_pack.pd_add();
            });
        }
    });

    mergadoSetHeurekaWidget();

    $(window).on('resize', function() {
        mergadoSetHeurekaWidget();
    })
});

function mergadoSetHeurekaWidget() {
    if (typeof mergado_heureka_widget_min_screen_width !== 'undefined') {
        var screen = $(this);

        var result = mergadoIsMobile();
        //console.log('is mobile: ' + result.toString());
        if(mergadoIsMobile()) {
            mergadoShowHeurekaWidget();
        } else if(screen.width() > mergado_heureka_widget_min_screen_width || mergado_heureka_widget_min_screen_width ==0) {
            mergadoShowHeurekaWidget();
        } else {
            mergadoHideHeurekaWidget();
        }
    }
}

function mergadoHideHeurekaWidget() {
    if($('#heurekaTableft').length > 0) {
        $('#heurekaTableft').parent().css('display','none');
    }
    if($('#heurekaTabright').length > 0) {
        $('#heurekaTabright').parent().css('display','none');
    }
}

function mergadoShowHeurekaWidget() {
    if($('#heurekaTableft').length > 0) {
        $('#heurekaTableft').parent().css('display','block');
    }
    if($('#heurekaTabright').length > 0) {
        $('#heurekaTabright').parent().css('display','block');
    }
}

function mergadoIsMobile() {

    var isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };

    var showWidgetOnMobile = typeof mergado_heureka_show_on_mobile !== 'undefined' && mergado_heureka_show_on_mobile == 1 ? 1 : 0;

    return isMobile.any() & showWidgetOnMobile;
}

function mergadoHeurekaSetProcessingData() {

    $.ajax({
        url: 'index.php?route=checkout/confirm/mergado-heureka-set-processing-data',
        method: 'POST',
        data: { 'heureka_disable' : $('#mergado_heureka_switch').prop('checked') ? 1 : 0 },
        success: function(response) {
            //console.log(response);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

function mergadoZboziSetProcessingData() {

    $.ajax({
        url: 'index.php?route=checkout/confirm/mergado-zbozi-set-processing-data',
        method: 'POST',
        data: { 'zbozi_disable' : $('#mergado_zbozi_switch').prop('checked') ? 1 : 0 },
        success: function(response) {
            //console.log(response);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}


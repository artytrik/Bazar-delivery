$(document).ready(function() {
    ocfilter.productViewChange();
    ocfilter.paginationChangeAction();
});

var ocfilter = {
    /* Filter action */
    'filter' : function(filter_url) {
        $.ajax({
            url         : filter_url,
            type        : 'get',
            beforeSend  : function () {
                $('.layered-navigation-block').show();
                $('.ajax-loader').show();
            },
            success     : function(json) {
                $('.filter-url').val(json['filter_action']);
                $('.price-url').val(json['price_action']);
                $('.custom-category').html(json['result_html']);
                $('.layered').html(json['layered_html']);
                ocfilter.paginationChangeAction();
                ocfilter.productViewChange();
                $('.layered-navigation-block').hide();
                $('.ajax-loader').hide();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        });

    },

    /* Use again and update ajaxComplete from common.js */
    'productViewChange' : function() {
        // Product List
        $('#list-view').click(function() {
			$('.custom-products').addClass('custom-products-row');
            $('#content .product-grid > .clearfix').remove();

            $('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
            $('#grid-view').removeClass('active');
            $('#list-view').addClass('active');
			$('#content .product-list .caption').addClass('col-md-8 col-sm-8 col-sms-12');
			$('#content .product-list .image').addClass('col-md-4 col-sm-4 col-sms-12');
            localStorage.setItem('display', 'list');
            localStorage.setItem('type', 'none');
        });
		$('.btn-list').click(function() {
			$('.custom-products').addClass('custom-products-row');
			$(this).addClass('selected');
			$('#grid-view').removeClass('selected');
			$('#content .product-grid > .clearfix').remove();
			$('#content .product-grid').attr('class', 'product-layout product-list col-xs-12');
			$('#content .custom-products-row .product-list .caption').addClass('col-md-8 col-sm-8 col-sms-12');
			$('#content .custom-products-row .product-list .images-container').addClass('col-md-4 col-sm-4 col-sms-12');
			

			localStorage.setItem('display', 'list');
		});
        // Product Grid
        $('#grid-view').click(function() {
			$('.custom-products').removeClass('custom-products-row');
			$(this).addClass('selected');
			$('#list-view').removeClass('selected');
            var cols = $('#column-right, #column-left').length;

            if (cols == 2) {
                $('#content .product-list').attr('class', 'product-layout product-grid module-style1 col-lg-6 col-md-6 col-sm-6 col-xs-6');
            } else if (cols == 1) {
                $('#content .product-list').attr('class', 'product-layout product-grid module-style1 col-lg-4 col-md-4 col-sm-6 col-xs-6');
            } else {
                $('#content .product-list').attr('class', 'product-layout product-grid module-style1 col-lg-3 col-md-3 col-sm-6 col-xs-6');
            }
            $('#list-view').removeClass('active');
            $('#grid-view').addClass('active');

            localStorage.setItem('display', 'grid');
            localStorage.setItem('type', 'none');
        });

        if (localStorage.getItem('display') == 'list') {
            $('#list-view').trigger('click');
            $('#list-view').addClass('active');
        } else {
            $('#grid-view').trigger('click');
            $('#grid-view').addClass('active');
        }
		// Product Grid
		$('.btn-grid').click(function() {
			$('.custom-products').removeClass('custom-products-row');
			$(this).addClass('selected');
			$('.btn-list').removeClass('selected');
			$('#content .custom-products .product-grid .caption').removeClass('col-md-8 col-sm-8 col-sms-12');
			$('#content .custom-products .product-grid .images-container').removeClass('col-md-4 col-sm-4 col-sms-12');
			

			 localStorage.setItem('display', 'grid');
		});
        // Custom Grid
        if(localStorage.getItem('type') == null) {
            var type = $('#category-view-type').val();
            var cols = $('#category-grid-cols').val();

            if(type == "list") {
                category_view.initView(type, cols, 'btn-list');
            }

            if(type == 'grid') {
                category_view.initView(type, cols, 'btn-grid-' + cols);
            }
        } else {
            var type = localStorage.getItem('type');
            var cols = localStorage.getItem('cols');
            var element = localStorage.getItem('element');

            if(type != 'none') {
                category_view.initView(type, cols, element);
            }
        }
    },

    /* Modify pagination links */
    paginationChangeAction: function () {
        $('.ajax_pagination .pagination a').each(function () {
            var href = $(this).attr('href');
            $(this).attr('onclick', 'ocfilter.filter("'+ href +'")');
            $(this).attr('href', 'javascript:void(0);');
        });
    }

};
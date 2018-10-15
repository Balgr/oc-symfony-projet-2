(function ($) {
    $(document).ready(function () {
        /*Share links popup*/
        $('.wpl_listing_links_container .share_toggle').click(function (e) {
            e.preventDefault();
            var wpl_share_width = $(this).parent('ul').innerWidth();
            $(this).parents('.wpl_listing_links_container').children('.wpl_links_share').fadeToggle().css({
                right: wpl_share_width+'px',
                display: 'flex'
            });
        });

        /*Property manager actions*/
        $('.pmanager-wp .property-actions').prepend('<span class="btn actions-btn">Actions</span>');
        $('.pmanager-wp .actions-btn').click(function () {
            $(this).parents('.pmanager-wp .property-actions').children('.pmanager_actions').fadeToggle();
        });

        $('.wpl-search-header').parent().addClass('wpl-search-header-cont');
        var adv_search_cont = $('.wpl-search-header-cont').html();
        $('.wpl-search-header-cont').remove();
        if ($(window).width() > 991) {
            $(adv_search_cont).prependTo('.advance-search-header>div');
        }
        else {
            $(adv_search_cont).prependTo('.advanced-search-mobile>div');
        }

    })


})(jQuery);
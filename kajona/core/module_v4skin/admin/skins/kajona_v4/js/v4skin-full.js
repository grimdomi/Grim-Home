//   (c) 2007-2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: v4skin.js 5766 2013-07-29 15:24:28Z sidler $


$(function () {

    $.widget('custom.catcomplete', $.ui.autocomplete, {
        _renderMenu: function(ul, items) {
            var self = this;
            var currentCategory = '';

            $.each(items, function(index, item) {
                if (item.module != currentCategory) {
                    ul.append('<li class="ui-autocomplete-category"><h3 class="small">' + item.module + '</h3></li>');
                    currentCategory = item.module;
                }
                self._renderItemData(ul, item);
            });

            ul.append('<li class="detailedResults"><a href="#">View detailed search results</a></li>');
            ul.addClass('dropdown-menu');
            ul.addClass('search-dropdown-menu');

            ul.find('.detailedResults a').click(function () {
                $('.navbar-search').submit();
            });
        },
        _renderItemData: function (ul, item) {
            return $('<li class="clearfix"></li>')
                .data('ui-autocomplete-item', item)
                .append('<a>' + item.icon + item.description + '</a>')
                .appendTo(ul);
        }
    });

    $('#globalSearchInput').catcomplete({
        //source: '_skinwebpath_/search.json',
        source: function(request, response) {
            $.ajax({
                url: KAJONA_WEBPATH+'/xml.php?admin=1',
                type: 'POST',
                dataType: 'json',
                data: {
                    search_query: request.term,
                    module: 'search',
                    action: 'searchXml',
                    asJson: '1'
                },
                success: response
            });
        },
        select: function (event, ui) {
            if(ui.item) {
                document.location = ui.item.link;
            }
        },
        messages: {
            noResults: '',
            results: function() {}
        },
        search: function(event, ui) {
            $(this).css("background-image", "url("+KAJONA_WEBPATH+"/core/module_v4skin/admin/skins/kajona_v4/img/loading-small.gif)").css("background-repeat", "no-repeat").
            css("background-position", "right center");
        },
        response: function(event, ui) {
            $(this).css("background-image", "none");
        }
    });





    // init popovers & tooltips
    $('#content a[rel=popover]').popover();
    KAJONA.admin.tooltip.initTooltip();

    KAJONA.admin.statusDisplay.classOfMessageBox = "alert alert-info";
    KAJONA.admin.statusDisplay.classOfErrorBox = "alert alert-error";

    KAJONA.admin.scroll = null;
    $(window).scroll(function() {
        var scroll = $(this).scrollTop();
        if(scroll > 10 && KAJONA.admin.scroll != 'top') {
            $("ul.breadcrumb").addClass("breadcrumbTop");
            KAJONA.admin.scroll = "top";
        }
        else if(scroll <= 10 && KAJONA.admin.scroll != 'margin') {
            $("ul.breadcrumb").removeClass("breadcrumbTop");
            KAJONA.admin.scroll = "fixed";
        }


    });
});

if (typeof KAJONA == "undefined") {
    alert('load kajona.js before!');
}

KAJONA.v4skin = {
    appendLinkToPathNavigation : function(strLinkContent) {
        var link = $("<li class='pathentry'></li>").append(strLinkContent);
        $("div.pathNaviContainer  ul.breadcrumb").append(link);
    }
};
//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: tags.js 5409 2012-12-30 13:09:07Z sidler $

if (typeof KAJONA == "undefined") {
    alert('load kajona.js before!');
}



/**
 * Tags-handling
 */
KAJONA.admin.tags = {};

KAJONA.admin.tags.saveTag = function(strTagname, strSystemid, strAttribute) {
    KAJONA.admin.ajax.genericAjaxCall("tags", "saveTag", strSystemid+"&tagname="+strTagname+"&attribute="+strAttribute, function(data, status, jqXHR) {
        if(status == 'success') {
            KAJONA.admin.tags.reloadTagList(strSystemid, strAttribute);
            document.getElementById('tagname').value='';
        }
        else {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
        }
    });
};

KAJONA.admin.tags.reloadTagList = function(strSystemid, strAttribute) {

    $("#tagsWrapper_"+strSystemid).addClass("loadingContainer");

    KAJONA.admin.ajax.genericAjaxCall("tags", "tagList", strSystemid+"&attribute="+strAttribute, function(data, status, jqXHR) {
        if(status == 'success') {
            var intStart = data.indexOf("<tags>")+6;
            var strContent = data.substr(intStart, data.indexOf("</tags>")-intStart);
            $("#tagsWrapper_"+strSystemid).removeClass("loadingContainer");
            $("#tagsWrapper_"+strSystemid).html(strContent);
            KAJONA.util.evalScript(strContent);
        }
        else {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            $("#tagsWrapper_"+strSystemid).removeClass("loadingContainer");
        }
    });
};

KAJONA.admin.tags.removeTag = function(strTagId, strTargetSystemid, strAttribute) {
    KAJONA.admin.ajax.genericAjaxCall("tags", "removeTag", strTagId+"&targetid="+strTargetSystemid+"&attribute="+strAttribute, function(data, status, jqXHR) {
        if(status == 'success') {
            KAJONA.admin.tags.reloadTagList(strTargetSystemid, strAttribute);
            document.getElementById('tagname').value='';
        }
        else {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
        }
    });
};

KAJONA.admin.tags.loadTagTooltipContent = function(strTargetSystemid, strAttribute, strTargetContainer) {
    $("#"+strTargetContainer).addClass("loadingContainer");

    KAJONA.admin.ajax.genericAjaxCall("tags", "tagList", strTargetSystemid+"&attribute="+strAttribute+"&delete=false", function(data, status, jqXHR) {
        if(status == 'success') {
            var intStart = data.indexOf("<tags>")+6;
            var strContent = data.substr(intStart, data.indexOf("</tags>")-intStart);
            $("#"+strTargetContainer).removeClass("loadingContainer");
            $("#"+strTargetContainer).html(strContent);
            KAJONA.util.evalScript(strContent);
        }
        else {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            $("#"+strTargetContainer).removeClass("loadingContainer");
        }
    });
};



//   (c) 2007-2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: packagemanager.js 5720 2013-07-11 09:48:50Z sidler $

if (!KAJONA) {
	alert('load kajona.js before!');
}


KAJONA.admin.packagemanager = {
    arrPackageNames : [],
    objWrapperNames : {},

    addPackageToTest : function(strName, strWrapperName) {
        this.arrPackageNames.push(strName);
        KAJONA.admin.packagemanager.objWrapperNames[strName] = strWrapperName ;
    },

    triggerUpdateCheck : function() {
        KAJONA.admin.ajax.genericAjaxCall('packagemanager', 'getUpdateIcons', '&packages='+this.arrPackageNames.join(","), function(data, status, jqXHR) {
            if(status == 'success') {
                $.each(jQuery.parseJSON(data), function (packageName, content) {
//                    console.log("parsing "+packageName+" target: "+KAJONA.admin.packagemanager.objWrapperNames.packageName);
                    $('#updateWrapper'+KAJONA.admin.packagemanager.objWrapperNames[packageName]).html(content);
                    KAJONA.util.evalScript(content);
                    KAJONA.admin.tooltip.addTooltip($('#updateWrapper'+KAJONA.admin.packagemanager.objWrapperNames[packageName]+' img'));
                    KAJONA.admin.tooltip.addTooltip($('#updateWrapper'+KAJONA.admin.packagemanager.objWrapperNames[packageName]+' a'));
                    KAJONA.admin.tooltip.addTooltip($('#updateWrapper'+KAJONA.admin.packagemanager.objWrapperNames[packageName]+' span'));
                });
            }
            else {
                KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);
            }

        });
    }
};






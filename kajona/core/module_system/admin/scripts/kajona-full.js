//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 5893 2013-09-29 11:27:24Z jschroeter $

if (typeof KAJONA == "undefined") {
	var KAJONA = {
		util: {},
		portal: {
			lang: {}
		},
		admin: {
			lang: {}
		}
	};
}


/*
 * -------------------------------------------------------------------------
 * Global functions
 * -------------------------------------------------------------------------
 */

/**
 * Function to evaluate the script-tags in a passed string, e.g. loaded by an ajax-request
 *
 * @param {String} scripts
 * @see http://wiki.ajax-community.de/know-how:nachladen-von-javascript
 **/
KAJONA.util.evalScript = function (scripts) {
	try {
        if(scripts != '')	{
            var script = "";
			scripts = scripts.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function() {
                 if (scripts !== null)
                         script += arguments[1] + '\n';
                return '';
            });
			if(script)
                (window.execScript) ? window.execScript(script) : window.setTimeout(script, 0);
		}
		return false;
	}
	catch(e) {
        alert(e);
	}
};

KAJONA.util.isTouchDevice = function() {
    return !!('ontouchstart' in window) ? 1 : 0;
};


/**
 * Checks if the given array contains the given string
 *
 * @param {String} strNeedle
 * @param {String[]} arrHaystack
 */
KAJONA.util.inArray = function (strNeedle, arrHaystack) {
    for (var i = 0; i < arrHaystack.length; i++) {
        if (arrHaystack[i] == strNeedle) {
            return true;
        }
    }
    return false;
};

/**
 * Used to show/hide an html element
 *
 * @param {String} strElementId
 * @param {Function} objCallbackVisible
 * @param {Function} objCallbackInvisible
 */
KAJONA.util.fold = function (strElementId, objCallbackVisible, objCallbackInvisible) {
	var element = document.getElementById(strElementId);
	if (element.style.display == 'none') 	{
		element.style.display = 'block';
		if ($.isFunction(objCallbackVisible)) {
			objCallbackVisible();
		}
    }
    else {
    	element.style.display = 'none';
		if ($.isFunction(objCallbackInvisible)) {
			objCallbackInvisible();
		}
    }
};

/**
 * Used to show/hide an html element and switch an image (e.g. a button)
 *
 * @param {String} strElementId
 * @param {String} strImageId
 * @param {String} strImageVisible
 * @param {String} strImageHidden
 */
KAJONA.util.foldImage = function (strElementId, strImageId, strImageVisible, strImageHidden) {
	var element = document.getElementById(strElementId);
	var image = document.getElementById(strImageId);
	if (element.style.display == 'none') 	{
		element.style.display = 'block';
		image.src = strImageVisible;
    }
    else {
    	element.style.display = 'none';
    	image.src = strImageHidden;
    }
};

KAJONA.util.setBrowserFocus = function (strElementId) {
	$(function() {
		try {
		    focusElement = $("#"+strElementId);
		    if (focusElement.hasClass("inputWysiwyg")) {
		    	CKEDITOR.config.startupFocus = true;
		    } else {
		        focusElement.focus();
		    }
		} catch (e) {}
	});
};

/**
 * some functions to track the mouse position and move an element
 * @deprecated will be removed with Kajona 3.4 or 3.5, use YUI Panel instead
 */
KAJONA.util.mover = (function() {
	var currentMouseXPos;
	var currentMouseYPos;
	var objToMove = null;
	var objDiffX = 0;
	var objDiffY = 0;

	function checkMousePosition(e) {
		if (document.all) {
			currentMouseXPos = event.clientX + document.body.scrollLeft;
			currentMouseYPos = event.clientY + document.body.scrollTop;
		} else {
			currentMouseXPos = e.pageX;
			currentMouseYPos = e.pageY;
		}

		if (objToMove != null) {
			objToMove.style.left = currentMouseXPos - objDiffX + "px";
			objToMove.style.top = currentMouseYPos - objDiffY + "px";
		}
	}

	function setMousePressed(obj) {
		objToMove = obj;
		objDiffX = currentMouseXPos - objToMove.offsetLeft;
		objDiffY = currentMouseYPos - objToMove.offsetTop;
	}

	function unsetMousePressed() {
		objToMove = null;
	}


	//public variables and methods
	return {
		checkMousePosition : checkMousePosition,
		setMousePressed : setMousePressed,
		unsetMousePressed : unsetMousePressed
	}
}());

/*
 * -------------------------------------------------------------------------
 * Admin-specific functions
 * -------------------------------------------------------------------------
 */

/**
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 */
KAJONA.admin.loader = new KAJONA.util.Loader();


/**
 * Folderview functions
 */
KAJONA.admin.folderview = {
	/**
	 * holds a reference to the ModalDialog
	 */
	dialog: undefined,

	/**
	 * holds CKEditors CKEditorFuncNum parameter to read it again in KAJONA.admin.folderview.fillFormFields()
	 * so we don't have to pass through the param with all requests
	 */
	selectCallbackCKEditorFuncNum: 0,

	/**
	 * To be called when the user selects an page/folder/file out of a folderview dialog/popup
	 * Detects if the folderview is embedded in a dialog or popup to find the right context
     *
     * @param {Array} arrTargetsValues
     * @param {function} objCallback
	 */
	selectCallback: function (arrTargetsValues, objCallback) {
		if (window.opener) {
			window.opener.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
		} else if (parent) {
			parent.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
		}

        if ($.isFunction(objCallback)) {
			objCallback();
		}

        this.close();

	},

	/**
	 * fills the form fields with the selected values
	 */
	fillFormFields: function (arrTargetsValues) {
		for (var i in arrTargetsValues) {
	    	if (arrTargetsValues[i][0] == "ckeditor") {
	    		CKEDITOR.tools.callFunction(this.selectCallbackCKEditorFuncNum, arrTargetsValues[i][1]);
	    	} else {
	    		var formField = $("#"+arrTargetsValues[i][0]).get(0);

                if (formField != null) {
                	formField.value = arrTargetsValues[i][1];

                	//fire the onchange event on the form field
                    if (document.createEvent) { //Firefox
                        var evt = document.createEvent("Events");
                        evt.initEvent('change', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                        formField.dispatchEvent(evt);
                    } else if (document.createEventObject) { //IE
                        var evt = document.createEventObject();
                        formField.fireEvent('onchange', evt);
                    }

                }
	    	}
		}
	},

	/**
	 * fills the form fields with the selected values
	 */
	close: function () {
		if (window.opener) {
			window.close();
		} else if (parent) {
			var context = parent.KAJONA.admin.folderview;
			context.dialog.hide();
			context.dialog.setContentRaw("");
		}
	}
};


KAJONA.admin.tooltip = {
    initTooltip : function() {
        KAJONA.admin.loader.loadFile(['/core/module_system/admin/scripts/qtip2/jquery.qtip.min.js', '/core/module_system/admin/scripts/qtip2/jquery.qtip.min.css'], function() {

            //common tooltips

            $('*[rel=tooltip][title!=""]').qtip({
                position: {
                    viewport: $(window)
                },
                style: {
                    classes: 'qtip-youtube qtip-shadow'
                }
            });

            //tag tooltips
            $('*[rel=tagtooltip][title!=""]').each( function() {
                $(this).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-youtube qtip-shadow'
                    },
                    content: {
                        text: $(this).attr("title")+"<div id='tags_"+$(this).data('systemid')+"' data-systemid='"+$(this).data('systemid')+"'></div>"
                    },
                    events: {
                        render: function(event, api) {
                            KAJONA.admin.loader.loadFile('/core/module_tags/admin/scripts/tags.js', function() {
                                KAJONA.admin.tags.loadTagTooltipContent($(api.elements.content).find('div').data('systemid'), "", $(api.elements.content).find('div').attr('id'));
                            })
                        }
                    }
                });
            })

        });
    },

    addTooltip : function(objElement, strText) {
        KAJONA.admin.loader.loadFile(['/core/module_system/admin/scripts/qtip2/jquery.qtip.min.js', '/core/module_system/admin/scripts/qtip2/jquery.qtip.min.css'], function() {

            if(strText) {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-youtube qtip-shadow'
                    },
                    content : {
                        text: strText
                    }
                });
            }
            else {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-youtube qtip-shadow'
                    }
                });
            }
        });
    },

    removeTooltip : function(objElement) {
        $(objElement).qtip('hide');
    }

};



/**
 * switches the edited language in admin
 */
KAJONA.admin.switchLanguage = function(strLanguageToLoad) {
	var url = window.location.href;
	url = url.replace(/(\?|&)language=([a-z]+)/, "");
	if (url.indexOf('?') == -1) {
		window.location.replace(url + '?language=' + strLanguageToLoad);
	} else {
		window.location.replace(url + '&language=' + strLanguageToLoad);
	}
};

/**
 * little helper function for the system right matrix
 */
KAJONA.admin.checkRightMatrix = function() {
	// mode 1: inheritance
	if (document.getElementById('inherit').checked) {
		// loop over all checkboxes to disable them
		for (var intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if (objCurElement.type == 'checkbox') {
				if (objCurElement.id != 'inherit') {
					objCurElement.disabled = true;
					objCurElement.checked = false;
					var strCurId = "inherit," + objCurElement.id;
					if (document.getElementById(strCurId) != null) {
						if (document.getElementById(strCurId).value == '1') {
							objCurElement.checked = true;
						}
					}
				}
			}
		}
	} else {
		// mode 2: no inheritance, make all checkboxes editable
		for (intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if (objCurElement.type == 'checkbox') {
				if (objCurElement.id != 'inherit') {
					objCurElement.disabled = false;
				}
			}
		}
	}
};

/**
 * General way to display a status message.
 * Therefore, the html-page should provide the following elements as noted as instance-vars:
 * - div,   id: jsStatusBox    				the box to be animated
 * 		 class: jsStatusBoxMessage			class in case of an informal message
 * 		 class: jsStatusBoxError		    class in case of an error message
 * - div,   id: jsStatusBoxContent			the box to place the message-content into
 *
 * Pass a xml-response from a Kajona server to displayXMLMessage() to start the logic
 * or use messageOK() / messageError() passing a regular string
 */
KAJONA.admin.statusDisplay = {
	idOfMessageBox : "jsStatusBox",
	idOfContentBox : "jsStatusBoxContent",
	classOfMessageBox : "jsStatusBoxMessage",
	classOfErrorBox : "jsStatusBoxError",
	timeToFadeOutMessage : 4000,
	timeToFadeOutError : 10000,
	timeToFadeOut : null,

	/**
	 * General entrance point. Use this method to pass an xml-response from the kajona server.
	 * Tries to find a message- or an error-tag an invokes the corresponding methods
	 *
	 * @param {String} message
	 */
	displayXMLMessage : function(message) {
		//decide, whether to show an error or a message, message only in debug mode
		if(message.indexOf("<message>") != -1 && KAJONA_DEBUG > 0 && message.indexOf("<error>") == -1) {
			var intStart = message.indexOf("<message>")+9;
			var responseText = message.substr(intStart, message.indexOf("</message>")-intStart);
			this.messageOK(responseText);
		}

		if(message.indexOf("<error>") != -1) {
			var intStart = message.indexOf("<error>")+7;
			var responseText = message.substr(intStart, message.indexOf("</error>")-intStart);
			this.messageError(responseText);
		}
	},

	/**
	 * Creates a informal message box contaning the passed content
	 *
	 * @param {String} strMessage
	 */
    messageOK : function(strMessage) {
		$("#"+this.idOfMessageBox).removeClass(this.classOfMessageBox).removeClass(this.classOfErrorBox).addClass(this.classOfMessageBox);
		this.timeToFadeOut = this.timeToFadeOutMessage;
		this.startFadeIn(strMessage);
    },

	/**
	 * Creates an error message box containg the passed content
	 *
	 * @param {String} strMessage
	 */
    messageError : function(strMessage) {
        $("#"+this.idOfMessageBox).removeClass(this.classOfMessageBox).removeClass(this.classOfErrorBox).addClass(this.classOfErrorBox);
		this.timeToFadeOut = this.timeToFadeOutError;
		this.startFadeIn(strMessage);
    },

	startFadeIn : function(strMessage) {
		var statusBox = $("#"+this.idOfMessageBox);
		var contentBox = $("#"+this.idOfContentBox);
		contentBox.html(strMessage);
		statusBox.css("display", "").css("opacity", 0.0);

		//place the element at the top of the page
		var screenWidth = $(window).width()
		var divWidth = statusBox.width();
		var newX = screenWidth/2 - divWidth/2;
		var newY = $(window).scrollTop() -2;
        statusBox.css('top', newY);
        statusBox.css('left', newX);

		//start fade-in handler

        KAJONA.admin.statusDisplay.fadeIn();

	},

	fadeIn : function () {
        $("#"+this.idOfMessageBox).animate({opacity: 0.8}, 1000, function() {  window.setTimeout("KAJONA.admin.statusDisplay.startFadeOut()", this.timeToFadeOut); });
	},

	startFadeOut : function() {
        $("#"+this.idOfMessageBox).animate(
            { top: -200 },
            1000,
            function() {
                $("#"+this.idOfMessageBox).css("display", "none");
            }
        );

	}
};


/**
 * Functions to execute system tasks
 */
KAJONA.admin.systemtask = {
    executeTask : function(strTaskname, strAdditionalParam, bitNoContentReset) {
        if(bitNoContentReset == null || bitNoContentReset == undefined) {

            if(document.getElementById('taskParamForm') != null) {
                document.getElementById('taskParamForm').style.display = "none";
            }

            jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE);
            jsDialog_0.setContentRaw(kajonaSystemtaskDialogContent);
            document.getElementById(jsDialog_0.containerId).style.width = "550px";
            document.getElementById('systemtaskCancelButton').onclick = this.cancelExecution;
            jsDialog_0.init();
        }

        KAJONA.admin.ajax.genericAjaxCall("system", "executeSystemTask", "&task="+strTaskname+strAdditionalParam, function(data, status, jqXHR) {
            if(status == 'success') {
                var strResponseText = data;

                //parse the response and check if it's valid
                if(strResponseText.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(strResponseText);
                }
                else if(strResponseText.indexOf("<statusinfo>") == -1) {
                	KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />"+strResponseText);
                }
                else {
                    var intStart = strResponseText.indexOf("<statusinfo>")+12;
                    var strStatusInfo = strResponseText.substr(intStart, strResponseText.indexOf("</statusinfo>")-intStart);

                    //parse text to decide if a reload is necessary
                    var strReload = "";
                    if(strResponseText.indexOf("<reloadurl>") != -1) {
                        intStart = strResponseText.indexOf("<reloadurl>")+11;
                        strReload = strResponseText.substr(intStart, strResponseText.indexOf("</reloadurl>")-intStart);
                    }

                    //show status info
                    document.getElementById('systemtaskStatusDiv').innerHTML = strStatusInfo;

                    if(strReload == "") {
                    	jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE_DONE);
                    	document.getElementById('systemtaskLoadingDiv').style.display = "none";
                    	document.getElementById('systemtaskCancelButton').value = KAJONA_SYSTEMTASK_CLOSE;
                    }
                    else {
                    	KAJONA.admin.systemtask.executeTask(strTaskname, strReload, true);
                    }
                }
            }

            else {
                jsDialog_0.hide();
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />"+data);
            }
        });
    },

    cancelExecution : function() {
        jsDialog_0.hide();
    },

    setName : function(strName) {
    	document.getElementById('systemtaskNameDiv').innerHTML = strName;
    }
};

/**
 * AJAX functions for connecting to the server
 */
KAJONA.admin.ajax = {

    getDataObjectFromString: function(strData, bitFirstIsSystemid) {
        //strip other params, backwards compatibility
        var arrElements = strData.split("&");
        var data = { };

        if(bitFirstIsSystemid)
            data["systemid"] = arrElements[0];

        //first one is the systemid
        if(arrElements.length > 1) {
            $.each(arrElements, function(index, strValue) {
                if(!bitFirstIsSystemid || index > 0) {
                    var arrSingleParams = strValue.split("=");
                    data[arrSingleParams[0]] = arrSingleParams[1];
                }
            });
        }
        return data;
    },

    regularCallback: function(data, status, jqXHR) {
		if(status == 'success') {
			KAJONA.admin.statusDisplay.displayXMLMessage(data)
		}
		else {
			KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
		}
	},


	genericAjaxCall : function(module, action, systemid, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
        var data = this.getDataObjectFromString(systemid, true);

        $.ajax({
            type: 'POST',
            url: postTarget,
            data: data,
            success: objCallback,
            dataType: 'text'
        });

	},

    setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList, objCallback, strTargetModule) {
        if(strTargetModule == null || strTargetModule == "")
            strTargetModule = "system";

        if(typeof objCallback == 'undefined' || objCallback == null)
            objCallback = KAJONA.admin.ajax.regularCallback;


        KAJONA.admin.ajax.genericAjaxCall(strTargetModule, "setAbsolutePosition", systemIdToMove + "&listPos=" + intNewPos, objCallback);
	},

	setSystemStatus : function(strSystemIdToSet, bitReload) {
        var objCallback = function(data, status, jqXHR) {
            if(status == 'success') {
				KAJONA.admin.statusDisplay.displayXMLMessage(data);

                if(bitReload !== null && bitReload === true)
                    location.reload();

                if (data.indexOf('<error>') == -1 && data.indexOf('<html>') == -1) {
                    var newStatus = $($.parseXML(data)).find("newstatus").text();
                    var link = $('#statusLink_' + strSystemIdToSet);

                    var adminListRow = link.parents('.admintable > tbody').first();
                    if (!adminListRow.length) {
                        adminListRow = link.parents('.grid > ul > li').first();
                    }

                    if (newStatus == 0) {
                        link.html(KAJONA.admin.ajax.setSystemStatusMessages.strInActiveIcon);
                        adminListRow.addClass('disabled');
                    } else {
                        link.html(KAJONA.admin.ajax.setSystemStatusMessages.strActiveIcon);
                        adminListRow.removeClass('disabled');
                    }

//                    KAJONA.admin.tooltip.addTooltip(link.find("img"));
                    KAJONA.admin.tooltip.addTooltip(link.find("span"));
				}
        	}
            else
        		KAJONA.admin.statusDisplay.messageError(data);
        };

        var link = $('#statusLink_' + strSystemIdToSet);
//        KAJONA.admin.tooltip.removeTooltip(link.find("img"));
        KAJONA.admin.tooltip.removeTooltip(link.find("span"));
        KAJONA.admin.ajax.genericAjaxCall("system", "setStatus", strSystemIdToSet, objCallback);
	},

    setSystemStatusMessages : {
        strInActiveIcon : '',
        strActiveIcon : ''
    }

};


/**
 * Form management
 */
KAJONA.admin.forms = {};
KAJONA.admin.forms.renderMandatoryFields = function(arrFields) {

    for(var i=0; i<arrFields.length; i++) {
        var arrElement = arrFields[i];
        if(arrElement.length == 2) {
            if(arrElement[1] == 'date') {
               $("#"+arrElement[0]+"_day").addClass("mandatoryFormElement");
               $("#"+arrElement[0]+"_month").addClass("mandatoryFormElement");
               $("#"+arrElement[0]+"_year").addClass("mandatoryFormElement");
            }

            if($("#"+arrElement[0]))
                $("#"+arrElement[0]).addClass("mandatoryFormElement");
        }

        //closest(".control-group").addClass("error")
    }
};

KAJONA.admin.forms.renderMissingMandatoryFields = function(arrFields) {
    $(arrFields).each(function() {
        if($("#"+this))
            $("#"+this).closest(".control-group").addClass("error");
    });
};

KAJONA.admin.lists = {
    arrSystemids : [],
    strConfirm : '',
    strCurrentUrl : '',
    strCurrentTitle : '',
    strDialogTitle : '',
    strDialogStart : '',
    intTotal : 0,

    toggleAllFields : function() {
        //batchActionSwitch
        $("table.admintable input[type='checkbox']").each(function() {
            if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                $(this)[0].checked = $('#kj_cb_batchActionSwitch')[0].checked;
            }
        });
    },

    updateToolbar : function() {
        if($("table.admintable  input:checked").length == 0) {
            $('.batchActionsWrapper').removeClass("visible");
        }
        else {
            $('.batchActionsWrapper').addClass("visible");
        }
    },

    triggerAction : function(strTitle, strUrl) {
        KAJONA.admin.lists.arrSystemids = [];
        KAJONA.admin.lists.strCurrentUrl = strUrl;
        KAJONA.admin.lists.strCurrentTitle = strTitle;

        //get the selected elements
        $("table.admintable  input:checked").each(function() {
            if($(this).attr('id').substr(0, 6) == "kj_cb_" && $(this).attr('id') != 'kj_cb_batchActionSwitch') {
                var sysid = $(this).closest("tr").data('systemid');
                if(sysid != "")
                    KAJONA.admin.lists.arrSystemids.push(sysid);
            }
        });


        if(KAJONA.admin.lists.arrSystemids.length == 0)
            return;

        var curConfirm = KAJONA.admin.lists.strConfirm.replace('%amount%', KAJONA.admin.lists.arrSystemids.length);
        curConfirm = curConfirm.replace('%title%', strTitle);

        jsDialog_1.setTitle(KAJONA.admin.lists.strDialogTitle);
        jsDialog_1.setContent(curConfirm, KAJONA.admin.lists.strDialogStart,  'javascript:KAJONA.admin.lists.executeActions();');
        jsDialog_1.init();

        //reset pending list on hide
        $('#'+jsDialog_1.containerId).on('hidden', function () {
            KAJONA.admin.lists.arrSystemids = [];
        });

        return false;
    },

    executeActions : function() {
        console.log("starting execution");
        KAJONA.admin.lists.intTotal = KAJONA.admin.lists.arrSystemids.length;

        $('.batchActionsProgress > .progresstitle').text(KAJONA.admin.lists.strCurrentTitle);
        $('.batchActionsProgress > .total').text(KAJONA.admin.lists.intTotal);
        jsDialog_1.setContentRaw($('.batchActionsProgress').html());

        KAJONA.admin.lists.triggerSingleAction();
    },

    triggerSingleAction : function() {
        if(KAJONA.admin.lists.arrSystemids.length > 0 && KAJONA.admin.lists.intTotal > 0) {
            $('.batch_progressed').text((KAJONA.admin.lists.intTotal - KAJONA.admin.lists.arrSystemids.length +1));
            $('.progress > .bar').css('width', ( (KAJONA.admin.lists.intTotal - KAJONA.admin.lists.arrSystemids.length) / KAJONA.admin.lists.intTotal   * 100)+'%');

            var strUrl = KAJONA.admin.lists.strCurrentUrl.replace("%systemid%", KAJONA.admin.lists.arrSystemids[0]);
            KAJONA.admin.lists.arrSystemids.shift();

            $.ajax({
                type: 'POST',
                url: strUrl,
                success: function() {
                    KAJONA.admin.lists.triggerSingleAction();
                },
                dataType: 'text'
            });
        }
        else {
            $('.batch_progressed').text((KAJONA.admin.lists.intTotal));
            $('.progress > .bar').css('width', 100+'%');


            document.location.reload();
        }



    }
};

/**
 * Dashboard calendar functions
 */
KAJONA.admin.dashboardCalendar = {};
KAJONA.admin.dashboardCalendar.eventMouseOver = function(strSourceId) {
    if(strSourceId == "")
        return;

    var sourceArray = eval("kj_cal_"+strSourceId);
    if(typeof sourceArray != undefined) {
        for(var i=0; i< sourceArray.length; i++) {
            $("#event_"+sourceArray[i]).addClass("mouseOver");
        }
    }
};

KAJONA.admin.dashboardCalendar.eventMouseOut = function(strSourceId) {
    if(strSourceId == "")
        return;

    var sourceArray = eval("kj_cal_"+strSourceId);
    if(typeof sourceArray != undefined) {
        for(var i=0; i< sourceArray.length; i++) {
            $("#event_"+sourceArray[i]).removeClass("mouseOver");
        }
    }
};


/**
 * Subsystem for all messaging related tasks. Queries the backend for the number of unread messages, ...
 * @type {Object}
 */
KAJONA.admin.messaging = {

    /**
     * Gets the number of unread messages for the current user.
     * Expects a callback-function whereas the number is passed as a param.
     *
     * @param objCallback
     * @deprecated replaced by getRecentMessages
     */
    getUnreadCount : function(objCallback) {

        KAJONA.admin.ajax.genericAjaxCall("messaging", "getUnreadMessagesCount", "", function(data, status, jqXHR) {
            if(status == 'success') {
                var objResponse = $($.parseXML(data));
                KAJONA.admin.messaging.intCount = objResponse.find("messageCount").text();
                objCallback(objResponse.find("messageCount").text());

            }
        });
    },

    /**
     * Loads the list of recent messages for the current user.
     * The callback is passed the json-object as a param.
     * @param objCallback
     */
    getRecentMessages : function(objCallback) {
        KAJONA.admin.ajax.genericAjaxCall("messaging", "getRecentMessages", "", function(data, status, jqXHR) {
            if(status == 'success') {
                var objResponse = $.parseJSON(data);
                objCallback(objResponse);
            }
        });
    }
};
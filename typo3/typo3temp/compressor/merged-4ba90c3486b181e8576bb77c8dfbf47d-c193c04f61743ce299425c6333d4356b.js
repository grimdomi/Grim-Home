
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Jeff Segars <jeff@webempoweredchurch.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


Element.addMethods({
	pngHack: function(element) {
		element = $(element);
		var transparentGifPath = 'clear.gif';

			// If there is valid element, it is an image and the image file ends with png:
		if (Object.isElement(element) && element.tagName === 'IMG' && element.src.endsWith('.png')) {
			var alphaImgSrc = element.src;
			var sizingMethod = 'scale';
			element.src = transparentGifPath;
		}

		if (alphaImgSrc) {
			element.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="#{alphaImgSrc}",sizingMethod="#{sizingMethod}")'.interpolate(
			{
				alphaImgSrc: alphaImgSrc,
				sizingMethod: sizingMethod
			});
		}

		return element;
	}
});

var IECompatibility = Class.create({

	/**
	 * initializes the compatibility class
	 */
	initialize: function() {
		Event.observe(document, 'dom:loaded', function() {
			$$('input[type="checkbox"]').invoke('addClassName', 'checkbox');
		}.bind(this));

		Event.observe(window, 'load', function() {
			if (Prototype.Browser.IE) {
				var version = parseFloat(navigator.appVersion.split(';')[1].strip().split(' ')[1]);
				if (version === 6) {
					$$('img').each(function(img) {
						img.pngHack();
					});
					$$('#typo3-menu li ul li').each(function(li) {
						li.setStyle({height: '21px'});
					});
				}
			}
		});
	}
});

if (Prototype.Browser.IE) {
	var TYPO3IECompatibilty = new IECompatibility();
}

/**
 * Javascript functions regarding the clickmenu
 * relies on the javascript library "prototype"
 *
 * (c) 2007-2011 Benjamin Mack <www.xnos.org>
 * All rights reserved
 *
 * This script is part of TYPO3 by
 * Kasper Skaarhoj <kasperYYYYY@typo3.com>
 *
 * Released under GNU/GPL (see license file in tslib/)
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This copyright notice MUST APPEAR in all copies of this script
 */

/**
 * new clickmenu code to make an AJAX call and render the
 * AJAX result in a layer next to the mouse cursor
 */
var Clickmenu = {
	clickURL: 'alt_clickmenu.php',	// URL to the clickmenu.php file, see template.php
	ajax: true,	// template.php -> isCMLayers check
	mousePos: { X: null, Y: null },
	delayClickMenuHide: false,

	/**
	 * main function, called from most clickmenu links
	 * @param	table		table from where info should be fetched
	 * @param	uid		the UID of the item
	 * @param	listFr		list Frame?
	 * @param	enDisItems	Items to disable / enable
	 * @param	backPath	TYPO3 backPath
	 * @param	addParams	additional params
	 * @return	nothing
	 */
	show: function(table, uid, listFr, enDisItems, backPath, addParams) {
		var params = 'table=' + encodeURIComponent(table) +
			'&uid=' + uid +
			'&listFr=' + listFr +
			'&enDisItems=' + enDisItems +
			'&backPath=' + backPath +
			'&addParams=' + addParams;
		this.callURL(params);
	},


	/**
	 * switch function that either makes an AJAX call
	 * or loads the request in the top frame
	 *
	 * @param	params	parameters added to the URL
	 * @return	nothing
	 */
	callURL: function(params) {
		if (this.ajax && Ajax.getTransport()) { // run with AJAX
			params += '&ajax=1';
			var call = new Ajax.Request(this.clickURL, {
				method: 'get',
				parameters: params,
				onComplete: function(xhr) {
					var response = xhr.responseXML;
					if (!response.getElementsByTagName('data')[0]) {
						var res = params.match(/&reloadListFrame=(0|1|2)(&|$)/);
						var reloadListFrame = parseInt(res[1], 0);
						if (reloadListFrame) {
							var doc = reloadListFrame != 2 ? top.content.list_frame : top.content;
							doc.location.reload(true);
						}
						return;
					}
					var menu  = response.getElementsByTagName('data')[0].getElementsByTagName('clickmenu')[0];
					var data  = menu.getElementsByTagName('htmltable')[0].firstChild.data;
					var level = menu.getElementsByTagName('cmlevel')[0].firstChild.data;
					this.populateData(data, level);
				}.bind(this)
			});
		}
	},


	/**
	 * fills the clickmenu with content and displays it correctly
	 * depending on the mouse position
	 * @param	data	the data that will be put in the menu
	 * @param	level	the depth of the clickmenu
	 */
	populateData: function(data, level) {
		if (!$('contentMenu0')) {
			this.addClickmenuItem();
		}
		level = parseInt(level, 10) || 0;
		var obj = $('contentMenu' + level);

		if (obj && (level === 0 || Element.visible('contentMenu' + (level-1)))) {
			obj.innerHTML = data;
			var x = this.mousePos.X;
			var y = this.mousePos.Y;
			var dimsWindow = document.viewport.getDimensions();
			dimsWindow.width = dimsWindow.width-20; // saving margin for scrollbars

			var dims = Element.getDimensions(obj); // dimensions for the clickmenu
			var offset = document.viewport.getScrollOffsets();
			var relative = { X: this.mousePos.X - offset.left, Y: this.mousePos.Y - offset.top };

			// adjusting the Y position of the layer to fit it into the window frame
			// if there is enough space above then put it upwards,
			// otherwise adjust it to the bottom of the window
			if (dimsWindow.height - dims.height < relative.Y) {
				if (relative.Y > dims.height) {
					y -= (dims.height - 10);
				} else {
					y += (dimsWindow.height - dims.height - relative.Y);
				}
			}
			// adjusting the X position like Y above, but align it to the left side of the viewport if it does not fit completely
			if (dimsWindow.width - dims.width < relative.X) {
				if (relative.X > dims.width) {
					x -= (dims.width - 10);
				} else if ((dimsWindow.width - dims.width - relative.X) < offset.left) {
					x = offset.left;
				} else {
					x += (dimsWindow.width - dims.width - relative.X);
				}
			}

			obj.style.left = x + 'px';
			obj.style.top  = y + 'px';
			Element.show(obj);
		}
		if (/MSIE5/.test(navigator.userAgent)) {
			this._toggleSelectorBoxes('hidden');
		}
	},


	/**
	 * event handler function that saves the actual position of the mouse
	 * in the Clickmenu object
	 * @param	event	the event object
	 */
	calcMousePosEvent: function(event) {
		if (!event) {
			event = window.event;
		}
		this.mousePos.X = Event.pointerX(event);
		this.mousePos.Y = Event.pointerY(event);
		this.mouseOutFromMenu('contentMenu0');
		this.mouseOutFromMenu('contentMenu1');
	},


	/**
	 * hides a visible menu if the mouse has moved outside
	 * of the object
	 * @param	obj	the object to hide
	 * @result	nothing
	 */
	mouseOutFromMenu: function(obj) {
		obj = $(obj);
		if (obj && Element.visible(obj) && !Position.within(obj, this.mousePos.X, this.mousePos.Y)) {
			this.hide(obj);
			if (/MSIE5/.test(navigator.userAgent) && obj.id === 'contentMenu0') {
				this._toggleSelectorBoxes('visible');
			}
		} else if (obj && Element.visible(obj)) {
			this.delayClickMenuHide = true;
		}
	},

	/**
	 * hides a clickmenu
	 *
	 * @param	obj	the clickmenu object to hide
	 * @result	nothing
	 */
	hide: function(obj) {
		this.delayClickMenuHide = false;
		window.setTimeout(function() {
			if (!Clickmenu.delayClickMenuHide) {
				Element.hide(obj);
			}
		}, 500);
	},

	/**
	 * hides all clickmenus
	 */
	hideAll: function() {
		this.hide('contentMenu0');
		this.hide('contentMenu1');
	},


	/**
	 * hides / displays all selector boxes in a page, fixes an IE 5 selector problem
	 * originally by Michiel van Leening
	 *
	 * @param	action 	hide (= "hidden") or show (= "visible")
	 * @result	nothing
	 */
	_toggleSelectorBoxes: function(action) {
		for (var i = 0; i < document.forms.length; i++) {
			for (var j = 0; j < document.forms[i].elements.length; j++) {
				if (document.forms[i].elements[j].type == 'select-one') {
					document.forms[i].elements[j].style.visibility = action;
				}
			}
		}
	},


	/**
	 * manipulates the DOM to add the divs needed for clickmenu at the bottom of the <body>-tag
	 *
	 * @return	nothing
	 */
	addClickmenuItem: function() {
		var code = '<div id="contentMenu0" style="display: block;"></div><div id="contentMenu1" style="display: block;"></div>';
		var insert = new Insertion.Bottom(document.getElementsByTagName('body')[0], code);
	}
}

// register mouse movement inside the document
Event.observe(document, 'mousemove', Clickmenu.calcMousePosEvent.bindAsEventListener(Clickmenu), true);


// @deprecated: Deprecated functions since 4.2, here for compatibility, remove in 4.4+
// ## BEGIN ##

// Still used in Core: typo3/template.php::wrapClickMenuOnIcon()
function showClickmenu(table, uid, listFr, enDisItems, backPath, addParams) {
	Clickmenu.show(table, uid, listFr, enDisItems, backPath, addParams);
}

// Still used in Core: typo3/alt_clickmenu.php::linkItem()
function showClickmenu_raw(url) {
	var parts = url.split('?');
	if (parts.length === 2) {
		Clickmenu.clickURL = parts[0];
		Clickmenu.callURL(parts[1]);
	} else {
		Clickmenu.callURL(url);
	}
}
function showClickmenu_noajax(url) {
	Clickmenu.ajax = false; showClickmenu_raw(url);
}
function setLayerObj(tableData, cmLevel) {
	Clickmenu.populateData(tableData, cmLevel);
}
function hideEmpty() {
	Clickmenu.hideAll();
	return false;
}
function hideSpecific(level) {
	if (level === 0 || level === 1) {
		Clickmenu.hide('contentMenu'+level);
	}
}
function showHideSelectorBoxes(action) {
	toggleSelectorBoxes(action);
}
// ## END ##

/* CodeMirror main module (http://codemirror.net/)
 *
 * Implements the CodeMirror constructor and prototype, which take care
 * of initializing the editor frame, and providing the outside interface.
 */

// The CodeMirrorConfig object is used to specify a default
// configuration. If you specify such an object before loading this
// file, the values you put into it will override the defaults given
// below. You can also assign to it after loading.
var CodeMirrorConfig = window.CodeMirrorConfig || {};

var CodeMirror = (function(){
  function setDefaults(object, defaults) {
    for (var option in defaults) {
      if (!object.hasOwnProperty(option))
        object[option] = defaults[option];
    }
  }
  function forEach(array, action) {
    for (var i = 0; i < array.length; i++)
      action(array[i]);
  }
  function createHTMLElement(el) {
    if (document.createElementNS && document.documentElement.namespaceURI !== null)
      return document.createElementNS("http://www.w3.org/1999/xhtml", el)
    else
      return document.createElement(el)
  }

  // These default options can be overridden by passing a set of
  // options to a specific CodeMirror constructor. See manual.html for
  // their meaning.
  setDefaults(CodeMirrorConfig, {
    stylesheet: [],
    path: "",
    parserfile: [],
    basefiles: ["util.js", "stringstream.js", "select.js", "undo.js", "editor.js", "tokenize.js"],
    iframeClass: null,
    passDelay: 200,
    passTime: 50,
    lineNumberDelay: 200,
    lineNumberTime: 50,
    continuousScanning: false,
    saveFunction: null,
    onLoad: null,
    onChange: null,
    undoDepth: 50,
    undoDelay: 800,
    disableSpellcheck: true,
    textWrapping: true,
    readOnly: false,
    width: "",
    height: "300px",
    minHeight: 100,
    autoMatchParens: false,
    markParen: null,
    unmarkParen: null,
    parserConfig: null,
    tabMode: "indent", // or "spaces", "default", "shift"
    enterMode: "indent", // or "keep", "flat"
    electricChars: true,
    reindentOnLoad: false,
    activeTokens: null,
    onCursorActivity: null,
    lineNumbers: false,
    firstLineNumber: 1,
    onLineNumberClick: null,
    indentUnit: 2,
    domain: null,
    noScriptCaching: false,
    incrementalLoading: false
  });

  function addLineNumberDiv(container, firstNum) {
    var nums = createHTMLElement("div"),
        scroller = createHTMLElement("div");
    nums.style.position = "absolute";
    nums.style.height = "100%";
    if (nums.style.setExpression) {
      try {nums.style.setExpression("height", "this.previousSibling.offsetHeight + 'px'");}
      catch(e) {} // Seems to throw 'Not Implemented' on some IE8 versions
    }
    nums.style.top = "0px";
    nums.style.left = "0px";
    nums.style.overflow = "hidden";
    container.appendChild(nums);
    scroller.className = "CodeMirror-line-numbers";
    nums.appendChild(scroller);
    scroller.innerHTML = "<div>" + firstNum + "</div>";
    return nums;
  }

  function frameHTML(options) {
    if (typeof options.parserfile == "string")
      options.parserfile = [options.parserfile];
    if (typeof options.basefiles == "string")
      options.basefiles = [options.basefiles];
    if (typeof options.stylesheet == "string")
      options.stylesheet = [options.stylesheet];

    var sp = " spellcheck=\"" + (options.disableSpellcheck ? "false" : "true") + "\"";
    var html = ["<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html" + sp + "><head>"];
    // Hack to work around a bunch of IE8-specific problems.
    html.push("<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE7\"/>");
    var queryStr = options.noScriptCaching ? "?nocache=" + new Date().getTime().toString(16) : "";
    forEach(options.stylesheet, function(file) {
      html.push("<link rel=\"stylesheet\" type=\"text/css\" href=\"" + file + queryStr + "\"/>");
    });
    forEach(options.basefiles.concat(options.parserfile), function(file) {
      if (!/^https?:/.test(file)) file = options.path + file;
      html.push("<script type=\"text/javascript\" src=\"" + file + queryStr + "\"><" + "/script>");
    });
    html.push("</head><body style=\"border-width: 0;\" class=\"editbox\"" + sp + "></body></html>");
    return html.join("");
  }

  var internetExplorer = document.selection && window.ActiveXObject && /MSIE/.test(navigator.userAgent);

  function CodeMirror(place, options) {
    // Use passed options, if any, to override defaults.
    this.options = options = options || {};
    setDefaults(options, CodeMirrorConfig);

    // Backward compatibility for deprecated options.
    if (options.dumbTabs) options.tabMode = "spaces";
    else if (options.normalTab) options.tabMode = "default";
    if (options.cursorActivity) options.onCursorActivity = options.cursorActivity;

    var frame = this.frame = createHTMLElement("iframe");
    if (options.iframeClass) frame.className = options.iframeClass;
    frame.frameBorder = 0;
    frame.style.border = "0";
    frame.style.width = '100%';
    frame.style.height = '100%';
    // display: block occasionally suppresses some Firefox bugs, so we
    // always add it, redundant as it sounds.
    frame.style.display = "block";

    var div = this.wrapping = createHTMLElement("div");
    div.style.position = "relative";
    div.className = "CodeMirror-wrapping";
    div.style.width = options.width;
    div.style.height = (options.height == "dynamic") ? options.minHeight + "px" : options.height;
    // This is used by Editor.reroutePasteEvent
    var teHack = this.textareaHack = createHTMLElement("textarea");
    div.appendChild(teHack);
    teHack.style.position = "absolute";
    teHack.style.left = "-10000px";
    teHack.style.width = "10px";
    teHack.tabIndex = 100000;

    // Link back to this object, so that the editor can fetch options
    // and add a reference to itself.
    frame.CodeMirror = this;
    if (options.domain && internetExplorer) {
      this.html = frameHTML(options);
      frame.src = "javascript:(function(){document.open();" +
        (options.domain ? "document.domain=\"" + options.domain + "\";" : "") +
        "document.write(window.frameElement.CodeMirror.html);document.close();})()";
    }
    else {
      frame.src = "javascript:;";
    }

    if (place.appendChild) place.appendChild(div);
    else place(div);
    div.appendChild(frame);
    if (options.lineNumbers) this.lineNumbers = addLineNumberDiv(div, options.firstLineNumber);

    this.win = frame.contentWindow;
    if (!options.domain || !internetExplorer) {
      this.win.document.open();
      this.win.document.write(frameHTML(options));
      this.win.document.close();
    }
  }

  CodeMirror.prototype = {
    init: function() {
      // Deprecated, but still supported.
      if (this.options.initCallback) this.options.initCallback(this);
      if (this.options.onLoad) this.options.onLoad(this);
      if (this.options.lineNumbers) this.activateLineNumbers();
      if (this.options.reindentOnLoad) this.reindent();
      if (this.options.height == "dynamic") this.setDynamicHeight();
    },

    getCode: function() {return this.editor.getCode();},
    setCode: function(code) {this.editor.importCode(code);},
    selection: function() {this.focusIfIE(); return this.editor.selectedText();},
    reindent: function() {this.editor.reindent();},
    reindentSelection: function() {this.focusIfIE(); this.editor.reindentSelection(null);},

    focusIfIE: function() {
      // in IE, a lot of selection-related functionality only works when the frame is focused
      if (this.win.select.ie_selection && document.activeElement != this.frame)
        this.focus();
    },
    focus: function() {
      this.win.focus();
      if (this.editor.selectionSnapshot) // IE hack
        this.win.select.setBookmark(this.win.document.body, this.editor.selectionSnapshot);
    },
    replaceSelection: function(text) {
      this.focus();
      this.editor.replaceSelection(text);
      return true;
    },
    replaceChars: function(text, start, end) {
      this.editor.replaceChars(text, start, end);
    },
    getSearchCursor: function(string, fromCursor, caseFold) {
      return this.editor.getSearchCursor(string, fromCursor, caseFold);
    },

    undo: function() {this.editor.history.undo();},
    redo: function() {this.editor.history.redo();},
    historySize: function() {return this.editor.history.historySize();},
    clearHistory: function() {this.editor.history.clear();},

    grabKeys: function(callback, filter) {this.editor.grabKeys(callback, filter);},
    ungrabKeys: function() {this.editor.ungrabKeys();},

    setParser: function(name, parserConfig) {this.editor.setParser(name, parserConfig);},
    setSpellcheck: function(on) {this.win.document.body.spellcheck = on;},
    setStylesheet: function(names) {
      if (typeof names === "string") names = [names];
      var activeStylesheets = {};
      var matchedNames = {};
      var links = this.win.document.getElementsByTagName("link");
      // Create hashes of active stylesheets and matched names.
      // This is O(n^2) but n is expected to be very small.
      for (var x = 0, link; link = links[x]; x++) {
        if (link.rel.indexOf("stylesheet") !== -1) {
          for (var y = 0; y < names.length; y++) {
            var name = names[y];
            if (link.href.substring(link.href.length - name.length) === name) {
              activeStylesheets[link.href] = true;
              matchedNames[name] = true;
            }
          }
        }
      }
      // Activate the selected stylesheets and disable the rest.
      for (var x = 0, link; link = links[x]; x++) {
        if (link.rel.indexOf("stylesheet") !== -1) {
          link.disabled = !(link.href in activeStylesheets);
        }
      }
      // Create any new stylesheets.
      for (var y = 0; y < names.length; y++) {
        var name = names[y];
        if (!(name in matchedNames)) {
          var link = this.win.document.createElement("link");
          link.rel = "stylesheet";
          link.type = "text/css";
          link.href = name;
          this.win.document.getElementsByTagName('head')[0].appendChild(link);
        }
      }
    },
    setTextWrapping: function(on) {
      if (on == this.options.textWrapping) return;
      this.win.document.body.style.whiteSpace = on ? "" : "nowrap";
      this.options.textWrapping = on;
      if (this.lineNumbers) {
        this.setLineNumbers(false);
        this.setLineNumbers(true);
      }
    },
    setIndentUnit: function(unit) {this.win.indentUnit = unit;},
    setUndoDepth: function(depth) {this.editor.history.maxDepth = depth;},
    setTabMode: function(mode) {this.options.tabMode = mode;},
    setEnterMode: function(mode) {this.options.enterMode = mode;},
    setLineNumbers: function(on) {
      if (on && !this.lineNumbers) {
        this.lineNumbers = addLineNumberDiv(this.wrapping,this.options.firstLineNumber);
        this.activateLineNumbers();
      }
      else if (!on && this.lineNumbers) {
        this.wrapping.removeChild(this.lineNumbers);
        this.wrapping.style.paddingLeft = "";
        this.lineNumbers = null;
      }
    },

    cursorPosition: function(start) {this.focusIfIE(); return this.editor.cursorPosition(start);},
    firstLine: function() {return this.editor.firstLine();},
    lastLine: function() {return this.editor.lastLine();},
    nextLine: function(line) {return this.editor.nextLine(line);},
    prevLine: function(line) {return this.editor.prevLine(line);},
    lineContent: function(line) {return this.editor.lineContent(line);},
    setLineContent: function(line, content) {this.editor.setLineContent(line, content);},
    removeLine: function(line){this.editor.removeLine(line);},
    insertIntoLine: function(line, position, content) {this.editor.insertIntoLine(line, position, content);},
    selectLines: function(startLine, startOffset, endLine, endOffset) {
      this.win.focus();
      this.editor.selectLines(startLine, startOffset, endLine, endOffset);
    },
    nthLine: function(n) {
      var line = this.firstLine();
      for (; n > 1 && line !== false; n--)
        line = this.nextLine(line);
      return line;
    },
    lineNumber: function(line) {
      var num = 0;
      while (line !== false) {
        num++;
        line = this.prevLine(line);
      }
      return num;
    },
    jumpToLine: function(line) {
      if (typeof line == "number") line = this.nthLine(line);
      this.selectLines(line, 0);
      this.win.focus();
    },
    currentLine: function() { // Deprecated, but still there for backward compatibility
      return this.lineNumber(this.cursorLine());
    },
    cursorLine: function() {
      return this.cursorPosition().line;
    },
    cursorCoords: function(start) {return this.editor.cursorCoords(start);},

    activateLineNumbers: function() {
      var frame = this.frame, win = frame.contentWindow, doc = win.document, body = doc.body,
          nums = this.lineNumbers, scroller = nums.firstChild, self = this;
      var barWidth = null;

      nums.onclick = function(e) {
        var handler = self.options.onLineNumberClick;
        if (handler) {
          var div = (e || window.event).target || (e || window.event).srcElement;
          var num = div == nums ? NaN : Number(div.innerHTML);
          if (!isNaN(num)) handler(num, div);
        }
      };

      function sizeBar() {
        if (frame.offsetWidth == 0) return;
        for (var root = frame; root.parentNode; root = root.parentNode){}
        if (!nums.parentNode || root != document || !win.Editor) {
          // Clear event handlers (their nodes might already be collected, so try/catch)
          try{clear();}catch(e){}
          clearInterval(sizeInterval);
          return;
        }

        if (nums.offsetWidth != barWidth) {
          barWidth = nums.offsetWidth;
          frame.parentNode.style.paddingLeft = barWidth + "px";
        }
      }
      function doScroll() {
        nums.scrollTop = body.scrollTop || doc.documentElement.scrollTop || 0;
      }
      // Cleanup function, registered by nonWrapping and wrapping.
      var clear = function(){};
      sizeBar();
      var sizeInterval = setInterval(sizeBar, 500);

      function ensureEnoughLineNumbers(fill) {
        var lineHeight = scroller.firstChild.offsetHeight;
        if (lineHeight == 0) return;
        var targetHeight = 50 + Math.max(body.offsetHeight, Math.max(frame.offsetHeight, body.scrollHeight || 0)),
            lastNumber = Math.ceil(targetHeight / lineHeight);
        for (var i = scroller.childNodes.length; i <= lastNumber; i++) {
          var div = createHTMLElement("div");
          div.appendChild(document.createTextNode(fill ? String(i + self.options.firstLineNumber) : "\u00a0"));
          scroller.appendChild(div);
        }
      }

      function nonWrapping() {
        function update() {
          ensureEnoughLineNumbers(true);
          doScroll();
        }
        self.updateNumbers = update;
        var onScroll = win.addEventHandler(win, "scroll", doScroll, true),
            onResize = win.addEventHandler(win, "resize", update, true);
        clear = function(){
          onScroll(); onResize();
          if (self.updateNumbers == update) self.updateNumbers = null;
        };
        update();
      }

      function wrapping() {
        var node, lineNum, next, pos, changes = [], styleNums = self.options.styleNumbers;

        function setNum(n, node) {
          // Does not typically happen (but can, if you mess with the
          // document during the numbering)
          if (!lineNum) lineNum = scroller.appendChild(createHTMLElement("div"));
          if (styleNums) styleNums(lineNum, node, n);
          // Changes are accumulated, so that the document layout
          // doesn't have to be recomputed during the pass
          changes.push(lineNum); changes.push(n);
          pos = lineNum.offsetHeight + lineNum.offsetTop;
          lineNum = lineNum.nextSibling;
        }
        function commitChanges() {
          for (var i = 0; i < changes.length; i += 2)
            changes[i].innerHTML = changes[i + 1];
          changes = [];
        }
        function work() {
          if (!scroller.parentNode || scroller.parentNode != self.lineNumbers) return;

          var endTime = new Date().getTime() + self.options.lineNumberTime;
          while (node) {
            setNum(next++, node.previousSibling);
            for (; node && !win.isBR(node); node = node.nextSibling) {
              var bott = node.offsetTop + node.offsetHeight;
              while (scroller.offsetHeight && bott - 3 > pos) {
                var oldPos = pos;
                setNum("&nbsp;");
                if (pos <= oldPos) break;
              }
            }
            if (node) node = node.nextSibling;
            if (new Date().getTime() > endTime) {
              commitChanges();
              pending = setTimeout(work, self.options.lineNumberDelay);
              return;
            }
          }
          while (lineNum) setNum(next++);
          commitChanges();
          doScroll();
        }
        function start(firstTime) {
          doScroll();
          ensureEnoughLineNumbers(firstTime);
          node = body.firstChild;
          lineNum = scroller.firstChild;
          pos = 0;
          next = self.options.firstLineNumber;
          work();
        }

        start(true);
        var pending = null;
        function update() {
          if (pending) clearTimeout(pending);
          if (self.editor.allClean()) start();
          else pending = setTimeout(update, 200);
        }
        self.updateNumbers = update;
        var onScroll = win.addEventHandler(win, "scroll", doScroll, true),
            onResize = win.addEventHandler(win, "resize", update, true);
        clear = function(){
          if (pending) clearTimeout(pending);
          if (self.updateNumbers == update) self.updateNumbers = null;
          onScroll();
          onResize();
        };
      }
      (this.options.textWrapping || this.options.styleNumbers ? wrapping : nonWrapping)();
    },

    setDynamicHeight: function() {
      var self = this, activity = self.options.onCursorActivity, win = self.win, body = win.document.body,
          lineHeight = null, timeout = null, vmargin = 2 * self.frame.offsetTop;
      body.style.overflowY = "hidden";
      win.document.documentElement.style.overflowY = "hidden";
      this.frame.scrolling = "no";

      function updateHeight() {
        var trailingLines = 0, node = body.lastChild, computedHeight;
        while (node && win.isBR(node)) {
          if (!node.hackBR) trailingLines++;
          node = node.previousSibling;
        }
        if (node) {
          lineHeight = node.offsetHeight;
          computedHeight = node.offsetTop + (1 + trailingLines) * lineHeight;
        }
        else if (lineHeight) {
          computedHeight = trailingLines * lineHeight;
        }
        if (computedHeight)
          self.wrapping.style.height = Math.max(vmargin + computedHeight, self.options.minHeight) + "px";
      }
      setTimeout(updateHeight, 300);
      self.options.onCursorActivity = function(x) {
        if (activity) activity(x);
        clearTimeout(timeout);
        timeout = setTimeout(updateHeight, 100);
      };
    }
  };

  CodeMirror.InvalidLineHandle = {toString: function(){return "CodeMirror.InvalidLineHandle";}};

  CodeMirror.replace = function(element) {
    if (typeof element == "string")
      element = document.getElementById(element);
    return function(newElement) {
      element.parentNode.replaceChild(newElement, element);
    };
  };

  CodeMirror.fromTextArea = function(area, options) {
    if (typeof area == "string")
      area = document.getElementById(area);

    options = options || {};
    if (area.style.width && options.width == null)
      options.width = area.style.width;
    if (area.style.height && options.height == null)
      options.height = area.style.height;
    if (options.content == null) options.content = area.value;

    function updateField() {
      area.value = mirror.getCode();
    }
    if (area.form) {
      if (typeof area.form.addEventListener == "function")
        area.form.addEventListener("submit", updateField, false);
      else
        area.form.attachEvent("onsubmit", updateField);
      var realSubmit = area.form.submit;
      function wrapSubmit() {
        updateField();
        // Can't use realSubmit.apply because IE6 is too stupid
        area.form.submit = realSubmit;
        area.form.submit();
        area.form.submit = wrapSubmit;
      }
      try {area.form.submit = wrapSubmit;} catch(e){}
    }

    function insert(frame) {
      if (area.nextSibling)
        area.parentNode.insertBefore(frame, area.nextSibling);
      else
        area.parentNode.appendChild(frame);
    }

    area.style.display = "none";
    var mirror = new CodeMirror(insert, options);
    mirror.save = updateField;
    mirror.toTextArea = function() {
      updateField();
      area.parentNode.removeChild(mirror.wrapping);
      area.style.display = "";
      if (area.form) {
        try {area.form.submit = realSubmit;} catch(e) {}
        if (typeof area.form.removeEventListener == "function")
          area.form.removeEventListener("submit", updateField, false);
        else
          area.form.detachEvent("onsubmit", updateField);
      }
    };

    return mirror;
  };

  CodeMirror.isProbablySupported = function() {
    // This is rather awful, but can be useful.
    var match;
    if (window.opera)
      return Number(window.opera.version()) >= 9.52;
    else if (/Apple Computer, Inc/.test(navigator.vendor) && (match = navigator.userAgent.match(/Version\/(\d+(?:\.\d+)?)\./)))
      return Number(match[1]) >= 3;
    else if (document.selection && window.ActiveXObject && (match = navigator.userAgent.match(/MSIE (\d+(?:\.\d*)?)\b/)))
      return Number(match[1]) >= 6;
    else if (match = navigator.userAgent.match(/gecko\/(\d{8})/i))
      return Number(match[1]) >= 20050901;
    else if (match = navigator.userAgent.match(/AppleWebKit\/(\d+)/))
      return Number(match[1]) >= 525;
    else
      return null;
  };

  return CodeMirror;
})();

/***************************************************************
*  Copyright notice
*
*  (c) 2007-2010 Tobias Liebig <mail_typo3@etobi.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/* t3editor.js uses the Codemirror editor.
 */

T3editor = T3editor || {};

// collection of all t3editor instances on the current page
T3editor.instances = {};

// path to the editor ext dir
// can be overwritten in class.tx_t3editor.php
T3editor.PATH_t3e = "../../../sysext/t3editor/";
T3editor.PATH_codemirror = "../../../contrib/codemirror/js/";


function T3editor(textarea) {
	var self = this;

		// memorize the textarea
	this.textarea = $(textarea);
	var textareaDim = $(this.textarea).getDimensions();
	this.textarea.hide();

		// outer wrap around the whole t3editor
	this.outerdiv = new Element("DIV", {
		"class": "t3e_wrap"
	});

		// place the div before the textarea
	this.textarea.parentNode.insertBefore(this.outerdiv, $(this.textarea));

	this.outerdiv.update(T3editor.template);

	this.modalOverlay = this.outerdiv.down('.t3e_modalOverlay');
	this.modalOverlay.setStyle(this.outerdiv.getDimensions());
	this.modalOverlay.setStyle({
		opacity: 0.8
	});

	this.mirror_wrap = this.outerdiv.down('.t3e_iframe_wrap');
	this.statusbar_wrap = this.outerdiv.down('.t3e_statusbar_wrap');
	this.statusbar_title = this.outerdiv.down('.t3e_statusbar_title');
	this.statusbar_status = this.outerdiv.down('.t3e_statusbar_status');

	this.statusbar_title.update( this.textarea.readAttribute('alt') );
	this.statusbar_status.update( '' );

		// setting options
	var options = {
		height: ( textareaDim.height ) + 'px',
		width: ( textareaDim.width + this.adjustWidth ) + 'px',
		content: $(this.textarea).value,
		parserfile: T3editor.parserfile,
		stylesheet: T3editor.stylesheet,
		path: T3editor.PATH_codemirror,
		outerEditor: this,
		saveFunction: this.saveFunction.bind(this),
		initCallback: this.init.bind(this),
		autoMatchParens: true,
		lineNumbers: true,
		onChange: this.onChange.bind(this)
	};

		// get the editor
	this.mirror = new CodeMirror(this.mirror_wrap, options);
	$(this.outerdiv).fire('t3editor:init', {t3editor: this});
}

T3editor.prototype = {
		saveFunctionEvent: null,
		saveButtons: null,
		updateTextareaEvent: null,
		adjustWidth: -30,
		disabled: false,

		init: function() {
			var textareaDim = $(this.textarea).getDimensions();
			// hide the textarea
			this.textarea.hide();

			this.attachEvents();
			this.resize(textareaDim.width + this.adjustWidth, textareaDim.height );

			this.modalOverlay.hide();
			$(this.outerdiv).fire('t3editor:initFinished', {t3editor: this});
		},

		attachEvents: function() {
			var that = this;

			// get the form object
			var form = $(this.textarea.form);
			this.saveButtons = form.getInputs('image', 'submit');

			// initialize ajax saving events
			this.saveFunctionEvent = this.saveFunction.bind(this);
			this.saveButtons.each(function(button) {
				Event.observe(button,'click',this.saveFunctionEvent);
			}.bind(this));

			this.updateTextareaEvent = this.updateTextarea.bind(this);

			Event.observe($(this.textarea.form), 'submit', this.updateTextareaEvent);

			Event.observe(this.mirror.win.document, 'keyup', function(event) {
				$(that.outerdiv).fire('t3editor:keyup', {t3editor: that, actualEvent: event});
			});
			Event.observe(this.mirror.win.document, 'keydown', function(event) {
				$(that.outerdiv).fire('t3editor:keydown', {t3editor: that, actualEvent: event});

				if ((event.ctrlKey || event.metaKey) && event.keyCode == 122) {
					that.toggleFullscreen();
					event.stop();
				}
			});
			Event.observe(this.mirror.win.document, 'click', function(event) {
				$(that.outerdiv).fire('t3editor:click', {t3editor: that, actualEvent: event});
			});
		},

		// indicates is content is modified and not safed yet
		textModified: false,

		// check if code in editor has been modified since last saving
		checkTextModified: function() {
			if (!this.textModified) {
				this.textModified = true;
			}
		},

		updateTextarea: function(event) {
			this.textarea.value = this.mirror.getCode();
		},

		onChange: function() {
			var that = this;
			this.checkTextModified();
			$(that.outerdiv).fire('t3editor:change', {t3editor: that});
		},

		saveFunction: function(event) {
			if (!T3editor.ajaxSavetype || T3editor.ajaxSavetype == '') {
				return;
			}
			this.modalOverlay.show();
			this.updateTextarea(event);

			if (event) {
				Event.stop(event);
			}

			var params = $(this.textarea.form).serialize(true);
			params = Object.extend( {t3editor_disableEditor: 'false'}, params);

			$(this.outerdiv).fire('t3editor:save', {parameters: params, t3editor: this});

		},

		// callback if saving was successful
		saveFunctionComplete: function(wasSuccessful,returnedData) {
			if (wasSuccessful) {
				this.textModified = false;
			} else {
				if (typeof returnedData.exceptionMessage != 'undefined') {
					TYPO3.Flashmessage.display(4, T3editor.lang.errorWhileSaving[0]['target'], returnedData.exceptionMessage);
				} else {
					TYPO3.Flashmessage.display(4, T3editor.lang.errorWhileSaving[0]['target']);
				}
			}
			this.modalOverlay.hide();
		},

		// toggle between the textarea and t3editor
		toggleView: function(disable) {
			$(this.outerdiv).fire('t3editor:toggleView', {t3editor: this, disable: disable});
			this.disabled = disable;
			if (disable) {
				this.textarea.value = this.mirror.editor.getCode();
				this.outerdiv.hide();
				this.textarea.show();
				this.saveButtons.each(function(button) {
					Event.stopObserving(button,'click',this.saveFunctionEvent);
				}.bind(this));
				Event.stopObserving($(this.textarea.form), 'submit', this.updateTextareaEvent);

			} else {
				this.mirror.editor.importCode(this.textarea.value);
				this.textarea.hide();
				this.outerdiv.show();
				this.saveButtons.each(function(button) {
					this.saveFunctionEvent = this.saveFunction.bind(this);
					Event.observe(button,'click',this.saveFunctionEvent);
				}.bind(this));
				Event.observe($(this.textarea.form), 'submit', this.updateTextareaEvent);
			}
		},

		resize: function(width, height) {
			if (this.outerdiv) {
				newheight = (height - 1);
				newwidth = (width + 11);
				if (Prototype.Browser.IE) newwidth = newwidth + 8;

				$(this.outerdiv).setStyle({
					height: newheight + 'px',
					width: newwidth + 'px'
				});

				$(this.mirror_wrap.firstChild).setStyle({
					'height': ((height - 22) + 'px'),
					'width': ((width - 13) + 'px')
				});

				$(this.modalOverlay).setStyle(this.outerdiv.getDimensions());

			}

		},

		// toggle between normal view and fullscreen mode
		toggleFullscreen: function() {
			if (this.outerdiv.hasClassName('t3e_fullscreen')) {
				// turn fullscreen off

				// unhide the scrollbar of the body
				this.outerdiv.offsetParent.setStyle({
					overflow: ''
				});

				this.outerdiv.removeClassName('t3e_fullscreen');
				h = this.textarea.getDimensions().height;
				w = this.textarea.getDimensions().width;

			} else {
					// turn fullscreen on
				this.outerdiv.addClassName('t3e_fullscreen');
				h = this.outerdiv.offsetParent.getHeight();
				w = this.outerdiv.offsetParent.getWidth();

				// less scrollbar width
				w = w - 13;

				// hide the scrollbar of the body
				this.outerdiv.offsetParent.setStyle({
					overflow: 'hidden'
				});
				this.outerdiv.offsetParent.scrollTop = 0;
			}
			this.resize(w, h);
		}

} // T3editor.prototype


// ------------------------------------------------------------------------


/**
 * toggle between enhanced editor (t3editor) and simple textarea
 */
T3editor.toggleEditor = function(checkbox, index) {
	if (!Prototype.Browser.MobileSafari) {
		if (index == undefined) {
			if (top.TYPO3.BackendUserSettings) {
				top.TYPO3.BackendUserSettings.ExtDirect.set(
					'disableT3Editor',
					checkbox.checked,
					function(response) {}
				);
			}
			$$('textarea.t3editor').each(
				function(textarea, i) {
					T3editor.toggleEditor(checkbox, i);
				}
			);
		} else {
			if (T3editor.instances[index] != undefined) {
				var t3e = T3editor.instances[index];
				t3e.toggleView(checkbox.checked);
			} else if (!checkbox.checked) {
				var t3e = new T3editor($$('textarea.t3editor')[index], index);
				T3editor.instances[index] = t3e;
			}
		}
	}
}

// ------------------------------------------------------------------------

if (!Prototype.Browser.MobileSafari) {
	// everything ready: turn textarea's into fancy editors
	Event.observe(window, 'load',
		function() {
			$$('textarea.t3editor').each(
				function(textarea, i) {
					if ($('t3editor_disableEditor_' + (i + 1) + '_checkbox')
					&& !$('t3editor_disableEditor_' + (i + 1) + '_checkbox').checked) {
						var t3e = new T3editor(textarea);
						T3editor.instances[i] = t3e;
					}
				}
			);

			if (T3editor.ajaxSavetype != "") {
				Event.observe(document, 't3editor:save',
					function(event) {
						var params = Object.extend({
							ajaxID: "T3Editor::saveCode",
							t3editor_savetype: T3editor.ajaxSavetype
						}, event.memo.parameters);

						new Ajax.Request(
							T3editor.URL_typo3 + "ajax.php", {
								parameters: params,
								onComplete: function(ajaxrequest) {
									var wasSuccessful = ajaxrequest.status == 200
									&& ajaxrequest.headerJSON.result == true
									event.memo.t3editor.saveFunctionComplete(wasSuccessful,ajaxrequest.headerJSON);
								}
							}
						);
					}
				);
			}
		}
	);
}
/**
*
*  MD5 (Message-Digest Algorithm)
*  http://www.webtoolkit.info/
*
**/

function MD5(string) {

	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}

	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}

 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }

	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};

	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};

	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	};

	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;

	string = Utf8Encode(string);

	x = ConvertToWordArray(string);

	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}

	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

	return temp.toLowerCase();
}
/**
 *
 * Ext.ux.EventZone Extension Class for Ext 3.x Library
 *
 * @author  Nigel White
 *
 * @license Ext.ux.EventZone is licensed under the terms of
 * the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
 * that the code/component(s) do NOT become part of another Open Source or Commercially
 * licensed development library or toolkit without explicit permission.
 *
 * License details: http://www.gnu.org/licenses/lgpl.html
 *
 * @class Ext.ux.EventZone
 * <p>This class implements a "virtual element" at a relative size and position
 * <i>within</i> an existing element. It provides mouse events from a zone of an element of
 * defined dimensions.</p>
 * <p>The zone is defined using <code>top</code>, <code>right</code>, <code>bottom</code>,
 * <code>left</code>, <code>width</code> and <code>height</code> options which specify
 * the bounds of the zone in a similar manner to the CSS style properties of those names.</p>
 * @cfg {String|HtmlElement} el The element in which to create the zone.
 * @cfg {Array} points An Array of points within the element defining the event zone.
 * @cfg {Number} top The top of the zone. If negative means an offset from the bottom.
 * @cfg {Number} right The right of the zone. If negative means an offset from the right.
 * @cfg {Number} left The left of the zone. If negative means an offset from the right.
 * @cfg {Number} bottom The bottom of the zone. If negative means an offset from the bottom.
 * @cfg {Number} width The width of the zone.
 * @cfg {Number} height The height of the zone.
 * @constructor
 * Create a new EventZone
 * @param {Object} config The config object.
 */
Ext.ux.EventZone = Ext.extend(Ext.util.Observable, {

    constructor: function(config) {
        this.initialConfig = config;
        this.addEvents(
            /**
             * @event mouseenter
             * This event fires when the mouse enters the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'mouseenter',
            /**
             * @event mousedown
             * This event fires when the mouse button is depressed within the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'mousedown',
            /**
             * @event mousemove
             * This event fires when the mouse moves within the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'mousemove',
            /**
             * @event mouseup
             * This event fires when the mouse button is released within the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'mouseup',
            /**
             * @event mouseenter
             * This event fires when the mouse is clicked within the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'click',
            /**
             * @event mouseleave
             * This event fires when the mouse leaves the zone.
             * @param {EventObject} e the underlying mouse event.
             * @param {EventZone} this
             */
            'mouseleave'
        );
        Ext.apply(this, config);
        this.el = Ext.get(this.el);

//      If a polygon within the element is specified...
        if (this.points) {
            this.polygon = new Ext.lib.Polygon(this.points);
            this.points = this.polygon.points;
        }

        Ext.ux.EventZone.superclass.constructor.call(this);
        this.el.on({
            mouseenter: this.handleMouseEvent,
            mousedown: this.handleMouseEvent,
            mousemove: this.handleMouseEvent,
            mouseup: this.handleMouseEvent,
            click: this.handleMouseEvent,
            mouseleave: this.handleMouseEvent,
            scope: this
        });
    },

    handleMouseEvent: function(e) {
        var r = this.polygon ? this.getPolygon() : this.getRegion();
        var inBounds = r.contains(e.getPoint());

        switch (e.type) {
            // mouseenter fires this
            case 'mouseover':
               if (inBounds) {
                   this.mouseIn = true;
                   this.fireEvent('mouseenter', e, this);
               }
               break;
            // mouseleave fires this
            case 'mouseout':
               this.mouseIn = false;
               this.fireEvent('mouseleave', e, this);
               break;
           case 'mousemove':
               if (inBounds) {
                   if (this.mouseIn) {
                       this.fireEvent('mousemove', e, this);
                   } else {
                       this.mouseIn = true;
                       this.fireEvent('mouseenter', e, this);
                   }
               } else {
                   if (this.mouseIn) {
                       this.mouseIn = false;
                       this.fireEvent('mouseleave', e, this);
                   }
               }
               break;
           default:
               if (inBounds) {
                   this.fireEvent(e.type, e, this);
               }
        }
    },

    getPolygon: function() {
        var xy = this.el.getXY();
        return this.polygon.translate(xy[0], xy[1]);
    },

    getRegion: function() {
        var r = this.el.getRegion();

//      Adjust left boundary of region
        if (Ext.isNumber(this.left)) {
            if (this.left < 0) {
                r.left = r.right + this.left;
            } else {
                r.left += this.left;
            }
        }

//      Adjust right boundary of region
        if (Ext.isNumber(this.width)) {
            r.right = r.left + this.width;
        } else if (Ext.isNumber(this.right)) {
            r.right = (this.right < 0) ? r.right + this.right : r.left + this.right;
        }

//      Adjust top boundary of region
        if (Ext.isNumber(this.top)) {
            if (this.top < 0) {
                r.top = r.bottom + this.top;
            } else {
                r.top += this.top;
            }
        }

//      Adjust bottom boundary of region
        if (Ext.isNumber(this.height)) {
            r.bottom = r.top + this.height;
        } else if (Ext.isNumber(this.bottom)) {
            r.bottom = (this.bottom < 0) ? r.bottom + this.bottom : r.top + this.bottom;
        }

        return r;
    }
});

/**
 * @class Ext.lib.Polygon
 * <p>This class encapsulates an absolute area of the document bounded by a list of points.</p>
 * @constructor
 * Create a new Polygon
 * @param {Object} points An Array of <code>[n,n]</code> point specification Arrays, or
 * an Array of Ext.lib.Points, or an HtmlElement, or an Ext.lib.Region.
 */
Ext.lib.Polygon = Ext.extend(Ext.lib.Region, {
    constructor: function(points) {
        var i, l, el;
        if (l = points.length) {
            if (points[0].x) {
                for (i = 0; i < l; i++) {
                    points[i] = [ points[i].x, points[i].y ];
                }
            }
            this.points = points;
        } else {
            if (el = Ext.get(points)) {
                points = Ext.lib.Region.getRegion(el.dom);
            }
            if (points instanceof Ext.lib.Region) {
                this.points = [
                    [points.left, points.top],
                    [points.right, points.top],
                    [points.right, points.bottom],
                    [points.left, points.bottom]
                ];
            }
        }
    },

    /**
     * Returns a new Polygon translated by the specified <code>X</code> and <code>Y</code> increments.
     * @param xDelta {Number} The <code>X</code> translation increment.
     * @param xDelta {Number} The <code>Y</code> translation increment.
     * @return {Polygon} The resulting Polygon.
     */
    translate: function(xDelta, yDelta) {
        var r = [], p = this.points, l = p.length, i;
        for (i = 0; i < l; i++) {
            r[i] = [ p[i][0] + xDelta, p[i][1] + yDelta ];
        }
        return new Ext.lib.Polygon(r);
    },

    /**
     * Returns the area of this Polygon.
     */
    getArea: function() {
        var p = this.points, l = p.length, area = 0, i, j = 0;
        for (i = 0; i < l; i++) {
            j++;
            if (j == l) {
                j = 0;
            }
            area += (p[i][0] + p[j][0]) * (p[i][1] - p[j][1]);
        }
        return area * 0.5;
    },

    /**
     * Returns <code>true</code> if this Polygon contains the specified point. Thanks
     * to http://www.ecse.rpi.edu/Homepages/wrf/Research/Short_Notes/pnpoly.html for the algorithm.
     * @param pt {Point|Number} Either an Ext.lib.Point object, or the <code>X</code> coordinate to test.
     * @param py {Number} <b>Optional.</b> If the first parameter was an <code>X</code> coordinate, this is the <code>Y</code> coordinate.
     */
    contains: function(pt, py) {
        var f = (arguments.length == 1),
            testX = f ? pt.x : pt,
            testY = f ? pt.y : py,
            p = this.points,
            nvert = p.length,
            j = nvert - 1,
            i, j, c = false;
        for (i = 0; i < nvert; j = i++) {
            if ( ((p[i][1] > testY) != (p[j][1] > testY)) &&
             (testX < (p[j][0]-p[i][0]) * (testY-p[i][1]) / (p[j][1]-p[i][1]) + p[i][0])) {
                c = !c;
            }
        }
        return c;
    }
});

/**
 * @class Ext.Resizable
 * @extends Ext.util.Observable
 * This is an override of Ext.Resizable to make usage of the Ex.ux.EventZone
 * <p>Applies virtual drag handles to an element to make it resizable.</p>
 * <p>Here is the list of valid resize handles:</p>
 * <pre>
Value   Description
------  -------------------
 'n'     north
 's'     south
 'e'     east
 'w'     west
 'nw'    northwest
 'sw'    southwest
 'se'    southeast
 'ne'    northeast
 'all'   all
</pre>
 * <p>Here's an example showing the creation of a typical Resizable:</p>
 * <pre><code>
var resizer = new Ext.Resizable('element-id', {
    handles: 'all',
    minWidth: 200,
    minHeight: 100,
    maxWidth: 500,
    maxHeight: 400,
    pinned: true
});
resizer.on('resize', myHandler);
</code></pre>
 * <p>To hide a particular handle, set its display to none in CSS, or through script:<br>
 * resizer.east.setDisplayed(false);</p>
 * @constructor
 * Create a new resizable component
 * @param {Mixed} el The id or element to resize
 * @param {Object} config configuration options
  */
Ext.Resizable = function(el, config){
    this.el = Ext.get(el);

    /**
     * The proxy Element that is resized in place of the real Element during the resize operation.
     * This may be queried using {@link Ext.Element#getBox} to provide the new area to resize to.
     * Read only.
     * @type Ext.Element.
     * @property proxy
     */
    this.proxy = this.el.createProxy({tag: 'div', cls: 'x-resizable-proxy', id: this.el.id + '-rzproxy'}, Ext.getBody());
    this.proxy.unselectable();
    this.proxy.enableDisplayMode('block');

    Ext.apply(this, config);

    if(this.pinned){
        this.disableTrackOver = true;
        this.el.addClass('x-resizable-pinned');
    }
    // if the element isn't positioned, make it relative
    var position = this.el.getStyle('position');
    if(position != 'absolute' && position != 'fixed'){
        this.el.setStyle('position', 'relative');
    }
    if(!this.handles){ // no handles passed, must be legacy style
        this.handles = 's,e,se';
        if(this.multiDirectional){
            this.handles += ',n,w';
        }
    }
    if(this.handles == 'all'){
        this.handles = 'n s e w ne nw se sw';
    }
    var hs = this.handles.split(/\s*?[,;]\s*?| /);
    var ps = Ext.Resizable.positions;
    for(var i = 0, len = hs.length; i < len; i++){
        if(hs[i] && ps[hs[i]]){
            var pos = ps[hs[i]];
            this[pos] = new Ext.Resizable.Handle(this, pos);
        }
    }
    // legacy
    this.corner = this.southeast;

    if(this.handles.indexOf('n') != -1 || this.handles.indexOf('w') != -1){
        this.updateBox = true;
    }
    this.activeHandle = null;

    if(this.adjustments == 'auto'){
        var hw = this.west, he = this.east, hn = this.north, hs = this.south;
        this.adjustments = [
            (he.el ? -he.el.getWidth() : 0) + (hw.el ? -hw.el.getWidth() : 0),
            (hn.el ? -hn.el.getHeight() : 0) + (hs.el ? -hs.el.getHeight() : 0) -1
        ];
    }

    if(this.draggable){
        this.dd = this.dynamic ?
            this.el.initDD(null) : this.el.initDDProxy(null, {dragElId: this.proxy.id});
        this.dd.setHandleElId(this.el.id);
    }

    this.addEvents(
        /**
         * @event beforeresize
         * Fired before resize is allowed. Set {@link #enabled} to false to cancel resize.
         * @param {Ext.Resizable} this
         * @param {Ext.EventObject} e The mousedown event
         */
        'beforeresize',
        /**
         * @event resize
         * Fired after a resize.
         * @param {Ext.Resizable} this
         * @param {Number} width The new width
         * @param {Number} height The new height
         * @param {Ext.EventObject} e The mouseup event
         */
        'resize'
    );

    if(this.width !== null && this.height !== null){
        this.resizeTo(this.width, this.height);
    }
    if(Ext.isIE){
        this.el.dom.style.zoom = 1;
    }
    Ext.Resizable.superclass.constructor.call(this);
};

Ext.extend(Ext.Resizable, Ext.util.Observable, {

    /**
     * @cfg {Array/String} adjustments String 'auto' or an array [width, height] with values to be <b>added</b> to the
     * resize operation's new size (defaults to <tt>[0, 0]</tt>)
     */
    adjustments : [0, 0],
    /**
     * @cfg {Boolean} animate True to animate the resize (not compatible with dynamic sizing, defaults to false)
     */
    animate : false,
    /**
     * @cfg {Mixed} constrainTo Constrain the resize to a particular element
     */
    /**
     * @cfg {Boolean} draggable Convenience to initialize drag drop (defaults to false)
     */
    draggable: false,
    /**
     * @cfg {Number} duration Animation duration if animate = true (defaults to 0.35)
     */
    duration : 0.35,
    /**
     * @cfg {Boolean} dynamic True to resize the element while dragging instead of using a proxy (defaults to false)
     */
    dynamic : false,
    /**
     * @cfg {String} easing Animation easing if animate = true (defaults to <tt>'easingOutStrong'</tt>)
     */
    easing : 'easeOutStrong',
    /**
     * @cfg {Boolean} enabled False to disable resizing (defaults to true)
     */
    enabled : true,
    /**
     * @property enabled Writable. False if resizing is disabled.
     * @type Boolean
     */
    /**
     * @cfg {String} handles String consisting of the resize handles to display (defaults to undefined).
     * Specify either <tt>'all'</tt> or any of <tt>'n s e w ne nw se sw'</tt>.
     */
    handles : false,
    /**
     * @cfg {Boolean} multiDirectional <b>Deprecated</b>.  Deprecated style of adding multi-direction resize handles.
     */
    multiDirectional : false,
    /**
     * @cfg {Number} height The height of the element in pixels (defaults to null)
     */
    height : null,
    /**
     * @cfg {Number} width The width of the element in pixels (defaults to null)
     */
    width : null,
    /**
     * @cfg {Number} heightIncrement The increment to snap the height resize in pixels
     * (only applies if <code>{@link #dynamic}==true</code>). Defaults to <tt>0</tt>.
     */
    heightIncrement : 0,
    /**
     * @cfg {Number} widthIncrement The increment to snap the width resize in pixels
     * (only applies if <code>{@link #dynamic}==true</code>). Defaults to <tt>0</tt>.
     */
    widthIncrement : 0,
    /**
     * @cfg {Number} minHeight The minimum height for the element (defaults to 5)
     */
    minHeight : 5,
    /**
     * @cfg {Number} minWidth The minimum width for the element (defaults to 5)
     */
    minWidth : 5,
    /**
     * @cfg {Number} maxHeight The maximum height for the element (defaults to 10000)
     */
    maxHeight : 10000,
    /**
     * @cfg {Number} maxWidth The maximum width for the element (defaults to 10000)
     */
    maxWidth : 10000,
    /**
     * @cfg {Number} minX The minimum x for the element (defaults to 0)
     */
    minX: 0,
    /**
     * @cfg {Number} minY The minimum x for the element (defaults to 0)
     */
    minY: 0,
    /**
     * @cfg {Boolean} pinned True to ensure that the resize handles are always visible, false to display them only when the
     * user mouses over the resizable borders. This is only applied at config time. (defaults to false)
     */
    pinned : false,
    /**
     * @cfg {Boolean} preserveRatio True to preserve the original ratio between height
     * and width during resize (defaults to false)
     */
    preserveRatio : false,
    /**
     * @cfg {Ext.lib.Region} resizeRegion Constrain the resize to a particular region
     */


    /**
     * Perform a manual resize and fires the 'resize' event.
     * @param {Number} width
     * @param {Number} height
     */
    resizeTo : function(width, height){
        this.el.setSize(width, height);
        this.fireEvent('resize', this, width, height, null);
    },

    // private
    startSizing : function(e, handle){
        this.fireEvent('beforeresize', this, e);
        if(this.enabled){ // 2nd enabled check in case disabled before beforeresize handler
            e.stopEvent();

            Ext.getDoc().on({
                scope: this,
                mousemove: this.onMouseMove,
                mouseup: {
                    fn: this.onMouseUp,
                    single: true,
                    scope: this
                }
            });
            Ext.getBody().addClass('ux-resizable-handle-' + handle.position);

            this.resizing = true;
            this.startBox = this.el.getBox();
            this.startPoint = e.getXY();
            this.offsets = [(this.startBox.x + this.startBox.width) - this.startPoint[0],
                            (this.startBox.y + this.startBox.height) - this.startPoint[1]];

            if(this.constrainTo) {
                var ct = Ext.get(this.constrainTo);
                this.resizeRegion = ct.getRegion().adjust(
                    ct.getFrameWidth('t'),
                    ct.getFrameWidth('l'),
                    -ct.getFrameWidth('b'),
                    -ct.getFrameWidth('r')
                );
            }

            this.proxy.setStyle('visibility', 'hidden'); // workaround display none
            this.proxy.show();
            this.proxy.setBox(this.startBox);
            if(!this.dynamic){
                this.proxy.setStyle('visibility', 'visible');
            }
        }
    },

    // private
    onMouseDown : function(handle, e){
        if(this.enabled && !this.activeHandle){
            e.stopEvent();
            this.activeHandle = handle;
            this.startSizing(e, handle);
        }
    },

    // private
    onMouseUp : function(e){
        Ext.getBody().removeClass('ux-resizable-handle-' + this.activeHandle.position)
            .un('mousemove', this.onMouseMove, this);
        var size = this.resizeElement();
        this.resizing = false;
        this.handleOut(this.activeHandle);
        this.proxy.hide();
        this.fireEvent('resize', this, size.width, size.height, e);
        this.activeHandle = null;
    },

    // private
    snap : function(value, inc, min){
        if(!inc || !value){
            return value;
        }
        var newValue = value;
        var m = value % inc;
        if(m > 0){
            if(m > (inc/2)){
                newValue = value + (inc-m);
            }else{
                newValue = value - m;
            }
        }
        return Math.max(min, newValue);
    },

    /**
     * <p>Performs resizing of the associated Element. This method is called internally by this
     * class, and should not be called by user code.</p>
     * <p>If a Resizable is being used to resize an Element which encapsulates a more complex UI
     * component such as a Panel, this method may be overridden by specifying an implementation
     * as a config option to provide appropriate behaviour at the end of the resize operation on
     * mouseup, for example resizing the Panel, and relaying the Panel's content.</p>
     * <p>The new area to be resized to is available by examining the state of the {@link #proxy}
     * Element. Example:
<pre><code>
new Ext.Panel({
    title: 'Resize me',
    x: 100,
    y: 100,
    renderTo: Ext.getBody(),
    floating: true,
    frame: true,
    width: 400,
    height: 200,
    listeners: {
        render: function(p) {
            new Ext.Resizable(p.getEl(), {
                handles: 'all',
                pinned: true,
                transparent: true,
                resizeElement: function() {
                    var box = this.proxy.getBox();
                    p.updateBox(box);
                    if (p.layout) {
                        p.doLayout();
                    }
                    return box;
                }
           });
       }
    }
}).show();
</code></pre>
     */
    resizeElement : function(){
        var box = this.proxy.getBox();
        if(this.updateBox){
            this.el.setBox(box, false, this.animate, this.duration, null, this.easing);
        }else{
            this.el.setSize(box.width, box.height, this.animate, this.duration, null, this.easing);
        }
        if(!this.dynamic){
            this.proxy.hide();
        }
        return box;
    },

    // private
    constrain : function(v, diff, m, mx){
        if(v - diff < m){
            diff = v - m;
        }else if(v - diff > mx){
            diff = v - mx;
        }
        return diff;
    },

    // private
    onMouseMove : function(e){
        if(this.enabled && this.activeHandle){
            try{// try catch so if something goes wrong the user doesn't get hung

            if(this.resizeRegion && !this.resizeRegion.contains(e.getPoint())) {
                return;
            }

            //var curXY = this.startPoint;
            var curSize = this.curSize || this.startBox,
                x = this.startBox.x, y = this.startBox.y,
                ox = x,
                oy = y,
                w = curSize.width,
                h = curSize.height,
                ow = w,
                oh = h,
                mw = this.minWidth,
                mh = this.minHeight,
                mxw = this.maxWidth,
                mxh = this.maxHeight,
                wi = this.widthIncrement,
                hi = this.heightIncrement,
                eventXY = e.getXY(),
                diffX = -(this.startPoint[0] - Math.max(this.minX, eventXY[0])),
                diffY = -(this.startPoint[1] - Math.max(this.minY, eventXY[1])),
                pos = this.activeHandle.position,
                tw,
                th;

            switch(pos){
                case 'east':
                    w += diffX;
                    w = Math.min(Math.max(mw, w), mxw);
                    break;
                case 'south':
                    h += diffY;
                    h = Math.min(Math.max(mh, h), mxh);
                    break;
                case 'southeast':
                    w += diffX;
                    h += diffY;
                    w = Math.min(Math.max(mw, w), mxw);
                    h = Math.min(Math.max(mh, h), mxh);
                    break;
                case 'north':
                    diffY = this.constrain(h, diffY, mh, mxh);
                    y += diffY;
                    h -= diffY;
                    break;
                case 'west':
                    diffX = this.constrain(w, diffX, mw, mxw);
                    x += diffX;
                    w -= diffX;
                    break;
                case 'northeast':
                    w += diffX;
                    w = Math.min(Math.max(mw, w), mxw);
                    diffY = this.constrain(h, diffY, mh, mxh);
                    y += diffY;
                    h -= diffY;
                    break;
                case 'northwest':
                    diffX = this.constrain(w, diffX, mw, mxw);
                    diffY = this.constrain(h, diffY, mh, mxh);
                    y += diffY;
                    h -= diffY;
                    x += diffX;
                    w -= diffX;
                    break;
               case 'southwest':
                    diffX = this.constrain(w, diffX, mw, mxw);
                    h += diffY;
                    h = Math.min(Math.max(mh, h), mxh);
                    x += diffX;
                    w -= diffX;
                    break;
            }

            var sw = this.snap(w, wi, mw);
            var sh = this.snap(h, hi, mh);
            if(sw != w || sh != h){
                switch(pos){
                    case 'northeast':
                        y -= sh - h;
                    break;
                    case 'north':
                        y -= sh - h;
                        break;
                    case 'southwest':
                        x -= sw - w;
                    break;
                    case 'west':
                        x -= sw - w;
                        break;
                    case 'northwest':
                        x -= sw - w;
                        y -= sh - h;
                    break;
                }
                w = sw;
                h = sh;
            }

            if(this.preserveRatio){
                switch(pos){
                    case 'southeast':
                    case 'east':
                        h = oh * (w/ow);
                        h = Math.min(Math.max(mh, h), mxh);
                        w = ow * (h/oh);
                       break;
                    case 'south':
                        w = ow * (h/oh);
                        w = Math.min(Math.max(mw, w), mxw);
                        h = oh * (w/ow);
                        break;
                    case 'northeast':
                        w = ow * (h/oh);
                        w = Math.min(Math.max(mw, w), mxw);
                        h = oh * (w/ow);
                    break;
                    case 'north':
                        tw = w;
                        w = ow * (h/oh);
                        w = Math.min(Math.max(mw, w), mxw);
                        h = oh * (w/ow);
                        x += (tw - w) / 2;
                        break;
                    case 'southwest':
                        h = oh * (w/ow);
                        h = Math.min(Math.max(mh, h), mxh);
                        tw = w;
                        w = ow * (h/oh);
                        x += tw - w;
                        break;
                    case 'west':
                        th = h;
                        h = oh * (w/ow);
                        h = Math.min(Math.max(mh, h), mxh);
                        y += (th - h) / 2;
                        tw = w;
                        w = ow * (h/oh);
                        x += tw - w;
                       break;
                    case 'northwest':
                        tw = w;
                        th = h;
                        h = oh * (w/ow);
                        h = Math.min(Math.max(mh, h), mxh);
                        w = ow * (h/oh);
                        y += th - h;
                        x += tw - w;
                        break;

                }
            }
            this.proxy.setBounds(x, y, w, h);
            if(this.dynamic){
                this.resizeElement();
            }
            }catch(ex){}
        }
    },

    // private
    handleOver : function(handle){
        if(this.enabled){
            Ext.getBody().addClass('ux-resizable-handle-' + handle.position);
        }
    },

    // private
    handleOut : function(handle){
        if(!this.resizing){
            Ext.getBody().removeClass('ux-resizable-handle-' + handle.position);
        }
    },

    /**
     * Returns the element this component is bound to.
     * @return {Ext.Element}
     */
    getEl : function(){
        return this.el;
    },

    /**
     * Destroys this resizable. If the element was wrapped and
     * removeEl is not true then the element remains.
     * @param {Boolean} removeEl (optional) true to remove the element from the DOM
     */
    destroy : function(removeEl){
        Ext.destroy(this.dd, this.proxy);
        this.proxy = null;

        var ps = Ext.Resizable.positions;
        for(var k in ps){
            if(typeof ps[k] != 'function' && this[ps[k]]){
                this[ps[k]].destroy();
            }
        }
        if(removeEl){
            this.el.update('');
            Ext.destroy(this.el);
            this.el = null;
        }
        this.purgeListeners();
    },

    syncHandleHeight : function(){
        var h = this.el.getHeight(true);
        if(this.west.el){
            this.west.el.setHeight(h);
        }
        if(this.east.el){
            this.east.el.setHeight(h);
        }
    }
});

// private
// hash to map config positions to true positions
Ext.Resizable.positions = {
    n: 'north', s: 'south', e: 'east', w: 'west', se: 'southeast', sw: 'southwest', nw: 'northwest', ne: 'northeast'
};
Ext.Resizable.cfg = {
    north: {left: 7, right: -7, height: 7},
    south: {left: 7, right: -7, top: -7},
    east: {top: 7, bottom: -7, left: -7},
    west: {top: 7, bottom: -7, width: 7},
    southeast: {top: -7, left: -7},
    southwest: {top: -7, width: 7},
    northwest: {height: 7, width: 7},
    northeast: {left: -7, height: 7}
};

// private
Ext.Resizable.Handle = function(rz, pos){
    this.position = pos;
    this.rz = rz;
    var cfg = Ext.Resizable.cfg[pos] || Ext.Resizable.cfg[Ext.Resizable.positions[pos]];
    this.ez = new Ext.ux.EventZone(Ext.apply({
        position: pos,
        el: rz.el
    }, cfg));
    this.ez.on({
        mousedown: this.onMouseDown,
        mouseenter: this.onMouseOver,
        mouseleave: this.onMouseOut,
        scope: this
    });
};

// private
Ext.Resizable.Handle.prototype = {
    cursor: 'move',

    // private
    afterResize : function(rz){
        // do nothing
    },
    // private
    onMouseDown : function(e){
        this.rz.onMouseDown(this, e);
    },
    // private
    onMouseOver : function(e){
        this.rz.handleOver(this, e);
    },
    // private
    onMouseOut : function(e){
        this.rz.handleOut(this, e);
    },
    // private
    destroy : function(){
        Ext.destroy(this.el);
        this.el = null;
    }
};

/**
*
* Ext.ux.elasticTextArea Extension Class for Ext 3.x Library
*
* @author  Steffen Kamper
*
* @license Ext.ux.elasticTextArea is licensed under the terms of
* the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
* that the code/component(s) do NOT become part of another Open Source or Commercially
* licensed development library or toolkit without explicit permission.
*
* License details: http://www.gnu.org/licenses/lgpl.html
*
*/
Ext.ux.elasticTextArea = function(){

    var defaultConfig = function(){
        return {
            minHeight : 0
            ,maxHeight : 0
            ,growBy: 12
        }
    }

    var processOptions = function(config){
        var o = defaultConfig();
        var options = {};
        Ext.apply(options, config, o);

        return options;
    }

    return {
        div : null
        ,applyTo: function(elementId, options){

            var el = Ext.get(elementId);
            var width = el.getWidth();
            var height = el.getHeight();

            var styles = el.getStyles('padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'line-height', 'font-size', 'font-family', 'font-weight');

            if(! this.div){
                var options = processOptions(options);

                this.div = Ext.DomHelper.append(Ext.getBody() || document.body, {
                    'id':elementId + '-preview-div'
                    ,'tag' : 'div'
                    ,'background': 'red'
                    ,'style' : 'position: absolute; top: -100000px; left: -100000px;'
                }, true)
                Ext.DomHelper.applyStyles(this.div, styles);

                el.on('keyup', function() {
                        this.applyTo(elementId, options);
                }, this);
            }
            this.div.setWidth(parseInt(el.getStyle('width')));
            //replace \n with <br>&nbsp; so that the enter key can trigger and height increase
            //but first remove all previous entries, so that the height measurement can be as accurate as possible
            this.div.update(
                    el.dom.value.replace(/<br \/>&nbsp;/, '<br />')
                                .replace(/<|>/g, ' ')
                                .replace(/&/g,"&amp;")
                                .replace(/\n/g, '<br />&nbsp;')
                    );

			var growBy = parseInt(el.getStyle('line-height'));
			growBy = growBy ? growBy + 1 : 1;
			if (growBy === 1) {
				growBy = options.growBy;
			}
			var textHeight = this.div.getHeight();
			textHeight = textHeight ? textHeight + growBy : growBy;

            if ( (textHeight > options.maxHeight ) && (options.maxHeight > 0) ){
                textHeight = options.maxHeight;
                el.setStyle('overflow', 'auto');
            }
            if ( (textHeight < options.minHeight ) && (options.minHeight > 0) ) {
                textHeight = options.minHeight;
                el.setStyle('overflow', 'auto');
            }

            el.setHeight(textHeight , true);
        }
    }
}

/***************************************************************
*
*  Evaluation of TYPO3 form field content
*
*
*
*  Copyright notice
*
*  (c) 1998-2011 Kasper Skaarhoj
*  All rights reserved
*
*  This script is part of the TYPO3 t3lib/ library provided by
*  Kasper Skaarhoj <kasper@typo3.com> together with TYPO3
*
*  Released under GNU/GPL (see license file in typo3/sysext/cms/tslib/)
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
*  This copyright notice MUST APPEAR in all copies of this script
***************************************************************/


function evalFunc() {
	this.input = evalFunc_input;
	this.output = evalFunc_output;
	this.parseInt = evalFunc_parseInt;
	this.getNumChars = evalFunc_getNumChars;
	this.parseDouble = evalFunc_parseDouble;
	this.noSpace = evalFunc_noSpace;
	this.getSecs = evalFunc_getSecs;
	this.getYear = evalFunc_getYear;
	this.getTimeSecs = evalFunc_getTimeSecs;
	this.getTime = evalFunc_getTime;
	this.getDate = evalFunc_getDate;
	this.getTimestamp = evalFunc_getTimestamp;
	this.caseSwitch = evalFunc_caseSwitch;
	this.evalObjValue = evalFunc_evalObjValue;
	this.outputObjValue = evalFunc_outputObjValue;
	this.split = evalFunc_splitStr;
	this.pol = evalFunc_pol;
	this.convertClientTimestampToUTC = evalFunc_convertClientTimestampToUTC;

	this.ltrim = evalFunc_ltrim;
	this.btrim = evalFunc_btrim;
	var today = new Date();
 	this.lastYear = this.getYear(today);
 	this.lastDate = this.getDate(today);
 	this.lastTime = 0;
	this.refDate = today;
	this.isInString = '';
	this.USmode = 0;
}
function evalFunc_pol(fortegn, value) {
	return eval (((fortegn=="-")?'-':'')+value);
}
function evalFunc_evalObjValue(FObj,value) {
	var evallist = FObj.evallist;
	this.isInString = (FObj.is_in) ? ''+FObj.is_in : '';
	var index=1;
	var theEvalType = (FObj.evallist) ? this.split(evallist, ",", index) : false;
	var newValue=value;
	while (theEvalType) {
		if (TBE_EDITOR.customEvalFunctions[theEvalType] && typeof TBE_EDITOR.customEvalFunctions[theEvalType] == 'function') {
			newValue = TBE_EDITOR.customEvalFunctions[theEvalType](newValue);
		} else {
			newValue = evalFunc.input(theEvalType, newValue);
		}
		index++;
		theEvalType = this.split(evallist, ",", index);
	}
	return newValue;
}
function evalFunc_outputObjValue(FObj,value) {
	var evallist = FObj.evallist;
	var index=1;
	var theEvalType = this.split(evallist, ",", index);
	var newValue=value;
	while (theEvalType) {
		if (theEvalType != 'required') {
			newValue = evalFunc.output(theEvalType, value, FObj);
		}
		index++;
		theEvalType = this.split(evallist, ",", index);
	}
	return newValue;
}
function evalFunc_caseSwitch(type,inVal) {
	var theVal = ''+inVal;
	var newString = '';
	switch (type) {
		case "alpha":
		case "num":
		case "alphanum":
		case "alphanum_x":
			for (var a=0;a<theVal.length;a++) {
				var theChar = theVal.substr(a,1);
				var special = (theChar == '_' || theChar == '-');
				var alpha = (theChar >= 'a' && theChar <= 'z') || (theChar >= 'A' && theChar <= 'Z');
				var num = (theChar>='0' && theChar<='9');
				switch(type) {
					case "alphanum":	special=0;		break;
					case "alpha":	num=0; special=0;		break;
					case "num":	alpha=0; special=0;		break;
				}
				if (alpha || num || theChar==' ' || special) {
					newString+=theChar;
				}
			}
		break;
		case "is_in":
			if (this.isInString) {
				for (var a=0;a<theVal.length;a++) {
					var theChar = theVal.substr(a,1);
					if (this.isInString.indexOf(theChar)!=-1) {
						newString+=theChar;
					}
				}
			} else {newString = theVal;}
		break;
		case "nospace":
			newString = this.noSpace(theVal);
		break;
		case "upper":
			newString = theVal.toUpperCase();
		break;
		case "lower":
			newString = theVal.toLowerCase();
		break;
		default:
			return inVal;
	}
	return newString;
}
function evalFunc_parseInt(value) {
	var theVal = ''+value;
	if (!value) {
		return 0;
	}
	for (var a = 0; a < theVal.length; a++) {
		if (theVal.substr(a,1)!='0') {
			return parseInt(theVal.substr(a,theVal.length)) || 0;
		}
	}
	return 0;
}
function evalFunc_getNumChars(value) {
	var theVal = ''+value;
	if (!value) {
		return 0;
	}
	var outVal="";
	for (var a = 0; a < theVal.length; a++) {
		if (theVal.substr(a,1)==parseInt(theVal.substr(a,1))) {
			outVal+=theVal.substr(a,1);
		}
	}
	return outVal;
}
function evalFunc_parseDouble(value) {
	var theVal = "" + value;
	theVal = theVal.replace(/[^0-9,\.-]/g, "");
	var negative = theVal.substring(0, 1) === '-';
	theVal = theVal.replace(/-/g, "");
	theVal = theVal.replace(/,/g, ".");
	if (theVal.indexOf(".") == -1) {
		theVal += ".0";
	}
	var parts = theVal.split(".");
	var dec = parts.pop();
	theVal = Number(parts.join("") + "." + dec);
	if (negative) {
	    theVal *= -1;
	}
	theVal = theVal.toFixed(2);

	return theVal;
}
function evalFunc_noSpace(value) {
	var theVal = ''+value;
	var newString="";
	for (var a=0;a<theVal.length;a++) {
		var theChar = theVal.substr(a,1);
		if (theChar!=' ') {
			newString+=theChar;
		}
	}
	return newString;
}
function evalFunc_ltrim(value) {
	var theVal = ''+value;
	if (!value) {
		return '';
	}
	for (var a = 0; a < theVal.length; a++) {
		if (theVal.substr(a,1)!=' ') {
			return theVal.substr(a,theVal.length);
		}
	}
	return '';
}
function evalFunc_btrim(value) {
	var theVal = ''+value;
	if (!value) {
		return '';
	}
	for (var a = theVal.length; a > 0; a--) {
		if (theVal.substr(a-1,1)!=' ') {
			return theVal.substr(0,a);
		}
	}
	return '';
}
function evalFunc_splitSingle(value) {
	var theVal = ''+value;
	this.values = new Array();
	this.pointer = 3;
	this.values[1]=theVal.substr(0,2);
	this.values[2]=theVal.substr(2,2);
	this.values[3]=theVal.substr(4,10);
}
function evalFunc_split(value) {
	this.values = new Array();
	this.valPol = new Array();
	this.pointer = 0;
	var numberMode = 0;
	var theVal = "";
	value+=" ";
	for (var a=0;a<value.length;a++) {
		var theChar = value.substr(a,1);
		if (theChar<"0" || theChar>"9") {
			if (numberMode) {
				this.pointer++;
				this.values[this.pointer]=theVal;
				theVal = "";
				numberMode=0;
			}
			if (theChar=="+" || theChar=="-") {
				this.valPol[this.pointer+1] = theChar;
			}
		} else {
			theVal+=theChar;
			numberMode=1;
		}
	}
}
function evalFunc_input(type,inVal) {
	if (type=="md5") {
		return MD5(inVal);
	}
	if (type=="trim") {
		return this.ltrim(this.btrim(inVal));
	}
	if (type=="int") {
		return this.parseInt(inVal);
	}
	if (type=="double2") {
		return this.parseDouble(inVal);
	}

	var today = new Date();
	var add=0;
	var value = this.ltrim(inVal);
	var values = new evalFunc_split(value);
	var theCmd = value.substr(0,1);
	value = this.caseSwitch(type,value);
	if (value=="") {
		return "";
		return 0;	// Why would I ever return a zero??? (20/12/01)
	}
	switch (type) {
		case "datetime":
			switch (theCmd) {
				case "d":
				case "t":
				case "n":
					this.lastTime = this.convertClientTimestampToUTC(this.getTimestamp(today), 0);
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				case "+":
				case "-":
					if (this.lastTime == 0) {
						this.lastTime = this.convertClientTimestampToUTC(this.getTimestamp(today), 0);
					}
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				default:
					var index = value.indexOf(' ');
					if (index!=-1) {
						var dateVal = this.input("date",value.substr(index,value.length));
							// set refDate so that evalFunc_input on time will work with correct DST information
						this.refDate = new Date(dateVal*1000);
						this.lastTime = dateVal + this.input("time",value.substr(0,index));
					} else	{
							// only date, no time
						this.lastTime = this.input("date", value);
					}
			}
			this.lastTime+=add*24*60*60;
			return this.lastTime;
		break;
		case "year":
			switch (theCmd) {
				case "d":
				case "t":
				case "n":
					this.lastYear = this.getYear(today);
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				case "+":
				case "-":
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				default:
					if (values.valPol[2]) {
						add = this.pol(values.valPol[2],this.parseInt(values.values[2]));
					}
					var year = (values.values[1])?this.parseInt(values.values[1]):this.getYear(today);
					if ((year >= 0 && year < 38) || (year >= 70 && year<100) || (year >= 1902 && year < 2038)) {
						if (year<100) {
							year = (year<38) ? year+=2000 : year+=1900;
						}
					} else {
						year = this.getYear(today);
					}
					this.lastYear = year;
			}
			this.lastYear+=add;
			return this.lastYear;
		break;
		case "date":
			switch (theCmd) {
				case "d":
				case "t":
				case "n":
					this.lastDate = this.getTimestamp(today);
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				case "+":
				case "-":
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				default:
					var index = 4;
					if (values.valPol[index]) {
						add = this.pol(values.valPol[index],this.parseInt(values.values[index]));
					}
					if (values.values[1] && values.values[1].length>2) {
						if (values.valPol[2]) {
							add = this.pol(values.valPol[2],this.parseInt(values.values[2]));
						}
						var temp = values.values[1];
						values = new evalFunc_splitSingle(temp);
					}

					var year = (values.values[3])?this.parseInt(values.values[3]):this.getYear(today);
					if ((year >= 0 && year < 38) || (year >= 70 && year < 100) || (year >= 1902 && year<2038)) {
						if (year<100) {
							year = (year<38) ? year+=2000 : year+=1900;
						}
					} else {
						year = this.getYear(today);
					}
					var month = (values.values[this.USmode?1:2])?this.parseInt(values.values[this.USmode?1:2]):today.getUTCMonth()+1;
					var day = (values.values[this.USmode?2:1])?this.parseInt(values.values[this.USmode?2:1]):today.getUTCDate();

					var theTime = new Date(parseInt(year), parseInt(month)-1, parseInt(day));

						// Substract timezone offset from client
					this.lastDate = this.convertClientTimestampToUTC(this.getTimestamp(theTime), 0);
			}
			this.lastDate+=add*24*60*60;
			return this.lastDate;
		break;
		case "time":
		case "timesec":
			switch (theCmd) {
				case "d":
				case "t":
				case "n":
					this.lastTime = this.getTimeSecs(today);
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				case "+":
				case "-":
					if (this.lastTime == 0) {
						this.lastTime = this.getTimeSecs(today);
					}
					if (values.valPol[1]) {
						add = this.pol(values.valPol[1],this.parseInt(values.values[1]));
					}
				break;
				default:
					var index = (type=="timesec")?4:3;
					if (values.valPol[index]) {
						add = this.pol(values.valPol[index],this.parseInt(values.values[index]));
					}
					if (values.values[1] && values.values[1].length>2) {
						if (values.valPol[2]) {
							add = this.pol(values.valPol[2],this.parseInt(values.values[2]));
						}
						var temp = values.values[1];
						values = new evalFunc_splitSingle(temp);
					}
					var sec = (values.values[3])?this.parseInt(values.values[3]):today.getUTCSeconds();
					if (sec > 59)	{sec=59;}
					var min = (values.values[2])?this.parseInt(values.values[2]):today.getUTCMinutes();
					if (min > 59)	{min=59;}
					var hour = (values.values[1])?this.parseInt(values.values[1]):today.getUTCHours();
					if (hour >= 24)	{hour=0;}

					var theTime = new Date(this.getYear(this.refDate), this.refDate.getUTCMonth(), this.refDate.getUTCDate(), hour, min, ((type=="timesec")?sec:0));

						// Substract timezone offset from client
					this.lastTime = this.convertClientTimestampToUTC(this.getTimestamp(theTime), 1);
			}
			this.lastTime+=add*60;
			if (this.lastTime<0) {this.lastTime+=24*60*60;}
			return this.lastTime;
		break;
		default:
			return value;
	}
}
function evalFunc_output(type,value,FObj) {
	var theString = "";
	switch (type) {
		case "date":
			if (!parseInt(value))	{return '';}
			var theTime = new Date(parseInt(value) * 1000);
			if (this.USmode) {
				theString = (theTime.getUTCMonth()+1)+'-'+theTime.getUTCDate()+'-'+this.getYear(theTime);
			} else {
				theString = theTime.getUTCDate()+'-'+(theTime.getUTCMonth()+1)+'-'+this.getYear(theTime);
			}
		break;
		case "datetime":
			if (!parseInt(value))	{return '';}
			theString = this.output("time",value)+' '+this.output("date",value);
		break;
		case "time":
		case "timesec":
			if (!parseInt(value))	{return '';}
			var theTime = new Date(parseInt(value) * 1000);
			var h = theTime.getUTCHours();
			var m = theTime.getUTCMinutes();
			var s = theTime.getUTCSeconds();
			theString = h+':'+((m<10)?'0':'')+m + ((type=="timesec")?':'+((s<10)?'0':'')+s:'');
		break;
		case "password":
			theString = (value)	? TS.passwordDummy : "";
		break;
		case "int":
			theString = (FObj.checkbox && value==FObj.checkboxValue)?'':value;
		break;
		default:
			theString = value;
	}
	return theString;
}
function evalFunc_getSecs(timeObj) {
	return timeObj.getUTCSeconds();
}
// Seconds since midnight:
function evalFunc_getTime(timeObj) {
	return timeObj.getUTCHours() * 60 * 60 + timeObj.getUTCMinutes() * 60 + this.getSecs(timeObj);
}
function evalFunc_getYear(timeObj) {
	return timeObj.getUTCFullYear();
}
// Seconds since midnight with client timezone offset:
function evalFunc_getTimeSecs(timeObj) {
	return timeObj.getHours()*60*60+timeObj.getMinutes()*60+timeObj.getSeconds();
}
function evalFunc_getDate(timeObj) {
	var theTime = new Date(this.getYear(timeObj), timeObj.getUTCMonth(), timeObj.getUTCDate());
	return this.getTimestamp(theTime);
}
function evalFunc_dummy (evallist,is_in,checkbox,checkboxValue) {
	this.evallist = evallist;
	this.is_in = is_in;
	this.checkboxValue = checkboxValue;
	this.checkbox = checkbox;
}
function evalFunc_splitStr(theStr1, delim, index) {
	var theStr = ''+theStr1;
	var lengthOfDelim = delim.length;
	sPos = -lengthOfDelim;
	if (index<1) {index=1;}
	for (a=1; a<index; a++) {
		sPos = theStr.indexOf(delim, sPos+lengthOfDelim);
		if (sPos==-1)	{return null;}
	}
	ePos = theStr.indexOf(delim, sPos+lengthOfDelim);
	if(ePos == -1)	{ePos = theStr.length;}
	return (theStr.substring(sPos+lengthOfDelim,ePos));
}
function evalFunc_getTimestamp(timeObj) {
	return Date.parse(timeObj)/1000;
}

// Substract timezone offset from client to a timestamp to get UTC-timestamp to be send to server
function evalFunc_convertClientTimestampToUTC(timestamp, timeonly) {
	var timeObj = new Date(timestamp*1000);
	timeObj.setTime((timestamp - timeObj.getTimezoneOffset()*60)*1000);
	if (timeonly) {
			// only seconds since midnight
		return this.getTime(timeObj);
	} else	{
			// seconds since the "unix-epoch"
		return this.getTimestamp(timeObj);
	}
}

/***************************************************************
*  Copyright notice
*
*  (c) 1999-2010 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains JavaScript for TYPO3 Core Form generator - AKA "TCEforms"
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @coauthor	Oliver Hader <oh@inpublica.de>
 */


var TBE_EDITOR = {
	/* Example:
		elements: {
			'data-parentPid-table-uid': {
				'field': {
					'range':		[0, 100],
					'rangeImg':		'',
					'required':		true,
					'requiredImg':	''
				}
			}
		},
	*/

	elements: {},
	nested: {'field':{}, 'level':{}},
	ignoreElements: [],
	recentUpdatedElements: {},
	actionChecks: { submit:	[] },

	formname: '',
	formnameUENC: '',
	loadTime: 0,
	isChanged: 0,
	auth_timeout_field: 0,

	backPath: '',
	prependFormFieldNames: 'data',
	prependFormFieldNamesUENC: 'data',
	prependFormFieldNamesCnt: 0,

	isPalettedoc: null,
	doSaveFieldName: 0,

	labels: {},
	images: {
		req: new Image(),
		cm: new Image(),
		sel: new Image(),
		clear: new Image()
	},

	clearBeforeSettingFormValueFromBrowseWin: [],

	// Handling of data structures:
	addElements: function(elements) {
		TBE_EDITOR.recentUpdatedElements = elements;
		TBE_EDITOR.elements = $H(TBE_EDITOR.elements).merge(elements).toObject();
	},
	addNested: function(elements) {
		// Merge data structures:
		if (elements) {
			$H(elements).each(function(element) {
				var levelMax, i, currentLevel, subLevel;
				var nested = element.value;
				if (nested.level && nested.level.length) {
						// If the first level is of type 'inline', it could be created by a AJAX request to IRRE.
						// So, try to get the upper levels this dynamic level is nested in:
					if (typeof inline!='undefined' && nested.level[0][0]=='inline') {
						nested.level = inline.findContinuedNestedLevel(nested.level, nested.level[0][1]);
					}
					levelMax = nested.level.length-1;
					for (i=0; i<=levelMax; i++) {
						currentLevel = TBE_EDITOR.getNestedLevelIdent(nested.level[i]);
						if (typeof TBE_EDITOR.nested.level[currentLevel] == 'undefined') {
							TBE_EDITOR.nested.level[currentLevel] = { 'clean': true, 'item': {}, 'sub': {} };
						}
							// Add next sub level to the current level:
						if (i<levelMax) {
							subLevel = TBE_EDITOR.getNestedLevelIdent(nested.level[i+1]);
							TBE_EDITOR.nested.level[currentLevel].sub[subLevel] = true;
							// Add the current item to the last level in nesting:
						} else {
							TBE_EDITOR.nested.level[currentLevel].item[element.key] = nested.parts;
						}
					}
				}
			});
				// Merge the nested fields:
			TBE_EDITOR.nested.field = $H(TBE_EDITOR.nested.field).merge(elements).toObject();
		}
	},
	removeElement: function(record) {
		if (TBE_EDITOR.elements && TBE_EDITOR.elements[record]) {
				// Inform envolved levels the this record is removed and the missing requirements are resolved:
			$H(TBE_EDITOR.elements[record]).each(
				function(pair) {
					TBE_EDITOR.notifyNested(record+'['+pair.key+']', true);
				}
			);
			delete(TBE_EDITOR.elements[record]);
		}
	},
	removeElementArray: function(removeStack) {
		if (removeStack && removeStack.length) {
			TBE_EDITOR.ignoreElements = removeStack;
			for (var i=removeStack.length; i>=0; i--) {
				TBE_EDITOR.removeElement(removeStack[i]);
			}
			TBE_EDITOR.ignoreElements = [];
		}
	},
	getElement: function(record, field, type) {
		var result = null;
		var element;

		if (TBE_EDITOR.elements && TBE_EDITOR.elements[record] && TBE_EDITOR.elements[record][field]) {
			element = TBE_EDITOR.elements[record][field];
			if (type) {
				if (element[type]) result = element;
			} else {
				result = element;
			}
		}

		return result;
	},
	checkElements: function(type, recentUpdated, record, field) {
		var result = 1;
		var elementName, elementData, elementRecord, elementField;
		var source = (recentUpdated ? TBE_EDITOR.recentUpdatedElements : TBE_EDITOR.elements);

		if (TBE_EDITOR.ignoreElements.length && TBE_EDITOR.ignoreElements.indexOf(record)!=-1) {
			return result;
		}

		if (type) {
			if (record && field) {
				elementName = record+'['+field+']';
				elementData = TBE_EDITOR.getElement(record, field, type);
				if (elementData) {
					if (!TBE_EDITOR.checkElementByType(type, elementName, elementData, recentUpdated)) {
						result = 0;
					}
				}

			} else {
				var elementFieldList, elRecIndex, elRecCnt, elFldIndex, elFldCnt;
				var elementRecordList = $H(source).keys();
				for (elRecIndex=0, elRecCnt=elementRecordList.length; elRecIndex<elRecCnt; elRecIndex++) {
					elementRecord = elementRecordList[elRecIndex];
					elementFieldList = $H(source[elementRecord]).keys();
					for (elFldIndex=0, elFldCnt=elementFieldList.length; elFldIndex<elFldCnt; elFldIndex++) {
						elementField = elementFieldList[elFldIndex];
						elementData = TBE_EDITOR.getElement(elementRecord, elementField, type);
						if (elementData) {
							elementName = elementRecord+'['+elementField+']';
							if (!TBE_EDITOR.checkElementByType(type, elementName, elementData, recentUpdated)) {
								result = 0;
							}
						}
					}
				}
			}
		}

		return result;
	},
	checkElementByType: function(type, elementName, elementData, autoNotify) {
		var form, result = 1;

		if (type) {
			if (type == 'required') {
				form = document[TBE_EDITOR.formname][elementName];
				if (form) {
						// Check if we are within a deleted inline element
					var testNode = $(form.parentNode);
					while(testNode) {
						if (testNode.hasClassName && testNode.hasClassName('inlineIsDeletedRecord')) {
							return result;
						}
						testNode = $(testNode.parentNode);
					}

					var value = form.value;
					if (!value || elementData.additional && elementData.additional.isPositiveNumber && (isNaN(value) || Number(value) <= 0)) {
						result = 0;
						if (autoNotify) {
							TBE_EDITOR.setImage('req_'+elementData.requiredImg, TBE_EDITOR.images.req);
							TBE_EDITOR.notifyNested(elementName, false);
						}
					}
				}
			} else if (type == 'range' && elementData.range) {
				var numberOfElements = 0;
				form = document[TBE_EDITOR.formname][elementName+'_list'];
				if (!form) {
						// special treatment for IRRE fields:
					var tempObj = document[TBE_EDITOR.formname][elementName];
					if (tempObj && (Element.hasClassName(tempObj, 'inlineRecord') || Element.hasClassName(tempObj, 'treeRecord'))) {
						form = tempObj.value ? tempObj.value.split(',') : [];
						numberOfElements = form.length;
					}

				} else {
						// special treatment for file uploads
					var tempObj = document[TBE_EDITOR.formname][elementName.replace(/^data/, 'data_files')];
					numberOfElements = form.length;

					if (tempObj && tempObj.type == 'file' && tempObj.value) {
						numberOfElements++; // Add new uploaded file to the number of elements
					}
				}

				if (!TBE_EDITOR.checkRange(numberOfElements, elementData.range[0], elementData.range[1])) {
					result = 0;
					if (autoNotify) {
						TBE_EDITOR.setImage('req_'+elementData.rangeImg, TBE_EDITOR.images.req);
						TBE_EDITOR.notifyNested(elementName, false);
					}
				}
			}
		}

		return result;
	},
	// Notify tabs and inline levels with nested requiredFields/requiredElements:
	notifyNested: function(elementName, resolved) {
		if (TBE_EDITOR.nested.field[elementName]) {
			var i, nested, element, fieldLevels, fieldLevelIdent, nestedLevelType, nestedLevelName;
			fieldLevels = TBE_EDITOR.nested.field[elementName].level;
			TBE_EDITOR.nestedCache = {};

			for (i=fieldLevels.length-1; i>=0; i--) {
				nestedLevelType = fieldLevels[i][0];
				nestedLevelName = fieldLevels[i][1];
				fieldLevelIdent = TBE_EDITOR.getNestedLevelIdent(fieldLevels[i]);
					// Construct the CSS id strings of the image/icon tags showing the notification:
				if (nestedLevelType == 'tab') {
					element = nestedLevelName+'-REQ';
				} else if (nestedLevelType == 'inline') {
					element = nestedLevelName+'_req';
				} else {
					continue;
				}
					// Set the icons:
				if (resolved) {
					if (TBE_EDITOR.checkNested(fieldLevelIdent)) {
						TBE_EDITOR.setImage(element, TBE_EDITOR.images.clear);
					} else {
						break;
					}
				} else {
					if (TBE_EDITOR.nested.level && TBE_EDITOR.nested.level[fieldLevelIdent]) {
						TBE_EDITOR.nested.level[fieldLevelIdent].clean = false;
					}
					TBE_EDITOR.setImage(element, TBE_EDITOR.images.req);
				}
			}
		}
	},
	// Check all the input fields on a given level of nesting - if only on is unfilled, the whole level is marked as required:
	checkNested: function(nestedLevelIdent) {
		var nestedLevel, isClean;
		if (nestedLevelIdent && TBE_EDITOR.nested.level && TBE_EDITOR.nested.level[nestedLevelIdent]) {
			nestedLevel = TBE_EDITOR.nested.level[nestedLevelIdent];
			if (!nestedLevel.clean) {
				if (typeof nestedLevel.item == 'object') {
					$H(nestedLevel.item).each(
						function(pair) {
							if (isClean || typeof isClean == 'undefined') {
								isClean = (
									TBE_EDITOR.checkElements('required', false, pair.value[0], pair.value[1]) &&
									TBE_EDITOR.checkElements('range', false, pair.value[0], pair.value[1])
								);
							}
						}
					);
					if (typeof isClean != 'undefined' && !isClean) {
						return false;
					}
				}
				if (typeof nestedLevel.sub == 'object') {
					$H(nestedLevel.sub).each(
						function(pair) {
							if (isClean || typeof isClean == 'undefined') {
								isClean = TBE_EDITOR.checkNested(pair.key);
							}
						}
					);
					if (typeof isClean != 'undefined' && !isClean) {
						return false;
					}
				}
					// Store the result, that this level (the fields on this and the sub levels) are clean:
				nestedLevel.clean = true;
			}
		}
		return true;
	},
	getNestedLevelIdent: function(level) {
		return level.join('::');
	},
	addActionChecks: function(type, checks) {
		TBE_EDITOR.actionChecks[type].push(checks);
	},

	// Regular TCEforms JSbottom scripts:
	loginRefreshed: function() {
		var date = new Date();
		TBE_EDITOR.loadTime = Math.floor(date.getTime()/1000);
		if (top.busy && top.busy.loginRefreshed) { top.busy.loginRefreshed(); }
	},
	checkLoginTimeout: function() {
		var date = new Date();
		var theTime = Math.floor(date.getTime()/1000);
		if (theTime > TBE_EDITOR.loadTime+TBE_EDITOR.auth_timeout_field-10) {
			return true;
		}
	},
	fieldChanged_fName: function(fName,el) {
		var idx=2+TBE_EDITOR.prependFormFieldNamesCnt;
		var table = TBE_EDITOR.split(fName, "[", idx);
		var uid = TBE_EDITOR.split(fName, "[", idx+1);
		var field = TBE_EDITOR.split(fName, "[", idx+2);

		table = table.substr(0,table.length-1);
		uid = uid.substr(0,uid.length-1);
		field = field.substr(0,field.length-1);
		TBE_EDITOR.fieldChanged(table,uid,field,el);
	},
	fieldChanged: function(table,uid,field,el) {
		var theField = TBE_EDITOR.prependFormFieldNames+'['+table+']['+uid+']['+field+']';
		var theRecord = TBE_EDITOR.prependFormFieldNames+'['+table+']['+uid+']';
		TBE_EDITOR.isChanged = 1;

			// Set change image:
		var imgObjName = "cm_"+table+"_"+uid+"_"+field;
		TBE_EDITOR.setImage(imgObjName,TBE_EDITOR.images.cm);

			// Set change image
		if (document[TBE_EDITOR.formname][theField] && document[TBE_EDITOR.formname][theField].type=="select-one" && document[TBE_EDITOR.formname][theField+"_selIconVal"]) {
			var imgObjName = "selIcon_"+table+"_"+uid+"_"+field+"_";
			TBE_EDITOR.setImage(imgObjName+document[TBE_EDITOR.formname][theField+"_selIconVal"].value,TBE_EDITOR.images.clear);
			document[TBE_EDITOR.formname][theField+"_selIconVal"].value = document[TBE_EDITOR.formname][theField].selectedIndex;
			TBE_EDITOR.setImage(imgObjName+document[TBE_EDITOR.formname][theField+"_selIconVal"].value,TBE_EDITOR.images.sel);
		}

			// Set required flag:
		var imgReqObjName = "req_"+table+"_"+uid+"_"+field;
		if (TBE_EDITOR.getElement(theRecord,field,'required') && document[TBE_EDITOR.formname][theField]) {
			if (TBE_EDITOR.checkElements('required', false, theRecord, field)) {
				TBE_EDITOR.setImage(imgReqObjName,TBE_EDITOR.images.clear);
				TBE_EDITOR.notifyNested(theField, true);
			} else {
				TBE_EDITOR.setImage(imgReqObjName,TBE_EDITOR.images.req);
				TBE_EDITOR.notifyNested(theField, false);
			}
		}
		if (TBE_EDITOR.getElement(theRecord,field,'range') && document[TBE_EDITOR.formname][theField]) {
			if (TBE_EDITOR.checkElements('range', false, theRecord, field)) {
				TBE_EDITOR.setImage(imgReqObjName,TBE_EDITOR.images.clear);
				TBE_EDITOR.notifyNested(theField, true);
			} else {
				TBE_EDITOR.setImage(imgReqObjName,TBE_EDITOR.images.req);
				TBE_EDITOR.notifyNested(theField, false);
			}
		}

		if (TBE_EDITOR.isPalettedoc) { TBE_EDITOR.setOriginalFormFieldValue(theField) };
	},
	setOriginalFormFieldValue: function(theField) {
		if (TBE_EDITOR.isPalettedoc && (TBE_EDITOR.isPalettedoc).document[TBE_EDITOR.formname] && (TBE_EDITOR.isPalettedoc).document[TBE_EDITOR.formname][theField]) {
			(TBE_EDITOR.isPalettedoc).document[TBE_EDITOR.formname][theField].value = document[TBE_EDITOR.formname][theField].value;
		}
	},
	isFormChanged: function(noAlert) {
		if (TBE_EDITOR.isChanged && !noAlert && confirm(TBE_EDITOR.labels.fieldsChanged)) {
			return 0;
		}
		return TBE_EDITOR.isChanged;
	},
	checkAndDoSubmit: function(sendAlert) {
		if (TBE_EDITOR.checkSubmit(sendAlert)) { TBE_EDITOR.submitForm(); }
	},
	/**
	 * Checks if the form can be submitted according to any possible restrains like required values, item numbers etc.
	 * Returns true if the form can be submitted, otherwise false (and might issue an alert message, if "sendAlert" is 1)
	 * If "sendAlert" is false, no error message will be shown upon false return value (if "1" then it will).
	 * If "sendAlert" is "-1" then the function will ALWAYS return true regardless of constraints (except if login has expired) - this is used in the case where a form field change requests a form update and where it is accepted that constraints are not observed (form layout might change so other fields are shown...)
	 */
	checkSubmit: function(sendAlert) {
		var funcIndex, funcMax, funcRes;
		var OK=1;

		// $this->additionalJS_submit:
		if (TBE_EDITOR.actionChecks && TBE_EDITOR.actionChecks.submit) {
			for (funcIndex=0, funcMax=TBE_EDITOR.actionChecks.submit.length; funcIndex<funcMax; funcIndex++) {
				try {
					eval(TBE_EDITOR.actionChecks.submit[funcIndex]);
				} catch(error) {}
			}
		}

		if(!OK) {
			if (!confirm(unescape("SYSTEM ERROR: One or more Rich Text Editors on the page could not be contacted. This IS an error, although it should not be regular.\nYou can save the form now by pressing OK, but you will loose the Rich Text Editor content if you do.\n\nPlease report the error to your administrator if it persists."))) {
				return false;
			} else {
				OK = 1;
			}
		}
		// $reqLinesCheck
		if (!TBE_EDITOR.checkElements('required', false)) { OK = 0; }
		// $reqRangeCheck
		if (!TBE_EDITOR.checkElements('range', false)) { OK = 0; }

		if (OK || sendAlert==-1) {
			return true;
		} else {
			if(sendAlert) alert(TBE_EDITOR.labels.fieldsMissing);
			return false;
		}
	},
	checkRange: function(numberOfElements, lower, upper) {
			// for backwards compatibility, check if we're dealing with an element as first parameter
		if(typeof numberOfElements == 'object') {
			numberOfElements = numberOfElements.length;
		}

		if (numberOfElements >= lower && numberOfElements <= upper) {
			return true;
		} else {
			return false;
		}
	},
	initRequired: function() {
		// $reqLinesCheck
		TBE_EDITOR.checkElements('required', true);

		// $reqRangeCheck
		TBE_EDITOR.checkElements('range', true);
	},
	setImage: function(name,image) {
		var object;
		if (document[name]) {
			object = document[name];
		} else if (document.getElementById(name)) {
			object = document.getElementById(name);
		}
		if (object) {
			if (typeof image == 'object') {
				document[name].src = image.src;
			} else {
				document[name].src = eval(image+'.src');
			}
		}
	},
	submitForm: function() {
		if (TBE_EDITOR.doSaveFieldName) {
			document[TBE_EDITOR.formname][TBE_EDITOR.doSaveFieldName].value=1;
		}
		document[TBE_EDITOR.formname].submit();
	},
	split: function(theStr1, delim, index) {
		var theStr = ""+theStr1;
		var lengthOfDelim = delim.length;
		sPos = -lengthOfDelim;
		if (index<1) {index=1;}
		for (var a=1; a<index; a++) {
			sPos = theStr.indexOf(delim, sPos+lengthOfDelim);
			if (sPos==-1) { return null; }
		}
		ePos = theStr.indexOf(delim, sPos+lengthOfDelim);
		if(ePos == -1) { ePos = theStr.length; }
		return (theStr.substring(sPos+lengthOfDelim,ePos));
	},
	curSelected: function(theField) {
		var fObjSel = document[TBE_EDITOR.formname][theField];
		var retVal="";
		if (fObjSel) {
			if (fObjSel.type=='select-multiple' || fObjSel.type=='select-one') {
				var l=fObjSel.length;
				for (a=0;a<l;a++) {
					if (fObjSel.options[a].selected==1) {
						retVal+=fObjSel.options[a].value+",";
					}
				}
			}
		}
		return retVal;
	},
	rawurlencode: function(str,maxlen) {
		var output = str;
		if (maxlen)	output = output.substr(0,200);
		output = encodeURIComponent(output);
		return output;
	},
	str_replace: function(match,replace,string) {
		var input = ''+string;
		var matchStr = ''+match;
		if (!matchStr) { return string; }
		var output = '';
		var pointer=0;
		var pos = input.indexOf(matchStr);
		while (pos!=-1) {
			output+=''+input.substr(pointer, pos-pointer)+replace;
			pointer=pos+matchStr.length;
			pos = input.indexOf(match,pos+1);
		}
		output+=''+input.substr(pointer);
		return output;
	},
	toggle_display_states: function(id, state_1, state_2) {
		var node = document.getElementById(id);
		if (node) {
			switch (node.style.display) {
				case state_1:
					node.style.display = state_2;
					break;
				case state_2:
					node.style.display = state_1;
					break;
			}
		}
		return false;
	},

	/**
	 * Determines backend path to be used for e.g. ajax.php
	 * @return string
	 */
	getBackendPath: function() {
		var backendPath = '';
		if (TYPO3) {
			if (TYPO3.configuration && TYPO3.configuration.PATH_typo3) {
				backendPath = TYPO3.configuration.PATH_typo3;
			} else if (TYPO3.settings && TYPO3.settings.PATH_typo3) {
				backendPath = TYPO3.settings.PATH_typo3;
			}
		}
		return backendPath;
	}
};

function typoSetup	() {
	this.passwordDummy = '********';
	this.decimalSign = '.';
}
var TS = new typoSetup();
var evalFunc = new evalFunc();

// backwards compatibility for extensions
var TBE_EDITOR_loginRefreshed = TBE_EDITOR.loginRefreshed;
var TBE_EDITOR_checkLoginTimeout = TBE_EDITOR.checkLoginTimeout;
var TBE_EDITOR_setHiddenContent = TBE_EDITOR.setHiddenContent;
var TBE_EDITOR_isChanged = TBE_EDITOR.isChanged;
var TBE_EDITOR_fieldChanged_fName = TBE_EDITOR.fieldChanged_fName;
var TBE_EDITOR_fieldChanged = TBE_EDITOR.fieldChanged;
var TBE_EDITOR_setOriginalFormFieldValue = TBE_EDITOR.setOriginalFormFieldValue;
var TBE_EDITOR_isFormChanged = TBE_EDITOR.isFormChanged;
var TBE_EDITOR_checkAndDoSubmit = TBE_EDITOR.checkAndDoSubmit;
var TBE_EDITOR_checkSubmit = TBE_EDITOR.checkSubmit;
var TBE_EDITOR_checkRange = TBE_EDITOR.checkRange;
var TBE_EDITOR_initRequired = TBE_EDITOR.initRequired;
var TBE_EDITOR_setImage = TBE_EDITOR.setImage;
var TBE_EDITOR_submitForm = TBE_EDITOR.submitForm;
var TBE_EDITOR_split = TBE_EDITOR.split;
var TBE_EDITOR_curSelected = TBE_EDITOR.curSelected;
var TBE_EDITOR_rawurlencode = TBE_EDITOR.rawurlencode;
var TBE_EDITOR_str_replace = TBE_EDITOR.str_replace;


var typo3form = {
	fieldSetNull: function(fieldName, isNull) {
		if (document[TBE_EDITOR.formname][fieldName]) {
			var formFieldItemWrapper = Element.up(document[TBE_EDITOR.formname][fieldName], '.t3-form-field-item');

			if (isNull) {
				formFieldItemWrapper.addClassName('disabled');
			} else {
				formFieldItemWrapper.removeClassName('disabled');
			}
		}
	},
	fieldSet: function(theField, evallist, is_in, checkbox, checkboxValue) {
		var i;

		if (document[TBE_EDITOR.formname][theField]) {
			var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
			var theValue = document[TBE_EDITOR.formname][theField].value;
			if (checkbox && theValue==checkboxValue) {
				document[TBE_EDITOR.formname][theField+"_hr"].value="";
				if (document[TBE_EDITOR.formname][theField+"_cb"])	document[TBE_EDITOR.formname][theField+"_cb"].checked = "";
			} else {
				document[TBE_EDITOR.formname][theField+"_hr"].value = evalFunc.outputObjValue(theFObj, theValue);
				if (document[TBE_EDITOR.formname][theField+"_cb"])	document[TBE_EDITOR.formname][theField+"_cb"].checked = "on";
			}
		}
	},
	fieldGet: function(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off, checkSetValue) {
		if (document[TBE_EDITOR.formname][theField]) {
			var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
			if (checkbox_off) {
				if (document[TBE_EDITOR.formname][theField+"_cb"].checked) {
					var split = evallist.split(',');
					for (var i = 0; split.length > i; i++) {
						var el = split[i].replace(/ /g, '');
						if (el == 'datetime' || el == 'date') {
							var now = new Date();
							checkSetValue = Date.parse(now)/1000 - now.getTimezoneOffset()*60;
							break;
						} else if (el == 'time' || el == 'timesec') {
							checkSetValue = evalFunc_getTimeSecs(new Date());
							break;
						}
					}
					document[TBE_EDITOR.formname][theField].value=checkSetValue;
				} else {
					document[TBE_EDITOR.formname][theField].value=checkboxValue;
				}
			}else{
				document[TBE_EDITOR.formname][theField].value = evalFunc.evalObjValue(theFObj, document[TBE_EDITOR.formname][theField+"_hr"].value);
			}
			typo3form.fieldSet(theField, evallist, is_in, checkbox, checkboxValue);
		}
	}
};

// backwards compatibility for extensions
var typo3FormFieldSet = typo3form.fieldSet;
var typo3FormFieldGet = typo3form.fieldGet;

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Tobias Liebig <tobias.liebig@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

Ext.onReady(function() {
	// Only use placeholder JavaScript fallback in Internet Explorer
	if (!Ext.isIE) {
		return;
	}

	// TODO rewrite in ExtJS
	$$('[placeholder]').each(function(el) {
		if (el.getAttribute('placeholder') != "") {
			el.observe('TYPO3:focus', function() {
				var input = Ext.get(this);
				if (this.getValue() == this.getAttribute('placeholder')) {
					this.setValue('');
					input.removeClass('placeholder');
				}
			});
			el.observe('focus', function() { el.fire('TYPO3:focus'); });
			el.observe('TYPO3:blur', function() {
				var input = Ext.get(this);
				if (input.getValue() == '' || this.getValue() == this.getAttribute('placeholder')) {
					this.setValue(this.getAttribute('placeholder'));
					input.addClass('placeholder');
				}
			});
			el.observe('blur', function() { el.fire('TYPO3:blur'); });
			el.fire('TYPO3:blur');
		}
	});
});
Ext.ns('Ext.ux', 'Ext.ux.menu', 'Ext.ux.form');

Ext.ux.DateTimePicker = Ext.extend(Ext.DatePicker, {

	timeFormat: 'H:i',

	initComponent: function() {
		var t = this.timeFormat.split(':');
		this.hourFormat = t[0];
		this.minuteFormat = t[1];

		Ext.ux.DateTimePicker.superclass.initComponent.call(this);
	},

	/**
	 * Replaces any existing {@link #minDate} with the new value and refreshes the DatePicker.
	 * @param {Date} value The minimum date that can be selected
	 */
	setMinTime: function(dt) {
		this.minTime = dt;
		this.update(this.value, true);
	},

	/**
	 * Replaces any existing {@link #maxDate} with the new value and refreshes the DatePicker.
	 * @param {Date} value The maximum date that can be selected
	 */
	setMaxTime: function(dt) {
		this.maxTime = dt;
		this.update(this.value, true);
	},

	/**
	 * Returns the value of the date/time field
	 */
	getValue: function() {
		return this.addTimeToValue(this.value);
	},

	/**
	 * Sets the value of the date/time field
	 * @param {Date} value The date to set
	 */
	setValue: function(value) {
		var old = this.value;
		this.value = value.clearTime(true);
		if (this.el) {
			this.update(this.value);
		}
		this.hourField.setValue(value.format(this.hourFormat));
		this.minuteField.setValue(value.format(this.minuteFormat));
	},

	/**
	 * Sets the value of the time field
	 * @param {Date} value The date to set
	 */
	setTime: function(value) {
		this.hourField.setValue(value.format(this.hourFormat));
		this.minuteField.setValue(value.format(this.minuteFormat));
	},

	/**
	 * Updates the date value with the time entered
	 * @param {Date} value The date to which time should be added
	 */
	addTimeToValue: function(date) {
		return date.clearTime().add(Date.HOUR, this.hourField.getValue()).add(Date.MINUTE, this.minuteField.getValue());
	},

	onRender: function(container, position) {
		var m = [
			'<table cellspacing="0">',
			'<tr><td class="x-date-left"><a href="#" title="',
			this.prevText ,
			'">&#160;</a></td><td class="x-date-middle" align="center"></td><td class="x-date-right"><a href="#" title="',
			this.nextText ,
			'">&#160;</a></td></tr>',
			'<tr><td colspan="3"><table class="x-date-inner" cellspacing="0"><thead><tr>'
		];
		var dn = this.dayNames;
		for (var i = 0; i < 7; i++) {
			var d = this.startDay + i;
			if (d > 6) {
				d = d - 7;
			}
			m.push('<th><span>', dn[d].substr(0, 1), '</span></th>');
		}
		m[m.length] = "</tr></thead><tbody><tr>";
		for (var i = 0; i < 42; i++) {
			if (i % 7 == 0 && i != 0) {
				m[m.length] = "</tr><tr>";
			}
			m[m.length] = '<td><a href="#" hidefocus="on" class="x-date-date" tabIndex="1"><em><span></span></em></a></td>';
		}
		m.push('</tr></tbody></table></td></tr>',
			this.showToday ? '<tr><td colspan="3" class="x-date-bottom" align="center"></td></tr>' : '',
			'</table><div class="x-date-mp"></div>'
		);

		var el = document.createElement("div");
		el.className = "x-date-picker";
		el.innerHTML = m.join("");

		container.dom.insertBefore(el, position);

		this.el = Ext.get(el);
		this.eventEl = Ext.get(el.firstChild);

		new Ext.util.ClickRepeater(this.el.child("td.x-date-left a"), {
			handler: this.showPrevMonth,
			scope: this,
			preventDefault:true,
			stopDefault:true
		});

		new Ext.util.ClickRepeater(this.el.child("td.x-date-right a"), {
			handler: this.showNextMonth,
			scope: this,
			preventDefault:true,
			stopDefault:true
		});

		this.mon(this.eventEl, "mousewheel", this.handleMouseWheel, this);

		this.monthPicker = this.el.down('div.x-date-mp');
		this.monthPicker.enableDisplayMode('block');

		var kn = new Ext.KeyNav(this.eventEl, {
			"left": function(e) {
				e.ctrlKey ?
					this.showPrevMonth() :
					this.update(this.activeDate.add("d", -1));
			},

			"right": function(e) {
				e.ctrlKey ?
					this.showNextMonth() :
					this.update(this.activeDate.add("d", 1));
			},

			"up": function(e) {
				e.ctrlKey ?
					this.showNextYear() :
					this.update(this.activeDate.add("d", -7));
			},

			"down": function(e) {
				e.ctrlKey ?
					this.showPrevYear() :
					this.update(this.activeDate.add("d", 7));
			},

			"pageUp": function(e) {
				this.showNextMonth();
			},

			"pageDown": function(e) {
				this.showPrevMonth();
			},

			"enter": function(e) {
				e.stopPropagation();
				this.fireEvent("select", this, this.value);
				return true;
			},

			scope : this
		});

		this.mon(this.eventEl, "click", this.handleDateClick, this, {delegate: "a.x-date-date"});

		this.el.select("table.x-date-inner").unselectable();
		this.cells = this.el.select("table.x-date-inner tbody td");
		this.textNodes = this.el.query("table.x-date-inner tbody span");

		this.mbtn = new Ext.Button({
			text: "&#160;",
			tooltip: this.monthYearText,
			renderTo: this.el.child("td.x-date-middle", true)
		});

		this.mon(this.mbtn, 'click', this.showMonthPicker, this);
		this.mbtn.el.child('em').addClass("x-btn-arrow");

		if (this.showToday) {
			this.todayKeyListener = this.eventEl.addKeyListener(Ext.EventObject.SPACE, this.selectToday, this);
			var today = (new Date()).dateFormat(this.format);
			this.todayBtn = new Ext.Button({
				text: String.format(this.todayText, today),
				tooltip: String.format(this.todayTip, today),
				handler: this.selectToday,
				scope: this
			});
		}

		this.formPanel = new Ext.form.FormPanel({
			layout: 'column',
			renderTo: this.el.child("td.x-date-bottom", true),
			baseCls: 'x-plain',
			hideBorders: true,
			labelAlign: 'left',
			labelWidth: 10,
			forceLayout: true,
			items: [
				{
					columnWidth: .4,
					layout: 'form',
					baseCls: 'x-plain',
					items: [
						{
							xtype: 'textfield',
							id: this.getId() + '_hour',
							maxLength: 2,
							fieldLabel: '',
							labelWidth: 30,
							width: 30,
							minValue: 0,
							maxValue: 24,
							allowBlank: false,
							labelSeparator: '',
							tabIndex: 1,
							maskRe: /[0-9]/
						}
					]
				},
				{
					columnWidth: .3,
					layout: 'form',
					baseCls: 'x-plain',
					items: [
						{
							xtype: 'textfield',
							id:	this.getId() + '_minute',
							maxLength: 2,
							fieldLabel: ':',
							labelWidth: 10,
							width: 30,
							minValue: 0,
							maxValue: 59,
							allowBlank: false,
							labelSeparator: '',
							tabIndex: 2,
							maskRe: /[0-9]/
						}
					]
				},
				{
					columnWidth: .3,
					layout: 'form',
					baseCls: 'x-plain',
					items: [this.todayBtn]
				}
			]
		});

		this.hourField = Ext.getCmp(this.getId() + '_hour');
		this.minuteField = Ext.getCmp(this.getId() + '_minute');

		this.hourField.on('blur', function(field) {
			var old = field.value;
			var h = parseInt(field.getValue());
			if (h > 23) {
				field.setValue(old);
			}
		});

		this.minuteField.on('blur', function(field) {
			var old = field.value;
			var h = parseInt(field.getValue());
			if (h > 59) {
				field.setValue(old);
			}
		});

		if (Ext.isIE) {
			this.el.repaint();
		}
		this.update(this.value);
	},

	// private
	handleDateClick : function(e, t) {
		e.stopEvent();
		if (t.dateValue && !Ext.fly(t.parentNode).hasClass("x-date-disabled")) {
			this.setValue(this.addTimeToValue(new Date(t.dateValue)));
			this.fireEvent("select", this, this.value);
		}
	},

	selectToday : function() {
		if (this.todayBtn && !this.todayBtn.disabled) {
			this.setValue(new Date());
			this.fireEvent("select", this, this.value);
		}
	},

	update : function(date, forceRefresh) {
		Ext.ux.DateTimePicker.superclass.update.call(this, date, forceRefresh);

		if (this.showToday) {
			this.setTime(new Date());
		}
	}
});
Ext.reg('datetimepicker', Ext.ux.DateTimePicker);


Ext.ux.menu.DateTimeMenu = Ext.extend(Ext.menu.Menu, {
	enableScrolling : false,
	hideOnClick : true,
	cls: 'x-date-menu x-datetime-menu',
	initComponent : function() {

		Ext.apply(this, {
			plain: true,
			showSeparator: false,
			items: this.picker = new Ext.ux.DateTimePicker(Ext.apply({
				internalRender: this.strict || !Ext.isIE,
				ctCls: 'x-menu-datetime-item x-menu-date-item'
			}, this.initialConfig))
		});
		this.picker.purgeListeners();

		Ext.ux.menu.DateTimeMenu.superclass.initComponent.call(this);
		this.relayEvents(this.picker, ['select']);
		this.on('select', this.menuHide, this);
		if (this.handler) {
			this.on('select', this.handler, this.scope || this)
		}
	},
	menuHide: function() {
		if (this.hideOnClick) {
			this.hide(true);
		}
	}
});
Ext.reg('datetimemenu', Ext.ux.menu.DateTimeMenu);
/***************************************************************
 * extJS for TCEforms
 *
 * Copyright notice
 *
 * (c) 2009-2011 Steffen Kamper <info@sk-typo3.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

Ext.ns('TYPO3');

	// class to manipulate TCEFORMS
TYPO3.TCEFORMS = {

	init: function() {
		Ext.QuickTips.init();

		this.update();
	},

	update: function() {
		this.convertDateFieldsToDatePicker();
		this.convertTextareasResizable();
	},

	convertDateFieldsToDatePicker: function() {
		var dateFields = Ext.select("input[id^=tceforms-date]"), minDate, maxDate, lowerMatch, upperMatch;
		dateFields.each(function(element) {
			var index = element.dom.id.match(/tceforms-datefield-/) ? 0 : 1;
			var format = TYPO3.settings.datePickerUSmode ? TYPO3.settings.dateFormatUS : TYPO3.settings.dateFormat;
			var datepicker = element.next('span'), menu;

			// check for daterange
			var lowerMatch = element.dom.className.match(/lower-(\d+)\b/);
			minDate = Ext.isArray(lowerMatch) ? new Date(lowerMatch[1] * 1000) : null;
			var upperMatch = element.dom.className.match(/upper-(\d+)\b/);
			maxDate = Ext.isArray(upperMatch) ? new Date(upperMatch[1] * 1000) : null;

			if (index === 0) {
				menu = new Ext.menu.DateMenu({
					id: 'p' + element.dom.id,
					format: format[index],
					value: Date.parseDate(element.dom.value, format[index]),
					minDate: minDate,
					maxDate: maxDate,
					handler: function(picker, date){
						var relElement = Ext.getDom(picker.ownerCt.id.substring(1));
						relElement.value = date.format(format[index]);
						if (Ext.isFunction(relElement.onchange)) {
							relElement.onchange.call(relElement);
						}
					},
					listeners: {
						beforeshow: function(obj) {
							var relElement = Ext.getDom(obj.picker.ownerCt.id.substring(1));
							if (relElement.value) {
								obj.picker.setValue(Date.parseDate(relElement.value, format[index]));
							}
						}
					}
				});
			} else {
				menu = new Ext.ux.menu.DateTimeMenu({
					id: 'p' + element.dom.id,
					format: format[index],
					value: Date.parseDate(element.dom.value, format[index]),
					minDate: minDate,
					maxDate: maxDate,
					listeners: {
						beforeshow: function(obj) {
							var relElement = Ext.getDom(obj.picker.ownerCt.id.substring(1));
							if (relElement.value) {
								obj.picker.setValue(Date.parseDate(relElement.value, format[index]));
							}
						},
						select: function(picker) {
							var relElement = Ext.getDom(picker.ownerCt.id.substring(1));
							relElement.value = picker.getValue().format(format[index]);
							if (Ext.isFunction(relElement.onchange)) {
								relElement.onchange.call(relElement);
							}
						}
					}
				});
			}

			datepicker.removeAllListeners();
			datepicker.on('click', function(){
				menu.show(datepicker);
			});
		});
	},

	convertTextareasResizable: function() {
		var textAreas = Ext.select("textarea[id^=tceforms-textarea-]");
		textAreas.each(function(element) {
			if (TYPO3.settings.textareaFlexible) {
				var elasticTextarea = new Ext.ux.elasticTextArea().applyTo(element.dom.id, {
					minHeight: 50,
					maxHeight: TYPO3.settings.textareaMaxHeight
				});
			}
			if (TYPO3.settings.textareaResize) {
				element.addClass('resizable');
				var dwrapped = new Ext.Resizable(element.dom.id, {
					minWidth:  300,
					minHeight: 50,
					dynamic:   true
				});
			}
		});
	}

}
Ext.onReady(TYPO3.TCEFORMS.init, TYPO3.TCEFORMS);

	// Fix for slider TCA control in IE9
Ext.override(Ext.dd.DragTracker, {
	onMouseMove:function (e, target) {
		var isIE9 = Ext.isIE && (/msie 9/.test(navigator.userAgent.toLowerCase())) && document.documentMode != 6;
		if (this.active && Ext.isIE && !isIE9 && !e.browserEvent.button) {
			e.preventDefault();
			this.onMouseUp(e);
			return;
		}
		e.preventDefault();
		var xy = e.getXY(), s = this.startXY;
		this.lastXY = xy;
		if (!this.active) {
			if (Math.abs(s[0] - xy[0]) > this.tolerance || Math.abs(s[1] - xy[1]) > this.tolerance) {
				this.triggerStart(e);
			} else {
				return;
			}
		}
		this.fireEvent('mousemove', this, e);
		this.onDrag(e);
		this.fireEvent('drag', this, e);
	}
});
/*
 * This code has been copied from Project_CMS
 * Copyright (c) 2005 by Phillip Berndt (www.pberndt.com)
 *
 * Extended Textarea for IE and Firefox browsers
 * Features:
 *  - Possibility to place tabs in <textarea> elements using a simply <TAB> key
 *  - Auto-indenting of new lines
 *
 * License: GNU General Public License
 */

function checkBrowser() {
	browserName = navigator.appName;
	browserVer = parseInt(navigator.appVersion);

	ok = false;
	if (browserName == "Microsoft Internet Explorer" && browserVer >= 4) {
		ok = true;
	} else if (browserName == "Netscape" && browserVer >= 3) {
		ok = true;
	}

	return ok;
}

	// Automatically change all textarea elements
function changeTextareaElements() {
	if (!checkBrowser()) {
			// Stop unless we're using IE or Netscape (includes Mozilla family)
		return false;
	}

	document.textAreas = document.getElementsByTagName("textarea");

	for (i = 0; i < document.textAreas.length; i++) {
			// Only change if the class parameter contains "enable-tab"
		if (document.textAreas[i].className && document.textAreas[i].className.search(/(^| )enable-tab( |$)/) != -1) {
			document.textAreas[i].textAreaID = i;
			makeAdvancedTextArea(document.textAreas[i]);
		}
	}
}

	// Wait until the document is completely loaded.
	// Set a timeout instead of using the onLoad() event because it could be used by something else already.
window.setTimeout("changeTextareaElements();", 200);

	// Turn textarea elements into "better" ones. Actually this is just adding some lines of JavaScript...
function makeAdvancedTextArea(textArea) {
	if (textArea.tagName.toLowerCase() != "textarea") {
		return false;
	}

		// On attempt to leave the element:
		// Do not leave if this.dontLeave is true
	textArea.onblur = function(e) {
		if (!this.dontLeave) {
			return;
		}
		this.dontLeave = null;
		window.setTimeout("document.textAreas[" + this.textAreaID + "].restoreFocus()", 1);
		return false;
	}

		// Set the focus back to the element and move the cursor to the correct position.
	textArea.restoreFocus = function() {
		this.focus();

		if (this.caretPos) {
			this.caretPos.collapse(false);
			this.caretPos.select();
		}
	}

		// Determine the current cursor position
	textArea.getCursorPos = function() {
		if (this.selectionStart) {
			currPos = this.selectionStart;
		} else if (this.caretPos) {	// This is very tricky in IE :-(
			oldText = this.caretPos.text;
			finder = "--getcurrpos" + Math.round(Math.random() * 10000) + "--";
			this.caretPos.text += finder;
			currPos = this.value.indexOf(finder);

			this.caretPos.moveStart('character', -finder.length);
			this.caretPos.text = "";

			this.caretPos.scrollIntoView(true);
		} else {
			return;
		}

		return currPos;
	}

		// On tab, insert a tabulator. Otherwise, check if a slash (/) was pressed.
	textArea.onkeydown = function(e) {
		if (this.selectionStart == null &! this.createTextRange) {
			return;
		}
		if (!e) {
			e = window.event;
		}

			// Tabulator
		if (e.keyCode == 9) {
			this.dontLeave = true;
			this.textInsert(String.fromCharCode(9));
		}

			// Newline
		if (e.keyCode == 13) {
				// Get the cursor position
			currPos = this.getCursorPos();

				// Search the last line
			lastLine = "";
			for (i = currPos - 1; i >= 0; i--) {
				if(this.value.substring(i, i + 1) == '\n') {
					break;
				}
			}
			lastLine = this.value.substring(i + 1, currPos);

				// Search for whitespaces in the current line
			whiteSpace = "";
			for (i = 0; i < lastLine.length; i++) {
				if (lastLine.substring(i, i + 1) == '\t') {
					whiteSpace += "\t";
				} else if (lastLine.substring(i, i + 1) == ' ') {
					whiteSpace += " ";
				} else {
					break;
				}
			}

				// Another ugly IE hack
			if (navigator.appVersion.match(/MSIE/)) {
				whiteSpace = "\\n" + whiteSpace;
			}

				// Insert whitespaces
			window.setTimeout("document.textAreas["+this.textAreaID+"].textInsert(\""+whiteSpace+"\");", 1);
		}
	}

		// Save the current cursor position in IE
	textArea.onkeyup = textArea.onclick = textArea.onselect = function(e) {
		if (this.createTextRange) {
			this.caretPos = document.selection.createRange().duplicate();
		}
	}

		// Insert text at the current cursor position
	textArea.textInsert = function(insertText) {
		if (this.selectionStart != null) {
			var savedScrollTop = this.scrollTop;
			var begin = this.selectionStart;
			var end = this.selectionEnd;
			if (end > begin + 1) {
				this.value = this.value.substr(0, begin) + insertText + this.value.substr(end);
			} else {
				this.value = this.value.substr(0, begin) + insertText + this.value.substr(begin);
			}

			this.selectionStart = begin + insertText.length;
			this.selectionEnd = begin + insertText.length;
			this.scrollTop = savedScrollTop;
		} else if (this.caretPos) {
			this.caretPos.text = insertText;
			this.caretPos.scrollIntoView(true);
		} else {
			text.value += insertText;
		}

		this.focus();
	}
}
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2011 Steffen Kamper <steffen@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

Ext.ns('TYPO3', 'TYPO3.CSH.ExtDirect');

/**
 * Class to show tooltips for links that have the css t3-help-link
 * need the tags data-table and data-field (HTML5)
 */


TYPO3.ContextHelp = function() {

	/**
	 * Cache for CSH
	 * @type {Ext.util.MixedCollection}
	 */
	var cshHelp = new Ext.util.MixedCollection(true),
	tip;

	/**
	 * Shows the tooltip, triggered from mouseover event handler
	 *
	 */
	function showToolTipHelp() {
		var link = tip.triggerElement;
		if (!link) {
			return false;
		}
		var table = link.getAttribute('data-table');
		var field = link.getAttribute('data-field');
		var key = table + '.' + field;
		var response = cshHelp.key(key);
		tip.target = tip.triggerElement;
		if (response) {
			updateTip(response);
		} else {
				// If a table is defined, use ExtDirect call to get the tooltip's content
			if (table) {
				var description = '';
				if (typeof(top.TYPO3.LLL) !== 'undefined') {
					description = top.TYPO3.LLL.core.csh_tooltip_loading;
				} else if (opener && typeof(opener.top.TYPO3.LLL) !== 'undefined') {
					description = opener.top.TYPO3.LLL.core.csh_tooltip_loading;
				}

					// Clear old tooltip contents
				updateTip({
					description: description,
					cshLink: '',
					moreInfo: '',
					title: ''
				});
					// Load content
				TYPO3.CSH.ExtDirect.getTableContextHelp(table, function(response, options) {
					Ext.iterate(response, function(key, value){
						cshHelp.add(value);
						if (key === field) {
							updateTip(value);
								// Need to re-position because the height may have increased
							tip.show();
						}
					});
				}, this);

				// No table was given, use directly title and description
			} else {
				updateTip({
					description: link.getAttribute('data-description'),
					cshLink: '',
					moreInfo: '',
					title: link.getAttribute('data-title')
				});
			}
		}
	}

	/**
	 * Update tooltip message
	 *
	 * @param {Object} response
	 */
	function updateTip(response) {
		tip.body.dom.innerHTML = response.description;
		tip.cshLink = response.id;
		tip.moreInfo = response.moreInfo;
		if (tip.moreInfo) {
			tip.addClass('tipIsLinked');
		}
		tip.setTitle(response.title);
		tip.doAutoWidth();
	}

	return {
		/**
		 * Constructor
		 */
		init: function() {
			tip = new Ext.ToolTip({
				title: 'CSH', // needs a title for init because of the markup
				html: '',
					// The tooltip will appear above the label, if viewport allows
				anchor: 'bottom',
				minWidth: 160,
				maxWidth: 240,
				target: Ext.getBody(),
				delegate: 'span.t3-help-link',
				renderTo: Ext.getBody(),
				cls: 'typo3-csh-tooltip',
				shadow: false,
				dismissDelay: 0, // tooltip stays while mouse is over target
				autoHide: true,
				showDelay: 1000, // show after 1 second
				hideDelay: 300, // hide after 0.3 seconds
				closable: true,
				isMouseOver: false,
				listeners: {
					beforeshow: showToolTipHelp,
					render: function(tip) {
						tip.body.on({
							'click': {
								fn: function(event) {
									event.stopEvent();
									if (tip.moreInfo) {
										try {
											top.TYPO3.ContextHelpWindow.open(tip.cshLink);
										} catch(e) {
											// do nothing
										}
									}
									tip.hide();
								}
							}
						});
						tip.el.on({
							'mouseover': {
								fn: function() {
									if (tip.moreInfo) {
										tip.isMouseOver = true;
									}
								}
							},
							'mouseout': {
								fn: function() {
									if (tip.moreInfo) {
										tip.isMouseOver = false;
										tip.hide.defer(tip.hideDelay, tip, []);
									}
								}
							}
						});
					},
					hide: function(tip) {
						tip.setTitle('');
						tip.body.dom.innerHTML = '';
					},
					beforehide: function(tip) {
						return !tip.isMouseOver;
					},
					scope: this
				}
			});

			Ext.getBody().on({
				'keydown': {
					fn: function() {
						tip.hide();
					}
				},
				'click': {
					fn: function() {
						tip.hide();
					}
				}
			});

			/**
			 * Adds a sequence to Ext.TooltTip::showAt so as to increase vertical offset when anchor position is 'botton'
			 * This positions the tip box closer to the target element when the anchor is on the bottom side of the box
			 * When anchor position is 'top' or 'bottom', the anchor is pushed slightly to the left in order to align with the help icon, if any
			 *
			 */
			Ext.ToolTip.prototype.showAt = Ext.ToolTip.prototype.showAt.createSequence(
				function() {
					var ap = this.getAnchorPosition().charAt(0);
					if (this.anchorToTarget && !this.trackMouse) {
						switch (ap) {
							case 'b':
								var xy = this.getPosition();
								this.setPagePosition(xy[0]-10, xy[1]+5);
								break;
							case 't':
								var xy = this.getPosition();
								this.setPagePosition(xy[0]-10, xy[1]);
								break;
						}
					}
				}
			);

		},

		/**
		 * Opens the help window, triggered from click event handler
		 *
		 * @param {Event} event
		 * @param {Node} link
		 */
		openHelpWindow: function(event, link) {
			var id = link.getAttribute('data-table') + '.' + link.getAttribute('data-field');
			event.stopEvent();
			top.TYPO3.ContextHelpWindow.open(id);
		}
	}
}();

/**
 * Calls the init on Ext.onReady
 */
Ext.onReady(TYPO3.ContextHelp.init, TYPO3.ContextHelp);

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2011 Steffen Kamper <info@sk-typo3.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Flashmessage rendered by ExtJS
 *
 *
 * @author Steffen Kamper <info@sk-typo3.de>
 */
Ext.ns('TYPO3');

/**
 * Object for named severities
 */
TYPO3.Severity = {
	notice: 0,
	information: 1,
	ok: 2,
	warning: 3,
	error: 4
};

/**
 * @class TYPO3.Flashmessage
 * Passive popup box singleton
 * @singleton
 *
 * Example (Information message):
 * TYPO3.Flashmessage.display(1, 'TYPO3 Backend - Version 4.4', 'Ready for take off', 3);
 */
TYPO3.Flashmessage = function() {
	var messageContainer;
	var severities = ['notice', 'information', 'ok', 'warning', 'error'];

	function createBox(severity, title, message) {
		return ['<div class="typo3-message message-', severity, '" style="width: 400px">',
				'<div class="t3-icon t3-icon-actions t3-icon-actions-message t3-icon-actions-message-close t3-icon-message-' + severity + '-close"></div>',
				'<div class="header-container">',
				'<div class="message-header">', title, '</div>',
				'</div>',
				'<div class="message-body">', message, '</div>',
				'</div>'].join('');
	}

	return {
		/**
		 * Shows popup
		 * @member TYPO3.Flashmessage
		 * @param int severity (0=notice, 1=information, 2=ok, 3=warning, 4=error)
		 * @param string title
		 * @param string message
		 * @param float duration in sec (default 5)
		 */
		display : function(severity, title, message, duration) {
			duration = duration || 5;
			if (!messageContainer) {
				messageContainer = Ext.DomHelper.insertFirst(document.body, {
					id   : 'msg-div',
					style: 'position:absolute;z-index:10000'
				}, true);
			}

			var box = Ext.DomHelper.append(messageContainer, {
				html: createBox(severities[severity], title, message)
			}, true);
			messageContainer.alignTo(document, 't-t');
			box.child('.t3-icon-actions-message-close').on('click',	function (e, t, o) {
				var node;
				node = new Ext.Element(Ext.get(t).findParent('div.typo3-message'));
				node.hide();
				Ext.removeNode(node.dom);
			}, box);
			box.slideIn('t').pause(duration).ghost('t', {remove: true});
		}
	};
}();

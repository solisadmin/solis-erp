//v.3.6 build 130619

/*
 Copyright Dinamenta, UAB http://www.dhtmlx.com
 You allowed to use this component or parts of it under GPL terms
 To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
 */
dhtmlXForm.prototype.items.colorpicker = {ev: !1, inp: null, colorpicker: {}, cz: {}, render: function (a, c) {
        var b = this;
        a._type = "colorpicker";
        a._enabled = !0;
        this.doAddLabel(a, c);
        this.doAddInput(a, c, "INPUT", "TEXT", !0, !0, "dhxform_textarea");
        this.doAttachChangeLS(a);
        a._value = c.value || "";
        a.childNodes[a._ll ? 1 : 0].childNodes[0].value = a._value;
        this.cz[a._idd] = document.createElement("DIV");
        this.cz[a._idd].style.position = "absolute";
        this.cz[a._idd].style.top = "0px";
        this.cz[a._idd].style.zIndex = 2000;
        document.body.insertBefore(this.cz[a._idd],
                document.body.firstChild);
        this.colorpicker[a._idd] = new dhtmlXColorPicker(this.cz[a._idd], null, a.getForm()._s2b(c.enableCustomColors), !0);
        this.colorpicker[a._idd].setImagePath(c.imagePath || "");
        this.colorpicker[a._idd].setSkin("");
        this.colorpicker[a._idd].init();
        typeof c.customColors != "undefined" && this.colorpicker[a._idd].setCustomColors(c.customColors);
        this.colorpicker[a._idd].elements.cs_Content.onclick = function (a) {
            (a || event).cancelBubble = !0
        };
        this.colorpicker[a._idd].setOnSelectHandler(function (d) {
            if (a._value !=
                    d && !(a.checkEvent("onBeforeChange") && a.callEvent("onBeforeChange", [a._idd, a._value, d]) !== !0))
                a._value = d, b.setValue(a, d), a.callEvent("onChange", [a._idd, a._value])
        });
        a.childNodes[a._ll ? 1 : 0].childNodes[0]._idd = a._idd;
        a.childNodes[a._ll ? 1 : 0].childNodes[0].onclick = function () {
            b.colorpicker[this._idd].isVisible() ? (b.colorpicker[this._idd].hide(), b.inp = null) : (b.checkEnteredValue(this.parentNode.parentNode), b.colorpicker[this._idd].setPosition(getAbsoluteLeft(this), getAbsoluteTop(this) + this.offsetHeight - 1),
                    b.colorpicker[this._idd].setColor(a._value), b.colorpicker[this._idd].show(), b.inp = this._idd)
        };
        a.childNodes[a._ll ? 1 : 0].childNodes[0].onblur = function () {
            var a = this.parentNode.parentNode;
            a.getForm()._ccDeactivate(a._idd);
            b.checkEnteredValue(this.parentNode.parentNode);
            a.getForm().callEvent("onBlur", [a._idd]);
            a = null
        };
        if (!this.ev)
            _isIE ? document.body.attachEvent("onclick", this.clickEvent) : window.addEventListener("click", this.clickEvent, !1), this.ev = !0;
        return this
    }, clickEvent: function () {
        dhtmlXForm.prototype.items.colorpicker.hideAllColorPickers()
    },
    hideAllColorPickers: function () {
        for (var a in this.colorpicker)
            a != this.inp && this.colorpicker[a].hide();
        this.inp = null
    }, getColorPicker: function (a) {
        return this.colorpicker[a._idd]
    }, destruct: function (a) {
        this.colorpicker[a._idd].elements.cs_Content.onclick = null;
        this.colorpicker[a._idd].unload && this.colorpicker[a._idd].unload();
        this.colorpicker[a._idd] = null;
        try {
            delete this.colorpicker[a._idd]
        } catch (c) {
        }
        this.cz[a._idd].parentNode.removeChild(this.cz[a._idd]);
        this.cz[a._idd] = null;
        try {
            delete this.cz[a._idd]
        } catch (b) {
        }
        var d =
                0, e;
        for (e in this.colorpicker)
            d++;
        if (d == 0)
            _isIE ? document.body.detachEvent("onclick", this.clickEvent) : window.removeEventListener("click", this.clickEvent, !1), this.ev = !1;
        a.childNodes[a._ll ? 1 : 0].childNodes[0]._idd = null;
        a.childNodes[a._ll ? 1 : 0].childNodes[0].onclick = null;
        a.childNodes[a._ll ? 1 : 0].childNodes[0].onblur = null;
        this.d2(a);
        a = null
    }, checkEnteredValue: function (a) {
        this.setValue(a, a.childNodes[a._ll ? 1 : 0].childNodes[0].value)
    }};
(function () {
    for (var a in{doAddLabel:1, doAddInput:1, doUnloadNestedLists:1, setText:1, getText:1, enable:1, disable:1, isEnabled:1, setWidth:1, setReadonly:1, isReadonly:1, setValue:1, getValue:1, updateValue:1, setFocus:1, getInput:1})
        dhtmlXForm.prototype.items.colorpicker[a] = dhtmlXForm.prototype.items.input[a]
})();
dhtmlXForm.prototype.items.colorpicker.doAttachChangeLS = dhtmlXForm.prototype.items.select.doAttachChangeLS;
dhtmlXForm.prototype.items.colorpicker.d2 = dhtmlXForm.prototype.items.input.destruct;
dhtmlXForm.prototype.getColorPicker = function (a) {
    return this.doWithItem(a, "getColorPicker")
};
if (typeof dhtmlXColorPicker != "undefined")
    dhtmlXColorPicker.prototype.unload = function () {
        this.elements.cs_SelectorVer.parentNode.removeChild(this.elements.cs_SelectorVer);
        this.elements.cs_SelectorHor.parentNode.removeChild(this.elements.cs_SelectorHor);
        this.elements.cs_SelectorVer = null;
        this.elements.cs_SelectorHor = null;
        this.elements.cs_SelectorDiv.ondblclick = null;
        this.elements.cs_SelectorDiv.onmousedown = null;
        this.elements.cs_SelectorDiv.z = null;
        this.elements.cs_SelectorDiv.parentNode.removeChild(this.elements.cs_SelectorDiv);
        this.elements.cs_SelectorDiv = null;
        this.elements.cs_LumSelectArrow.onmousedown = null;
        this.elements.cs_LumSelectArrow.z = null;
        this.elements.cs_LumSelectArrow.parentNode.removeChild(this.elements.cs_LumSelectArrow);
        this.elements.cs_LumSelectArrow = null;
        this.elements.cs_LumSelectLine.parentNode.removeChild(this.elements.cs_LumSelectLine);
        for (this.elements.cs_LumSelectLine = null; this.elements.cs_LumSelect.childNodes.length > 0; )
            this.elements.cs_LumSelect.removeChild(this.elements.cs_LumSelect.childNodes[0]);
        this.elements.cs_LumSelect.ondblclick = null;
        this.elements.cs_LumSelect.onmousedown = null;
        this.elements.cs_LumSelect.z = null;
        this.elements.cs_LumSelect.parentNode.removeChild(this.elements.cs_LumSelect);
        this.elements.cs_LumSelect = null;
        this.elements.cs_EndColor.parentNode.removeChild(this.elements.cs_EndColor);
        this.elements.cs_EndColor = null;
        this.elements.cs_InputHue.onchange = null;
        this.elements.cs_InputHue.z = null;
        this.elements.cs_InputHue.parentNode.removeChild(this.elements.cs_InputHue);
        this.elements.cs_InputHue =
                null;
        this.elements.cs_InputRed.onchange = null;
        this.elements.cs_InputRed.z = null;
        this.elements.cs_InputRed.parentNode.removeChild(this.elements.cs_InputRed);
        this.elements.cs_InputRed = null;
        this.elements.cs_InputSat.onchange = null;
        this.elements.cs_InputSat.z = null;
        this.elements.cs_InputSat.parentNode.removeChild(this.elements.cs_InputSat);
        this.elements.cs_InputSat = null;
        this.elements.cs_InputGreen.onchange = null;
        this.elements.cs_InputGreen.z = null;
        this.elements.cs_InputGreen.parentNode.removeChild(this.elements.cs_InputGreen);
        this.elements.cs_InputGreen = null;
        this.elements.cs_Hex.onchange = null;
        this.elements.cs_Hex.z = null;
        this.elements.cs_Hex.parentNode.removeChild(this.elements.cs_Hex);
        this.elements.cs_Hex = null;
        this.elements.cs_InputLum.onchange = null;
        this.elements.cs_InputLum.z = null;
        this.elements.cs_InputLum.parentNode.removeChild(this.elements.cs_InputLum);
        this.elements.cs_InputLum = null;
        this.elements.cs_InputBlue.onchange = null;
        this.elements.cs_InputBlue.z = null;
        this.elements.cs_InputBlue.parentNode.removeChild(this.elements.cs_InputBlue);
        this.elements.cs_InputBlue = null;
        this.elements.cs_ButtonOk.onclick = null;
        this.elements.cs_ButtonOk.onmouseout = null;
        this.elements.cs_ButtonOk.onmouseover = null;
        this.elements.cs_ButtonOk.z = null;
        this.elements.cs_ButtonOk.parentNode.removeChild(this.elements.cs_ButtonOk);
        this.elements.cs_ButtonOk = null;
        this.elements.cs_ButtonCancel.onclick = null;
        this.elements.cs_ButtonCancel.onmouseout = null;
        this.elements.cs_ButtonCancel.onmouseover = null;
        this.elements.cs_ButtonCancel.z = null;
        this.elements.cs_ButtonCancel.parentNode.removeChild(this.elements.cs_ButtonCancel);
        this.elements.cs_ButtonCancel = null;
        this.elements.cs_ContentTable.parentNode.removeChild(this.elements.cs_ContentTable);
        this.elements.cs_ContentTable = null;
        this.elements.cs_Content.parentNode.removeChild(this.elements.cs_Content);
        this.z = this.elements.cs_Content = null;
        this.detachAllEvents();
        this.customColors = this.linkToObjects = this.language = this.elements = this.container = this._changeValueHEX = this._changeValueRGB = this._changeValueHSV = this._selectCustomColor = this._getColorHEX = this._reinitCustomColors = this._initCustomColors =
                this._hex2dec = this._dec2hex = this._colorizeLum = this._drawLum = this._rgb2hsv = this._hsv2rgb = this._drawValues = this._calculateColor = this._getOffsetLeft = this._getOffsetTop = this._getOffset = this._stopMoveLum = this._mouseMoveLum = this._startMoveLum = this._stopMoveColor = this._mouseMoveColor = this._startMoveColor = this._setLumPos = this._getScrollers = this._setCrossPos = this._initEvents = this._initCsIdElement = this.unload = this.showA = this.show = this.normalButton = this.hoverButton = this.isVisible = this.setSkin = this.loadUserLanguage =
                this.init = this.setImagePath = this.hideOnSelect = this.linkTo = this.getSelectedColor = this.setOnCancelHandler = this.setOnSelectHandler = this.hide = this.setPosition = this.close = this.setColor = this.setCustomColors = this.redraw = this.restoreFromHEX = this.restoreFromHSV = this.restoreFromRGB = this.addCustomColor = this.restoreColor = this.saveColor = this.clickCancel = this.clickOk = this.resetHandlers = this.generate = this.detachAllEvents = this.detachEvent = this.eventCatcher = this.checkEvent = this.callEvent = this.attachEvent = null
    };

//v.3.6 build 130619

/*
 CopyrigDinamenta, UABTD. http://www.dhtmlx.com
 You allowed to use this component or parts of it under GPL terms
 To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
 */
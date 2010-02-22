
function UriAliasHandlerInit(editingMode, autoMode) {
    var uriAliasHandler = new UriAliasHandler(editingMode, autoMode);
    
    //  Events supported
    uriAliasHandler.enableButton.onclick = function() {
        uriAliasHandler.checkEnableButtonState(this.checked);
    }
    uriAliasHandler.autoModeButton.onclick = function() {
        uriAliasHandler.checkAutoModeButtonState(this.checked);
    }
    uriAliasHandler.titleOfPage.onkeyup = function() {
        uriAliasHandler.updateUriAliasFromTitle();
    }
    uriAliasHandler.aliasInputText.onkeyup = function() {
        uriAliasHandler.updateUriAliasFromInputText();
    }

    //  Perform first check
    uriAliasHandler.checkEnableButtonState(uriAliasHandler.enableButton.checked);
}

function UriAliasHandler(editingMode, autoMode)
{
    var allowSlashes            = false;
    var debug                   = 0;
    var enableButton            = "section_uriAliasEnable";
    var autoModeButton          = "section_uriAliasAutoMode";
    var uriAlias                = "section_uriAlias";
    var uriAliasDisplay         = "uriAliasDisplay";
    var titleOfPage             = "section[title]";
    var aliasInputText          = "aliasInputText";
    var uriAliasEnableBox       = "uriAliasEnableBox";
    var uriAliasAutoModeBox     = "uriAliasAutoModeBox";
    var uriAliasDisplayBox      = "uriAliasDisplayBox";

    this.editingMode            = (typeof editingMode != "undefined") ? editingMode : "add";
    this.autoMode               = (typeof autoMode != "undefined") ? autoMode : "auto";
    this.enableButton           = document.getElementById(enableButton);
    this.autoModeButton         = document.getElementById(autoModeButton);
    this.titleOfPage            = document.getElementById(titleOfPage);
    this.aliasInputText         = document.getElementById(aliasInputText);
    this.uriAlias               = document.getElementById(uriAlias);
    this.uriAliasDisplay        = document.getElementById(uriAliasDisplay);
    this.aliasBackup            = this.uriAlias;
    this.allowSlashes           = allowSlashes;
    this.uriAliasEnableBox      = document.getElementById(uriAliasEnableBox);
    this.uriAliasAutoModeBox    = document.getElementById(uriAliasAutoModeBox);
    this.uriAliasDisplayBox     = document.getElementById(uriAliasDisplayBox);
    this.translit = new Translit();

    //  Set auto mode checkbox depending on autoMode
    if (this.editingMode == 'add') {
        this.autoModeButton.checked = true;
    }
    this.checkEnableButtonState = function(EnableButtonState) {
        if (EnableButtonState) {
            this.uriAliasDisplayBox.style.display = "block";
            this.uriAliasAutoModeBox.style.display = "block";
            this.uriAlias.value = this.aliasBackup.value;
            this.checkAutoModeButtonState();
        } else {
            this.uriAliasDisplayBox.style.display = "none";
            this.uriAliasAutoModeBox.style.display = "none";
            this.aliasBackup.value = this.uriAlias.value;
            this.uriAlias.value = "";
        }
    }

    this.checkAutoModeButtonState = function() {
        if (this.autoModeButton.checked) {
            this.autoMode = "auto";
            this.aliasInputText.style.display = "none";
            this.updateUriAliasFromTitle();
        } else {
            this.autoMode = "manual"
            this.aliasInputText.style.display = "inline";
            if (this.uriAlias.value == "") {
                this.updateUriAliasFromInputText();
            }
        }
    }

    this.updateUriAliasFromTitle = function() {
        if (this.enableButton.checked && this.autoMode == "auto") {
            input = this.titleOfPage.value;
            this.uriAlias.value = this.uriAliasDisplay.innerHTML = this.translit.UrlTranslit(input, this.allowSlashes);
        }
    }
    this.updateUriAliasFromInputText = function() {
        input = this.aliasInputText.value;
        this.uriAlias.value = this.uriAliasDisplay.innerHTML = this.translit.UrlTranslit(input, this.allowSlashes);
    }

    this.debug = function() {
        debugMessage = "Debug Infos :\n"
        debugMessage += (this.enableButton.checked)
            ? "uriAlias enabled\n"
            : "uriAlias disabled\n";
        debugMessage += "editing mode = " +this.editingMode +"\n";
        debugMessage += "auto mode = " +this.autoMode +"\n";
        debugMessage += "titleOfPage = " +this.titleOfPage.value +"\n";
        debugMessage += "uriAlias = " +this.uriAlias.value +"\n";
        debugMessage += "uriAlias Backup = " +this.aliasBackup.value +"\n";
        alert(debugMessage);
    }

    if (debug) {
        this.debug();
    }
}
    


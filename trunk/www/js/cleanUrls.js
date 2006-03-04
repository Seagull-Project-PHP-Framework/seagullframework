


function UriAliasHandler(editingMode, autoMode)
{
    var debug                   = 0;
    var uriAliasButton          = "section[uri_alias_enable]";
    var uriAutoAliasButton      = "section[uri_auto_alias]";
    var uriAlias                = "section[uri_alias]";
    var uriAliasDisplay         = "uriAliasDisplay";
    var allowSlashes            = false;
    var titleOfPage             = "section[title]";
    var aliasInputText          = "aliasInputText";
    var uriAliasEnableBox       = "uriAliasEnableBox";
    var uriAutoAliasBox         = "uriAutoAliasBox";
    var uriAliasBox             = "uriAliasBox";

    this.editingMode            = (typeof editingMode != "undefined") ? editingMode : "add";
    this.autoMode               = (typeof autoMode != "undefined") ? autoMode : "auto";
    this.uriAliasEnableButton   = document.getElementById(uriAliasButton);
    this.uriAutoAliasButton     = document.getElementById(uriAutoAliasButton);
    this.titleOfPage            = document.getElementById(titleOfPage);
    this.aliasInputText         = document.getElementById(aliasInputText);
    this.uriAlias               = document.getElementById(uriAlias);
    this.uriAliasDisplay        = document.getElementById(uriAliasDisplay);
    this.aliasBackup            = this.uriAlias;
    this.allowSlashes           = allowSlashes;
    this.uriAliasEnableBox      = document.getElementById(uriAliasEnableBox);
    this.uriAutoAliasBox        = document.getElementById(uriAutoAliasBox);
    this.uriAliasBox            = document.getElementById(uriAliasBox);
    this.translit = new Translit();

    this.checkEnableButtonState = function(EnableButtonState) {
        if (EnableButtonState) {
            this.uriAliasBox.style.display = "block";
            this.uriAutoAliasBox.style.display = "block";
            this.uriAlias.value = this.aliasBackup.value;
            if (this.editingMode == "edit") {
                this.uriAutoAliasButton.checked = false;
                this.uriAutoAliasButton.disabled = true;
                this.aliasInputText.value = this.uriAlias.value;
                this.checkAutoAliasButtonState();
            }
        } else {
            this.uriAliasBox.style.display = "none";
            this.uriAutoAliasBox.style.display = "none";
            this.aliasBackup.value = this.uriAlias.value;
            this.uriAlias.value = "";
        }
    }

    this.checkAutoAliasButtonState = function() {
        if (this.uriAutoAliasButton.checked) {
            this.autoMode = "auto";
            this.aliasInputText.style.display = "none";
        } else {
            this.autoMode = "manual"
            this.aliasInputText.style.display = "inline";
        }
        this.updateUriAlias();
    }

    this.updateUriAlias = function() {
        if (this.uriAliasEnableButton.checked) {
            if (this.autoMode == "auto") {
                input = this.titleOfPage.value;
            } else{
                input = this.aliasInputText.value;
            }
            this.uriAlias.value = this.uriAliasDisplay.textContent = this.translit.UrlTranslit(input, this.allowSlashes);
        }
    }

    this.debug = function() {
        debugMessage = "Debug Infos :\n"
        debugMessage += (this.uriAliasEnableButton.checked)
            ? "uriAlias enabled\n"
            : "uriAlias disabled\n";
        debugMessage += "editing mode = " +this.editingMode +"\n";
        debugMessage += "auto mode = " +this.autoMode +"\n";
        debugMessage += "titleOfPage = " +this.titleOfPage.value +"\n";
        debugMessage += "uriAlias = " +this.uriAlias.value +"\n";
        debugMessage += "uriAlias Backup = " +this.aliasBackup.value +"\n";
        alert(debugMessage);
    }

    //  Events supported
    this.uriAliasEnableButton.onclick = function() {
        checkEnableButtonState(this.checked);
    }
    this.uriAutoAliasButton.onclick = function() {
        checkAutoAliasButtonState(this.checked);
    }
    this.titleOfPage.onkeyup = function() {
        updateUriAlias();
    }
    this.aliasInputText.onkeyup = function() {
        updateUriAlias();
    }

    this.checkEnableButtonState(uriAliasEnableButton.checked);

    if (debug) {
        this.debug();
    }
}
    


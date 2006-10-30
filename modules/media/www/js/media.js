
var MediaList = {
    debug: 0,
    provider: new MediaAjaxProvider(),
    
    options: {
        byTypeId: '',
        byDateRange: '',
        viewType: 'thumb'
    },
    
    aMedia:     Array(),
    aValid:     Array(),
    aInvalid:   Array(),
    
    init: function() {
        var aTmp = $A(document.getElementsByClassName('thumbBox'));
        aTmp.each(function(media) {
            var id = media.id.replace('media', '');
            MediaList.aMedia.push(id);
            MediaList.aValid = MediaList.aMedia;
        });
        
        if (MediaList.debug) {
            bod = document.getElementsByTagName('body')[0];
            debug = document.createElement('div');
            valid = document.createElement('div');
            valid.id = 'validItems';
            invalid = document.createElement('div');
            invalid.id = 'invalidItems';
        }
    },
    
    switchViewType: function() {
        var options = this.options;

        if (this.options.viewType == 'thumb') {
            this.options.viewType = 'list';
        } else {
            this.options.viewType = 'thumb';
        }

        Media.narrowResults(options);
    },
    
    narrowResults: function(options) {
        if (!options) {
            //  we want to clear any filter
            this.options.byTypeId = this.options.byDateRange = null;
            $('filerByType').selectedIndex = 0;
            $('filerByDate').selectedIndex = 0;
        } else {
            if (typeof options.byTypeId != "undefined") {
                this.options.byTypeId = options.byTypeId;
            }
            if (typeof options.byDateRange != "undefined") {
                this.options.byDateRange = options.byDateRange;
            }
            if (typeof options.viewType != "undefined") {
                this.options.viewType = options.viewType;
            }
        }

        var aMatching = MediaList.getValidMedia(this.options);
        
        MediaList.updateValidMedia(aMatching);
        
        return false;
    },
    
    getValidMedia: function(options) {
        var HW = new MediaAjaxProvider();
        var aValidIds = HW.getValidIds(options);

        return aValidIds;
    },
    
    showAll: function() {
        MediaList.aInvalid = MediaList.aInvalid.clear();
        MediaList.aValid = MediaList.aMedia;
        MediaList.refreshScreen();
        
        return false;
    },
    
    updateValidMedia: function(aValidItems) {
        //  make a copy of former valid media
        var aOldValid = MediaList.aValid;
        var aToHide = new Array();
        var aToShow = new Array();

        aOldValid.each( function(mediaId) {
            if (aValidItems.indexOf(mediaId) == -1) {
                aToHide.push(mediaId);
            }
        });
        
        aValidItems.each( function(mediaId) {
            //alert('id ' +mediaId +' indexOf aOldValid ' +aOldValid.indexOf(mediaId));
            if (aOldValid.indexOf(mediaId) == -1) {
                aToShow.push(mediaId);
            }
        });
        
        MediaList.aValid = aValidItems;
        
        if (this.debug) {
            MediaList.aOldValid = aOldValid;
            MediaList.aValidItems = aValidItems;
            MediaList.aToHide = aToHide;
            MediaList.aToShow = aToShow;
            MediaList._debug();
        }
        
        MediaList.refreshScreen(aToHide, aToShow);
    },
    
    deleteMediaById: function(mediaId) {
        if (confirmDelete('media')) {
            var ok = this.provider.deleteMediaById(mediaId);
            if(ok.messageType == "info") {
                // and remove it from screen
                this.hideItem(mediaId);
            }
            Element.update('ajaxMessage', '<div class="' +ok.messageType +'Message">' +ok.message +'</div>');
        }
        return false;
    },
    
    refreshScreen: function(aToHide, aToShow) {
        aToHide.each(function(mediaId) {
             MediaList.hideItem(mediaId);
        });
        
        aToShow.each(function(mediaId) {
             MediaList.showItem(mediaId);
        });
    },
    
    hideItem: function(itemId) {
        //Effect.Shrink($('media' +itemId));
        Element.hide($('media' +itemId));
    },
    
    showItem: function(itemId) {
        //Effect.Grow($('media' +itemId));
        Element.show($('media' +itemId));
    },
    
    _debug: function() {
        confirm('Options : \n' +
              'By type : ' +this.options.byTypeId +
              '\n' +
              'By date : ' +this.options.byDateRange +
              '\n\n' +
              'Anciens valid : \n' +MediaList.aOldValid +
              '\n' +
              'Valid : \n' +MediaList.aValidItems +
              '\n\n' +
              'To hide : \n' + MediaList.aToHide +
              '\n' +
              'To show : \n' + MediaList.aToShow
              );
    }
    
}

var Media = {
    options: {
        byTypeId: '',
        byDateRange: '',
        viewType: 'thumb'
    },
    narrowResults: function(options) {
        if(typeof options.byTypeId != "undefined") {
            this.options.byTypeId = options.byTypeId;
        }
        if(typeof options.byDateRange != "undefined") {
            this.options.byDateRange = options.byDateRange;
        }
        if(typeof options.viewType != "undefined") {
            this.options.viewType = options.viewType;
        }
        
        Media._getMediaFiles(this.options);

        return true;
    },
    _getMediaFiles: function(options) {
        var HW = new MediaAjaxProvider();
        var mediaFiles = HW.getMediaFiles(options);
        Element.update('mediaItems', mediaFiles[0]);
    }
    
}
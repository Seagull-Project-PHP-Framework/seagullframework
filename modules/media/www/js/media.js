var Media = {
    options: {
        byTypeId: '',
        byDateRange: ''
    },
    narrowResults: function(options) {
        if(typeof options.byTypeId != "undefined") {
            this.options.byTypeId = options.byTypeId;
        }
        if(typeof options.byDateRange != "undefined") {
            this.options.byDateRange = options.byDateRange;
        }
        
        Media.getMediaFiles(this.options);

        return true;
    },
    getMediaFiles: function(options) {
        var HW = new MediaAjaxProvider();
        var mediaFiles = HW.getMediaFiles(options);
        Element.update('mediaList-items', mediaFiles[0]);
    }
    
}
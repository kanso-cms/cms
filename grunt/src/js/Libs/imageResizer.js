// ##############################################################################
// FILE: Libs/ImageResizer.js
// ##############################################################################

/* Image resizer for CTX - Done right */
var ImageResizer = function(width, height, allow_enlarge) {
    if (!(this instanceof ImageResizer)) {
        return new ImageResizer(width, height, allow_enlarge)
    }

    this.original_w = width;
    this.original_h = height;
    this.allow_enlarge = allow_enlarge;

    this.dest_x = 0;
    this.dest_y = 0;

    this.source_x;
    this.source_y;

    this.source_w;
    this.source_h;

    this.dest_w;
    this.dest_h;
};

ImageResizer.prototype = {

    resizeToHeight: function(height) {
        var ratio = height / this.getSourceHeight();
        var width = this.getSourceWidth() * ratio;

        this.resize(width, height);

        return this;
    },

    resizeToWidth: function(width) {
        var ratio = width / this.getSourceWidth();
        var height = this.getSourceHeight() * ratio;
        this.resize(width, height);
        return this;
    },

    scale: function(scale) {
        var width = this.getSourceWidth() * scale / 100;
        var height = this.getSourceHeight() * scale / 100;
        this.resize(width, height);
        return this;
    },

    resize: function(width, height) {
        if (!this.allow_enlarge) {
            if (width > this.getSourceWidth() || height > this.getSourceHeight()) {
                width = this.getSourceWidth();
                height = this.getSourceHeight();
            }
        }

        this.source_x = 0;
        this.source_y = 0;

        this.dest_w = width;
        this.dest_h = height;

        this.source_w = this.getSourceWidth();
        this.source_h = this.getSourceHeight();

        return this;
    },

    crop: function(width, height) {
        if (!this.allow_enlarge) {
            // this logic is slightly different to resize(),
            // it will only reset dimensions to the original
            // if that particular dimenstion is larger

            if (width > this.getSourceWidth()) {
                width = this.getSourceWidth();
            }

            if (height > this.getSourceHeight()) {
                height = this.getSourceHeight();
            }
        }

        var ratio_source = this.getSourceWidth() / this.getSourceHeight();
        var ratio_dest = width / height;

        if (ratio_dest < ratio_source) {
            this.resizeToHeight(height);

            var excess_width = (this.getDestWidth() - width) / this.getDestWidth() * this.getSourceWidth();

            this.source_w = this.getSourceWidth() - excess_width;
            this.source_x = excess_width / 2;

            this.dest_w = width;
        } else {
            this.resizeToWidth(width);

            var excess_height = (this.getDestHeight() - height) / this.getDestHeight() * this.getSourceHeight();

            this.source_h = this.getSourceHeight() - excess_height;
            this.source_y = excess_height / 2;

            this.dest_h = height;
        }

        return this;
    },

    getSourceWidth: function() {
        return this.original_w;
    },

    getSourceHeight: function() {
        return this.original_h;
    },

    getDestWidth: function() {
        return this.dest_w;
    },

    getDestHeight: function() {
        return this.dest_h;
    },

};

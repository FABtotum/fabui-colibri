/*!
 * JogTouch v1.0
 * 
 * Author: Daniel Kesler <kesler.daniel@gmail.com>
 *
 * Copyright (c) 2014-2016 FABtotum
 * Released under the GPLv3 license
 *
 * Date: 2017-01-17
 */

(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as anonymous module.
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node / CommonJS
    factory(require('jquery'));
  } else {
    // Browser globals.
    factory(jQuery);
  }
})(function ($) {

  'use strict';

  // Globals
  var $window = $(window);
  var $document = $(document);
  var location = window.location;
  var navigator = window.navigator;
  var ArrayBuffer = window.ArrayBuffer;
  var Uint8Array = window.Uint8Array;
  var DataView = window.DataView;
  var btoa = window.btoa;

  // Constants
  var NAMESPACE = 'jogtouch';
  
  // Classes
  var CLASS_MODAL = 'jogtouch-modal';
  var CLASS_HIDE = 'jogtouch-hide';
  var CLASS_HIDDEN = 'jogtouch-hidden';
  var CLASS_INVISIBLE = 'jogtouch-invisible';
  var CLASS_MOVE = 'jogtouch-move';
  var CLASS_CROP = 'jogtouch-crop';
  var CLASS_DISABLED = 'jogtouch-disabled';
  var CLASS_BG = 'jogtouch-bg';
  
  // Events
  var EVENT_MOUSE_DOWN = 'mousedown touchstart pointerdown MSPointerDown';
  var EVENT_MOUSE_MOVE = 'mousemove touchmove pointermove MSPointerMove';
  var EVENT_MOUSE_UP = 'mouseup touchend touchcancel pointerup pointercancel MSPointerUp MSPointerCancel';
  var EVENT_WHEEL = 'wheel mousewheel DOMMouseScroll';
  var EVENT_DBLCLICK = 'dblclick';
  var EVENT_ERROR = 'error.' + NAMESPACE;
  var EVENT_RESIZE = 'resize.' + NAMESPACE; // Bind to window with namespace
  var EVENT_BUILD = 'build.' + NAMESPACE;
  var EVENT_BUILT = 'built.' + NAMESPACE;
  var EVENT_TOUCH = 'touch.' + NAMESPACE;
  
  // Supports
  var SUPPORT_CANVAS = $.isFunction($('<canvas>')[0].getContext);
  var IS_SAFARI_OR_UIWEBVIEW = navigator && /(Macintosh|iPhone|iPod|iPad).*AppleWebKit/i.test(navigator.userAgent);

  // Maths
  var num = Number;
  var min = Math.min;
  var max = Math.max;
  var abs = Math.abs;
  var sin = Math.sin;
  var cos = Math.cos;
  var sqrt = Math.sqrt;
  var round = Math.round;
  var floor = Math.floor;

  // Utilities
  var fromCharCode = String.fromCharCode;

  function isNumber(n) {
    return typeof n === 'number' && !isNaN(n);
  }

  function isUndefined(n) {
    return typeof n === 'undefined';
  }

  function toArray(obj, offset) {
    var args = [];

    // This is necessary for IE8
    if (isNumber(offset)) {
      args.push(offset);
    }

    return args.slice.apply(obj, args);
  }

  
  // Custom proxy to avoid jQuery's guid
  function proxy(fn, context) {
    var args = toArray(arguments, 2);

    return function () {
      return fn.apply(context, args.concat(toArray(arguments)));
    };
  }

  function isCrossOriginURL(url) {
    var parts = url.match(/^(https?:)\/\/([^\:\/\?#]+):?(\d*)/i);

    return parts && (
      parts[1] !== location.protocol ||
      parts[2] !== location.hostname ||
      parts[3] !== location.port
    );
  }

  function addTimestamp(url) {
    var timestamp = 'timestamp=' + (new Date()).getTime();

    return (url + (url.indexOf('?') === -1 ? '?' : '&') + timestamp);
  }

  function getCrossOrigin(crossOrigin) {
    return crossOrigin ? ' crossOrigin="' + crossOrigin + '"' : '';
  }

  function getImageSize(image, callback) {
    var newImage;

    // Modern browsers (ignore Safari, #120 & #509)
    if (image.naturalWidth && !IS_SAFARI_OR_UIWEBVIEW) {
      return callback(image.naturalWidth, image.naturalHeight);
    }

    // IE8: Don't use `new Image()` here (#319)
    newImage = document.createElement('img');

    newImage.onload = function () {
      callback(this.width, this.height);
    };

    newImage.src = image.src;
  }
  
  function JogTouch(element, options) {
    this.$element = $(element);
    this.options = $.extend({}, JogTouch.DEFAULTS, $.isPlainObject(options) && options);
    this.isBuilt = false;
    this.cursorX = 0;
    this.cursorY = 0;
    this.isLoaded = false;
    this.isDisabled = false;
    this.isImg = false;
    this.originalUrl = '';
    this.canvas = null;
    this.cropBox = null;
    this.init();
  }
  
  JogTouch.prototype = {
    constructor: JogTouch,
    
    init: function () {
      var $this = this.$element;
      var url;

      if ($this.is('img')) {
        this.isImg = true;

        // Should use `$.fn.attr` here. e.g.: "img/picture.jpg"
        this.originalUrl = url = $this.attr('src');

        // Stop when it's a blank image
        if (!url) {
          return;
        }

        // Should use `$.fn.prop` here. e.g.: "http://example.com/img/picture.jpg"
        //url = $this.prop('src');
      } else if ($this.is('canvas') && SUPPORT_CANVAS) {
        url = $this[0].toDataURL();
      }
      
      this.start();
    },
    
    // A shortcut for triggering custom events
    trigger: function (type, data) {
      var e = $.Event(type, data);

      this.$element.trigger(e);

      return e;
    },
    
    start: function () {
      var $image = this.$element;
      var $clone = this.$clone;

      if (!this.isImg) {
        $clone.off(EVENT_ERROR, this.stop);
        $image = $clone;
      }

      this.image = {};

      getImageSize($image[0], $.proxy(function (naturalWidth, naturalHeight) {
        $.extend(this.image, {
          naturalWidth: naturalWidth,
          naturalHeight: naturalHeight,
          aspectRatio: naturalWidth / naturalHeight
        });

        this.isLoaded = true;
        this.build();
      }, this));
    },

    stop: function () {
      this.$clone.remove();
      this.$clone = null;
    },
    
    cursor: function(x, y) {
      var options = this.options;
      var canvas = this.canvas;
      
      var width = this.$canvas.width();
      var height = this.$canvas.height();
      
      var max_x = Math.max(options.left, options.right);
      var min_x = Math.min(options.left, options.right);
      var max_y = Math.max(options.top, options.bottom);
      var min_y = Math.min(options.top, options.bottom);
      
      var mx = Math.max(x, min_x);
      var my = Math.max(y, min_y);
      
      mx = Math.min(x, max_x);
      my = Math.min(y, max_y);
      
      this.cursorX = mx;
      this.cursorY = my;
      
      var mappedWidth = options.right - options.left;
      var mappedX1 = options.left;
      var mappedX2 = options.right;
      var mappedHeight = options.bottom - options.top;
      var mappedY1 = options.top;
      var mappedY2 = options.bottom;
      
      var offX = Math.min(mappedX1, mappedX2)
      var offY = Math.min(mappedY1, mappedY2)
      
      var px = (mx-offX) / mappedWidth;
      var py = (my-offY) / mappedHeight;
      
      var rx = width * px;
      var ry = height + height * py;
      
      // move the cursor
      this.$cross.css(
        {
          left:rx-8,
          top:ry-8
        }
      );
    },
    
    touch: function(event) {
      var options = this.options;
      var canvas = this.canvas;
      var originalEvent = event.originalEvent;
      var touches = originalEvent && originalEvent.touches;
      var e = event;
      var touchesLength;
      var $jogtouch = this.$jogtouch;
      
      if (this.isDisabled) {
        return;
      }
      
      if (touches) {
        touchesLength = touches.length;

        if (touchesLength > 1) {
            return;
        }

        e = touches[0];
      }
      
      event.preventDefault();
      
      // IE8  has `event.pageX/Y`, but not `event.originalEvent.pageX/Y`
      // IE10 has `event.originalEvent.pageX/Y`, but not `event.pageX/Y`
      this.touchX = e.pageX || originalEvent && originalEvent.pageX;
      this.touchY = e.pageY || originalEvent && originalEvent.pageY;
      
      var offset2 = this.$canvas.offset();
      var width = this.$canvas.width();
      var height = this.$canvas.height();
      
      var rx = this.touchX-offset2.left;
      var ry = this.touchY-offset2.top;
      
      rx = Math.max(rx, 0);
      ry = Math.max(ry, 0);
      
      rx = Math.min(rx, width);
      ry = Math.min(ry, height);
      
      var px = rx / width;
      var py = ry / height;      
      
      // trigger touch event with mapped coordinates
      
      var mappedWidth = options.right - options.left;
      var mappedX = options.left;
      var mappedHeight = options.bottom - options.top;
      var mappedY = options.top;
      
      var data = {
        x: mappedX + px*mappedWidth,
        y: mappedY + py*mappedHeight
      };
      
      var execute = true;
      if(options.touch)
      {
        execute = options.touch(data);
      }
      
      if(execute)
      {
        this.cursor(data.x, data.y);
        this.trigger(EVENT_TOUCH, data);
      }
    },
        
    build: function () {
      
      var options = this.options;
      var $this = this.$element;
      var $clone = this.$clone;
      var $jogtouch;
      var $face;

      // Unbuild first when replace
      if (this.isBuilt) {
        this.unbuild();
      }

      // Create jogtouch elements
      this.$container = $this.parent();
      this.$jogtouch = $jogtouch = $(JogTouch.TEMPLATE);
      this.$canvas = $jogtouch.find('.jogtouch-canvas');
      this.$cross = $jogtouch.find('.jogtouch-cross');
      this.$face = $face = $jogtouch.find('.jogtouch-face');

      if (!options.guides) {
        $jogtouch.find('.jogtouch-dashed').addClass(CLASS_HIDDEN);
      }

      if (!options.center) {
        $jogtouch.find('.jogtouch-center').addClass(CLASS_HIDDEN);
      }

      if (!options.highlight) {
        $face.addClass(CLASS_INVISIBLE);
      }

      if (options.background) {
        $jogtouch.addClass(CLASS_BG);
      }

      // Hide the original image
      $this.addClass(CLASS_HIDE).after($jogtouch);
      $this.removeClass(CLASS_HIDE);
      
      if (options.disabled)
      {
        this.isDisabled = true;
        $jogtouch.addClass(CLASS_DISABLED);
      }
      
      this.cursorX = options.cursorX;
      this.cursorY = options.cursorY;
      this.bind();
      this.initContainer();
      this.initCanvas();
      this.isBuilt = true;
    },
    
    unbuild: function () {
      this.isBuilt = false;
    },
    
    bind: function () {
      var options = this.options;
      var $this = this.$element;
      var $jogtouch = this.$jogtouch;

      $jogtouch.on(EVENT_MOUSE_DOWN, $.proxy(this.touch, this));

      /*$document.on(EVENT_MOUSE_MOVE, (this._move = proxy(this.move, this))).*/

      $window.on(EVENT_RESIZE, (this._resize = proxy(this.resize, this)));
    },

    unbind: function () {
      var options = this.options;
      var $this = this.$element;
      var $jogtouch = this.$jogtouch;

      $jogtouch.off(EVENT_MOUSE_DOWN, this.touch);

      if (options.responsive) {
        $window.off(EVENT_RESIZE, this._resize);
      }
    },

    resize: function () {
      var $container = this.$container;
      var container = this.container;
      var ratio;

      // Check `container` is necessary for IE8
      /*if (this.isDisabled || !container) {
        return;
      }*/

      if( $container.width() < 100 )
        return;

      ratio = $container.width() / container.width;

      // Resize when width changed or height changed
      if (ratio !== 1 || $container.height() !== container.height) {
        this.initContainer();
        this.initCanvas();
      }
      
    },
    
    // Enable (unfreeze) the jogtouch
    enable: function () {
      if (this.isBuilt) {
        this.isDisabled = false;
        this.$jogtouch.removeClass(CLASS_DISABLED);
      }
    },

    // Disable (freeze) the jogtouch
    disable: function () {
      if (this.isBuilt) {
        this.isDisabled = true;
        this.$jogtouch.addClass(CLASS_DISABLED);
      }
    },
    
    initContainer: function () {
      var options = this.options;
      var $this = this.$element;
      var $container = this.$container;
      var $jogtouch = this.$jogtouch;

      $jogtouch.addClass(CLASS_HIDDEN);

      var width = $container.width();
      var height = $container.height();

      $jogtouch.css((this.container = {
        width: max($container.width(), num(options.minContainerWidth) || 200),
        height: max($container.height(), num(options.minContainerHeight) || 100)
      }));

      // Prevent flickering on rezise if the container was hidden
      if(width != 0 && height != 0)
      {
        $jogtouch.removeClass(CLASS_HIDDEN);
      }
    },
    
    // Canvas (image wrapper)
    initCanvas: function () {
      var options = this.options;
      var container = this.container;
      var containerWidth = container.width;
      var containerHeight = container.height;
      var image = this.image;
      var imageNaturalWidth = image.naturalWidth;
      var imageNaturalHeight = image.naturalHeight;
      var is90Degree = abs(image.rotate) === 90;
      var naturalWidth = is90Degree ? imageNaturalHeight : imageNaturalWidth;
      var naturalHeight = is90Degree ? imageNaturalWidth : imageNaturalHeight;
      var aspectRatio = naturalWidth / naturalHeight;
      var canvasWidth = containerWidth;
      var canvasHeight = containerHeight;
      var canvas;

      canvas = {
        naturalWidth: naturalWidth,
        naturalHeight: naturalHeight,
        aspectRatio: aspectRatio,
        width: canvasWidth,
        height: canvasHeight
      };

      canvas.oldLeft = canvas.left = (containerWidth - canvasWidth) / 2;
      canvas.oldTop = canvas.top = (containerHeight - canvasHeight) / 2;

      this.canvas = canvas;      
      this.cursor(this.cursorX, this.cursorY);
    },
    
  }
  
  JogTouch.DEFAULTS = {
    // Show the dashed lines for guiding
    guides: true,

    // Show the center indicator for guiding
    center: true,

    // Show the white modal to highlight the crop box
    highlight: true,

    // Show the grid background
    background: false,
    
    // Initialize disabled jogtouch
    disabled: false,
    
    left: 0,
    right: 100,
    top: 0,
    bottom: 100,
    
    cursorX: 0,
    cursorY: 0,
    
    touch: null
  };
  
  JogTouch.TEMPLATE = (
    '<div class="jogtouch-container">' +
      '<div class="jogtouch-wrap-box">' +
        '<div class="jogtouch-canvas"></div>' +
        
        '<div>' +
          '<span class="jogtouch-dashed dashed-h"></span>' +
          '<span class="jogtouch-dashed dashed-v"></span>' +
          '<span class="jogtouch-center"></span>' +
          '<span class="jogtouch-face"></span>' +
        '</div>' +
        
        '<div class="jogtouch-cross"></div>' +
      '</div>' +
    '</div>'
  );
  
  JogTouch.setDefaults = function (options) {
    $.extend(JogTouch.DEFAULTS, options);
  };
  
  // Save the other JogTouch
  JogTouch.other = $.fn.jogtouch;
  
  // Register as jQuery plugin
  $.fn.jogtouch = function (option) {
    var args = toArray(arguments, 1);
    var result;

    this.each(function () {
      var $this = $(this);
      var data = $this.data(NAMESPACE);
      var options;
      var fn;

      if (!data) {
        if (/destroy/.test(option)) {
          return;
        }

        options = $.extend({}, $this.data(), $.isPlainObject(option) && option);
        $this.data(NAMESPACE, (data = new JogTouch(this, options)));
      }

      if (typeof option === 'string' && $.isFunction(fn = data[option])) {
        result = fn.apply(data, args);
      }
    });

    return isUndefined(result) ? this : result;
  };

  $.fn.jogtouch.Constructor = JogTouch;
  $.fn.jogtouch.setDefaults = JogTouch.setDefaults;

  // No conflict
  $.fn.jogtouch.noConflict = function () {
    $.fn.jogtouch = JogTouch.other;
    return this;
  };
  
});

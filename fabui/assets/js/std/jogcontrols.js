/*!
 * JogTouch v1.0
 * 
 * Author: Daniel Kesler <kesler.daniel@gmail.com>
 *
 * Copyright (c) 2014-2016 FABtotum
 * Released under the GPLv3 license
 *
 * Date: 2017-01-20
 */

(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as anonymous module.
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node / CommonJS
    factory(require('jquery'));
    factory(require('raphael'));
    factory(require('modernizer'));
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
  var NAMESPACE = 'jogcontrols';

  // Classes
  var CLASS_MODAL = 'jogcontrols-modal';
  var CLASS_HIDE = 'jogcontrols-hide';
  var CLASS_HIDDEN = 'jogcontrols-hidden';
  var CLASS_INVISIBLE = 'jogcontrols-invisible';
  var CLASS_MOVE = 'jogcontrols-move';
  var CLASS_CROP = 'jogcontrols-crop';
  var CLASS_DISABLED = 'jogcontrols-disabled';
  var CLASS_BG = 'jogcontrols-bg';
  
  // Events
  var EVENT_RESIZE = 'resize.' + NAMESPACE; // Bind to window with namespace
  var EVENT_BUILD = 'build.' + NAMESPACE;
  var EVENT_BUILT = 'built.' + NAMESPACE;
  var EVENT_CLICK = 'click';
  
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
  var rad = Math.PI / 180;

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
  
  function sector(paper, cx, cy, r1, r2, startAngle, endAngle, params) {
      
      var x1 = cx + r1 * Math.cos(-startAngle * rad),
          x2 = cx + r1 * Math.cos(-endAngle * rad),
          y1 = cy + r1 * Math.sin(-startAngle * rad),
          y2 = cy + r1 * Math.sin(-endAngle * rad);
          
      var x3 = cx + r2 * Math.cos(-startAngle * rad),
          x4 = cx + r2 * Math.cos(-endAngle * rad),
          y3 = cy + r2 * Math.sin(-startAngle * rad),
          y4 = cy + r2 * Math.sin(-endAngle * rad);
          
      
      if(r2 == 0)
          return paper.circle(cx, cy, r1).attr(params);
      else
          return paper.path(["M", x3, y3, "L", x1, y1, "A", r1, r1, 0, +(endAngle - startAngle > 180), 0, x2, y2, "L", x4, y4, "A", r2, r2, 0, -(endAngle - startAngle> 180), 1, x3, y3]).attr(params);
  }

  function JogControls(element, options) {
    this.$element = $(element);
    this.options = $.extend({}, JogControls.DEFAULTS, $.isPlainObject(options) && options);
    this.isBuilt = false;
    this.isDisabled = false;
    this.init();
  }
  
  JogControls.prototype = {
    constructor: JogControls,
    
    init: function () {
      var $this = this.$element;
      
      this.build();
    },
    
    // A shortcut for triggering custom events
    trigger: function (type, data) {
      var e = $.Event(type, data);

      this.$element.trigger(e);

      return e;
    },
       
    build: function () {
      var options = this.options;
      var $this = this.$element;
      var $container;
      var paper;
      var ui;
      
      var id = "jogcontrol-container-" + (new Date()).getTime();
      
      // Unbuild first when replace
      if (this.isBuilt) {
        this.unbuild();
      }
      
      this.$container = $container = $('<div id="'+id+'" class="jogcontrol-container"></div>');
      
      $this.addClass(CLASS_HIDDEN).after($container);
      
      this.paper = paper = Raphael(id, $container.width(), $container.width() );
      this.ui = ui = paper.set();
      
      if (options.disabled)
      {
        this.isDisabled = true;
        $jogcontrols.addClass(CLASS_DISABLED);
      }
      
      var scale = $container.width() / 400;
      
      if(options.multiplier >= options.multipliers.legnth)
        options.multiplier = 0;
      
      this.multiplier = options.multiplier;
      
      this.initUI(scale);
      this.bind();
      this.isBuilt = true;
    },
    
    unbuild: function () {
      this.isBuilt = false;
    },
    
    bind: function () {
      var options = this.options;
      var $this = this.$element;
      var $jogcontrols = this.$jogcontrols;

      /*if ($.isFunction(options.touch)) {
        $this.on(EVENT_TOUCH, options.touch);
      }*/

      $window.on(EVENT_RESIZE, (this._resize = proxy(this.resize, this)));
    },

    unbind: function () {
      var options = this.options;
      var $this = this.$element;
      var $jogcontrols = this.$jogcontrols;

      $window.off(EVENT_RESIZE, this._resize);
    },

    resize: function () {
      var $container = this.$container;
      var paper = this.paper;
      var ratio;

      if( $container.width() < 150 )
        return;

      ratio = $container.width() / paper.width;

      // Resize when width changed or height changed
      if (ratio !== 1 || $container.height() !== paper.height) {
        var scale = $container.width() / 400;
        this.initUI(scale);
      }
      
    },
    
    // Enable (unfreeze) the jogcontrols
    enable: function () {
      if (this.isBuilt) {
        this.isDisabled = false;
        this.$jogcontrols.removeClass(CLASS_DISABLED);
      }
    },

    // Disable (freeze) the jogcontrols
    disable: function () {
      if (this.isBuilt) {
        this.isDisabled = true;
        this.$jogcontrols.addClass(CLASS_DISABLED);
      }
    },
    
    getMultiplier: function () {
        return this.options.multipliers[this.multiplier];
    },
    
    __addButton: function (button, global_scale = 1.0) {
      var $this = this;
      var options = this.options;
      var paper = this.paper;
      var ui = this.ui;
      
      var r = (paper.width / 2) * 0.8;
      var cx = paper.width / 2;
      var cy = paper.height / 2;
      
      var natural_width = 400;
      var natural_scale = natural_width / paper.width;
      
      var angleplus = button.A1;
      var angle = button.A0;
      var popangle = angle + (angleplus / 2);
      
      
      var color = Raphael.color("white");
      var stroke = Raphael.color("#d1d1d1");
      var text = Raphael.color("black");
      
      var r1 = r * button.R1;
      var r2 = r * button.R2;
      
      var x0 = (button.x == undefined)?0:button.x;
      var y0 = (button.y == undefined)?0:button.y;
      
      var x0 = cx+x0 * paper.width/2;
      var y0 = cy+y0 * paper.width/2;
      
      var st = paper.set();
      
      var p = sector(paper, x0, y0, r1, r2, angle, angle + angleplus, {fill:color});
      p.node.setAttribute("class","jog-btn");
      st.push(p);
      
      var scale = 3 * global_scale;
      
      var sym = null;
      if(button.SYM != undefined)
      {
          var sym_scale = (button.sym_scale == undefined)?1:button.sym_scale;
        
          sym = paper.path(button.SYM).attr({fill:text, stroke: "none"});
          sym.scale(1,1,0.5,0.5);
          var a = popangle ;
          
          if(r2 != 0)
          {
              var r3 = (r1+r2) / 2;
              
              var x1 = x0 + (r3) * Math.cos(-popangle * rad),
                  y1 = y0 + (r3) * Math.sin(-popangle * rad);
          }
          else
          {
              var x1 = x0,
                  y1 = y0;
          }
          
          var crx = (button.crx == undefined)?0:button.crx;
          var cry = (button.cry == undefined)?0:button.cry;

          var xx = x1;
          var yy = y1;
          
          x1 += crx*global_scale - 1/global_scale;
          y1 += cry*global_scale - 1/global_scale;
          
          var A2 = (button.A2 == undefined)?180-popangle:button.A2;
          
          sym.translate(x1, y1).rotate(A2).scale(scale*sym_scale,scale*sym_scale);
          sym.node.setAttribute("class","jog-btn-symbol");
          st.push(sym);
      }
      
      var txt = null;
      if(button.TXT != undefined)
      {
          var txt_scale = (button.txt_scale == undefined)?1:button.txt_scale;
         
          if(r2 != 0)
          {
              var r3 = (r1+r2) / 2;
              
              var x1 = x0 + (r3) * Math.cos(-popangle * rad),
                  y1 = y0 + (r3) * Math.sin(-popangle * rad);
          }
          else
          {
              var x1 = x0,
                  y1 = y0;
          }
          
          var crx = (button.tcrx == undefined)?0:button.tcrx;
          var cry = (button.tcry == undefined)?0:button.tcry;
          
          x1 += crx * global_scale;
          y1 += cry * global_scale;
          
          txt = paper.text(x1, y1, button.TXT).attr({fill: text, stroke: "none", "font-size": 20});
          txt.node.setAttribute("class","jog-btn-text");
          txt.scale(global_scale*txt_scale);
          st.push(txt);
      }
            
      function set_hover()
      {
          st.toFront();
          p.node.setAttribute("class","jog-btn hover");
          if(sym != null)
              sym.node.setAttribute("class","jog-btn-symbol hover");
          if(txt != null)
              txt.node.setAttribute("class","jog-btn-text hover");
      }
      
      function set_active()
      {
          st.toFront();
          p.node.setAttribute("class","jog-btn active");
          if(sym != null)
              sym.node.setAttribute("class","jog-btn-symbol active");
          if(txt != null)
              txt.node.setAttribute("class","jog-btn-text active");
      }
      
      function set_normal()
      {
          st.toFront();
          p.node.setAttribute("class","jog-btn");
          if(sym != null)
              sym.node.setAttribute("class","jog-btn-symbol");
          if(txt != null)
              txt.node.setAttribute("class","jog-btn-text");
      }
      
      function clicked() {
                
          var action = button.name;
          
          if( button.name == "mul")
          {
              
              $this.multiplier++;
              if($this.multiplier >= options.multipliers.length)
                  $this.multiplier = 0;
              
              if(txt != null)
              {
                var attr = txt.attr();    
                attr.text = 'x' + options.multipliers[$this.multiplier];
                txt.attr(attr);
              }
          }
          else
          {
            var e = {
              action: action
            };
            
            $this.trigger(EVENT_CLICK, e);
          }
      }
      
      if (Modernizr.touchevents) {
          // supported
          st.touchstart ( function() {
            set_active();
            clicked();
            });
          st.touchend   (set_normal);
      } else {
          // not-supported
          
          st.mouseover  (set_hover);
          st.mousedown  (function() {
              set_active();
              clicked();
            });
          st.mouseup    (set_hover);
          st.mouseout   (set_normal);
          //st.click      (clicked);
      }

      ui.push(st);
    },
    
    initUI: function (scale = 1.0) {
      var options = this.options;
      var $this = this.$element;
      var $jogcontrols = this.$jogcontrols;
      var paper = this.paper;
      var ui = this.ui;
      paper.clear();
      ui.clear();
      
      paper.setSize(400*scale, 400*scale);
      
      var start = 67.5;
      var r1 = 1;
      var r2 = 0.55;
      var r3 = 0.2;
      var r4 = 0.2;
      
      var arrow_down = "M9.99 5.07C9.99 4.88 9.91 4.7 9.78 4.56L9.36 4.15C9.23 4.02 9.05 3.94 8.86 3.94 8.67 3.94 8.48 4.02 8.35 4.15L6.71 5.79 6.71 1.86C6.71 1.47 6.39 1.14 6 1.14L5.29 1.14C4.9 1.14 4.57 1.47 4.57 1.86L4.57 5.79 2.93 4.15C2.8 4.02 2.62 3.94 2.43 3.94 2.24 3.94 2.05 4.02 1.92 4.15L1.51 4.56C1.37 4.7 1.3 4.88 1.3 5.07 1.3 5.26 1.37 5.45 1.51 5.57L5.14 9.21C5.27 9.34 5.45 9.42 5.64 9.42 5.83 9.42 6.02 9.34 6.15 9.21L9.78 5.57C9.91 5.45 9.99 5.26 9.99 5.07L9.99 5.07Z";
      var arrow_left = "M9.57 5.43C9.57 5.05 9.32 4.71 8.92 4.71L4.99 4.71 6.63 3.08C6.76 2.95 6.84 2.76 6.84 2.57 6.84 2.38 6.76 2.2 6.63 2.06L6.21 1.65C6.07 1.52 5.89 1.44 5.7 1.44 5.51 1.44 5.33 1.52 5.2 1.65L1.56 5.28C1.44 5.41 1.36 5.6 1.36 5.79 1.36 5.98 1.44 6.16 1.56 6.29L5.2 9.93C5.33 10.05 5.51 10.13 5.7 10.13 5.89 10.13 6.08 10.05 6.21 9.93L6.63 9.5C6.76 9.37 6.84 9.19 6.84 9 6.84 8.81 6.76 8.63 6.63 8.5L4.99 6.86 8.92 6.86C9.32 6.86 9.57 6.52 9.57 6.14L9.57 5.43Z";
      var arrow_right = "M9.21 5.79C9.21 5.6 9.14 5.41 9.01 5.28L5.38 1.65C5.24 1.52 5.06 1.44 4.87 1.44 4.68 1.44 4.5 1.52 4.36 1.65L3.95 2.07C3.81 2.2 3.73 2.38 3.73 2.57 3.73 2.76 3.81 2.95 3.95 3.07L5.58 4.71 1.65 4.71C1.25 4.71 1 5.05 1 5.43L1 6.14C1 6.52 1.25 6.86 1.65 6.86L5.58 6.86 3.95 8.49C3.81 8.63 3.73 8.81 3.73 9 3.73 9.19 3.81 9.37 3.95 9.51L4.36 9.93C4.5 10.05 4.68 10.13 4.87 10.13 5.06 10.13 5.24 10.05 5.38 9.93L9.01 6.29C9.14 6.16 9.21 5.98 9.21 5.79L9.21 5.79Z";
      var arrow_up = "M9.99 5.85C9.99 5.66 9.91 5.47 9.78 5.34L6.15 1.71C6.02 1.57 5.83 1.5 5.64 1.5 5.45 1.5 5.27 1.57 5.14 1.71L1.51 5.34C1.37 5.47 1.3 5.66 1.3 5.85 1.3 6.04 1.37 6.22 1.51 6.35L1.93 6.77C2.05 6.9 2.24 6.98 2.43 6.98 2.62 6.98 2.8 6.9 2.93 6.77L4.57 5.13 4.57 9.06C4.57 9.46 4.91 9.71 5.29 9.71L6 9.71C6.38 9.71 6.71 9.46 6.71 9.06L6.71 5.13 8.35 6.77C8.48 6.9 8.67 6.98 8.86 6.98 9.05 6.98 9.23 6.9 9.36 6.77L9.78 6.35C9.91 6.22 9.99 6.04 9.99 5.85L9.99 5.85Z";
            
      var bulzeye = "M6.71 5.43C6.71 4.64 6.07 4 5.29 4 4.5 4 3.86 4.64 3.86 5.43 3.86 6.22 4.5 6.86 5.29 6.86 6.07 6.86 6.71 6.22 6.71 5.43L6.71 5.43ZM7.43 5.43C7.43 6.61 6.47 7.57 5.29 7.57 4.1 7.57 3.14 6.61 3.14 5.43 3.14 4.25 4.1 3.29 5.29 3.29 6.47 3.29 7.43 4.25 7.43 5.43L7.43 5.43ZM8.14 5.43C8.14 3.85 6.86 2.57 5.29 2.57 3.71 2.57 2.43 3.85 2.43 5.43 2.43 7.01 3.71 8.29 5.29 8.29 6.86 8.29 8.14 7.01 8.14 5.43L8.14 5.43ZM8.86 5.43C8.86 7.4 7.26 9 5.29 9 3.32 9 1.71 7.4 1.71 5.43 1.71 3.46 3.32 1.86 5.29 1.86 7.26 1.86 8.86 3.46 8.86 5.43L8.86 5.43ZM9.57 5.43C9.57 3.06 7.65 1.14 5.29 1.14 2.92 1.14 1 3.06 1 5.43 1 7.79 2.92 9.71 5.29 9.71 7.65 9.71 9.57 7.79 9.57 5.43L9.57 5.43Z"
      var home = "M8.86 5.96C8.86 5.95 8.86 5.94 8.85 5.93L5.64 3.29 2.43 5.93C2.43 5.94 2.43 5.95 2.43 5.96L2.43 8.64C2.43 8.84 2.59 9 2.79 9L4.93 9 4.93 6.86 6.36 6.86 6.36 9 8.5 9C8.7 9 8.86 8.84 8.86 8.64L8.86 5.96ZM10.1 5.58C10.16 5.51 10.15 5.39 10.08 5.33L8.86 4.31 8.86 2.04C8.86 1.94 8.78 1.86 8.68 1.86L7.61 1.86C7.51 1.86 7.43 1.94 7.43 2.04L7.43 3.12 6.07 1.99C5.83 1.79 5.45 1.79 5.22 1.99L1.21 5.33C1.13 5.39 1.12 5.51 1.18 5.58L1.53 5.99C1.56 6.03 1.6 6.05 1.65 6.05 1.7 6.06 1.74 6.04 1.78 6.01L5.64 2.79 9.5 6.01C9.54 6.04 9.58 6.05 9.62 6.05L9.64 6.05C9.68 6.05 9.73 6.03 9.76 5.99L10.1 5.58Z";
      var folder_open = "M10.94 5.62C10.94 5.71 10.89 5.78 10.84 5.84L9.2 7.87C9.01 8.1 8.62 8.29 8.32 8.29L2.25 8.29C2.13 8.29 1.95 8.25 1.95 8.09 1.95 8.01 2 7.93 2.05 7.87L3.7 5.84C3.89 5.61 4.28 5.43 4.57 5.43L10.64 5.43C10.77 5.43 10.94 5.47 10.94 5.62L10.94 5.62ZM4.57 4.71C4.06 4.71 3.47 5 3.14 5.4L1.71 7.15 1.71 2.39C1.71 2.1 1.95 1.86 2.25 1.86L4.04 1.86C4.33 1.86 4.57 2.1 4.57 2.39L4.57 2.75C4.57 3.05 4.81 3.29 5.11 3.29L8.32 3.29C8.62 3.29 8.86 3.53 8.86 3.82L8.86 4.71 4.57 4.71ZM11.65 5.62C11.65 5.49 11.63 5.36 11.57 5.24 11.4 4.89 11.02 4.71 10.64 4.71L9.57 4.71 9.57 3.82C9.57 3.14 9.01 2.57 8.32 2.57L5.29 2.57 5.29 2.39C5.29 1.71 4.72 1.14 4.04 1.14L2.25 1.14C1.56 1.14 1 1.71 1 2.39L1 7.75C1 8.44 1.56 9 2.25 9L8.32 9C8.82 9 9.43 8.71 9.75 8.32L11.4 6.29C11.55 6.1 11.65 5.87 11.65 5.62L11.65 5.62Z";
      
      var buttons = [
          {
              name:'up',
              R1:r1,
              R2:r2,
              A0:start, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'up-left',
              R1:r1,
              R2:r2,
              A0:start+45, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'left',
              R1:r1,
              R2:r2,
              A0:start+90, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'down-left',
              R1:r1,
              R2:r2,
              A0:start+135, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'down',
              R1:r1,
              R2:r2,
              A0:start+180, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'down-right',
              R1:r1,
              R2:r2,
              A0:start+225, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'right',
              R1:r1,
              R2:r2,
              A0:start+270, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },
          {
              name:'up-right',
              R1:r1,
              R2:r2,
              A0:start+315, // start angle
              A1:45, // start angle + angle
              SYM:arrow_left,
              crx: -5,
              cry: -6
          },       
      ];
      
      if(options.hasRestore)
      {
          buttons.push(
            {
                name:'restore-xy',
                R1:r4,
                R2:0,
                A0:0, // start angle
                A1:360, // start angle + angle
                SYM:folder_open,
                sym_scale: 0.6,
                crx:-5,
                cry:-5,
                x: -0.8,
                y: -0.7
            }
          );
          buttons.push(
            {
                name:'restore-z',
                R1:r4,
                R2:0,
                A0:0, // start angle
                A1:360, // start angle + angle
                SYM:folder_open,
                sym_scale: 0.6,
                crx:-5,
                cry:-5,
                x: -0.8,
                y: 0.7
            }
          );
      }
      
      if(options.compact)
      {
          buttons.push({
              name:'z-up',
              R1:r2,
              R2:r3,
              A0:90, // start angle
              A1:90, // start angle + angle
              SYM:arrow_up,
              A2:0,
          });
          buttons.push({
              name:'z-down',
              R1:r2,
              R2:r3,
              A0:180, // start angle
              A1:90, // start angle + angle
              SYM:arrow_down,
              A2:0,
          });
          buttons.push({
              name:'home-xyz',
              R1:r2,
              R2:r3,
              A0:270, // start angle
              A1:90, // start angle + angle
              SYM:home,
              A2:0,
              crx: -5,
              cry: -5,
          });
          buttons.push({
              name:'mul',
              values: ['x10', 'x1', 'x0.1', 'x1'],
              value: 0,
              R1:r2,
              R2:r3,
              A0:0, // start angle
              A1:90, // start angle + angle
              TXT: 'x' + options.multipliers[this.multiplier]
          });
      }
      else
      {
          buttons.push({
              name:'home-xyz',
              R1:r2,
              R2:r3,
              A0:90, // start angle
              A1:90, // start angle + angle
              SYM:home,
              A2:0,
              crx: 20,
              cry: -20,
              TXT: "XYZ",
              tcrx: -10,
              tcry: 20
          });
          buttons.push({
              name:'home-xy',
              R1:r2,
              R2:r3,
              A0:180, // start angle
              A1:90, // start angle + angle
              SYM:home,
              A2:0,
              crx: -15,
              cry: -25,
              TXT: "XY",
              tcrx: 20,
              tcry: 15
          });
          buttons.push({
              name:'home-z',
              R1:r2,
              R2:r3,
              A0:270, // start angle
              A1:90, // start angle + angle
              SYM:home,
              A2:0,
              cry: -25,
              crx: 5,
              TXT: "Z",
              tcrx: -20,
              tcry: 15
          });
          buttons.push({
              name:'z-up',
              R1:r4,
              R2:0,
              A0:0, // start angle
              A1:360, // start angle + angle
              SYM:arrow_up,
              sym_scale: 0.8,
              cry: -5,
              crx: -5,
              x: 0.8,
              y: -0.7
          });
          buttons.push({
              name:'z-down',
              R1:r4,
              R2:0,
              A0:0,
              A1:360,
              SYM:arrow_down,
              sym_scale: 0.8,
              cry: -5,
              crx: -5,
              x: 0.8,
              y: 0.7
          });
          buttons.push({
              name:'mul',
              R1:r2,
              R2:r3,
              A0:0, // start angle
              A1:90, // start angle + angle
              TXT: 'x' + options.multipliers[this.multiplier]
          });
      }
      
      if(options.hasZero)
      {
          buttons.push(
            {
                name:'zero',
                R1:r3,
                R2:0,
                A0:0, // start angle
                A1:360, // start angle + angle
                SYM:bulzeye,
                crx:-5,
                cry:-5
            }
          );
      }
      
     /*var txt = paper.text(50, 50, 'Test').attr({"font-size": 20});
      ui.push(txt);*/
      
      for(var i=0; i<buttons.length; i++)
      {
        this.__addButton(buttons[i], scale);
      }
    },
    
  };

  JogControls.DEFAULTS = {
    //
    multipliers: [10, 1, 0.1, 1],
    //
    multiplier: 0,
    //
    hasRestore: true,
    //
    hasZero: true,
    //
    compact: false
  };

  JogControls.setDefaults = function (options) {
    $.extend(JogControls.DEFAULTS, options);
  };
  
  // Save the other JogControls
  JogControls.other = $.fn.jogcontrols;
  
  // Register as jQuery plugin
  $.fn.jogcontrols = function (option) {
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
        $this.data(NAMESPACE, (data = new JogControls(this, options)));
      }

      if (typeof option === 'string' && $.isFunction(fn = data[option])) {
        result = fn.apply(data, args);
      }
    });

    return isUndefined(result) ? this : result;
  };

  $.fn.jogcontrols.Constructor = JogControls;
  $.fn.jogcontrols.setDefaults = JogControls.setDefaults;

  // No conflict
  $.fn.jogcontrols.noConflict = function () {
    $.fn.jogcontrols = JogControls.other;
    return this;
  };

});

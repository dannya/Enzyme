/** 
 * @description    prototype.js based context menu
 * @author        Juriy Zaytsev; kangax [at] gmail [dot] com; http://thinkweb2.com/projects/prototype/
 * @version       0.6
 * @date          12/03/07
 * @requires      prototype.js 1.6
*/

Menu = Class.create({
  initialize: function() {
    var e = Prototype.emptyFunction;
    this.ie = Prototype.Browser.IE;

    this.options = Object.extend({
      selector: '.contextmenu',
      className: 'protoMenu',
      title:     null,
      pageOffset: 25,
      fade: false,
      zIndex: 1020,
      beforeShow: e,
      beforeHide: e,
      beforeSelect: e
    }, arguments[0] || { });
    
    if (this.ie) {
      this.shim = new Element('iframe', {
        style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);display:none',
        src: 'javascript:false;',
        frameborder: 0
      });
    }
    
    this.options.fade = this.options.fade && !Object.isUndefined(Effect);
    this.container = new Element('div', {className: this.options.className, style: 'display:none'});
    
    // show title on menu?
    if (this.options.title) {
      var title = new Element('div', { className: 'title' });
      title.update(this.options.title);

      this.container.insert(title);
    }


    var list = new Element('ul');

    this.options.menuItems.each(function(item) {
      // determine type of elements to insert
      if (item.separator) {
        var class = 'separator';
        var icon  = null;
        var link  = null;

      } else {
        if (item.className) {
          var class = item.className;
        }

        var icon  = new Element('div', { className: 'icon' });
        var link  = new Element('a', { href: '#',
                                       title: item.name,
                                       className: (item.disabled ? 'disabled' : 'enabled')
                                     });
        link.update(item.name);
      }
      
      // create inserted elements
      var li = Object.extend(new Element('li', { className: class + (item.disabled ? ' disabled' : ' enabled') }),
                                               { _callback: item.callback });

      li.observe('click', this.onClick.bind(this));
      li.observe('contextmenu', Event.stop);

      if (icon) {
        li.insert(icon);
      }
      if (link) {
        li.insert(link);
      }

      list.insert(li);

    }.bind(this));

    $(document.body).insert(this.container.insert(list).observe('contextmenu', Event.stop));

    if (this.ie) {
      $(document.body).insert(this.shim);
    }
    
    document.observe('click', function(e) {
      if (this.container.visible() && !e.isRightClick()) {
        this.options.beforeHide(e);
        if (this.ie) this.shim.hide();
        this.container.hide();
      }
    }.bind(this));
    
    $$(this.options.selector).invoke('observe', Prototype.Browser.Opera ? 'click' : 'contextmenu', function(e){
      if ((Prototype.Browser.Opera && !e.ctrlKey) || e.ctrlKey) {
        // don't show if ctrlKey is not held in Opera, or held in any other browser
        return;
      }
      this.show(e);
    }.bind(this));
  },
  show: function(e) {
    e.stop();
    this.options.beforeShow(e);
    var x = Event.pointer(e).x,
      y = Event.pointer(e).y,
      vpDim = document.viewport.getDimensions(),
      vpOff = document.viewport.getScrollOffsets(),
      elDim = this.container.getDimensions(),
      elOff = {
        left: ((x + elDim.width + this.options.pageOffset) > vpDim.width 
          ? (vpDim.width - elDim.width - this.options.pageOffset) : x) + 'px',
        top: ((y - vpOff.top + elDim.height) > vpDim.height && (y - vpOff.top) > elDim.height 
          ? (y - elDim.height) : y) + 'px'
      };
    this.container.setStyle(elOff).setStyle({zIndex: this.options.zIndex});
    if (this.ie) { 
      this.shim.setStyle(Object.extend(Object.extend(elDim, elOff), {zIndex: this.options.zIndex - 1})).show();
    }
    this.options.fade ? Effect.Appear(this.container, {duration: 0.25}) : this.container.show();
    this.event = e;
  },
  onClick: function(e) {
    e.stop();

    var clicked = e.target.up('li');
    
    if (clicked._callback && !e.target.hasClassName('disabled')) {
      this.options.beforeSelect(e);
      if (this.ie) this.shim.hide();
      this.container.hide();
      clicked._callback(this.event);
    }
  }
})
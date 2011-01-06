/*-------------------------------------------------------+
| Enzyme
| Copyright 2010-2011 Danny Allen <danny@enzyme-project.org>
| http://www.enzyme-project.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


// initialise
var Hotkey = {
  enabled: [],

  add: function(keyCombo, callback, pos) {
    // make all combo elements uppercase
    for (var i = 0; i < keyCombo.length; i++) {
      keyCombo[i] = keyCombo[i].toUpperCase();
    }

    // put into specific position?
    if (typeof pos != 'undefined') {
      Hotkey.enabled[pos] = {key:keyCombo, callback:callback};

    } else {
      Hotkey.enabled.push({key:keyCombo, callback:callback});
    }
  },
  
  remove: function(keyCombo, pos){
    for (var i = 0; i < keyCombo.length; i++) {
      keyCombo[i] = keyCombo[i].toUpperCase();
    }
    
    // look for specific position?
    if (typeof pos != 'undefined') {
      Hotkey.enabled[pos].callback = function(){ return false; };

    } else {
      // look for specified keycombo in list, remove if found
      for (var i = 0; i < Hotkey.enabled.length; i++){
        if ((Hotkey.enabled[i].key[0] == keyCombo[0]) && (Hotkey.enabled[i].key[1] == keyCombo[1])){
          Hotkey.enabled[i].callback = function(){ return false; };            
        }
      }
    }
  }
};


// setup the observer
Event.observe(document, 'keydown', function(event){
  if (($(Event.element(event)).tagName != 'INPUT') && 
      ($(Event.element(event)).tagName != 'TEXTAREA')) {

    // check if a shortcut has been matched
    var match;
    var keyCode;

    Hotkey.enabled.each(function(theShortcut) {
      match = 0;

      // look for the special keys
      if (event.shiftKey && (theShortcut.key.indexOf('SHIFT') != -1)) {
        match++;
      } else if (event.ctrlKey && (theShortcut.key.indexOf('CTRL') != -1)) {
        match++;
      } else if (event.ctrlKey && (theShortcut.key.indexOf('ALT') != -1)) {
        match++;
      } else if ((event.keyCode == Event.KEY_LEFT) && (theShortcut.key.indexOf('LEFT') != -1)) {
        match++;
      } else if ((event.keyCode == Event.KEY_RIGHT) && (theShortcut.key.indexOf('RIGHT') != -1)) {
        match++;
      } else if ((event.keyCode == Event.KEY_DOWN) && (theShortcut.key.indexOf('DOWN') != -1)) {
        match++;
      } else if ((event.keyCode == Event.KEY_UP) && (theShortcut.key.indexOf('UP') != -1)) {
        match++;
      } else if ((event.keyCode == Event.KEY_ESC) && (theShortcut.key.indexOf('ESC') != -1)) {
        match++;
      }

      keyCode = String.fromCharCode(event.keyCode).toUpperCase();

      // look for other keys
      if (theShortcut.key.indexOf(keyCode) != -1) {
        match++;
      }

      // fire off the associated action?
      if (match == theShortcut.key.length) {
        theShortcut.callback(event);
        match = 0;
        throw $break;
      } 
    });
  }
});
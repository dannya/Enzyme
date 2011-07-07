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


var searching   = false;
var searchValue = '';

var editable    = null;



// make 'esc' cancel field editing without save
Hotkey.add(['ESC'], function(event) { finishEdit(); }, 1, true);

// make 'enter' finish field editing with save
Hotkey.add(['ENTER'], function(event) { finishEdit(true); }, 2, true);



// onload...
document.observe('dom:loaded', function() {
  if ($('interact-bar') && $('interact-field') && $('interact-value')) {
  	// ensure we don't show operations unsupported by field type
  	changeInteractField(null, $('interact-field'));

    // intercept regular form submit
    Event.observe($('interact-bar'), 'submit', function(event) {
      Event.stop(event);

      // submit form through function
      if (!searching) {
        interactSearch();
      }

      return false;
    });


    // observe keypress so we can do filtering
    Event.observe($('interact-value'), 'keyup', function(event) {
      Event.stop(event);

      if ($('interact-type').value == 'filter') {
      	//console.debug($('interact-value').value);
      }

      return false;
    });
  }
	

	// observe scroll of developers list view so we can keep headers in view
  if ($('developers-container')) {
		Event.observe($('developers-container'), 'scroll', function() {
			$('developers-headers').setStyle({
				top: $('developers-container').scrollTop + 'px'
			});
		});
  }
  
  
  // observe clicks on table so we can fields edit-on-click
  $('body').observe('click', function(event) {
  	var element = Event.element(event);

  	// catch clicks anywhere (except editable cells!) to finish editing
  	if ((element.tagName != 'OPTION') && !element.hasClassName('editable')) {
      finishEdit(true);
  	}

    // catch clicks on cells to initiate editing
  	if ((element.tagName == 'TD') && element.hasClassName('column')) {
  		var type = element.readAttribute('class').sub('column-', '').sub('column', '').sub('empty', '').trim();
  		
  		// add editing class
  		element.addClassName('editing');

  		// setup editable element
  		if (element.readAttribute('data-type') == 'enum') {
  			// select field, fetch from form
  			if ($('enum-' + type)) {
  				// clone field
  				editable = $('enum-' + type).clone(true);
  				editable.writeAttribute('id', null);


          // set existing value as selected option
          var selectIndex = 0;

          Object.values(editable.options).each(function(item) {
          	if (item.tagName == 'OPTION') {
          		if (item.value == element.readAttribute('data-value')) {
          			// matching option
          	    throw $break;
              }
              
              ++selectIndex;
          	}
          });
          
          if (selectIndex >= editable.options.length) {
            selectIndex = 0;
          }

          editable.selectedIndex = selectIndex;


          // make new select element visible
          editable.addClassName('editable');
          editable.show();
  			}

  		} else {
  			// input field
        editable = new Element('input', { type:   'text',
                                          class:  'editable',
                                          value:  element.readAttribute('data-value') });  			
  		}

  		// put editable element into field
  		element.update(editable);
  		
  		// focus inserted editable element
  		editable.focus();
  	}
  });
});



function finishEdit(doSave) {
	// sanity check
	if ((typeof editable != 'object') || (editable === null)) {
		return false;
	}
	

  // get elements
	var theParent   = editable.up('td.editing');
  var theAccount  = theParent.up('tr').down('td.column-account').readAttribute('data-value').trim();
  var theField    = theParent.readAttribute('data-field').trim();
  var theValue    = editable.value.trim();


	// save changes? (only save if value has changed)
	if ((typeof doSave == 'boolean') && doSave && 
	    !theAccount.empty() && (theValue != theParent.readAttribute('data-value').trim())) {

	  // show spinner
	  $('interact-spinner').show();

	  // send off data
	  new Ajax.Request(BASE_URL + '/get/developer-data.php', {
	    method: 'post',
	    parameters: {
	      context:  'save', 
	      account:  theAccount,
	      field:    theField,
	      value:    theValue
	    },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;

	      if ((typeof result.success != 'undefined') && result.success) {
	      	// change data-value to new value for future saves
	      	theParent.writeAttribute('data-value', theValue);
	        
	      } else {
	      	// failure
	      	alert('Failure');
	      }

	      // hide spinner
        $('interact-spinner').hide();
	    }
	  });

	} else {
		// don't save:
		// define original value to put back into static cell
	  var theValue = theParent.readAttribute('data-value').trim();   
	}


  // run value through display method?
  if (theParent.readAttribute('data-type') == 'enum') {
    theValue = enumToString(theValue);
  }

  // remove / add empty class to cell?
  if (theValue.empty() && !editable.hasClassName('empty')) {
  	theParent.addClassName('empty');
  } else {
    theParent.removeClassName('empty');
  }

	// put static value back into cell
	editable.insert({ after: theValue });

  // remove editing cell class
  theParent.removeClassName('editing');

	// remove editable input from document
	Element.remove(editable);

  // set editable pointer to null
	editable = null;
}



function interactSearch(event) {
	// sanity check
	if (!$('interact-field') || !$('interact-op') || !$('interact-value')) {
		return false;
	}
	
	// hide button, show spinner
	searching = true;
	$('interact-button').hide();
	$('interact-spinner').show();

  // send off data
  new Ajax.Request(BASE_URL + '/get/developer-data.php', {
    method: 'post',
    parameters: {
    	context:  'draw', 
      field:    $('interact-field').value,
      operator: $('interact-op').value,
      value:    $('interact-value').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // insert returned rows into table
        $('developers-body').update(transport.responseText);
        
        // show number of results
        if ($('interact-results')) {
          $('interact-results').update(sprintf(strings.num_results_plural, result.results));
          $('interact-results').show();
        }

        // hide prompt
        $('developers-prompt').hide();
        
        if (result.results == 0) {
	        // show "search again" prompt
	        $('developers-again').show();

	        $('developers-headers').hide();
	        $('developers').hide();

        } else {
          // show table
          $('developers-headers').show();
          $('developers').show();
          
          $('developers-again').hide();        	
        }
        
        // scroll to top of developers container
        $('developers-container').scrollTop   = 0;
        $('developers-container').scrollLeft  = 0;

      } else {
        // failure
        alert('Failure');
      }
      
      // hide spinner, show button
      $('interact-spinner').hide();
      $('interact-button').show();

      searching = false;
    }
  });

	return false;
}


function changeInteractType(event) {
	if ((typeof event == 'undefined') || !$('interact-button')) {
		return false;
	}
	
	var element = Event.element(event);

	if (element.value == 'filter') {
		// hide button
		$('interact-button').hide();

		// blank input field
		searchValue = $('interact-value').value;
		$('interact-value').value = '';

	} else {
		// show button
		$('interact-button').show();
		
    // refill input field
    $('interact-value').value = searchValue;
	}
}


function changeInteractField(event, element) {
  if (((typeof event != 'object') && (typeof element != 'object')) || 
      !$('interact-op')) {

    return false;
  }
  
  // get element
  if (typeof element != 'object') {
    var element = Event.element(event);
  }
  
  if ((element.value == 'gender') || (element.value == 'continent') || (element.value == 'motivation') || (element.value == 'microblog_type')) {
  	// enum
  	var isEnum = true;

    $('interact-op').select('option').each(function(item) {
      item.hide();
    });

    $('interact-op').select('option[value="eq"]').first().show();

  } else if ((element.value == 'dob') || (element.value == 'latitude') || (element.value == 'longitude')) {
    // numeric
    var isEnum = false;

    $('interact-op').select('option').each(function(item) {
    	item.show();
    });

  } else {
  	// textual
    var isEnum = false;

    $('interact-op').select('option').each(function(item) {
      item.show();
    });

    $('interact-op').select('option[value="lt"]').first().hide();
    $('interact-op').select('option[value="gt"]').first().hide();
  }
  
  
  // enum / not-enum specific actions
  if (isEnum) {
    // reset to default element so we don't show hidden options
    $('interact-op').selectedIndex = 0;

    // change to select box
    if ($('interact-value')) {
      Element.remove($('interact-value'));
    }

    var enumElement = $('enum-' + element.value).clone(true);
    enumElement.writeAttribute('id', 'interact-value');
    enumElement.show();
    Element.insert($('interact-op'), { after: enumElement });

  } else {
  	// select 'contains' as default option
  	$('interact-op').selectedIndex = 5;
  	
    // change to input box
    Element.remove($('interact-value'));
    Element.insert($('interact-op'), { after: '<input id="interact-value" type="text" value="" />' });
  }
}


function deleteDeveloper(event) {
	Event.stop(event);

  if (confirm(confirm_dev_delete)) {
	  // get username
	  var theParent   = Event.element(event).up('tr');
    var theAccount  = theParent.down('td.column-account').readAttribute('data-value').trim();

	  // send off delete request
	  new Ajax.Request(BASE_URL + '/get/developer-data.php', {
	    method: 'post',
	    parameters: {
	      context:  'delete', 
	      account:  theAccount
	    },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;

	      if ((typeof result.success != 'undefined') && result.success) {
	      	// remove row from DOM
	      	Element.remove(theParent);
	      	
	      	// decrement counter displays
	        if ($('developers-num-records')) {
	          $('developers-num-records').update(sprintf(strings.num_developer_records, (parseInt($('developers-num-records').innerHTML) - 1)));
	        }
	        if ($('interact-results')) {
            $('interact-results').update(sprintf(strings.num_results_plural, (parseInt($('interact-results').innerHTML)) - 1));
	        }
	      }
	    }
	  });
  }

	return false;
}


function enumToString(key) {
  var tmpKey = key.gsub('-', '_');

  // return
  if (typeof enums[tmpKey] == 'string') {
    return enums[tmpKey];

  } else {
    return key;
  }
}


function addDeveloper() {
	// XX:todo
	alert('Not implemented yet');
}
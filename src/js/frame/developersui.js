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



document.observe('dom:loaded', function() {
  if ($('interact-bar') && $('interact-value')) {
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
      	console.debug($('interact-value').value);
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
  	var element = event.element();

  	// catch clicks anywhere to finish editing
    finishEdit();

    // catch clicks on cells to initiate editing
  	if (element.hasClassName('column')) {
  		var type = element.readAttribute('class').sub('column-', '').sub('column', '').sub('empty', '').trim();
  		
  		// add editing class
  		element.addClassName('editing');
  		
  		// setup editable element
  		editable = new Element('input', { type:   'text',
  		                                  value:  'boo' });

  		// put editable element into field
  		element.update(editable);
  	}
  });
});



function finishEdit(doSave) {
	// sanity check
	if ((typeof editable != 'object') || (editable === null)) {
		return false;
	}

	// save changes?
	if ((typeof doSave == 'boolean') && doSave) {
		
	}

	// put static value back into cell
	editable.insert({ after: 'boo' });
	
  // remove editing cell class
  editable.up('td.editing').removeClassName('editing');

	// remove editable input from document
	editable.remove();
	
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
        $('developers-container').scrollTop = 0;
        
			  // hide spinner, show button
			  $('interact-spinner').hide();
        $('interact-button').show();

        searching = false;
      }
    }
  });

	return false;
}


function changeInteractType(event) {
	if ((typeof event == 'undefined') || !$('interact-button')) {
		return false;
	}
	
	var element = event.element();

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


function changeInteractField(event) {
  if ((typeof event == 'undefined') || !$('interact-op')) {
    return false;
  }
  
  var element = event.element();
  
  if ((element.value == 'gender') || (element.value == 'continent') || (element.value == 'motivation')) {
  	// enum
    $('interact-op').select('option').each(function(item) {
      item.hide();
    });

    $('interact-op').select('option[value="eq"]').first().show();

  } else if ((element.value == 'dob') || (element.value == 'latitude') || (element.value == 'longitude')) {
    // numeric
    $('interact-op').select('option').each(function(item) {
    	item.show();
    });

  } else {
  	// textual
    $('interact-op').select('option').each(function(item) {
      item.show();
    });

    $('interact-op').select('option[value="lt"]').first().hide();
    $('interact-op').select('option[value="gt"]').first().hide();
  }

  // reset to first element so we don't show hidden options
  $('interact-op').selectedIndex = 0;
}
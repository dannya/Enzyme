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


// keep scroll at bottom
document.observe('dom:loaded', function() {
  if ($('result')) {
    var checkScrollRepeat = window.setInterval(checkScroll, 500);
  }
});


function parseDevelopers(event) {
  Event.stop(event);

  if (!$('show-skipped') || !$('result')) {
    return false;
  }

  // change result iframe URL to start insert process (and show results!)
  $('result').src = BASE_URL + '/tool/parse-developers.php?show_skipped=' + $('show-skipped').checked;
}


function addNewFilter() {
	if (!$('path-filters-items') || !$('path-filters-new')) {
		return false;
	}
	
  // clone new row, so we can keep adding new rows after this one
  var newRow = $('path-filters-new').innerHTML;

  // make row and elements visible, change id's  
  var tmpId  = Math.floor(Math.random() * 10000); 

  $('path-filters-new').show();
  $('path-filters-new').select('select, input').invoke('show');
  $('path-filters-new').writeAttribute('class', $('path-filters-items').select('tr').size());

  $('path-filters-new').id  = 'path-filter-' + tmpId;
  $('path-new').id          = 'path-' + tmpId;
  $('target-new').id        = 'target-' + tmpId;

  // insert original "new" row back into table
  $('path-filters-items').insert({ bottom: '<tr id="path-filters-new">' + newRow + '</tr>' });

  // scroll to new row
  if ($('path-filter-' + tmpId)) {
    $('path-filter-' + tmpId).scrollTo();
    $('path-filter-' + tmpId).select('input[type="text"]').first().focus();
  }
}


function saveFilters() {
	if (!$('path-filters-data')) {
	  return false;
	}


	// ensure all values are filled, and serialise form data
	var error              = false;

	var formData           = {};
	formData['id[]']       = [];
	formData['targets[]']  = [];
	formData['matches[]']  = [];
	formData['areas[]']    = [];
	
	$('path-filters-data').getElements().each(function(item) {
		if (item.visible()) {
			if ((item.type != 'hidden') && (item.value.empty() || (item.value == 0))) {
				item.addClassName('failure');
				error = true;

			} else {
				item.removeClassName('failure');

				// add to data
				formData[item.name].push(item.value);
			}
		}
	});

	if (error) {
	  return false;
	}


  // send off values
  new Ajax.Request(BASE_URL + '/get/commit-area-filtering.php', {
    method: 'post',
    parameters: formData,
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator('save-filters', 'indicator-success');
      } else {
        showIndicator('save-filters', 'indicator-failure');
      }
      
      // update total display
      if ($('status')) {
      	$('status').update(sprintf(strings.num_filters_plural, ($('path-filters-items').select('tr').size() - 1)));
      }

      return true;
    },
    onFailure: function() {
      return false;
    }
  });
}


function addNewLink() {
  if (!$('path-links-external-items') || !$('path-links-external-new')) {
    return false;
  }

  // clone new row, so we can keep adding new rows after this one
  var newRow = $('path-links-external-new').innerHTML;

  // make row and elements visible, change id's  
  var tmpId  = Math.floor(Math.random() * 10000); 

  $('path-links-external-new').show();
  $('path-links-external-new').select('select, input').invoke('show');
  $('path-links-external-new').writeAttribute('class', $('path-links-external-items').select('tr').size());

  $('path-links-external-new').id  = 'path-links-external-' + tmpId;
  $('type-external-new').id        = 'type-external-new-' + tmpId;

  // insert original "new" row back into table
  $('path-links-external-items').insert({ bottom: '<tr id="path-links-external-new">' + newRow + '</tr>' });

  // scroll to new row
  if ($('path-links-external-' + tmpId)) {
    $('path-links-external-' + tmpId).scrollTo();
    $('path-links-external-' + tmpId).select('input[type="text"]').first().focus();
  }
}


function saveLinks() {
  if (!$('path-links-data')) {
    return false;
  }


  // ensure all values are filled, and serialise form data
  var error              = false;

  var formData           = {};
  formData['types[]']    = [];
  formData['names[]']    = [];
  formData['links[]']    = [];
  formData['areas[]']    = [];

  $('path-links-data').getElements().each(function(item) {
    if (item.visible()) {
      if ((item.type != 'hidden') && (item.name != 'areas[]') && (item.name != 'links[]') 
          && (item.value.empty() || (item.value == 0))) {

        item.addClassName('failure');
        error = true;

      } else {
        item.removeClassName('failure');

        // add to data
        formData[item.name].push(item.value);
      }
    }
  });

  if (error) {
    return false;
  }


  // send off values
  new Ajax.Request(BASE_URL + '/get/project-links.php', {
    method: 'post',
    parameters: formData,
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator('save-links', 'indicator-success');
      } else {
        showIndicator('save-links', 'indicator-failure');
      }
      
      // update total display
      if ($('status')) {
      	var numItems = 0;

      	$('path-links-data').select('tbody').each(function(item) {
      		numItems += item.select('tr').size();
        })

        $('status').update(sprintf(strings.num_links_plural, (numItems - 4)));
      }

      return true;
    },
    onFailure: function() {
      return false;
    }
  });
}


function deleteItem(theContext, elementId, theName) {
  if ((typeof theContext != 'string') || (typeof elementId != 'string') || (typeof theName != 'string')) {
    return false;
  }


  // send off delete request
  new Ajax.Request(BASE_URL + '/get/delete-item.php', {
    method: 'post',
    parameters: {
    	context: theContext,
    	id:      theName
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // remove link row from page
        if ($(elementId)) {
        	new Effect.Fade(elementId, {
        		duration: 0.3,
        		afterFinish: function() {
        			$(elementId).remove();

			        // update total display
			        if ($('status')) {
			          var numItems = 0;

                if (theContext == 'filter') {
                	// filters
                  numItems += $('path-filters-items').select('tr').size();
  
                  $('status').update(sprintf(strings.num_filters_plural, (numItems - 1)));

                } else if (theContext == 'link') {
                  // links
				          $('path-links-data').select('tbody').each(function(item) {
				            numItems += item.select('tr').size();
				          })
	
				          $('status').update(sprintf(strings.num_links_plural, (numItems - 4)));
                }
			        }
        		}
        	});
        }

      } else {
        showIndicator('save-items', 'indicator-failure');
      }

      return true;
    },
    onFailure: function() {
      return false;
    }
  });
}
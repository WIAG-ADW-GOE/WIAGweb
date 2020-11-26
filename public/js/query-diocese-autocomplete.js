'use strict';
$(document).ready(function() {
    $('.js-name-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
            {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.names);
		    });
		},
		displayKey: 'suggestion',
		debounce: 400 // only request every 400 ms
            }
	]);
    });

});


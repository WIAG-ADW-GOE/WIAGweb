$(document).ready(function() {
    $('.js-name-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
            {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.persons);
		    });
		},
		displayKey: 'suggestion',
		debounce: 400 // only request every 400 ms
            }
	]);
	// window.alert('suggest name');
    });

    $('.js-place-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
            {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.places);
		    });
		},
		displayKey: 'suggestion',
		debounce: 400 // only request every 400 ms
            }
	]);
	// window.alert('suggest place');
    });

});


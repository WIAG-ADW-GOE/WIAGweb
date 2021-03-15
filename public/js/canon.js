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

    $('.js-office-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
	    {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.offices);
		    });
		},
		displayKey: 'suggestion',
		debounce: 400 // only request every 400 ms
	    }
	]);
	// window.alert('suggest office');
    });

    // copy link for the current person to clipboard
    $('.to-clipboard').each(function() {
	$(this).click(function(event) {
	    var buttonElem = $(this);
	    var clipboardText = buttonElem[0].getAttribute('title');
	    navigator.clipboard.writeText(clipboardText);
	});
    });

    // copy citation to clipboard
    $('#copy-citation').click(function() {
	var citationElem = $('#citation');
	var clipboardText = citationElem.text();
	navigator.clipboard.writeText(clipboardText);
    });


});

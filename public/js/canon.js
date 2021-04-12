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

    // click on a checkbox submits the form
    var fpx = $("input[id*='canon_form_facet']");
    fpx.each(function() {
	var btn_submit = $("#canon_form_searchHTML");
	$(this)[0].addEventListener('click', function(event) {
	    btn_submit.click();
	});
    });

    // reset the facet if textboxes change
    var input_text = $("#canon_form_name, #canon_form_place, #canon_form_office, #canon_form_year, #canon_form_someid");
    input_text.each(function() {
	$(this)[0].addEventListener('change', function(event) {
	    fpx.each(function() {
		console.log('remove checked');
		$(this)[0].removeAttribute('checked');
	    });
	});
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

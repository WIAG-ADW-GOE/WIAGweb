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

    /** collapse long facets
     * dioceses
     */
    var input_statefctdioc = $('#bishop_query_form_stateFctDioc');
    var div_fctcpsdioc = $('#restFctDioc');
    var btn_cpsdioc = $('#btnTglFctDioc');

    // see bootstrap for shown.bs.collaps, hidden.bs.collaps, collaps
    if (div_fctcpsdioc.length > 0) {
	div_fctcpsdioc.on('shown.bs.collapse', function () {
	    var btn_html = btn_cpsdioc.html();
	    btn_cpsdioc.html(btn_html.replace('arrow-down', 'arrow-up'));
	    input_statefctdioc[0].setAttribute('value', '1');
	    // console.log(input_statefctdioc);
	});

	div_fctcpsdioc.on('hidden.bs.collapse', function () {
	    var btn_html = btn_cpsdioc.html();
	    btn_cpsdioc.html(btn_html.replace('arrow-up', 'arrow-down'));
	    input_statefctdioc[0].setAttribute('value', '0');
	    // console.log(input_statefctdioc);
	});

	if (input_statefctdioc[0].getAttribute('value') != '0') {
	    // console.log(input_statefctdioc[0].getAttribute('value'));
	    div_fctcpsdioc.collapse('show');
	} else {
	    div_fctcpsdioc.collapse('hide');
	}

    }

        /** collapse long facets
     * offices
     */
    var input_statefctofc = $('#bishop_query_form_stateFctOfc');
    var div_fctcpsofc = $('#restFctOfc');
    var btn_cpsofc = $('#btnTglFctOfc');

    if (div_fctcpsofc.length > 0) {
	div_fctcpsofc.on('shown.bs.collapse', function () {
	    var btn_html = btn_cpsofc.html();
	    btn_cpsofc.html(btn_html.replace('arrow-down', 'arrow-up'));

	    input_statefctofc[0].setAttribute('value', '1');
	    // console.log(input_statefctofc);
	});

	div_fctcpsofc.on('hidden.bs.collapse', function () {
	    var btn_html = btn_cpsofc.html();
	    btn_cpsofc.html(btn_html.replace('arrow-up', 'arrow-down'));

	    input_statefctofc[0].setAttribute('value', '0');
	    // console.log(input_statefctofc);
	});

	if (input_statefctofc[0].getAttribute('value') != '0') {
	    // console.log(input_statefctofc[0].getAttribute('value'));
	    div_fctcpsofc.collapse('show');
	} else {
	    div_fctcpsofc.collapse('hide');
	}

    }

        // click on a checkbox submits the form
    // console.log('query-bishop-fire-facet');
    var fpx = $("input[id*='bishop_query_form_facet']");
    var btn_submit = $("#bishop_query_form_searchHTML");
    fpx.each(function() {
	$(this)[0].addEventListener('click', function(event) {
	    btn_submit.click();
	});
    });

    // reset the facet if textboxes change
    var input_text = $("#bishop_query_form_name, #bishop_query_form_place, #bishop_query_form_office, #bishop_query_form_year, #bishop_query_form_someid");
    input_text.each(function() {
	$(this)[0].addEventListener('change', function(event) {
	    fpx.each(function() {
		console.log('remove checked');
		$(this)[0].removeAttribute('checked');
	    });
	    input_statefctdioc[0].setAttribute('value', '0');
	    input_statefctofc[0].setAttribute('value', '0');
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

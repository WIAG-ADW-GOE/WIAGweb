'use strict';
$(document).ready(function() {
    /**
     * autocompletion
     * In most of the cases, we can use a general function.
     * Use `autocompleteUrl` to generate a specific list of completions
     */

    $('.js-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
	    {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.choices);
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

    $('.js-domstift-autocomplete').each(function() {
	var autocompleteUrl = $(this).data('autocomplete-url');
	$(this).autocomplete({hint: false}, [
	    {
		source: function(query, cb) {
		    $.ajax({
			url: autocompleteUrl+'?query='+query
		    }).then(function(data) {
			cb(data.monasteries);
		    });
		},
		displayKey: 'suggestion',
		debounce: 400 // only request every 400 ms
	    }
	]);
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
     * locations
     */
    var input_statefctloc = $('#canon_form_stateFctLoc');
    var div_fctcpsloc = $('#restFctLoc');
    var btn_cpsloc = $('#btnTglFctLoc');

    // see bootstrap for shown.bs.collaps, hidden.bs.collaps, collaps
    if (div_fctcpsloc.length > 0) {
	div_fctcpsloc.on('shown.bs.collapse', function () {
	    var btn_html = btn_cpsloc.html();
	    btn_cpsloc.html(btn_html.replace('arrow-down', 'arrow-up'));
	    input_statefctloc[0].setAttribute('value', '1');
	    // console.log(input_statefctloc);
	});

	div_fctcpsloc.on('hidden.bs.collapse', function () {
	    var btn_html = btn_cpsloc.html();
	    btn_cpsloc.html(btn_html.replace('arrow-up', 'arrow-down'));
	    input_statefctloc[0].setAttribute('value', '0');
	    // console.log(input_statefctloc);
	});

	if (input_statefctloc[0].getAttribute('value') != '0') {
	    // console.log(input_statefctloc[0].getAttribute('value'));
	    div_fctcpsloc.collapse('show');
	} else {
	    div_fctcpsloc.collapse('hide');
	}

    }

    /** collapse long facets
     * monasteries
     */
    var input_statefctmon = $('#canon_form_stateFctMon');
    var div_fctcpsmon = $('#restFctMon');
    var btn_cpsmon = $('#btnTglFctMon');

    if (div_fctcpsmon.length > 0) {
	div_fctcpsmon.on('shown.bs.collapse', function () {
	    var btn_html = btn_cpsmon.html();
	    btn_cpsmon.html(btn_html.replace('arrow-down', 'arrow-up'));

	    input_statefctmon[0].setAttribute('value', '1');
	    // console.log(input_statefctmon);
	});

	div_fctcpsmon.on('hidden.bs.collapse', function () {
	    var btn_html = btn_cpsmon.html();
	    btn_cpsmon.html(btn_html.replace('arrow-up', 'arrow-down'));

	    input_statefctmon[0].setAttribute('value', '0');
	    // console.log(input_statefctmon);
	});

	if (input_statefctmon[0].getAttribute('value') != '0') {
	    // console.log(input_statefctmon[0].getAttribute('value'));
	    div_fctcpsmon.collapse('show');
	} else {
	    div_fctcpsmon.collapse('hide');
	}

    }

    /** collapse long facets
     * offices
     */
    var input_statefctofc = $('#canon_form_stateFctOfc');
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
    var fpx = $("input[id*='canon_form_facet']");
    fpx.each(function() {
	var btn_submit = $("#canon_form_searchHTML");
	$(this)[0].addEventListener('click', function(event) {
	    btn_submit.click();
	});
    });

    // reset the facets if textboxes change
    var input_text = $("#canon_form_name, #canon_form_monastery, #canon_form_place, #canon_form_office, #canon_form_year, #canon_form_someid");
    input_text.each(function() {
	$(this)[0].addEventListener('change', function(event) {
	    fpx.each(function() {
		// console.log('remove checked');
		$(this)[0].removeAttribute('checked');
	    });
	    input_statefctloc[0].setAttribute('value', '0');
	    input_statefctmon[0].setAttribute('value', '0');
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

    // activate save button in edit form
    var edit_safe = $( '#cn-edit-save' );
    if( edit_safe.length != 0 ) {
	edit_safe[0].disabled = true;
    }

    $( '[id^=canon_edit_form]' ).on( "input", function( event ) {
	edit_safe[0].disabled = false;
    });

    // activate save button in office edit form
    var edit_office_safe = $( '#cn_office_edit_form_btn_save' );
    if( edit_office_safe.length != 0 ) {
	edit_office_safe[0].disabled = true;
    }

    $( '[id^=cn_office_edit_form]' ).on( "input", function( event ) {
	edit_office_safe[0].disabled = false;
    });

    // hide result list to avoid click on invalidated links/buttons
    var list_result = $( '#list-result' );
    var count_result = $ ('#count-result' );
    $( '[id^=canon_form]' ).on( "input", function( event ) {
	if (list_result.length > 0) {
	    list_result[0].style.visibility = "hidden";
	}
	if (count_result.length > 0) {
	    count_result[0].style.visibility = "hidden";
	}
    });


});

'use strict';
$(document).ready(function () {
    // click on a checkbox submits the form
    console.log('query-bishop-fire-facet');
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
	});
    });
});


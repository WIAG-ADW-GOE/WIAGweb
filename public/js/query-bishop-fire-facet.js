'use strict';
$(document).ready(function () {
    console.log('query-bishop-fire-facet');
    var fpx = $("input[id*='bishop_query_form_facet']");
    var btn_submit = $("#bishop_query_form_submit");
    fpx.each(function() {
	$(this)[0].addEventListener('click', function(event) {
	    btn_submit.click();
	});
    });    
});


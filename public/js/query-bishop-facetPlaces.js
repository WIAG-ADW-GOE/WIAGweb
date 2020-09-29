'use strict';
$(document).ready(function () {
    console.log('facet-options start');
    var i_name = document.getElementById('bishop_query_form_name');
    var v_name = fieldname.getAttribute('value');
    console.log(q_name);
    $.ajax({
	method: 'POST',
	// TODO set this path in the form
        url: '/query-bishops/utility/facetplaces',
        data: {
            name: q_name,
	    place: 'Pedena',
        },
        success: function (html) {
	    console.log(html);
        }
    });
});

'use strict';
$(document).ready(function () {
    console.log('document ready');
    document.getElementById('toggleFacetPlaces').addEventListener('click', function(event) {
	var inputelem = document.getElementById('facetPlacesState');
	var state = event.target.getAttribute('aria-expanded');
	if (state == 'false') {
	    inputelem.setAttribute('value', 'show');
	} else {
	    inputelem.setAttribute('value', 'hide');
	}
	// close facet offices if open
	var btnfctoffices = document.getElementById('toggleFacetOffices');
	var btnstate = btnfctoffices.getAttribute('aria-expanded');
	if (btnstate == 'true') {
	    btnfctoffices.click();
	}
    });

    var infofctpl = document.getElementById('facetPlacesState');
    var facetPlacesState = infofctpl.getAttribute('value');
    var btnfctpl = document.getElementById('toggleFacetPlaces');
    if (facetPlacesState == 'show') {
	btnfctpl.click();
    };

    document.getElementById('toggleFacetOffices').addEventListener('click', function(event) {
	var inputelem = document.getElementById('facetOfficesState');
	var state = event.target.getAttribute('aria-expanded');
	if (state == 'false') {
	    inputelem.setAttribute('value', 'show');
	} else {
	    inputelem.setAttribute('value', 'hide');
	}
	// close facet places if open
	var btnfctplaces = document.getElementById('toggleFacetPlaces');
	var btnstate = btnfctplaces.getAttribute('aria-expanded');
	if (btnstate == 'true') {
	    btnfctplaces.click();
	}
    });

    var infofctof = document.getElementById('facetOfficesState');
    var facetOfficesState = infofctof.getAttribute('value');
    var btnfctof = document.getElementById('toggleFacetOffices');
    if (facetOfficesState == 'show') {
	btnfctof.click();
    };

});

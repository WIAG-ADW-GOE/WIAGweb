'use strict';
$(document).ready(function () {
    document.getElementById('toggleFacetPlaces').addEventListener('click', function(event) {
    	var inputelem = document.getElementById('facetPlacesState');
    	var state = event.target.getAttribute('aria-expanded');
    	if (state == 'false') {
    	    inputelem.setAttribute('value', 'show');
    	} else {
    	    inputelem.setAttribute('value', 'hide');
    	}
    });
    
    var infofctpl = document.getElementById('facetPlacesState');
    var facetPlacesState = infofctpl.getAttribute('value');
    var btnfctpl = document.getElementById('toggleFacetPlaces');
    if (facetPlacesState == 'show') {
    	btnfctpl.click();
    }
});

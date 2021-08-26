import { Controller } from 'stimulus';

export default class extends Controller {
    connect() {
	// console.log('this is hide-on-input');
    }

    hideResults(event) {
	// console.log('event bubbles up');
	var targets = this.element.getElementsByClassName('result');
	const eventTargetType = event.target.getAttribute('type');
	if (eventTargetType == 'text') {
	    for (let target of targets) {
		target.style.visibility = "hidden";
	    }
	}
    }

}

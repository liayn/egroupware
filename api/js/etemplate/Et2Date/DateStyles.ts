/**
 * Sharable date styles constant
 */

import {css} from "@lion/core";
import {colorsDefStyles} from "../Styles/colorsDefStyles";
import {cssImage} from "../Et2Widget/Et2Widget";

export const dateStyles = [
	colorsDefStyles,
	css`
	:host {
		display: inline-block;
		white-space: nowrap;
		min-width: 20ex;
	}
	.overdue {
		color: red; // var(--whatever the theme color)
	}
	input.flatpickr-input {
		border: 1px solid;
		border-color: var(--input-border-color);
		color: var(--input-text-color);
		padding-top: 4px;
		padding-bottom: 4px;
	}
	input.flatpickr-input:hover {
		background-image: ${cssImage("datepopup")};
		background-repeat: no-repeat;
		background-position-x: right;
		background-position-y: 1px;
		background-size: 18px;
	}
`];

// The lit-flatpickr package uses a CDN, in violation of best practices
// I've downloaded it
const themeUrl = "api/js/etemplate/Et2Date/flatpickr_material_blue.css";
const styleElem = document.createElement('link');
styleElem.rel = 'stylesheet';
styleElem.type = 'text/css';
styleElem.href = themeUrl;
document.head.append(styleElem);

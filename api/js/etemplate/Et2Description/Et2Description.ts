/**
 * EGroupware eTemplate2 - Description WebComponent
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @link https://www.egroupware.org
 * @author Nathan Gray
 */

import {Et2Widget} from "../Et2Widget/Et2Widget";
import {css, html, LitElement, render} from "@lion/core";
import {et2_IDetachedDOM} from "../et2_core_interfaces";
import {activateLinks} from "../ActivateLinksDirective";

export class Et2Description extends Et2Widget(LitElement) implements et2_IDetachedDOM
{

	protected _value : string = "";

	static get styles()
	{
		return [
			...super.styles,
			css`
			* {
				white-space: pre-wrap;
			}
			:host a {
				cursor: pointer;
				color: #26537c;
				text-decoration: none;
			}`
		];
	}

	static get properties()
	{
		return {
			...super.properties,
			/**
			 * Scan the value, and if there are any links (URL, mailto:) then wrap them in a clickable
			 * <a/> tag
			 */
			activate_links: {
				type: Boolean,
				reflect: true
			},
			/**
			 * Extra link target
			 * Goes with href.  If provided, that's the target for opening the link.
			 */
			extra_link_target: {
				type: String,
				reflect: true
			},
			/**
			 * widthxheight, if popup should be used, eg. 640x480
			 */
			extra_link_popup: {
				type: String,
				reflect: true
			},
			/**
			 * Link URL
			 * If provided, will be clickable and open this URL
			 */
			href: {
				type: String,
				reflect: true
			},
			value: String,
		}
	}

	constructor()
	{
		super();

		// Initialize properties
		this.activate_links = false;
		this.extra_link_popup = "";
		this.extra_link_target = "_browser";
		// Don't initialize this to avoid href(unknown) when rendered
		//this.href = "";
		this.value = "";

		this._handleClick = this._handleClick.bind(this);
	}

	connectedCallback()
	{
		super.connectedCallback();

		// Put content directly in DOM
		if(this.value)
		{
			render(this._renderContent(), this);
		}
	}

	set_value(value)
	{
		this.value = value;
	}

	get value()
	{
		return this._value;
	}

	set value(_value : string)
	{
		let oldValue = this.value;

		if(!_value)
		{
			_value = "";
		}

		// Do we do this here, or in transformAttributes()?
		if(_value && !this.no_lang)
		{
			_value = this.egw().lang(_value);
		}

		if(_value && (_value + "").indexOf('%s') != -1)
		{
			_value = _value.replace(/%s/g, _value);
		}

		this._value = _value;
		this.requestUpdate('value', oldValue);
	}

	requestUpdate(attribute, oldValue)
	{
		super.requestUpdate(...arguments);
		// Due to how we do the rendering into the light DOM (not sure it's right) we need this after
		// value change or it won't actually show up
		if(["value", "href", "activate_links"].indexOf(attribute) != -1 && this.parentNode)
		{
			this.updateComplete.then(() => render(this._renderContent(), <HTMLElement><unknown>this));
		}
	}

	_renderContent()
	{
		let render = null;

		// Add hover action button (Edit)
		if(this.hover_action)
		{
			// TODO
		}


		// If there's a link, wrap that
		if(this.href && this.value)
		{
			render = this.wrapLink(this.href, this.value);
		}
		// If we want to activate links inside, do that
		else if(this.activate_links && this.value)
		{
			render = this.getActivatedValue(this.value, this.href ? this.extra_link_target : '_blank');
		}
		// Just do the value
		else
		{
			render = html`${this.value}`;
		}
		return render;
	}

	render()
	{
		// Turn off IDE reformatting, or it will add an extra line break into the template
		// @formatter:off
		return html`<slot></slot>`;
		// @formatter:on
	}


	async firstUpdated()
	{
		this.removeEventListener('click.extra_link', this._handleClick);
		if(this.extra_link_popup || this.mime)
		{
			// Add click listener
			this.addEventListener('click.extra_link', this._handleClick);
		}
	}

	_handleClick(_ev : MouseEvent) : boolean
	{
		// call super to get the onclick handling running
		super._handleClick(_ev);

		if(this.mime_data || this.href)
		{
			egw(window).open_link(this.mime_data || this.href, this.extra_link_target, this.extra_link_popup, null, null, this.mime);
		}
		_ev.preventDefault();
		return false;
	}

	protected wrapLink(href, value)
	{
		if(href.indexOf('/') == -1 && href.split('.').length >= 3 &&
			!(href.indexOf('mailto:') != -1 || href.indexOf('://') != -1 || href.indexOf('javascript:') != -1)
		)
		{
			href = "/index.php?menuaction=" + href;
		}
		if(href.charAt(0) == '/')             // link relative to eGW
		{
			href = egw.link(href);
		}
		return html`<a href="${href}" target="${this.target ?? "_blank"}">${value}</a>`;
	}

	protected getActivatedValue(value, target)
	{
		return html`${activateLinks(value, target)}`;
	}

	getDetachedAttributes(attrs)
	{
		attrs.push("id", "value", "class", "href");
	}

	getDetachedNodes() : HTMLElement[]
	{
		return [<HTMLElement><unknown>this];
	}

	setDetachedAttributes(_nodes : HTMLElement[], _values : object, _data? : any) : void
	{
		for(let attr in _values)
		{
			this[attr] = _values[attr];
		}
	}

	loadFromXML()
	{
		// nope
	}
}
// @ts-ignore TypeScript is not recognizing that this widget is a LitElement
customElements.define("et2-description", Et2Description);

export class Et2Label extends Et2Description {}
// @ts-ignore TypeScript is not recognizing that this widget is a LitElement
customElements.define("et2-label", Et2Label);
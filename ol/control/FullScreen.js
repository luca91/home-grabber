/**
 * @module ol/control/FullScreen
 */
import Control from './Control.js';
import {CLASS_CONTROL, CLASS_UNSELECTABLE, CLASS_UNSUPPORTED} from '../css.js';
import {replaceNode} from '../dom.js';
import {listen} from '../events.js';
import EventType from '../events/EventType.js';

const events = ['fullscreenchange', 'webkitfullscreenchange', 'MSFullscreenChange'];

/**
 * @typedef {Object} Options
 * @property {string} [className='ol-full-screen'] CSS class name.
 * @property {string|Text} [label='\u2922'] Text label to use for the button.
 * Instead of text, also an element (e.g. a `span` element) can be used.
 * @property {string|Text} [labelActive='\u00d7'] Text label to use for the
 * button when full-screen is active.
 * Instead of text, also an element (e.g. a `span` element) can be used.
 * @property {string} [tipLabel='Toggle full-screen'] Text label to use for the button tip.
 * @property {boolean} [keys=false] Full keyboard access.
 * @property {HTMLElement|string} [target] Specify a target if you want the
 * control to be rendered outside of the map's viewport.
 * @property {HTMLElement|string} [source] The element to be displayed
 * fullscreen. When not provided, the element containing the map viewport will
 * be displayed fullscreen.
 */


/**
 * @classdesc
 * Provides a button that when clicked fills up the full screen with the map.
 * The full screen source element is by default the element containing the map viewport unless
 * overridden by providing the `source` option. In which case, the dom
 * element introduced using this parameter will be displayed in full screen.
 *
 * When in full screen mode, a close button is shown to exit full screen mode.
 * The [Fullscreen API](http://www.w3.org/TR/fullscreen/) is used to
 * toggle the map in full screen mode.
 *
 * @api
 */
class FullScreen extends Control {

  /**
   * @param {Options=} opt_options Options.
   */
  constructor(opt_options) {

    const options = opt_options ? opt_options : {};

    super({
      element: document.createElement('div'),
      target: options.target
    });

    /**
     * @private
     * @type {string}
     */
    this.cssClassName_ = options.className !== undefined ? options.className :
      'ol-full-screen';

    const label = options.label !== undefined ? options.label : '\u2922';

    /**
     * @private
     * @type {Text}
     */
    this.labelNode_ = typeof label === 'string' ?
      document.createTextNode(label) : label;

    const labelActive = options.labelActive !== undefined ? options.labelActive : '\u00d7';

    /**
     * @private
     * @type {Text}
     */
    this.labelActiveNode_ = typeof labelActive === 'string' ?
      document.createTextNode(labelActive) : labelActive;

    /**
     * @private
     * @type {HTMLElement}
     */
    this.button_ = document.createElement('button');

    const tipLabel = options.tipLabel ? options.tipLabel : 'Toggle full-screen';
    this.setClassName_(this.button_, isFullScreen());
    this.button_.setAttribute('type', 'button');
    this.button_.title = tipLabel;
    this.button_.appendChild(this.labelNode_);

    this.button_.addEventListener(EventType.CLICK, this.handleClick_.bind(this), false);

    const cssClasses = this.cssClassName_ + ' ' + CLASS_UNSELECTABLE +
        ' ' + CLASS_CONTROL + ' ' +
        (!isFullScreenSupported() ? CLASS_UNSUPPORTED : '');
    const element = this.element;
    element.className = cssClasses;
    element.appendChild(this.button_);

    /**
     * @private
     * @type {boolean}
     */
    this.keys_ = options.keys !== undefined ? options.keys : false;

    /**
     * @private
     * @type {HTMLElement|string|undefined}
     */
    this.source_ = options.source;

  }

  /**
   * @param {MouseEvent} event The event to handle
   * @private
   */
  handleClick_(event) {
    event.preventDefault();
    this.handleFullScreen_();
  }

  /**
   * @private
   */
  handleFullScreen_() {
    if (!isFullScreenSupported()) {
      return;
    }
    const map = this.getMap();
    if (!map) {
      return;
    }
    if (isFullScreen()) {
      exitFullScreen();
    } else {
      let element;
      if (this.source_) {
        element = typeof this.source_ === 'string' ?
          document.getElementById(this.source_) :
          this.source_;
      } else {
        element = map.getTargetElement();
      }
      if (this.keys_) {
        requestFullScreenWithKeys(element);

      } else {
        requestFullScreen(element);
      }
    }
  }

  /**
   * @private
   */
  handleFullScreenChange_() {
    const map = this.getMap();
    if (isFullScreen()) {
      this.setClassName_(this.button_, true);
      replaceNode(this.labelActiveNode_, this.labelNode_);
    } else {
      this.setClassName_(this.button_, false);
      replaceNode(this.labelNode_, this.labelActiveNode_);
    }
    if (map) {
      map.updateSize();
    }
  }

  /**
   * @param {HTMLElement} element Target element
   * @param {boolean} fullscreen True if fullscreen class name should be active
   * @private
   */
  setClassName_(element, fullscreen) {
    const activeClassName = this.cssClassName_ + '-true';
    const inactiveClassName = this.cssClassName_ + '-false';
    const nextClassName = fullscreen ? activeClassName : inactiveClassName;
    element.classList.remove(activeClassName);
    element.classList.remove(inactiveClassName);
    element.classList.add(nextClassName);
  }

  /**
   * @inheritDoc
   * @api
   */
  setMap(map) {
    super.setMap(map);
    if (map) {
      for (let i = 0, ii = events.length; i < ii; ++i) {
        this.listenerKeys.push(
          listen(document, events[i], this.handleFullScreenChange_, this));
      }
    }
  }
}


/**
 * @return {boolean} Fullscreen is supported by the current platform.
 */
function isFullScreenSupported() {
  const body = document.body;
  return !!(
    body.webkitRequestFullscreen ||
    (body.msRequestFullscreen && document.msFullscreenEnabled) ||
    (body.requestFullscreen && document.fullscreenEnabled)
  );
}

/**
 * @return {boolean} Element is currently in fullscreen.
 */
function isFullScreen() {
  return !!(
    document.webkitIsFullScreen || document.msFullscreenElement || document.fullscreenElement
  );
}

/**
 * Request to fullscreen an element.
 * @param {HTMLElement} element Element to request fullscreen
 */
function requestFullScreen(element) {
  if (element.requestFullscreen) {
    element.requestFullscreen();
  } else if (element.msRequestFullscreen) {
    element.msRequestFullscreen();
  } else if (element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  }
}

/**
 * Request to fullscreen an element with keyboard input.
 * @param {HTMLElement} element Element to request fullscreen
 */
function requestFullScreenWithKeys(element) {
  if (element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else {
    requestFullScreen(element);
  }
}

/**
 * Exit fullscreen.
 */
function exitFullScreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  }
}

export default FullScreen;

<?php
/* SVN FILE: $Id: ajax.php 6311 2008-01-02 06:33:52Z phpnut $ */

/**
 * Helper for AJAX operations.
 *
 * Helps doing AJAX using the Prototype library.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs.view.helpers
 * @since			CakePHP(tm) v 0.10.0.1076
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * AjaxHelper helper library.
 *
 * Helps doing AJAX using the Prototype library.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.view.helpers
 */
class AjaxHelper extends AppHelper {
/**
 * Included helpers.
 *
 * @var array
 */
	var $helpers = array('Html', 'Javascript', 'Form');
/**
 * HtmlHelper instance
 *
 * @var object
 * @access public
 */
	var $Html = null;
/**
 * JavaScriptHelper instance
 *
 * @var object
 * @access public
 */
	var $Javascript = null;
/**
 * Names of Javascript callback functions.
 *
 * @var array
 */
	var $callbacks = array('complete', 'create', 'exception', 'failure', 'interactive', 'loading', 'loaded', 'success', 'uninitialized');
/**
 * Names of AJAX options.
 *
 * @var array
 */
	var $ajaxOptions = array('after', 'asynchronous', 'before', 'confirm', 'condition', 'contentType', 'encoding', 'evalScripts', 'failure', 'fallback', 'form', 'indicator', 'insertion', 'interactive', 'loaded', 'loading', 'method', 'onCreate', 'onComplete', 'onException', 'onFailure', 'onInteractive', 'onLoaded', 'onLoading', 'onSuccess', 'onUninitialized', 'parameters', 'position', 'postBody', 'requestHeaders', 'success', 'type', 'update', 'url', 'with');
/**
 * Options for draggable.
 *
 * @var array
 */
	var $dragOptions = array('handle', 'revert', 'snap', 'zindex', 'constraint', 'change', 'ghosting', 'starteffect', 'reverteffect', 'endeffect');
/**
 * Options for droppable.
 *
 * @var array
 */
	var $dropOptions = array('accept', 'containment', 'greedy', 'hoverclass', 'onHover', 'onDrop', 'overlap');
/**
 * Options for sortable.
 *
 * @var array
 */
	var $sortOptions = array('constraint', 'containment', 'dropOnEmpty', 'ghosting', 'handle', 'hoverclass', 'onUpdate', 'onChange', 'only', 'overlap', 'scroll', 'scrollSensitivity', 'scrollSpeed', 'tag', 'tree', 'treeTag', 'update');
/**
 * Options for slider.
 *
 * @var array
 */
	var $sliderOptions = array('alignX', 'alignY', 'axis', 'disabled', 'handleDisabled', 'handleImage', 'increment', 'maximum', 'minimum', 'onChange', 'onSlide', 'range', 'sliderValue', 'values');
/**
 * Options for in-place editor.
 *
 * @var array
 */
	var $editorOptions = array('okText', 'cancelText', 'savingText', 'formId', 'externalControl', 'rows', 'cols', 'size', 'highlightcolor', 'highlightendcolor', 'savingClassName', 'formClassName', 'loadTextURL', 'loadingText', 'callback', 'ajaxOptions', 'clickToEditText', 'collection');
/**
 * Options for auto-complete editor.
 *
 * @var array
 */
	var $autoCompleteOptions = array('afterUpdateElement', 'callback', 'frequency', 'indicator', 'minChars', 'onShow', 'onHide', 'parameters', 'paramName', 'tokens', 'updateElement');
/**
 * Output buffer for Ajax update content
 *
 * @var array
 */
	var $__ajaxBuffer = array();
/**
 * Returns link to remote action
 *
 * Returns a link to a remote action defined by <i>options[url]</i>
 * (using the url() format) that's called in the background using
 * XMLHttpRequest. The result of that request can then be inserted into a
 * DOM object whose id can be specified with <i>options[update]</i>.
 *
 * Examples:
 * <code>
 *  link("Delete this post",
 * array("update" => "posts", "url" => "delete/{$postid->id}"));
 *  link(imageTag("refresh"),
 *		array("update" => "emails", "url" => "list_emails" ));
 * </code>
 *
 * By default, these remote requests are processed asynchronous during
 * which various callbacks can be triggered (for progress indicators and
 * the likes).
 *
 * Example:
 * <code>
 *	link (word,
 *		array("url" => "undo", "n" => word_counter),
 *		array("complete" => "undoRequestCompleted(request)"));
 * </code>
 *
 * The callbacks that may be specified are:
 *
 * - <i>loading</i>::		Called when the remote document is being
 *							loaded with data by the browser.
 * - <i>loaded</i>::		Called when the browser has finished loading
 *							the remote document.
 * - <i>interactive</i>::	Called when the user can interact with the
 *							remote document, even though it has not
 *							finished loading.
 * - <i>complete</i>:: Called when the XMLHttpRequest is complete.
 *
 * If you for some reason or another need synchronous processing (that'll
 * block the browser while the request is happening), you can specify
 * <i>options[type] = synchronous</i>.
 *
 * You can customize further browser side call logic by passing
 * in Javascript code snippets via some optional parameters. In
 * their order of use these are:
 *
 * - <i>confirm</i>:: Adds confirmation dialog.
 * -<i>condition</i>::	Perform remote request conditionally
 *                      by this expression. Use this to
 *                      describe browser-side conditions when
 *                      request should not be initiated.
 * - <i>before</i>::		Called before request is initiated.
 * - <i>after</i>::		Called immediately after request was
 *						initiated and before <i>loading</i>.
 *
 * @param string $title Title of link
 * @param string $href Href string "/products/view/12"
 * @param array $options		Options for JavaScript function
 * @param string $confirm		Confirmation message. Calls up a JavaScript confirm() message.
 * @param boolean $escapeTitle  Escaping the title string to HTML entities
 *
 * @return string				HTML code for link to remote action
 */
	function link($title, $href = null, $options = array(), $confirm = null, $escapeTitle = true) {
		if (!isset($href)) {
			$href = $title;
		}

		if (!isset($options['url'])) {
			$options['url'] = $href;
		}

		if (isset($confirm)) {
			$options['confirm'] = $confirm;
			unset($confirm);
		}

		$htmlOptions = $this->__getHtmlOptions($options);

		if (empty($options['fallback']) || !isset($options['fallback'])) {
			$options['fallback'] = $href;
		}

		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = 'link' . intval(rand());
		}

		if (!isset($htmlOptions['onclick'])) {
			$htmlOptions['onclick'] = '';
		}

		$htmlOptions['onclick'] .= ' event.returnValue = false; return false;';
		$return = $this->Html->link($title, $href, $htmlOptions, null, $escapeTitle);
		$script = $this->Javascript->event("'{$htmlOptions['id']}'", "click", $this->remoteFunction($options));

		if (is_string($script)) {
			$return .= $script;
		}

		return $return;
	}
/**
 * Creates JavaScript function for remote AJAX call
 *
 * This function creates the javascript needed to make a remote call
 * it is primarily used as a helper for AjaxHelper::link.
 *
 * @see AjaxHelper::link() for docs on options parameter.
 * @param array $options options for javascript
 * @return string html code for link to remote action
 */
	function remoteFunction($options = null) {
		if (isset($options['update'])) {
			if (!is_array($options['update'])) {
				$func = "new Ajax.Updater('{$options['update']}',";
			} else {
				$func = "new Ajax.Updater(document.createElement('div'),";
			}
			if (!isset($options['requestHeaders'])) {
				$options['requestHeaders'] = array();
			}
			if (is_array($options['update'])) {
				$options['update'] = join(' ', $options['update']);
			}
			$options['requestHeaders']['X-Update'] = $options['update'];
		} else {
			$func = "new Ajax.Request(";
		}

		$func .= "'" . $this->url(isset($options['url']) ? $options['url'] : "") . "'";
		$func .= ", " . $this->__optionsForAjax($options) . ")";

		if (isset($options['before'])) {
			$func = "{$options['before']}; $func";
		}

		if (isset($options['after'])) {
			$func = "$func; {$options['after']};";
		}

		if (isset($options['condition'])) {
			$func = "if ({$options['condition']}) { $func; }";
		}

		if (isset($options['confirm'])) {
			$func = "if (confirm('" . $this->Javascript->escapeString($options['confirm'])
				. "')) { $func; } else { event.returnValue = false; return false; }";
		}
		return $func;
	}
/**
 * Periodically call remote url via AJAX.
 *
 * Periodically calls the specified url (<i>options[url]</i>) every <i>options[frequency]</i> seconds (default is 10).
 * Usually used to update a specified div (<i>options[update]</i>) with the results of the remote call.
 * The options for specifying the target with url and defining callbacks is the same as linkToRemote.
 *
 * @param array $options Callback options
 * @return string Javascript code
 */
	function remoteTimer($options = null) {
		$frequency = (isset($options['frequency'])) ? $options['frequency'] : 10;
		$code = "new PeriodicalExecuter(function() {" . $this->remoteFunction($options) . "}, $frequency)";
		return $this->Javascript->codeBlock($code);
	}
/**
 * Returns form tag that will submit using Ajax.
 *
 * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
 * reloading POST arrangement. Even though it's using Javascript to serialize the form elements, the form submission
 * will work just like a regular submission as viewed by the receiving side (all elements available in params).
 * The options for defining callbacks is the same as link().
 *
 * @param array $params Form target
 * @param array $type How form data is posted: 'get' or 'post'
 * @param array $options Callback/HTML options
 * @return string JavaScript/HTML code
 */
	function form($params = null, $type = 'post', $options = array()) {
		if (is_array($params)) {
			extract($params, EXTR_OVERWRITE);

			if (!isset($action)) {
				$action = null;
			}

			if (!isset($type)) {
				$type = 'post';
			}

			if (!isset($options)) {
				$options = array();
			}
		} else {
			$action = $params;
		}
		$htmlOptions = array_merge(
			array(
				'id'		=> 'form' . intval(rand()),
				'action'	=> $action,
				'onsubmit'	=> "event.returnValue = false; return false;",
				'type'		=> $type
			),
			$this->__getHtmlOptions($options)
		);
		$options = array_merge(
			array(
				'url' => $htmlOptions['action'],
				'model' => false,
				'with' => "Form.serialize('{$htmlOptions['id']}')"
			),
			$options
		);
		foreach (array_keys($options) as $key) {
			unset($htmlOptions[$key]);
		}

		return $this->Form->create($options['model'], $htmlOptions)
			. $this->Javascript->event("'" . $htmlOptions['id']. "'", 'submit', $this->remoteFunction($options));
	}
/**
 * Returns a button input tag that will submit using Ajax
 *
 * Returns a button input tag that will submit form using XMLHttpRequest in the background instead of regular
 * reloading POST arrangement. <i>options</i> argument is the same as in <i>form_remote_tag</i>
 *
 * @param string $title Input button title
 * @param array $options Callback options
 * @return string Ajaxed input button
 */
	function submit($title = 'Submit', $options = array()) {
		$htmlOptions = $this->__getHtmlOptions($options);
		$htmlOptions['value'] = $title;

		if (!isset($options['with'])) {
			$options['with'] = 'Form.serialize(Event.element(event).form)';
		}
		if (!isset($htmlOptions['id'])) {
			$htmlOptions['id'] = 'submit' . intval(rand());
		}

		$htmlOptions['onclick'] = "event.returnValue = false; return false;";
		return $this->Form->submit($title, $htmlOptions)
			. $this->Javascript->event('"' . $htmlOptions['id'] . '"', 'click', $this->remoteFunction($options));
	}
/**
 * Observe field and call ajax on change.
 *
 * Observes the field with the DOM ID specified by <i>field</i> and makes
 * an Ajax when its contents have changed.
 *
 * Required +options+ are:
 * - <i>frequency</i>:: The frequency (in seconds) at which changes to
 *						this field will be detected.
 * - <i>url</i>::		@see url() -style options for the action to call
 *						when the field has changed.
 *
 * Additional options are:
 * - <i>update</i>::	Specifies the DOM ID of the element whose
 *						innerHTML should be updated with the
 *						XMLHttpRequest response text.
 * - <i>with</i>:: A Javascript expression specifying the
 *						parameters for the XMLHttpRequest. This defaults
 *						to Form.Element.serialize('$field'), which can be
 *						accessed from params['form']['field_id'].
 *
 * Additionally, you may specify any of the options documented in
 * @see linkToRemote().
 *
 * @param string $field DOM ID of field to observe
 * @param array $options ajax options
 * @return string ajax script
 */
	function observeField($field, $options = array()) {
		if (!isset($options['with'])) {
			$options['with'] = 'Form.Element.serialize(\'' . $field . '\')';
		}
		$observer = 'Observer';
		if (!isset($options['frequency']) || intval($options['frequency']) == 0) {
			$observer = 'EventObserver';
		}
		return $this->Javascript->codeBlock($this->_buildObserver('Form.Element.' . $observer, $field, $options));
	}
/**
 * Observe entire form and call ajax on change.
 *
 * Like @see observeField(), but operates on an entire form identified by the
 * DOM ID <b>form</b>. <b>options</b> are the same as <b>observeField</b>, except
 * the default value of the <i>with</i> option evaluates to the
 * serialized (request string) value of the form.
 *
 * @param string $form DOM ID of form to observe
 * @param array $options ajax options
 * @return string ajax script
 */
	function observeForm($form, $options = array()) {
		if (!isset($options['with'])) {
			$options['with'] = 'Form.serialize(\'' . $form . '\')';
		}
		$observer = 'Observer';
		if (!isset($options['frequency']) || intval($options['frequency']) == 0) {
			$observer = 'EventObserver';
		}
		return $this->Javascript->codeBlock($this->_buildObserver('Form.' . $observer, $form, $options));
	}
/**
 * Create a text field with Autocomplete.
 *
 * Creates an autocomplete field with the given ID and options.
 *
 * options['with'] defaults to "Form.Element.serialize('$field')",
 * but can be any valid javascript expression defining the
 *
 * @param string $field DOM ID of field to observe
 * @param string $url URL for the autocomplete action
 * @param array $options Ajax options
 * @return string Ajax script
 */
	function autoComplete($field, $url = "", $options = array()) {
		$var = '';
		if (isset($options['var'])) {
			$var = 'var ' . $options['var'] . ' = ';
			unset($options['var']);
		}

		if (!isset($options['id'])) {
			$options['id'] = Inflector::camelize(str_replace(".", "_", $field));
		}

		$divOptions = array('id' => $options['id'] . "_autoComplete", 'class' => isset($options['class']) ? $options['class'] : 'auto_complete');
		if (isset($options['div_id'])) {
			$divOptions['id'] = $options['div_id'];
			unset($options['div_id']);
		}

		$htmlOptions = $this->__getHtmlOptions($options);
		$htmlOptions['autocomplete'] = "off";

		foreach ($this->autoCompleteOptions as $opt) {
			unset($htmlOptions[$opt]);
		}

		if (isset($options['tokens'])) {
			if (is_array($options['tokens'])) {
				$options['tokens'] = $this->Javascript->object($options['tokens']);
			} else {
				$options['tokens'] = '"' . $options['tokens'] . '"';
			}
		}

		$options = $this->_optionsToString($options, array('paramName', 'indicator'));
		$options = $this->_buildOptions($options, $this->autoCompleteOptions);

		return $this->Form->text($field, $htmlOptions) . "\n" .
				$this->Html->div(null, '', $divOptions) . "\n" .
				$this->Javascript->codeBlock("{$var}new Ajax.Autocompleter('" . $htmlOptions['id']
					. "', '" . $divOptions['id'] . "', '" . $this->Html->url($url) . "', " .
						$options . ");");
	}
/**
 * Creates an Ajax-updateable DIV element
 *
 * @param string $id options for javascript
 * @return string HTML code
 */
	function div($id, $options = array()) {
		if (env('HTTP_X_UPDATE') != null) {
			$this->Javascript->enabled = false;
			$divs = explode(' ', env('HTTP_X_UPDATE'));

			if (in_array($id, $divs)) {
				@ob_end_clean();
				ob_start();
				return '';
			}
		}
		$attr = $this->_parseAttributes(array_merge($options, array('id' => $id)));
		return $this->output(sprintf($this->Html->tags['blockstart'], $attr));
	}
/**
 * Closes an Ajax-updateable DIV element
 *
 * @param string $id The DOM ID of the element
 * @return string HTML code
 */
	function divEnd($id) {
		if (env('HTTP_X_UPDATE') != null) {
			$divs = explode(' ', env('HTTP_X_UPDATE'));
			if (in_array($id, $divs)) {
				$this->__ajaxBuffer[$id] = ob_get_contents();
				ob_end_clean();
				ob_start();
				return '';
			}
		}
		return $this->output($this->Html->tags['blockend']);
	}
/**
 * Detects Ajax requests
 *
 * @return boolean True if the current request is a Prototype Ajax update call
 */
	function isAjax() {
		return (isset($this->params['isAjax']) && $this->params['isAjax'] === true);
	}
/**
 * Creates a draggable element.  For a reference on the options for this function,
 * check out http://wiki.script.aculo.us/scriptaculous/show/Draggable
 *
 * @param unknown_type $id
 * @param array $options
 * @return unknown
 */
	function drag($id, $options = array()) {
		$var = '';
		if (isset($options['var'])) {
			$var = 'var ' . $options['var'] . ' = ';
			unset($options['var']);
		}
		$options = $this->_buildOptions($this->_optionsToString($options, array('handle', 'constraint')), $this->dragOptions);
		return $this->Javascript->codeBlock("{$var}new Draggable('$id', " .$options . ");");
	}
/**
 * For a reference on the options for this function, check out
 * http://wiki.script.aculo.us/scriptaculous/show/Droppables.add
 *
 * @param unknown_type $id
 * @param array $options
 * @return string
 */
	function drop($id, $options = array()) {
		$optionsString = array('overlap', 'hoverclass');
		if (!isset($options['accept']) || !is_array($options['accept'])) {
			$optionsString[] = 'accept';
		} else if (isset($options['accept'])) {
			$options['accept'] = $this->Javascript->object($options['accept']);
		}
		$options = $this->_buildOptions($this->_optionsToString($options, $optionsString), $this->dropOptions);
		return $this->Javascript->codeBlock("Droppables.add('{$id}', {$options});");
	}
/**
 * Make an element with the given $id droppable, and trigger an Ajax call when a draggable is
 * dropped on it.
 *
 * For a reference on the options for this function, check out
 * http://wiki.script.aculo.us/scriptaculous/show/Droppables.add
 *
 * @param string $id
 * @param array $options
 * @param array $ajaxOptions
 * @return string JavaScript block to create a droppable element
 */
	function dropRemote($id, $options = array(), $ajaxOptions = array()) {
		$options['onDrop'] = "function(element, droppable, event) {" . $this->remoteFunction($ajaxOptions) . "}";
		$optionsString = array('overlap', 'hoverclass');
		if (!isset($options['accept']) || !is_array($options['accept'])) {
			$optionsString[] = 'accept';
		} else if (isset($options['accept'])) {
			$options['accept'] = $this->Javascript->object($options['accept']);
		}
		$options = $this->_buildOptions($this->_optionsToString($options, $optionsString), $this->dropOptions);
		return $this->Javascript->codeBlock("Droppables.add('{$id}', {$options});");
	}
/**
 * Makes a slider control.
 *
 * @param string $id DOM ID of slider handle
 * @param string $trackId DOM ID of slider track
 * @param array $options Array of options to control the slider
 * @link http://wiki.script.aculo.us/scriptaculous/show/Slider
 */
	function slider($id, $trackId, $options = array()) {
		if (isset($options['var'])) {
			$var = 'var ' . $options['var'] . ' = ';
			unset($options['var']);
		} else {
			$var = 'var ' . $id . ' = ';
		}

		$options = $this->_optionsToString($options, array('axis', 'handleImage', 'handleDisabled'));
		$callbacks = array('change', 'slide');

		foreach($callbacks as $callback) {
			if (isset($options[$callback])) {
				$options['on' . ucfirst($callback)] = 'function(value) {' . $options[$callback] . '}';
				unset($options[$callback]);
			}
		}

		if (isset($options['values']) && is_array($options['values'])) {
			$options['values'] = $this->Javascript->object($options['values']);
		}

		$options = $this->_buildOptions($options, $this->sliderOptions);
		return $this->Javascript->codeBlock("{$var}new Control.Slider('$id', '$trackId', $options);");
	}
/**
 * Makes an Ajax In Place editor control.
 *
 * @param string $id DOM ID of input element
 * @param string $url Postback URL of saved data
 * @param array $options Array of options to control the editor, including ajaxOptions (see link).
 * @link http://wiki.script.aculo.us/scriptaculous/show/Ajax.InPlaceEditor
 */
	function editor($id, $url, $options = array()) {
		$url = $this->url($url);
		$options['ajaxOptions'] = $this->__optionsForAjax($options);

		foreach ($this->ajaxOptions as $opt) {
			if (isset($options[$opt])) {
				unset($options[$opt]);
			}
		}

		if (isset($options['callback'])) {
			$options['callback'] = 'function(form, value) {' . $options['callback'] . '}';
		}

		$type = 'InPlaceEditor';
		if (isset($options['collection']) && is_array($options['collection'])) {
			$options['collection'] = $this->Javascript->object($options['collection']);
			$type = 'InPlaceCollectionEditor';
		}

		$var = '';
		if (isset($options['var'])) {
			$var = 'var ' . $options['var'] . ' = ';
			unset($options['var']);
		}

		$options = $this->_optionsToString($options, array('okText', 'cancelText', 'savingText', 'formId', 'externalControl', 'highlightcolor', 'highlightendcolor', 'savingClassName', 'formClassName', 'loadTextURL', 'loadingText', 'clickToEditText'));
		$options = $this->_buildOptions($options, $this->editorOptions);
		return $this->Javascript->codeBlock("{$var}new Ajax.{$type}('{$id}', '{$url}', {$options});");
	}
/**
 * Makes a list or group of floated objects sortable.
 *
 * @param string $id DOM ID of parent
 * @param array $options Array of options to control sort.http://wiki.script.aculo.us/scriptaculous/show/Sortable.create
 * @link http://wiki.script.aculo.us/scriptaculous/show/Sortable.create
 */
	function sortable($id, $options = array()) {
		if (!empty($options['url'])) {
			if (empty($options['with'])) {
				$options['with'] = "Sortable.serialize('$id')";
			}
			$options['onUpdate'] = 'function(sortable) {' . $this->remoteFunction($options) . '}';
		}

		$options = $this->_optionsToString($options, array('tag', 'constraint', 'only', 'handle', 'hoverclass', 'scroll', 'tree', 'treeTag', 'update'));
		$options = array_merge($options, $this->_buildCallbacks($options));
		$options = $this->_buildOptions($options, $this->sortOptions);
		return $this->Javascript->codeBlock("Sortable.create('$id', $options);");
	}
/**
 * Private helper function for Javascript.
 *
 */
	function __optionsForAjax($options = array()) {
		if (isset($options['indicator'])) {
			if (isset($options['loading'])) {
				if (!empty($options['loading']) && substr(trim($options['loading']), -1, 1) != ';') {
					$options['loading'] .= '; ';
				}
				$options['loading'] .= "Element.show('{$options['indicator']}');";
			} else {
				$options['loading'] = "Element.show('{$options['indicator']}');";
			}
			if (isset($options['complete'])) {
				if (!empty($options['complete']) && substr(trim($options['complete']), -1, 1) != ';') {
					$options['complete'] .= '; ';
				}
				$options['complete'] .= "Element.hide('{$options['indicator']}');";
			} else {
				$options['complete'] = "Element.hide('{$options['indicator']}');";
			}
			unset($options['indicator']);
		}

		$jsOptions = array_merge(
			array('asynchronous' => 'true', 'evalScripts'  => 'true'),
			$this->_buildCallbacks($options)
		);
		$options = $this->_optionsToString($options, array('contentType', 'encoding', 'fallback', 'method', 'postBody', 'update', 'url'));
		$jsOptions = array_merge($jsOptions, array_intersect_key($options, array_flip(array('contentType', 'encoding', 'method', 'postBody'))));

		foreach ($options as $key => $value) {
			switch($key) {
				case 'type':
					$jsOptions['asynchronous'] = ife(($value == 'synchronous'), 'false', 'true');
				break;
				case 'evalScripts':
					$jsOptions['evalScripts'] = ife($value, 'true', 'false');
				break;
				case 'position':
					$jsOptions['insertion'] = "Insertion." . Inflector::camelize($options['position']);
				break;
				case 'with':
					$jsOptions['parameters'] = $options['with'];
				break;
				case 'form':
					$jsOptions['parameters'] = 'Form.serialize(this)';
				break;
				case 'requestHeaders':
					$keys = array();
					foreach ($value as $key => $val) {
						$keys[] = "'" . $key . "'";
						$keys[] = "'" . $val . "'";
					}
					$jsOptions['requestHeaders'] = '[' . join(', ', $keys) . ']';
				break;
			}
		}
		return $this->_buildOptions($jsOptions, $this->ajaxOptions);
	}
/**
 * Private Method to return a string of html options
 * option data as a JavaScript options hash.
 *
 * @param array $options	Options in the shape of keys and values
 * @param array $extra	Array of legal keys in this options context
 * @return array Array of html options
 * @access private
 */
	function __getHtmlOptions($options, $extra = array()) {
		foreach ($this->ajaxOptions as $key) {
			if (isset($options[$key])) {
				unset($options[$key]);
			}
		}

		foreach ($extra as $key) {
			if (isset($options[$key])) {
				unset($options[$key]);
			}
		}

		return $options;
	}
/**
 * Returns a string of JavaScript with the given option data as a JavaScript options hash.
 *
 * @param array $options	Options in the shape of keys and values
 * @param array $acceptable	Array of legal keys in this options context
 * @return string	String of Javascript array definition
 */
	function _buildOptions($options, $acceptable) {
		if (is_array($options)) {
			$out = array();

			foreach ($options as $k => $v) {
				if (in_array($k, $acceptable)) {
					if ($v === true) {
						$v = 'true';
					} elseif ($v === false) {
						$v = 'false';
					}
					$out[] = "$k:$v";
				}
			}

			$out = join(', ', $out);
			$out = '{' . $out . '}';
			return $out;
		} else {
			return false;
		}
	}
/**
 * Return JavaScript text for an observer...
 *
 * @param string $klass Name of JavaScript class
 * @param string $name
 * @param array $options	Ajax options
 * @return string Formatted JavaScript
 */
	function _buildObserver($klass, $name, $options = null) {
		if (!isset($options['with']) && isset($options['update'])) {
			$options['with'] = 'value';
		}

		$callback = $this->remoteFunction($options);
		$javascript  = "new $klass('$name', ";
		$javascript .= (!isset($options['frequency']) || intval($options['frequency']) == 0 ? '' : $options['frequency'] . ', ' ) . "function(element, value) {";
		$javascript .= "$callback})";
		return $javascript;
	}
/**
 * Return Javascript text for callbacks.
 *
 * @param array $options Option array where a callback is specified
 * @return array Options with their callbacks properly set
 * @access protected
 */
	function _buildCallbacks($options) {
		$callbacks = array();

		foreach ($this->callbacks as $callback) {
			if (isset($options[$callback])) {
				$name = 'on' . ucfirst($callback);
				$code = $options[$callback];
				switch($name) {
					case 'onComplete':
						$callbacks[$name] = "function(request, json) {" . $code . "}";
						break;
					case 'onCreate':
						$callbacks[$name] = "function(request, xhr) {" . $code . "}";
						break;
					case 'onException':
						$callbacks[$name] = "function(request, exception) {" . $code . "}";
						break;
					default:
						$callbacks[$name] = "function(request) {" . $code . "}";
						break;
				}
				if (isset($options['bind'])) {
					if ((is_array($options['bind']) && in_array($callback, $options['bind'])) || (is_string($options['bind']) && strpos($options['bind'], $callback) !== false)) {
						$callbacks[$name] .= ".bind(this)";
					}
				}
			}
		}
		return $callbacks;
	}
/**
 * Returns a string of JavaScript with a string representation of given options array.
 *
 * @param array $options	Ajax options array
 * @param array $stringOpts	Options as strings in an array
 * @access private
 * @return array
 */
	function _optionsToString($options, $stringOpts = array()) {
		foreach ($stringOpts as $option) {
			if (isset($options[$option]) && !empty($options[$option]) && is_string($options[$option]) && $options[$option][0] != "'") {
				if ($options[$option] === true || $options[$option] === 'true') {
					$options[$option] = 'true';
				} elseif ($options[$option] === false || $options[$option] === 'false') {
					$options[$option] = 'false';
				} else {
					$options[$option] = "'{$options[$option]}'";
				}
			}
		}
		return $options;
	}
/**
 * Executed after a view has rendered, used to include bufferred code
 * blocks.
 *
 * @access public
 */
	function afterRender() {
		if (env('HTTP_X_UPDATE') != null && !empty($this->__ajaxBuffer)) {
			@ob_end_clean();

			$data = array();
			$divs = explode(' ', env('HTTP_X_UPDATE'));
			$keys = array_keys($this->__ajaxBuffer);

			if (count($divs) == 1 && in_array($divs[0], $keys)) {
				e($this->__ajaxBuffer[$divs[0]]);
			} else {
				foreach ($this->__ajaxBuffer as $key => $val) {
					if (in_array($key, $divs)) {
						$data[] = $key . ':"' . rawurlencode($val) . '"';
					}
				}
				$out  = 'var __ajaxUpdater__ = {' . join(", \n", $data) . '};' . "\n";
				$out .= 'for (n in __ajaxUpdater__) { if (typeof __ajaxUpdater__[n] == "string" && $(n)) Element.update($(n), unescape(decodeURIComponent(__ajaxUpdater__[n]))); }';

				e($this->Javascript->codeBlock($out, false));
			}
			$scripts = $this->Javascript->getCache();

			if (!empty($scripts)) {
				e($this->Javascript->codeBlock($scripts, false));
			}
			exit();
		}
	}
}

?>
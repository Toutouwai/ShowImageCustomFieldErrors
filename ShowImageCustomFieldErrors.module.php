<?php namespace ProcessWire;

class ShowImageCustomFieldErrors extends WireData implements Module {

	/**
	 * Ready
	 */
	public function ready() {
		$this->addHookAfter('InputfieldImage::processItemInputfields', $this, 'afterProcessItemInputfields');
		$this->addHookAfter('InputfieldImage::processInput', $this, 'afterProcessInput');
	}

	/**
	 * After InputfieldImage::processItemInputfields
	 *
	 * @param HookEvent $event
	 */
	protected function afterProcessItemInputfields(HookEvent $event) {
		/** @var InputfieldImage $inputfield */
		$inputfield = $event->object;
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $event->arguments(1);
		$field = $inputfield->hasField;
		$page = $inputfield->hasPage;

		// Return early if there is no corresponding field or page
		if(!$field || !$page) return;

		// Check if any custom fields have errors
		$errors = false;
		foreach($wrapper->getAll() as $f) {
			if($f->getErrors()) {
				$errors = true;
				break;
			}
		}

		// If there are errors then set a custom property on the inputfield
		if($errors) $inputfield->customFieldErrors = true;
	}

	/**
	 * After InputfieldImage::processInput
	 *
	 * @param HookEvent $event
	 */
	protected function afterProcessInput(HookEvent $event) {
		/** @var InputfieldImage $inputfield */
		$inputfield = $event->object;
		$field = $inputfield->hasField;
		$page = $inputfield->hasPage;
		$session = $this->wire()->session;

		// Return early if there is no corresponding field or page
		if(!$field || !$page) return;

		$name = $inputfield->name;
		$key = "$name|$page->id";

		// Get cookie data
		$cookieData = $this->getCookieData();

		// Work out what to save as the restore grid mode
		// Use the grid mode from the cookie so long as it wasn't forced to "list" by this module
		// Otherwise use the grid mode from the session, falling back to the grid mode from the field settings
		if(isset($cookieData[$name]['mode']) && empty($cookieData[$name]['ShowImageCustomFieldErrors'])) {
			$restoreGridMode = $cookieData[$name]['mode'];
		} else {
			$restoreGridMode = $session->getFor($this, $key) ?? $field->gridMode;
		}

		// Choose settings based on whether there are errors
		if($inputfield->customFieldErrors) {
			// Force mode to "list" so error fields are visible
			$cookieData[$name]['mode'] = 'list';
			// Set an item in the cookie to flag that the grid mode was forced to "list"
			$cookieData[$name]['ShowImageCustomFieldErrors'] = true;
		} else {
			// Apply the restore grid mode
			$cookieData[$name]['mode'] = $restoreGridMode;
			// Clear the item that flags the grid mode as forced to "list"
			unset($cookieData[$name]['ShowImageCustomFieldErrors']);
		}

		// Set the cookie data
		$this->setCookieData($cookieData);

		// Save the restore grid mode to the session
		$session->setFor($this, $key, $restoreGridMode);
	}

	/**
	 * Get data from the InputfieldImage cookie
	 *
	 * @return array
	 */
	protected function getCookieData() {
		$data = [];
		$cookieContents = $this->wire()->input->cookie->get('InputfieldImage');
		// jQuery Cookie plugin adds 'JSON' prefix when saving object data
		if($cookieContents && substr($cookieContents, 0, 4) === 'JSON') {
			$data = wireDecodeJSON(substr($cookieContents, 4));
		}
		return $data;
	}

	/**
	 * Set data to the InputfieldImage cookie
	 *
	 * @param array
	 */
	protected function setCookieData($data) {
		$cookieContents = wireEncodeJSON($data);
		$options = [
			'path' => $this->wire()->config->urls->admin . 'page/edit',
			'age' => 0,
		];
		// Add JSON prefix for compatibility with jQuery Cookie plugin
		$this->wire()->input->cookie->set('InputfieldImage', 'JSON' . $cookieContents, $options);
	}

}

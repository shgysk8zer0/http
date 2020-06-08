<?php

namespace shgysk8zer0\HTTP\Interfaces;

use \Serializable;

interface BodyInterface extends Serializable
{
	public function text():? string;

	/**
	 * Returns the JSON decoded body
	 * @return object or array
	 */
	public function json();

	public function formData():? FormDataInterface;
}

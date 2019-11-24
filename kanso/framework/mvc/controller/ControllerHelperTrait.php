<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\mvc\controller;

use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Controller helper methods.
 *
 * @author Joe J. Howard
 */
trait ControllerHelperTrait
{
	use ContainerAwareTrait;

	/**
	 *  Loads the next middleware layer.
	 *
	 * @return mixed
	 */
	protected function nextMiddleware()
	{
		$nextMiddleware = $this->nextMiddleware;

		return $nextMiddleware();
	}

	/**
	 * Sets a file content response.
	 *
	 * @param string $file     Absolute path to file
	 * @param array  $data     Data to send to view (optional) (default [])
	 * @param string $format   Format to send the response in (optional) (default 'text/html')
	 * @param string $encoding Encoding for response (optional) (default 'utf-8')
	 */
	protected function fileResponse(string $file, array $data = [], string $format = 'text/html', string $encoding = 'utf-8'): void
	{
		$this->Response->format()->set($format);

		$this->Response->format()->setEncoding($encoding);

		$this->Response->body()->set($this->View->display($file, $data));
	}

	/**
	 *  Sets a JSON content response.
	 *
	 * @param array $data Data to send as json
	 */
	protected function jsonResponse(array $data): void
	{
		$this->Response->format()->set('json');

		$this->Response->body()->set(json_encode($data));
	}

	/**
	 * Sends a temporary redirect response immediately.
	 *
	 * @param string $location Relative location to redirect to
	 */
	protected function redirectResponse(string $location): void
	{
		$this->Response->redirect($this->Request->environment()->HTTP_HOST . '/' . ltrim($location, '/'));
	}

	/**
	 * Sends a 404 response immediately.
	 */
	protected function notFoundResponse(): void
	{
		$this->Response->notFound();
	}
}

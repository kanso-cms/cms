<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\email;

use kanso\cms\email\utility\Log;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class LogTest extends TestCase
{
	/**
	 *
	 */
	public function testSave(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$logDir     = '/foo/bar';
		$log        = new Log($filesystem, $logDir);
		$content    = 'htmlemailcontent';
		$info       =
		[
			'to_email'   => 'foo@bar.com',
			'from_name'  => 'foo bar',
			'from_email' => 'foo@bar.com',
			'subject'    => 'foobar',
			'format'     => 'html',
		];

		$filesystem->shouldReceive('putContents')->andReturn(true);

		$filesystem->shouldReceive('putContents')->andReturn(true);

		$this->assertTrue(is_string($log->save($info['to_email'], $info['from_name'], $info['from_email'], $info['subject'], $content, $info['format'])));
	}
}

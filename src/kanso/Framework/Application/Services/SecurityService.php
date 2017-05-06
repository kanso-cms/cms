<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\Security\Key;
use Kanso\Framework\Security\Crypto;
use Kanso\Framework\Security\Password\Encrypters\NativePHP;
use Kanso\Framework\Security\Crypto\Signer;
use Kanso\Framework\Security\SPAM\Gibberish\Gibberish;
use Kanso\Framework\Security\SPAM\SpamProtector;

/**
 * Security service
 *
 * @author Joe J. Howard
 */
class SecurityService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Crypto', function ($container) 
		{
			return new Crypto($this->getSinger(), $this->getEncrypter(), $this->getPassword());
		});

		$this->container->singleton('SpamProtector', function ($container) 
		{
			return new SpamProtector($this->getGibberish(), $container->Config);
		});
	}

	/**
	 * Returns the encryption signer
	 *
	 * @access private
	 * @return \Kanso\Framework\Security\Crypto\Signer
	 */	
	private function getSinger(): Signer
	{
		return new Signer($this->container->Config->get('application.secret'));
	}

	/**
	 * Returns the encryption library
	 *
	 * @access private
	 * @return mixed
	 */
	protected function getEncrypter()
	{
		$configuration = $this->container->Config->get('crypto.configurations.'.$this->container->Config->get('crypto.default'));
		
		$library = $configuration['library'];

		return new $library(Key::decode($configuration['key']), $configuration['cipher']);
	}

	/**
	 * Returns the password hashing library
	 *
	 * @access private
	 * @return mixed
	 */
	protected function getPassword()
	{
		$passwordConfiguration = $this->container->Config->get('password.configurations.'.$this->container->Config->get('password.default'));
		
		$library = $passwordConfiguration['library'];

		if ($library === 'nativePHP')
		{
			return $this->nativePasswordHasher($passwordConfiguration);
		}
	}

	/**
	 * Returns the the native PHP password hasher
	 *
	 * @access private
	 * @param  array   $passwordConfiguration Configuration to pass to constructor
	 * @return \Kanso\Framework\Security\Password\Encrypters\NativePHP
	 */
	protected function nativePasswordHasher(array $passwordConfiguration): NativePHP
	{
		return new NativePHP($passwordConfiguration['algo']);
	}

	/**
	 * Returns the gibberish detector
	 *
	 * @access private
	 * @return \Kanso\Framework\Security\SPAM\Gibberish\Gibberish
	 */
	protected function getGibberish(): Gibberish
	{
		return new Gibberish($this->container->Config->get('spam.gibberish_lib'));
	}
}

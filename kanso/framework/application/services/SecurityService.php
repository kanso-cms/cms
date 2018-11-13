<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\security\Crypto;
use kanso\framework\security\crypto\encrypters\OpenSSL;
use kanso\framework\security\crypto\Key;
use kanso\framework\security\crypto\Signer;
use kanso\framework\security\password\encrypters\NativePHP;
use kanso\framework\security\spam\gibberish\Gibberish;
use kanso\framework\security\spam\SpamProtector;
use kanso\framework\validator\ValidatorFactory;

/**
 * Security service.
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
		$this->container->singleton('Crypto', function($container)
		{
			return new Crypto($this->getSinger(), $this->getEncrypter(), $this->getPassword());
		});

		$this->container->singleton('SpamProtector', function($container)
		{
			return new SpamProtector($this->getGibberish(), $container->Config);
		});

		$this->container->singleton('Validator', function($container)
		{
			return new ValidatorFactory($container);
		});
	}

	/**
	 * Returns the encryption signer.
	 *
	 * @access private
	 * @return \kanso\framework\security\crypto\Signer
	 */
	private function getSinger(): Signer
	{
		return new Signer($this->container->Config->get('application.secret'));
	}

	/**
	 * Returns the encryption library.
	 *
	 * @access private
	 * @return mixed
	 */
	protected function getEncrypter()
	{
		$configuration = $this->container->Config->get('crypto.configurations.' . $this->container->Config->get('crypto.default'));

		$library = $configuration['library'];

		if ($library === 'openssl')
		{
			return $this->openSSLEncrypter($configuration);
		}
	}

	/**
	 * Returns the the Open SSL Encrypter/Decrypter implementation.
	 *
	 * @access private
	 * @param  array                                               $configuration Encryption configuration
	 * @return \kanso\framework\security\crypto\encrypters\OpenSSL
	 */
	protected function openSSLEncrypter(array $configuration): OpenSSL
	{
		return new OpenSSL(Key::decode($configuration['key']), $configuration['cipher']);
	}

	/**
	 * Returns the password hashing library.
	 *
	 * @access private
	 * @return mixed
	 */
	protected function getPassword()
	{
		$passwordConfiguration = $this->container->Config->get('password.configurations.' . $this->container->Config->get('password.default'));

		$library = $passwordConfiguration['library'];

		if ($library === 'nativePHP')
		{
			return $this->nativePasswordHasher($passwordConfiguration);
		}
	}

	/**
	 * Returns the the native PHP password hasher.
	 *
	 * @access private
	 * @param  array                                                   $passwordConfiguration Configuration to pass to constructor
	 * @return \kanso\framework\security\password\encrypters\NativePHP
	 */
	protected function nativePasswordHasher(array $passwordConfiguration): NativePHP
	{
		return new NativePHP($passwordConfiguration['algo']);
	}

	/**
	 * Returns the gibberish detector.
	 *
	 * @access private
	 * @return \kanso\framework\security\spam\gibberish\Gibberish
	 */
	protected function getGibberish(): Gibberish
	{
		return new Gibberish($this->container->Config->get('spam.gibberish_lib'));
	}
}

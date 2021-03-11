<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link	  https://cakephp.org CakePHP(tm) Project
 * @since	 3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\Exception\MissingPluginException;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

// https://book.cakephp.org/4/ja/tutorials-and-examples/cms/authentication.html
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
	/**
	 * Load all the application configuration and bootstrap logic.
	 *
	 * @return void
	 */
	public function bootstrap(): void
	{
		// Call parent to load bootstrap from files.
		parent::bootstrap();

		if (PHP_SAPI === 'cli') {
			$this->bootstrapCli();
		} else {
			FactoryLocator::add(
				'Table',
				(new TableLocator())->allowFallbackClass(false)
			);
		}

		/*
		 * Only try to load DebugKit in development mode
		 * Debug Kit should not be installed on a production system
		 */
		if (Configure::read('debug')) {
			$this->addPlugin('DebugKit');
		}

		// Load more plugins here
	}

	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
	{
		$middlewareQueue
			->add(new ErrorHandlerMiddleware(Configure::read('Error')))
			->add(new AssetMiddleware([
				'cacheTime' => Configure::read('Asset.cacheTime'),
			]))
			->add(new RoutingMiddleware($this))
			->add(new BodyParserMiddleware())
			->add(new CsrfProtectionMiddleware([
				'httponly' => true,
			]))
			->add(new AuthenticationMiddleware($this));

		return $middlewareQueue;
	}

	/**
	 * Register application container services.
	 *
	 * @param \Cake\Core\ContainerInterface $container The Container to update.
	 * @return void
	 * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
	 */
	public function services(ContainerInterface $container): void
	{
	}

	/**
	 * Bootstrapping for CLI application.
	 *
	 * That is when running commands.
	 *
	 * @return void
	 */
	protected function bootstrapCli(): void
	{
		try {
			$this->addPlugin('Bake');
		} catch (MissingPluginException $e) {
			// Do not halt if the plugin is missing
		}

		$this->addPlugin('Migrations');

		// Load more plugins here
	}


	public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
	{
		$authenticationService = new AuthenticationService([
			'unauthenticatedRedirect' => '/users/login',
			'queryParam' => 'redirect',
		]);

		// identifiers を読み込み、email と password のフィールドを確認します
		$authenticationService->loadIdentifier('Authentication.Password', [
			'fields' => [
				'username' => 'email',
				'password' => 'password',
			]
		]);

		//  authenticatorsをロードしたら, 最初にセッションが必要です
		$authenticationService->loadAuthenticator('Authentication.Session');
		// 入力した email と password をチェックする為のフォームデータを設定します
		$authenticationService->loadAuthenticator('Authentication.Form', [
			'fields' => [
				'username' => 'email',
				'password' => 'password',
			],
			'loginUrl' => '/users/login',
		]);

		return $authenticationService;
	}
}

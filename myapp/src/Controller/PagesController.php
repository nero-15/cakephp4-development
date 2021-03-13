<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

class PagesController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();
		$this->loadComponent('Math');
		$this->loadComponent('Paginator');
	}

	public function display(string ...$path): ?Response
	{
		if (!$path) {
			return $this->redirect('/');
		}
		if (in_array('..', $path, true) || in_array('.', $path, true)) {
			throw new ForbiddenException();
		}
		$page = $subpage = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		$this->set(compact('page', 'subpage'));

		try {
			return $this->render(implode('/', $path));
		} catch (MissingTemplateException $exception) {
			if (Configure::read('debug')) {
				throw $exception;
			}
			throw new NotFoundException();
		}
	}

	public function workspace()
	{
		$this->loadModel('Articles');
		$Articles = $this->Articles->find('all', [
			'order' => 'Articles.id DESC'
		]);

		$this->set([
			'h1' => 'ワークスペース',
			'discription' => '内容です。',
			'Articles' => $this->paginate($Articles),
			'doComplexOperation' => $this->Math->doComplexOperation(10, 20)
		]);

		$this->Flash->greatSuccess('This was greatly successful');
	}
}

<?php
declare(strict_types=1);

namespace App\Controller;

class UsersController extends AppController
{

	public function beforeFilter(\Cake\Event\EventInterface $event)
	{
		parent::beforeFilter($event);
		$this->Authentication->addUnauthenticatedActions(['login']);
	}

	public function login()
	{
		$this->request->allowMethod(['get', 'post']);
		$result = $this->Authentication->getResult();
		// POST, GET を問わず、ユーザーがログインしている場合はリダイレクトします
		if ($result->isValid()) {
			// redirect to /articles after login success
			$redirect = $this->request->getQuery('redirect', [
				'controller' => 'Articles',
				'action' => 'index',
			]);

			return $this->redirect($redirect);
		}
		// ユーザーが submit 後、認証失敗した場合は、エラーを表示します
		if ($this->request->is('post') && !$result->isValid()) {
			$this->Flash->error('Invalid username or password');
		}
	}

	public function index()
	{
		$users = $this->paginate($this->Users);

		$this->set(compact('users'));
	}

	public function view($id = null)
	{
		$user = $this->Users->get($id, [
			'contain' => ['Articles'],
		]);

		$this->set(compact('user'));
	}

	public function add()
	{
		$user = $this->Users->newEmptyEntity();
		if ($this->request->is('post')) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(__('The user has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The user could not be saved. Please, try again.'));
		}
		$this->set(compact('user'));
	}

	public function edit($id = null)
	{
		$user = $this->Users->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->getData());
			if ($this->Users->save($user)) {
				$this->Flash->success(__('The user has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The user could not be saved. Please, try again.'));
		}
		$this->set(compact('user'));
	}

	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$user = $this->Users->get($id);
		if ($this->Users->delete($user)) {
			$this->Flash->success(__('The user has been deleted.'));
		} else {
			$this->Flash->error(__('The user could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
}

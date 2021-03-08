<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use Cake\ORM\Query;

class ArticlesTable extends Table
{

	public function initialize(array $config) : void
	{
		$this->addBehavior('Timestamp');
		$this->belongsToMany('Tags');
	}

	public function beforeSave(EventInterface $event, $entity, $options)
	{
		if ($entity->isNew() && !$entity->slug) {
			$sluggedTitle = Text::slug($entity->title);
			// スラグをスキーマで定義されている最大長に調整
			$entity->slug = substr($sluggedTitle, 0, 191);
		}
	}

	public function validationDefault(Validator $validator): Validator
	{
		$validator
			->notEmptyString('title')
			->minLength('title', 10)
			->maxLength('title', 255)

			->notEmptyString('body')
			->minLength('body', 10);

		return $validator;
	}

	public function findTagged(Query $query, array $options)
	{
		$columns = [
			'Articles.id', 'Articles.user_id', 'Articles.title',
			'Articles.body', 'Articles.published', 'Articles.created',
			'Articles.slug',
		];

		$query = $query
			->select($columns)
			->distinct($columns);

		if (empty($options['tags'])) {
			// タグが指定されていない場合は、タグのない記事を検索します。
			$query->leftJoinWith('Tags')
				->where(['Tags.title IS' => null]);
		} else {
			// 提供されたタグが1つ以上ある記事を検索します。
			$query->innerJoinWith('Tags')
				->where(['Tags.title IN' => $options['tags']]);
		}

		return $query->group(['Articles.id']);
	}


}

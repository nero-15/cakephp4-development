<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Event\EventInterface;

class ArticlesTable extends Table
{

	public function beforeSave(EventInterface $event, $entity, $options)
	{
		if ($entity->isNew() && !$entity->slug) {
			$sluggedTitle = Text::slug($entity->title);
			// スラグをスキーマで定義されている最大長に調整
			$entity->slug = substr($sluggedTitle, 0, 191);
		}
	}

	public function initialize(array $config) : void
	{
		$this->addBehavior('Timestamp');
	}
}

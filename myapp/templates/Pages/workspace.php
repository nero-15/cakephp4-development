<h1><?php echo h($h1); ?></h1>
<div>
	<p><?php echo h($discription); ?></p>
	<p><?php echo h($doComplexOperation); ?></p>
</div>
<table>
	<tr>
		<th>タイトル</th>
	</tr>
	<?php foreach ($Articles as $Article): ?>
	<tr>
		<td>
			<?php echo $this->Html->link($Article->title, ['controller' => 'Articles', 'action' => 'view', $Article->slug]) ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

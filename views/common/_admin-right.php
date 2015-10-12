<?php
/**
 * @link http://www.simpleforum.org/
 * @copyright Copyright (c) 2015 Simple Forum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;

$items = [
	'配置管理'=>['admin/setting'],
	'节点管理'=>['admin/node'],
	'用户管理'=>['admin/user'],
	'链接管理'=>['admin/link'],
	'邮件测试'=>['admin/setting/test-email'],
];

?>

<div class="box">
	<div class="inner gray">论坛管理
	</div>
	<div class="cell sf-btn">
<?php
	foreach($items as $k=>$v) {
		echo Html::a($k, $v, ['class'=>'btn btn-default']);
	}
?>
    </div>
</div>

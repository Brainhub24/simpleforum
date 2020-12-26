<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\ActiveForm;
use app\models\User;

$this->title = Yii::t('app/admin', 'Members');
?>

<div class="row">
<!-- sf-left start -->
<div class="col-md-8 sf-left">

<ul class="list-group sf-box">
    <li class="list-group-item">
        <?php echo Html::a(Yii::t('app/admin', 'Forum Manager'), ['admin/setting/all']), '&nbsp;/&nbsp;', $this->title; ?>
    </li>
	<li class="list-group-item list-group-item-info"><strong><?php echo Yii::t('app', 'Search'); ?></strong></li>
	<li class="list-group-item sf-box-form">
    <?php $form = ActiveForm::begin([
	    'layout' => 'horizontal',
		'id' => 'form-setting',
	    'fieldConfig' => [
//			        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
	        'horizontalCssClasses' => [
	            'label' => 'col-sm-2',
//			            'offset' => 'col-sm-offset-4',
	            'wrapper' => 'col-sm-10',
	            'error' => '',
	            'hint' => 'col-sm-offset-2 col-sm-10',
	        ],
	    ],
	]); ?>
		<?php echo $form->field($model, "username"); ?>
        <div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
            <?php echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']); ?>
			</div>
        </div>
	<?php
		ActiveForm::end();
	?>
	</li>
    <li class="list-group-item list-group-item-info"><strong>
        <?php
                foreach(User::getStatusList() as $status=>$statusName) {
                    $links[] = Html::a($statusName, ['index', 'status'=>$status]);
                }
                echo implode('&nbsp;|&nbsp;', $links);
        ?></strong>
    </li>
    <li class="list-group-item">
        <ul>
        <?php
            $status = intval(Yii::$app->getRequest()->get('status'));
            foreach($users as $user) {
                echo '<li>[', $user['id'], '] ', Html::a($user['username'], ['info', 'id'=>$user['id']]), ($status===User::STATUS_ACTIVE?'':'&nbsp;|&nbsp;'.Html::a(Yii::t('app/admin', 'Activate'), ['activate', 'id'=>$user['id']])), '</li>';
            }
        ?>
        </ul>
    </li>
    <li class="list-group-item item-pagination">
    <?php
    echo LinkPager::widget([
        'pagination' => $pages,
    ]);
    ?>
    </li>
</ul>

</div>
<!-- sf-left end -->

<!-- sf-right start -->
<div class="col-md-4 sf-right">
<?php echo $this->render('@app/views/common/_admin-right'); ?>
</div>
<!-- sf-right end -->

</div>

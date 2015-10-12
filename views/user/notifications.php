<?php
/**
 * @link http://www.simpleforum.org/
 * @copyright Copyright (c) 2015 Simple Forum
 * @author Jiandong Yu admin@simpleforum.org
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\models\Notice;
use app\models\Topic;

$this->title = '提醒系统';
?>

<div class="row">
<div class="col-md-8 sf-left">

<div class="box">
<div class="inner">
	<?= Html::a('首页', ['topic/index']), '&nbsp;/&nbsp;', $this->title ?>
</div>
<?php
foreach($notices as $notice) {
	echo '<div class="cell item clearfix">
			<div class="item-avatar-small">',
				Html::a(Html::img('@web/'.str_replace('{size}', 'small', $notice['source']['avatar']), ["alt" => Html::encode($notice['source']['username'])]), ['user/view', 'username'=>Html::encode($notice['source']['username'])]),
			'</div>
		 	 <div class="item-notice">',
				Html::a(Html::encode($notice['source']['username']), ['user/view', 'username'=>Html::encode($notice['source']['username'])]), ' ';
				if($notice['type'] == Notice::TYPE_COMMENT) {
					echo '回复了您的帖子【'. Html::a(Html::encode($notice['topic']['title']), Topic::getRedirectUrl($notice['topic_id'], $notice['position'])) . '】',
						$notice['notice_count']>0?'<span class="small gray">(省略类似通知'.$notice['notice_count'].'次)</span>':'';
				} else if($notice['type'] == Notice::TYPE_MENTION) {
					if ($notice['position'] > 0) {
						echo '在主题【'. Html::a(Html::encode($notice['topic']['title']), Topic::getRedirectUrl($notice['topic_id'], $notice['position'])) . '】的回帖中提到了您';
					} else {
						echo '在主题【'. Html::a(Html::encode($notice['topic']['title']), ['topic/view', 'id'=>$notice['topic_id']]) . '】中提到了您';
					}
				} else if($notice['type'] == Notice::TYPE_FOLLOW_TOPIC) {
						echo '收藏了您发布的主题【', Html::a(Html::encode($notice['topic']['title']), ['topic/view', 'id'=>$notice['topic_id']]), '】';
				} else if($notice['type'] == Notice::TYPE_FOLLOW_USER) {
						echo '关注了您';
				}
		echo '</div>';
	echo '</div>';
}
?>
<div class="item-pagination">
<?php
echo LinkPager::widget([
    'pagination' => $pages,
]);
?>
</div>

</div>
</div>

<div class="col-md-4 sf-right">
<?= $this->render('@app/views/common/_right') ?>
</div>

</div>

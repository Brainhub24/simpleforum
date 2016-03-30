<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2016 Simple Forum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\lib\Util;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_BANNED = 0;
    const STATUS_INACTIVE = 8;
    const STATUS_ADMIN_VERIFY = 9;
    const STATUS_ACTIVE = 10;
    const ROLE_MEMBER = 0;
    const ROLE_ADMIN = 10;
	const USERNAME_PATTERN = '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]*$/u';
	const USER_MENTION_PATTERN = '/\B\@([a-zA-Z0-9\x{4e00}-\x{9fa5}]{4,20})/u';

	public static $statusOptions = [
		0 => '被屏蔽用户',
		8 => '待激活用户',
		9 => '待管理员验证用户',
		10 => '正常用户',
	];

	public static $roleOptions = [
		0 => '用户组',
		10 => '管理员',
	];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['avatar', 'safe'],
//            ['role', 'default', 'value' => self::ROLE_MEMBER],
//            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'integer', 'max'=>self::STATUS_ACTIVE, 'min'=>self::STATUS_BANNED],
        ];
    }

	public function getNoticeCount()
    {
        return Notice::find()->where(['status'=>0, 'target_id' => $this->id])->count('id');
    }

	public function getNotices()
    {
        return $this->hasMany(Notice::className(), ['target_id' => 'id'])
			->where(['status'=>0])->orderBy(['updated_at'=>SORT_DESC]);
    }

	public function getAuths()
    {
        return $this->hasMany(Auth::className(), ['user_id' => 'id'])
			->orderBy(['id'=>SORT_DESC]);
    }

	public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['user_id' => 'id'])
			->select(['id', 'node_id', 'user_id', 'reply_id', 'title', 'comment_count', 'replied_at'])
			->limit(10)->orderBy(['id'=>SORT_DESC]);
    }

	public function getComments()
    {
        return $this->hasMany(Comment::className(), ['user_id' => 'id'])
			->select(['id', 'user_id', 'topic_id', 'created_at', 'invisible', 'content'])
			->limit(10)->orderBy(['id'=>SORT_DESC]);
    }

	public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
    }

	public function isAdmin()
	{
		return (intval($this->role) === self::ROLE_ADMIN);
	}

	public function isActive()
	{
		return (intval($this->status) >= self::STATUS_ACTIVE);
	}

	public function isInactive()
	{
		return (intval($this->status) === self::STATUS_INACTIVE || intval($this->status) === self::STATUS_ADMIN_VERIFY);
	}

	public function isWatingActivation()
	{
		return (intval($this->status) === self::STATUS_INACTIVE);
	}

	public function isWatingVerification()
	{
		return (intval($this->status) === self::STATUS_ADMIN_VERIFY);
	}

	public function isAuthor($user_id)
	{
		return ($this->id == $user_id);
	}

	public function isExpired($created_at)
	{
		return ( time() > $created_at + intval(Yii::$app->params['settings']['edit_space'])*60 );
	}

	public function canEdit($model, $status=0)
	{
		return ( self::isAdmin() || $status == 0 && self::isActive()
				 && self::isAuthor($model['user_id'])
				 && !self::isExpired($model['created_at'])
				);
	}

	public function canAdd($model)
	{
		return ( self::isAdmin() || self::isActive() && !self::isExpired($model['created_at']) );
	}

	public function canReply($model)
	{
		return ( intval($model['comment_closed']) === 0 && self::isActive() );
	}

	public function canUpload($settings)
	{
		if ( $settings['upload_file'] === 'disable' ) {
			return false;
		}
		if ( self::isAdmin() ) {
			return true;
		} else if ( !self::isActive() ) {
			return false;
		}

		return (
			$this->created_at+intval($settings['upload_file_regday'])*24*3600 < time()
			&& $this->userInfo->topic_count >= intval($settings['upload_file_topicnum'])
		);
	}

	public function getStatus()
	{
		return self::$statusOptions[$this->status];
	}

	public function getRole()
	{
		return self::$roleOptions[$this->role];
	}

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
//        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        return static::find()->where(['id' => $id])
				->andWhere(['>=', 'status', self::STATUS_INACTIVE])
				->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
        return static::find()->where(['username' => $username])
				->andWhere(['>=', 'status', self::STATUS_INACTIVE])
				->one();
    }

    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email])
				->andWhere(['>=', 'status', self::STATUS_INACTIVE])
				->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Util::generateRandomString();
    }

	public function afterSave($insert, $changedAttributes)
	{
		if ($insert === true) {
			$userIP = sprintf("%u", ip2long(Yii::$app->getRequest()->getUserIP()));
			(new UserInfo([
				'user_id' => $this->id,
				'reg_ip' => $userIP,
			]))->save(false);
			Siteinfo::updateCounterInfo('addUser');
/*			if ( intval(Yii::$app->params['settings']['email_verify']) === 1) {
				Token::sendActivateMail($this);
			}*/
			(new History([
				'user_id' => $this->id,
				'action' => History::ACTION_REG,
				'action_time' => $this->created_at,
				'target' => $userIP,
			]))->save(false);
		}
		return parent::afterSave($insert, $changedAttributes);
	}
/*
	public function afterDelete()
	{
		$userId = Yii::$app->getUser()->id;
		(new History([
			'user_id' => $userId,
			'action' => History::ACTION_DELETE_USER,
			'target' => $this->id,
		]))->save(false);
		$userInfo = $this->userInfo;
		$userInfo->delete();
		Topic::afterUserDelete($this->id);
		Comment::afterUserDelete($this->id);
		Siteinfo::updateCountersInfo( ['users'=>-1, 'topics'=>$userInfo->topic_count, 'comments'=>$userInfo->comment_count] );
		return parent::afterDelete();
	}
*/
}

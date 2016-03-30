<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2016 Simple Forum
 * @author Jiandong Yu admin@simpleforum.org
 */

return [
	'/' => 'topic/index', 
	'navi/<name:(\w|-)+>' => 'topic/navi',
	'nodes' => 'node/index',
	't/<id:\d+>' => 'topic/view',
	'new' => 'topic/new',
	'search' => 'topic/search',
	'new/<node:(\w|-)+>' => 'topic/add',
	'n/<name:(\w|-)+>' => 'topic/node',
//	'redirect/<tid:\d+>/<cid:\d+>' => 'topic/redirect',
	'tag/<name>' => 'tag/index',
	'my/<action:(nodes|topics|following)>' => 'favorite/<action>',
	'my/<action:(notifications|info|setting)>' => 'user/<action>',
//	'member/<username:(?!(notices|avatar|info))\w+>' => 'user/view',
	'member/<username:\w+>' => 'user/view',
	'member/<username:\w+>/topics' => 'user/topics',
	'member/<username:\w+>/comments' => 'user/comments',
	'favorite/<type:(node|topic|user)>/<id:\d+>' => 'favorite/add',
	'unfavorite/<type:(node|topic|user)>/<id:\d+>' => 'favorite/cancel',
	'site/auth-<authclient:(qq|weibo|weixin|baidu)>' => 'site/auth',
	'admin/' => 'admin/setting/all',
];

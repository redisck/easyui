<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 模块信息
 */
return [
    // 模块名[必填]
    'name'        => 'cms',
    // 模块标题[必填]
    'title'       => '门户',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'cms.ming.module',
    // 模块描述[选填]
    'description' => '门户模块',
    // 开发者[必填]
    'author'      => 'CaiWeiMing',
    // 开发者网址[选填]
    'author_url'  => 'http://www.dolphinphp.com',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    'version'     => '1.0.0',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'need_module' => [
        ['admin', 'admin.luckphp.module', '1.0.0']
    ],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'need_plugin' => [],
    // 数据表[有数据库表时必填]
    'tables' => [
        'cms_advert',
        'cms_advert_type',
        'cms_column',
        'cms_document',
        'cms_document_article',
        'cms_field',
        'cms_link',
        'cms_menu',
        'cms_model',
        'cms_nav',
        'cms_page',
        'cms_slider',
        'cms_support',
    ],
    // 原始数据库表前缀
    // 用于在导入模块sql时，将原有的表前缀转换成系统的表前缀
    // 一般模块自带sql文件时才需要配置
    'database_prefix' => 'dp_',

    // 模块参数配置
    'config' => [
        ['text', 'summary', '默认摘要字数', '发布文章时，如果没有填写摘要，则自动获取文档内容为摘要。如果此处不填写或填写0，则不提取摘要。', 0],
        ['textarea', 'meta_head', '顶部代码', '代码会放在 <code>&lt;/head&gt;</code> 标签以上'],
        ['textarea', 'meta_foot', '底部代码', '代码会放在 <code>&lt;/body&gt;</code> 标签以上'],
        ['radio', 'support_status', '在线客服', '', ['禁用', '启用'], 1],
    ],
];

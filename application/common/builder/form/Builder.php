<?php
/**
 * Created by PhpStorm.
 * User: 式神 (luck48.com)
 * Email: 289650682@qq.com
 * Name: ${NAME}Administrator
 * Date: 2017-06-14
 * Time: 14:54
 */
namespace app\common\builder\form;

use app\common\builder\LBuilder;

/**
 * 表单构建器
 * @package app\common\builder\type
 * @author 式神 <289650682@qq.com>
 */
class Builder extends LBuilder
{
    /**
     * @var string 模板路径
     */
    private $_template = '';
    /**
     * @var array 模板变量
     */
    private $_vars = [
        'page_title'      => '',    // 页面标题
        'page_tips'       => '',    // 页面提示
        'tips_type'       => '',    // 提示类型
        //'tab_nav'         => [],    // 页面Tab导航
        'form_items'      => [],    // 表单项目
        'form_id'           => 'add' ,    // 表单id
        'form_submit'       => '' ,    // 表单id
        //'btn_hide'        => [],    // 要隐藏的按钮
        //'btn_title'       => [],    // 按钮标题
        'post_url'        => '',    // 表单提交地址
        'form_data'       => [],    // 表单数据
        /*'extra_html'      => '',    // 额外HTML代码
        'extra_js'        => '',    // 额外JS代码
        'extra_css'       => '',    // 额外CSS代码*/
        //'ajax_submit'     => true,  // 是否ajax提交
        //'hide_header'     => false, // 是否隐藏表单头部标题
        //'header_title'    => '',    // 表单头部标题
        /*'js_list'         => [],    // 需要引入的js文件名
        'css_list'        => [],    // 需要引入的css文件名*/
        'field_triggers'  => [],    // 需要触发的表单项名
        'field_hide'      => '',    // 需要隐藏的表单项
        'field_values'    => '',    // 触发表单项的值
        /*'_js_files'       => [],    // 需要加载的js（合并输出）
        '_js_init'        => [],    // 初始化的js（合并输出）
        '_css_files'      => [],    // 需要加载的css（合并输出）*/
        '_layout'         => [],    // 布局参数
        'btn_extra'       => [],    // 额外按钮
        'submit_confirm'  => false, // 提交确认
    ];
    /**
     * @var bool 是否组合分组
     */
    private $_is_group = false;

    /**
     * 初始化
     * @author 式神 <289650682@qq.com>
     */
    public function _initialize()
    {
        $this->_template = APP_PATH. 'common/builder/form/layout.html';
        $this->_vars['post_url'] = $this->request->url(true);
    }



    /**
     * 设置页面标题
     * @param string $title 页面标题 有需要使用
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function setPageTitle($title = '')
    {
        if ($title != '') {
            $this->_vars['page_title'] = trim($title);
        }
        return $this;
    }
    /**
     * 设置表单页提示信息
     * @param string $tips 提示信息
     * @param string $type 提示类型：success,info,danger,warning
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function setPageTips($tips = '', $type = 'info')
    {
        if ($tips != '') {
            $this->_vars['page_tips'] = $tips;
            $this->_vars['tips_type'] = trim($type);
        }
        return $this;
    }
    public function setFormId($id='')
    {
        if ($id!= '') {
            $this->_vars['form_id'] = $id;
        }
        return $this;
    }
    public function addSubmit($name='',$url='')
    {
        if($url!='') {
            $this->_vars['post_url'] = $url;
        }else{
            $module=$this->request->dispatch()['module'];
            $module_name=strtolower($module[0].'/'.$module[1].'/'.$module[2]);
            $this->_vars['post_url'] = url($module_name);
        }
        if ($name!= '') {
            $this->_vars['form_submit'] = $name;
        }
        return $this;
    }
    /**
     * 设置表单提交地址
     * @param string $post_url 提交地址
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function setUrl($post_url = '')
    {
        if ($post_url != '') {
            $this->_vars['post_url'] = trim($post_url);
        }
        return $this;
    }

    /**
     * 添加表单项
     * 这个是addCheckbox等方法的别名方法，第一个参数传表单项类型，其余参数与各自方法中的参数一致
     * @param string $type 表单项类型
     * @param string $name 表单项名
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function addFormItem($type = '', $name = '')
    {
        if ($type != '') {
            // 获取所有参数值
            $args = func_get_args();
            array_shift($args);

            // 判断是否有布局参数
            if (strpos($type, ':')) {
                list($type, $this->_vars['_layout'][$name]) = explode(':', $type);
            }

            $method = 'add'. ucfirst($type);
            call_user_func_array([$this, $method], $args);
        }
        return $this;
    }

    /**
     * 一次性添加多个表单项
     * @param array $items 表单项
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function addFormItems($items = [])
    {
        if (!empty($items)) {
            foreach ($items as $item) {
                call_user_func_array([$this, 'addFormItem'], $item);
            }
        }
        return $this;
    }
    /**
     * 设置表单数据
     * @param array $form_data 表单数据
     * @author 式神 <289650682@qq.com>
     * @return $this
     */
    public function setFormData($form_data = [])
    {
        if (!empty($form_data)) {
            $this->_vars['form_data'] = $form_data;
        }
        return $this;
    }
    /**
     * 设置表单项的值
     * @author 蔡伟明 <314013107@qq.com>
     */
    private function setFormValue()
    {
        if ($this->_vars['form_data']) {
            foreach ($this->_vars['form_items'] as &$item) {
                // 判断是否为分组
                if ($item['type'] == 'group') {
                    foreach ($item['options'] as &$group) {
                        foreach ($group as $key => $value) {
                            if (isset($this->_vars['form_data'][$value['name']])) {
                                $group[$key]['value'] = $this->_vars['form_data'][$value['name']];
                            } else {
                                $group[$key]['value'] = '';
                            }
                        }
                    }
                } else {
                    // 针对日期范围特殊处理
                    if ($item['type'] == 'daterange') {
                        if ($item['name_from'] == $item['name_to']) {
                            list($item['value_from'], $item['value_to']) = $this->_vars['form_data'][$item['id']];
                        } else {
                            $item['value_from'] = $this->_vars['form_data'][$item['name_from']];
                            $item['value_to']   = $this->_vars['form_data'][$item['name_to']];
                        }
                    } else {
                        if (isset($this->_vars['form_data'][$item['name']])) {
                            $item['value'] = $this->_vars['form_data'][$item['name']];
                        } else {
                            $item['value'] = isset($item['value']) ? $item['value'] : '';
                        }
                    }
                }
            }
        }
    }

    /**
     * 添加单选
     * @param string $name 单选名
     * @param string $title 单选标题
     * @param string $tips 提示
     * @param array $options 单选数据
     * @param string $default 默认值
     * @param array $attr 属性，
     *      color-颜色(default/primary/info/success/warning/danger)，默认primary
     *      size-尺寸(sm,nm,lg)，默认sm
     * @param string $extra_attr 额外属性
     * @param string $extra_class 额外css类名
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function addRadio($name = '', $title = '', $tips = '', $options = [], $default = '', $attr = [], $extra_attr = '', $extra_class = ''){
        $item = [
            'type'        => 'radio',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'options'     => $options == '' ? [] : $options,
            'value'       => $default,
            'attr'        => $attr,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }

    /**
     * @param string $name
     * @param string $title
     * @param string $tips
     * @param array $options
     * @param string $default
     * @param array $attr
     * @param string $extra_attr
     * @param string $extra_class
     * @return $this|array 数组
     */
    public function addArray($name = '', $title = '', $tips = '', $options = [], $default = '', $attr = [], $extra_attr = '', $extra_class = ''){
        $item = [
            'type'        => 'array',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'options'     => $options == '' ? [] : $options,
            'value'       => $default,
            'attr'        => $attr,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }

    /**
     * @param string $name
     * @param string $title
     * @param string $tips
     * @param array $options
     * @param string $default
     * @param array $attr
     * @param string $extra_attr
     * @param string $extra_class
     * @return $this|select
     */
    public function addSelect($name = '', $title = '', $tips = '', $options = [], $default = '', $attr = [], $extra_attr = '', $extra_class = ''){
        $item = [
            'type'        => 'select',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'options'     => $options == '' ? [] : $options,
            'value'       => $default,
            'attr'        => $attr,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }
    /**
     * 添加多选
     * @param string $name 单选名
     * @param string $title 单选标题
     * @param string $tips 提示
     * @param array $options 单选数据
     * @param string $default 默认值
     * @param array $attr 属性，
     *      color-颜色(default/primary/info/success/warning/danger)，默认primary
     *      size-尺寸(sm,nm,lg)，默认sm
     * @param string $extra_attr 额外属性
     * @param string $extra_class 额外css类名
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function addCheckbox($name = '', $title = '', $tips = '', $options = [], $default = '', $attr = [], $extra_attr = '', $extra_class = ''){
        $item = [
            'type'        => 'checkbox',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'options'     => $options == '' ? [] : $options,
            'value'       => $default,
            'attr'        => $attr,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }
    /**
     * 添加单行文本框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array $group 标签组，可以在文本框前后添加按钮或者文字
     * @param string $extra_attr 额外属性
     * @param string $extra_class 额外css类名
     * @example ['text', 'text', '单行文本', '提示', 'x',['email','length[20,40]'],'required','']
     * @example [必须text, name名称, 文本标题, 提示信息, value,验证规则,属性'required',class]
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function addText($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = '')
    {
        $item = [
            'type'        => 'text',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'group'       => $group == '' ? [] : $group,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];

        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }
    /**
     * 添加多行文本框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param string $extra_attr 额外属性
     * @param string $extra_class 额外css类名
     * @author 式神 <289650682@qq.com>
     * @example [必须textarea, name名称, 文本标题, 提示信息, value,验证规则,高度的属性,class]
     * @return mixed
     */
    public function addTextarea($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = '')
    {
        $item = [
            'type'        => 'textarea',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'group'       => $group == '' ? [] : $group,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];
        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }

    public function addHidden($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = '')
    {
        $item = [
            'type'        => 'hidden',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'group'       => $group == '' ? [] : $group,
            'extra_class' => $extra_class,
            'extra_attr'  => $extra_attr,
        ];

        if ($this->_is_group) {
            return $item;
        }

        $this->_vars['form_items'][] = $item;
        return $this;
    }
    public function addCkeditor($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = ''){
        return;
    }
    public function addColorpicker($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = ''){
        return;
    }
    public function addImage($name = '', $title = '', $tips = '', $group = [], $default = '', $extra_attr = '', $extra_class = ''){
        return;
    }
    /**
     * 添加分组
     * @param array $groups 分组数据
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function addGroup($groups = [])
    {
        if (is_array($groups) && !empty($groups)) {
            $this->_is_group = true;
            foreach ($groups as &$group) {
                foreach ($group as $key => $item) {
                    $type = array_shift($item);
                    if (strpos($type, ':')) {
                        list($type, $this->_vars['_layout'][$item[0]]) = explode(':', $type);
                    }
                    $group[$key] = call_user_func_array([$this, 'add'.ucfirst($type)], $item);
                }
            }
            $this->_is_group = false;
        }

        $item = [
            'type'    => 'group',
            'options' => $groups
        ];

        if ($this->_is_group) {
            return $item;
        }
        $this->_vars['form_items'][] = $item;
        return $this;
    }

    /**
     * 加载模板输出
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @author 式神 <289650682@qq.com>
     * @return mixed
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if (!empty($vars)) {
            $this->_vars['form_data'] = $vars;
        }
        // 设置表单值
        $this->setFormValue();
        // 另外设置模板
        if ($template != '') {
            $this->_template = $template;
        }
        //print_r($this->_vars['form_items']);
        // 实例化视图并渲染
        echo parent::fetch($this->_template, $this->_vars, $replace, $config);exit;
    }
}
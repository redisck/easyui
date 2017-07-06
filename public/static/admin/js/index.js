$(function(){
	/*布局部分*/
	$('#master-layout').layout({
		fit:true/*布局框架全屏*/
	});   

    //修改密码
	$(".theme-navigate-user-modify").on("click",function(){
        $('.theme-navigate-user-panel').menu('hide');
        $('#pass-dialog').dialog({
            title: '修改密码',
            width: 400,
            height:270,
            closed: false,
            cache: false,
            cls:'theme-panel-deepblue luck-dialog-buttons',
            modal: true,
            href:PASS_URL,
            //maximizable:true,
            buttons:[{
                cls:'easyui-linkbutton button-blue',
                iconCls:'fa fa-check fa-lg',
                text:'保存',
                handler:function(){
                    luck.edit_pass();
                }
            },{
                iconCls:'fa fa-close fa-lg',
                text:'关闭',
                handler:function(){
                    $('#pass-dialog').dialog('close');
                }
            }]
        });

    });
    $(".theme-navigate-user-logout").on("click",function(){
        $('.theme-navigate-user-panel').menu('hide');
        $.messager.confirm('确定操作', '您正在退出系统？', function (flag){
            if(flag){
                window.location.href=LOGOUT_URL;
            }
        })
    });


	$('#luck-menu').on("click", "a[data-bind=click]", function(){
        var id=$(this).attr('data-id')?$(this).attr('data-id'):'';
        var url=$(this).attr('data-url')?$(this).attr('data-url'):'';
        var title=$(this).attr('data-title')?$(this).attr('data-title'):'';
        luck.addPanel(id,title,url);
    });

    $("a[data-bind=menu]").click(luck.topmenu);

    luck.topmenu_init($("a[data-bind=menu]").eq(0));

    tabCloseEven();
});
function doSearch(value,name){
	alert('You input: ' + value+'('+name+')');
}

function tabCloseEven(){
    $(".tabs-header").bind('contextmenu',function(e){
        e.preventDefault();
        $('#rcmenu').menu('show', {
            left: e.pageX,
            top: e.pageY
        });
    });
    /**
     * 刷新tabs会重复dialog的标签影响关闭
     * @param  {[type]} ){                     luck.refresh();    } [description]
     * @return {[type]}     [description]
     */
    $("#refreshcur").bind("click",function(){
        luck.refresh();
    });
    //关闭当前标签页
    $("#closecur").bind("click",function(){
        var tab = $('#mainTabs').tabs('getSelected');
        var index = $('#mainTabs').tabs('getTabIndex',tab);
        if(index==0){return false;}
        $('#mainTabs').tabs('close',index);
    });
    //关闭所有标签页
    $("#closeall").bind("click",function(){
        var tablist = $('#mainTabs').tabs('tabs');
        for(var i=tablist.length-1;i>0;i--){
            $('#mainTabs').tabs('close',i);
        }
    });
    //关闭非当前标签页（先关闭右侧，再关闭左侧）
    $("#closeother").bind("click",function(){
        var tablist = $('#mainTabs').tabs('tabs');
        var tab = $('#mainTabs').tabs('getSelected');
        var index = $('#mainTabs').tabs('getTabIndex',tab);
        for(var i=tablist.length-1;i>index;i--){
            $('#mainTabs').tabs('close',i);
        }
        var num = index-1;
        for(var i=num;i>0;i--){
            $('#mainTabs').tabs('close',1);
        }
        $('#pagetabs').tabs('select',1);
    });
    //关闭当前标签页右侧标签页
    $("#closeright").bind("click",function(){
        var tablist = $('#mainTabs').tabs('tabs');
        var tab = $('#mainTabs').tabs('getSelected');
        var index = $('#mainTabs').tabs('getTabIndex',tab);
        for(var i=tablist.length-1;i>index;i--){
            $('#mainTabs').tabs('close',i);
        }
    });
    //关闭当前标签页左侧标签页
    $("#closeleft").bind("click",function(){
        var tab = $('#mainTabs').tabs('getSelected');
        var index = $('#mainTabs').tabs('getTabIndex',tab);
        var num = index-1;
        for(var i=0;i<num;i++){
            $('#mainTabs').tabs('close',1);
        }
    });
}
var luck={
    //刷新tabs
    refresh:function(id){
        var tab = $('#mainTabs').tabs('getSelected');
        var index = $('#mainTabs').tabs('getTabIndex',tab);
        $('#mainTabs').tabs('getTab',index).panel('refresh');
        if(id){
            $(id).dialog('destroy');
        }
    },
    addPanel:function(id,title,url){
        var options_s
        if ($('#mainTabs').tabs('exists', title)){
            $('#mainTabs').tabs('select', title);
        } else {
            options_s={};
            options_s.id=id;
            options_s.title=title;
            options_s.href=url;
            options_s.closable=true;
            options_s.bodyCls="tabs_box";

            $('#mainTabs').tabs('add',options_s);
        }
    },
    menu:function(id,title){
        var listmenu=$('#luck-menu .panel-title');
        for (var i = 0; i <listmenu.length; i++) {
            $("#luck-menu").accordion('remove',listmenu.eq(i).html());
        }
        $.ajax({
            url: MENU_URL,
            data:{id:id},
            type: "POST",
            beforeSend:function(xhr){
                //console.log(xhr);
            },
            success:function(result){
                var result=$.parseJSON(result);
                if(result.status==200){
                    var data=result.data;

                    for (var i = 0; i < data.length; i++) {
                        var html="";
                        if(data[i].child.length>0){
                            html+='<ul class="easyui-datalist" data-options="border:false,fit:true">';
                                for (var n = 0; n < data[i].child.length; n++) {
                                    html+='<li><a href="#" data-bind="click" data-id="tabs_'+data[i].child[n]['id']+'" data-url="'+data[i].child[n]['url']+'" data-title="'+data[i].child[n]['title']+'">'+data[i].child[n]['title']+'</a></li>';
                                }
                            html+='</ul>';
                        }
                        var selected=i==0?true:false;
                        $('#luck-menu').accordion('add',{
                            title:data[i]['title'],
                            content:html,
                            selected:selected
                        });
                    }
                }
            },
            complete:function(){
                //console.log(111);
            },
            error:function(err){
                console.log(err);
            }
        });
    },
    topmenu_init:function(obj){
        var id=obj.attr('data-id')?obj.attr('data-id'):'';
        var title=obj.attr('data-title')?obj.attr('data-title'):'';
        luck.menu(id,title);
    },
    topmenu:function(){
        /*if(obj.html()==$("a[data-bind=menu]").eq(0).html()){
            var id=obj.attr('data-id')?obj.attr('data-id'):'';
            var title=obj.attr('data-title')?obj.attr('data-title'):'';
            luck.menu(id,title);
        }else{*/
        var id=$(this).attr('data-id')?$(this).attr('data-id'):'';
        var title=$(this).attr('data-title')?$(this).attr('data-title'):'';
        //$.insdep.control(url);
        if(!$(this).hasClass('theme-navigate-button')){
            $(this).addClass('theme-navigate-button').siblings().removeClass('theme-navigate-button');
            luck.menu(id,title);
        }

    },
    //关闭dialog
    closeDialog:function(){
        $('#add-dialog').dialog('close');
    }
}
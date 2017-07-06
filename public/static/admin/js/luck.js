/**
 * Created by Administrator on 2017-05-12.
 */
luck.edit_pass=function(){
    $("#form-pass").form('submit',{
        url:PASS_URL,
        onSubmit:function(){
            var isValid = $(this).form('validate');
            isValid?$.messager.progress():"";
            return isValid;
        },
        success:function(data){
            $.messager.progress('close');
            try
            {
                var data= $.parseJSON(data);
                if(data.status==200){
                    $.messager.alert('提示', data.message, 'success',function(){
                        window.location.reload();
                    });
                }else{
                    $.messager.alert('提示',data.message,'error',function(){
                        $("#form-pass").form('clear');
                    });
                }
            }
            catch (e)
            {
                $.messager.alert('提示', '网络连接错误,请重试!', 'warning');
            }
        }
    })
};
luck.success=function(data,dg,op){
    $.messager.progress('close');
    try
    {
        if(typeof data=='string'){
            var data= $.parseJSON(data);
        }
        if(data.status==200){
            /*$.messager.alert('提示', data.message, 'success',function(){
                luck.closeDialog();
                dg.datagrid('reload');
                dg.datagrid('clearSelections');
            });*/
            if(op){
                op.dialog('close');
            }else{
                luck.closeDialog();
            }
            dg.datagrid('reload');
            dg.datagrid('clearSelections');
            $.messager.show({
                title:'消息提示',
                msg:data.message,
                timeout:5000,
                showType:'slide'
            });
        }else{
            $.messager.alert('错误',data.message,'error'/*,function(){
             $("#form-add").form('clear');
             }*/);
        }
    }
    catch (e)
    {
        $.messager.alert('警告', '网络连接错误,请重试!', 'warning');
    }
};
luck.successtree=function(data,dg){
    $.messager.progress('close');
    try
    {
        if(typeof data=='string'){
            var data= $.parseJSON(data);
        }
        if(data.status==200){
            /*$.messager.alert('提示', data.message, 'success',function(){
                luck.closeDialog();
                dg.treegrid('reload');
                dg.treegrid('clearSelections');
            });*/
            luck.closeDialog();
            dg.treegrid('reload');
            dg.treegrid('clearSelections');
            $.messager.show({
                title:'消息提示',
                msg:data.message,
                timeout:5000,
                showType:'slide'
            });
        }else{
            $.messager.alert('错误',data.message,'error'/*,function(){
             $("#form-add").form('clear');
             }*/);
        }
    }
    catch (e)
    {
        $.messager.alert('警告', '网络连接错误,请重试!', 'warning');
    }
};

luck.success2=function(data,dg){
    try
    {
        if(typeof data=='string'){
            var data= $.parseJSON(data);
        }
        if(data.status==200){
            /*$.messager.alert('提示', data.message, 'success',function(){
                dg.datagrid('reload');
                dg.datagrid('clearSelections');
            });*/
            dg.datagrid('reload');
            dg.datagrid('clearSelections');
            $.messager.show({
                title:'消息提示',
                msg:data.message,
                timeout:5000,
                showType:'slide'
            });
        }else{
            $.messager.alert('错误',data.message,'error'/*,function(){
             $("#form-add").form('clear');
             }*/);
        }
    }
    catch (e)
    {
        $.messager.alert('警告', '网络连接错误,请重试!', 'warning');
    }
};
luck.successtree2=function(data,dg){
    try
    {
        if(typeof data=='string'){
            var data= $.parseJSON(data);
        }
        if(data.status==200){
            /*$.messager.alert('提示', data.message, 'success',function(){
                dg.treegrid('reload');
                dg.treegrid('clearSelections');
            });*/
            //方法2
            dg.treegrid('reload');
            dg.treegrid('clearSelections');
            $.messager.show({
                title:'消息提示',
                msg:data.message,
                timeout:5000,
                showType:'slide'
            });
        }else{
            $.messager.alert('错误',data.message,'error'/*,function(){
             $("#form-add").form('clear');
             }*/);
        }
    }
    catch (e)
    {
        $.messager.alert('警告', '网络连接错误,请重试!', 'warning');
    }
};
//时间戳过滤开始
luck.datagrid_time=function(value,row,index){
    if(value<1){
        return '无';
    }else{
        var d=new Date(value*1000);
        return luck.formatDate(d);
    }
};
luck.formatDate=function(now) {
    var year=now.getFullYear();
    var month=now.getMonth()+1;
    var date=now.getDate();
    var hour=now.getHours();
    var minute=now.getMinutes();
    var second=now.getSeconds();
    if(month<10){
        month='0'+month;
    }
    if(date<10){
        date='0'+date;
    }
    if(hour<10){
        hour='0'+hour;
    }
    if(minute<10){
        minute='0'+minute;
    }
    if(second<10){
        second='0'+second;
    }
    return year+"-"+month+"-"+date+" "+hour+":"+minute+":"+second;
}
//结束时间戳

//ip转换
luck.datagrid_ip=function( proper_address,row,index) {
    proper_address = proper_address>>>0;
    var output = false;  // www.
    if ( !isNaN ( proper_address ) && ( proper_address >= 0 || proper_address <= 4294967295 ) ) {
        output = Math.floor (proper_address / Math.pow ( 256, 3 ) ) + '.' +
            Math.floor ( ( proper_address % Math.pow ( 256, 3 ) ) / Math.pow ( 256, 2 ) ) + '.' +
            Math.floor ( ( ( proper_address % Math.pow ( 256, 3 ) ) % Math.pow ( 256, 2 ) ) /
                Math.pow ( 256, 1 ) ) + '.' +
            Math.floor ( ( ( ( proper_address % Math.pow ( 256, 3 ) ) % Math.pow ( 256, 2 ) ) %
                Math.pow ( 256, 1 ) ) / Math.pow ( 256, 0 ) );
    }
    return output;
};
//状态
luck.datagrid_status=function(value,row,index){
    if(value==0){
        return '<span class="font-red">禁用中</span>';
    }else if(value==1){
        return '<span class="font-green">启用中</span>';
    }else if(value==-1){
        return '';
    }else{
        return value;
    }
}
//状态
luck.datagrid_showed=function(value,row,index){
    if(value==0){
        return '<span class="font-red">隐藏</span>';
    }else if(value==1){
        return '<span class="font-green">显示</span>';
    }else{
        return value;
    }
}
//状态
luck.datagrid_if=function(value,row,index){
    if(value==0){
        return '<span class="font-red">否</span>';
    }else if(value==1){
        return '<span class="font-green">是</span>';
    }else{
        return value;
    }
}

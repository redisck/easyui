/**
 * Created by Administrator on 2017-06-05.
 */

luck.uploadOne=function(size){
    var uploader = new plupload.Uploader({
        runtimes: 'gears,html5,html4,silverlight,flash', //上传插件初始化选用那种方式的优先级顺序
        browse_button : 'choseOne', //触发文件选择对话框的按钮，为那个元素id
        url : UPLOAD_URL, //服务器端的上传页面地址
        flash_swf_url : flash_swf_url, //swf文件，当需要使用swf方式进行上传时需要配置该参数
        max_file_size: size,//限制为10MB
        filters: [{title: "选择文件",extensions: "jpg,gif,png"}],//图片限制
        silverlight_xap_url : silverlight_xap_url, //silverlight文件，当需要使用silverlight方式进行上传时需要配置该参数
        multi_selection: MULT
    });
    uploader.init();

    //图片选择完毕触发
    uploader.bind('FilesAdded',function(uploader,files){
        $(".luck-upbox").hide();
        $("#progress").show();
        uploader.start();
    });

    //图片上传成功触发，ps:data是返回值（第三个参数是返回值）
    uploader.bind('FileUploaded',function(uploader,files,data){
        var res=$.parseJSON(data.response);
        $.messager.show({
            title:'消息',
            msg:res.message,
            timeout:5000,
            showType:'slide'
        });
        $("#luck-heading").html("<img  src='"+UPLOAD+"/"+URL_NANE+"/" + res.url + "'  class='luck-avatar' />");
        $("input[name='head_img']").val(UPLOAD+"/"+URL_NANE+"/" + res.url);
        $("#progress").hide();
        $(".luck-upbox").show();
    });
    //会在文件上传过程中不断触发，可以用此事件来显示上传进度监听（比如说上传进度）
    uploader.bind('UploadProgress',function(uploader,file){
        var percent = file.percent;
        $('#progress').progressbar('setValue', percent);

    });
    //错误
    uploader.bind('Error',function(uploader,err){
        $.messager.alert('错误',err.message,'error');
        $("#progress").hide();
    });
}

/*
var uploader = new plupload.Uploader({
    runtimes: 'gears,html5,html4,silverlight,flash', //上传插件初始化选用那种方式的优先级顺序
    browse_button : 'choseOne', //触发文件选择对话框的按钮，为那个元素id
    url : 'images/upload.shtml', //服务器端的上传页面地址
    flash_swf_url : '__STATIC__/plugin/plupload-2.3.1/js/Moxie.swf', //swf文件，当需要使用swf方式进行上传时需要配置该参数
    max_file_size: '2mb',//限制为2MB
    filters: [{title: "选中图片文件",extensions: "jpg,gif,png"}],//图片限制
    silverlight_xap_url : '__STATIC__/plugin/plupload-2.3.1/js/Moxie.xap', //silverlight文件，当需要使用silverlight方式进行上传时需要配置该参数
    multi_selection: false
});
uploader.init();

//图片选择完毕触发
uploader.bind('FilesAdded',function(uploader,files){
    var html="";
    for (var i in files) {
        html += files[i].name + ' (' + plupload.formatSize(files[i].size) + ')';
    }
    $('#filelist').html(html);
});

//图片上传成功触发，ps:data是返回值（第三个参数是返回值）
uploader.bind('FileUploaded',function(uploader,files,data){
    console.log('222');
    console.log(uploader);
    console.log(files);
    console.log(data);
});
//会在文件上传过程中不断触发，可以用此事件来显示上传进度监听（比如说上传进度）
uploader.bind('UploadProgress',function(uploader,file){
    $('#progress').progressbar('setValue', file.percent);
    console.log(uploader);
    console.log(files);

});*/

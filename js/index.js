$(function() {
  var url = 'http://192.168.4.26/fastdfs/fastdfs_php_demo/upload.php'; //文件上传地址

  //上传头像
  var upload = new plupload.Uploader({
    runtimes: 'html5,flash,silverlight,html4', //上传插件初始化选用那种方式的优先级顺序
    browse_button: "file", //触发浏览文件按钮标签的唯一id
    url: url, //上传服务器地址
    flash_swf_url: 'js/plugins/plupload/Moxie.swf', //flash文件地址
    silverlight_xap_url: 'js/plugins/plupload/Moxie.xap',
    multi_selection: false, //是否可以多选
    init: {
      FilesAdded: function(up, files) {
        $('.form-result').addClass('hide');
        up.start();
      },
      UploadProgress: function(up, file) {
        $('#file').text('正在上传中(' + file.percent + '%)');
      },
      /** 上传出错的时候触发 **/
      Error: function(up, err) {},
      FileUploaded: function(up, files, message) {
        $('#file').button('reset');
        $('#file').text('选择上传文件');
        var model = JSON.parse(message.response);
        $('.form-result').removeClass('hide');
        $('#p_group_name').text(model.group_name);
        $('#p_file_id').text(model.file_id);
        $('#p_source_ip_address').text(model.source_ip_address);
        $('#p_file_name').text(model.file_name);
      }
    }
  });
  upload.init();
});
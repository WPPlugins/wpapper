/*
 * 发布管理页面JS
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.init = function() {
            var thiso = this;

            $("#subbtn").click(function(){
				thiso.submit();
            });
			
            set_value("list_style",_wpapper_obj.list_style);
        };

        this.submit = function() {
            var params = {
                "list_style": get_text_value("list_style")
            };
            //print_r(params);
            //return;


            var thiso = this;
            $.ajax({
                type: "get",
                async: false,
                url: _wpapper_obj.ajax_url,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
					    alert("设置已更改");
					} else {
                        if (res.error_code==100803) {
                            alert("您没有权限管理此页面");
                        } else {
                            alert(res.error_msg);
                        }
					}
                },
                error: function (data) {
					print_r(data); return;
                    alert("设置保存失败");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);

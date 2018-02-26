//当浏览器窗口大小改变时重载网页
/*window.onresize=function(){
    window.location.reload();
}*/


$(function(){
	//检测用户名和密码
	$("#userName").on('keyup',hided);
	$("#userPwd").on('keyup',hided);
	function hided() {
		var name = $("#userName").val();
		var pwd  = $("#userPwd").val();
		if(name !== "" && pwd !== ""){
			$("#error").hide();
		}
	}

	//用户登录的提交
	$("#sub").click(function () {
		var name = $("#userName").val();
		var pwd  = $("#userPwd").val();
		if(name == ""){
			$("#error").show();
			$("#error").text("名字不能为空");
			return false;
		}else if (pwd == ""){
			$("#error").show();
			$("#error").text("密码不能为空");
			return false;
		}
		data = {
			name : name,
			pwd  : pwd
		}
		$.post("/admin/login",data,function(data,textStatus,xhr){
			//返回检测结果如果成功，登录管理系统
			if(textStatus == 'success'){
				var data = $.parseJSON(data);
				if(data.error == '0'){
					alert(data.msg);
					window.location="/admin/main";
				}else {
					$("#error").show();
					$("#error").text("用户名或者密码不正确");
				}
			}
		});
	});

	//增加或更新栏目的提交
		$('#name').focus(function (){
			$('#error').hide();
		});
		$('#nickname').focus(function (){
			$('#error').hide();
		});
		$("#subCategory").click(function () {
			var name = $("#name").val();
			var nickname  = $("#nickname").val();
			var keywords = $("#keywords").val();
			if(name == "" || nickname == ""){
				return false
			}
			data = {
				name : name,
				nickname : nickname,
				keywords : keywords,
				id       : ''
			}
			if($("#id").val() !=='') data.id = $("#id").val();
			$.post("/admin/addCategory",data,function (data,textStatus,xhr) {
				if (textStatus == 'success') {
					var data = $.parseJSON(data);
					if (data.error == '0') {
						alert(data.msg);
						window.location = "/admin/category";
					} else {
						$("#error").show();
						$("#error").text('此栏目名称或别名已存在');
					}
				}
			});
		});

	//增加或更新文章
	$('#title').focus(function (){
		$('#error').hide();
	});
	$("#subArticle").click(function () {
		var title =  $("#title").val();
		if(title == ""){
			$('#error').show();
			$('#error').text('标题不能为空');
			return false
		}

		data = {
			title   : title,
			cat_id  : '' ,
			art_id  : '' ,
			content : ''
		}
		var cat_id = $("input[type='radio']:checked").val();
		if(ue.getContent() !== "") data.content = ue.getContent();
		if($("#art_id").val() !== "") data.art_id = $("#art_id").val();
		if(typeof (cat_id) !=='undefined') data.cat_id = cat_id;
		$.post("/admin/newArticle",data,function (data,textStatus,xhr) {
			if (textStatus == 'success') {
				var data = $.parseJSON(data);
				if (data.error == '0') {
					alert(data.msg);
					window.location = "/admin/article";
				}
			}
		});
	});

	//批量删除文章
	$("#subdelete").click(function (){
		var id= new Array();
		$('input[name="checkbox[]"]:checked').each(function(){
			id.push($(this).val());//向数组中添加元素
		});
		if(id == '') {
			alert('请选择要删除的文章')
			return false;
		}else {
			$.post("/admin/delArticle",{id:id},function (data,textStatus,xhr) {

				if (textStatus == 'success') {
					var data = $.parseJSON(data);
					if (data.error == '0') {
						alert(data.msg);
						window.location = "/admin/article";
					}
				}
			});
		}
	});

	//单个删除
	$("#main table tbody tr td a").click(function(){
		var name = $(this).attr("class");
		switch(name){
			//删除文章
			case 'delart':
				var id = [$(this).attr("rel")];
				var url = "/admin/delArticle";
				var back = "/admin/article";
				break;
		    //删除目录
			case 'delcat':
				var id = $(this).attr("rel");
				var url = "/admin/deleteCategory";
				var back = "/admin/category";
				break;
			//删除链接
			case 'dellink':
				var id = $(this).attr("rel");
				var url = "/admin/delLink";
				var back = "/admin/flink"
		}
		if (event.target.outerText == "删除")
		{
			if(window.confirm("此操作不可逆，是否确认？"))
			{
				$.ajax({
				 type: "POST",
				 url: url,
				 data: {id:id},
				 cache: false, //不缓存此页面
				 success: function (data) {
				 alert('删除成功');
				 window.location = back;
				 }
				 });

			};
		};
	});

});


//多选
var checkall=document.getElementsByName("checkbox[]");
//全选
function select(){

	for(var $i=0;$i<checkall.length;$i++){
		checkall[$i].checked=true;
	}
}
//反选
function reverse(){
	for(var $i=0;$i<checkall.length;$i++){
		if(checkall[$i].checked){
			checkall[$i].checked=false;
		}else{
			checkall[$i].checked=true;
		}
	}
}
//全不选
function noselect(){
	for(var $i=0;$i<checkall.length;$i++){
		checkall[$i].checked=false;
	}
}




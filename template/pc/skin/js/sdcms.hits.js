$(function()
{
	if($("#hits").length>0||$("#commentnum").length>0)
	{
		$.ajax(
		{
			type:"post",
			cache:false,
			url: webroot+"plug/hits.asp",
			data:"id="+infoid,
			success:function(_)
			{
				var arr=_.split(":");
				if($("#hits").length>0){$("#hits").html(arr[0]);}
				if($("#commentnum").length>0){$("#commentnum").html("<a href='"+webroot+"plug/comment.asp?id="+infoid+"'>"+arr[1]+"</a>");}
			}
		});
	}
})
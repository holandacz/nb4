function quick_delete(postid, type)
{

// By default, don't comfirm.. they're doing a regular
answer_delete = true;

// Trying to do a physical deletion?
if(type == 'remove'){
	var answer_delete = confirm("Sure you want to delete?")
}

	if(answer_delete){

		var oCallback = {
		  success: quick_delete_done(true, type, postid),
		  failure: quick_delete_done(false, type, postid),
		  //scope: this
		  timeout: vB_Default_Timeout
		 }

		if(YAHOO.util.Connect.isCallInProgress(oConnect)) {
		    YAHOO.util.Connect.abort(oConnect);
		}

		var oConnect = 
		YAHOO.util.Connect.asyncRequest("POST", "editpost.php?&ajax=1", oCallback,SESSIONURL + "securitytoken=" + SECURITYTOKEN + "&do=deletepost&p=" + postid + "&deletepost=" + type);

	}

}

function quick_delete_done(status, type, postid)
{
		var obj = document.getElementById('quick_delete_' + type + '_' + postid);
		if(status){
			obj.style.display = "none";
		}
}
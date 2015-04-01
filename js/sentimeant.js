window.Setting = {
	webSiteApi : '/',
	lang 	   : 'en',
	imagePath  : 'images/upload/'
};

/*HTTP GET&URL Helper*/
window.URL = {
	/***HTTP GET Helper**/
	get : function(arg){
		//get query params
		var self = this,
		    o = this.getUriObject(window.self.location.href);
		if(typeof o.query != "undefined"){
		    var q = $(this.getQueryObject(o.query)),      
		    m = decodeURIComponent(q[0][arg]);
		    return m;
		}else{
		  return false;
		}          
	},
	getQueryObject: function(q) {
		var vars = q.split(/[&;]/);
		var rs = {};    
		if (vars.length) 
		$.each(vars,function(i,val) {
		  var keys = val.split('=');
		  if (keys.length && keys.length == 2) {
		      rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
		  }
		});
		return rs;
	},
	getUriObject: function(u){
		var bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);    
		return (bits)
		  ? { uri : bits[0], scheme : bits[1], authority : bits[2], 
		      domain : bits[3], port : bits[4], path : bits[5], 
		      directory : bits[6], file : bits[7], query : bits[8], fragment : bits[9]}
		  : null;
	}
};

window.Sentimeant = {
	init : function() {
		var self = this;
		if(window.URL.get('code') != '' && typeof window.URL.get('code') != 'undefined'){
			self.setToken();
		}else{
			self.authen();	
		}
	},
	authen : function(){
	var box 	= jQuery('div.box'),
			self 	= this ;
		//Send Save command Asynchronous to server
		jQuery.ajax({
		    type 		:'GET',
		    url 		: 'authen.php',
		    dataType 	: 'json',     
		    success 	: function(response){
		     if(response == null){
		     	box.find('a.login').remove();
		     	var btn = jQuery("<button type='submit'>start</button>");
		     	btn.('bind',function() {
		     		self.
		     	});
		     	box.append(btn);
		     	return false;
		     }
					if(response.authUrl != '' && typeof response.authUrl != 'undefined' ){
						 box.append("<a class='login' href='" + response.authUrl + "'>Connect Me!</a>");
					}
					
					
		    }
		 });	
	},
	setToken : function(){
			jQuery.ajax({
				type 			: 'GET',
				url 			: 'authen.php',
				dataType 	: 'json',   
				data 			: { code : window.URL.get('code')},
				success 	: function(response){
					if(response == null) return false;
					window.location = response.redirect_url;
				}
		 });
  }
  
  },getMailList : function() {
  
  },getMailDetail : function() {
  
  },getSentiment : function() {
  
  
  };


jQuery(document).ready(function(){

	window.Sentimeant.init();

});
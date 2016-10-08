
SAPO.namespace('Communication');SAPO.Communication.Ajax=function(url,options){this.init(url,options);};SAPO.Communication.Ajax.version=0.1;SAPO.Communication.Ajax.prototype={version:SAPO.Communication.Ajax.version,init:function(url){var options=SAPO.extendObj({asynchronous:true,method:'post',parameters:false,timeout:false,postBody:false,encoding:'UTF-8',contentType:'application/x-www-form-urlencoded',requestHeaders:false,onComplete:false,onSuccess:false,onFailure:false,onException:false,onCreate:false,onInit:false,onTimeout:false,sanitizeJSON:false,evalJS:true,debug:false},arguments[1]||{});this.options=options;this.options.method=this.options.method.toLowerCase();if(this.options.method!='get'&&this.options.method=='post'){this.options.method='post';}
if(this.options.onInit){this.options.onInit();}
this.stoTimeout=false;this.url=url;this.transport=this.getTransport();this.request();},getTransport:function()
{if(typeof(XMLHttpRequest)!='undefined'){return new XMLHttpRequest();}else if(typeof(ActiveXObject)!='undefined'){try{return new ActiveXObject('Msxml2.XMLHTTP');}catch(e){return new ActiveXObject('Microsoft.XMLHTTP');}}else{return false;}},setHeaders:function(url)
{if(this.transport){try{var headers={"Accept":"text/javascript,text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,image/jpeg,image/gif;q=0.2,*/*;q=0.1","Accept-Language":navigator.language};if(this.options.method=='post'){headers['Content-Type']=this.options.contentType+(this.options.encoding?'; charset='+this.options.encoding:'');if(this.options.postBody){headers['Content-length']=this.options.postBody.length;}}
headers['X-Requested-With']='XMLHttpRequest';headers['X-SAPO-Version']='0.1';if(this.options.method=='post'){this.transport.setRequestHeader("Content-Type",this.options.contentType+(this.options.encoding?'; charset='+this.options.encoding:''));}
if(this.transport.overrideMimeType&&(navigator.userAgent.match(/Gecko\/(\d{4})/)||[0,2005])[1]<2005){headers['Connection']='close';}
if(this.options.requestHeaders&&typeof(this.options.requestHeaders)=='object'){for(var headerReqName in this.options.requestHeaders){headers[headerReqName]=this.options.requestHeaders[headerReqName];}}
for(var headerName in headers){if(headers.hasOwnProperty(headerName)){this.transport.setRequestHeader(headerName,headers[headerName]);}}}catch(e){}}},paramsObjToStr:function(optParams){var params='';for(var p in optParams){if(optParams.hasOwnProperty(p)){if(optParams[p].constructor===Array){var arr=optParams[p];for(var i=0,l=arr.length;i<l;i++){params+=encodeURIComponent(p)+"="+arr[i]+"&";}}else{params+=p+"="+optParams[p]+"&";}}}
if(params!==''){params=params.substr(0,params.length-1);}
return params;},setParams:function()
{var params=false,optParams=this.options.parameters;if(typeof optParams==="string"){params=optParams;}else if(typeof optParams==="object"){params=this.paramsObjToStr(optParams);}
if(params){if(this.url.indexOf('?')>-1){this.url=this.url+'&'+params;}else{this.url=this.url+'?'+params;}}},getHeader:function(name)
{try{return this.transport.getResponseHeader(name);}catch(e){return null;}},getResponse:function(){var t=this.transport,r={headerJSON:null,responseJSON:null,getHeader:this.getHeader,request:this,transport:t};r.readyState=t.readyState;r.responseText=t.responseText;r.responseXML=t.responseXML;r.status=t.status;r.statusText=t.statusText;return r;},runStateChange:function()
{var responseJSON,responseContent,response;try{var curStatus=this.transport.status||0;}catch(e){var curStatus=0;}
if(this.transport.readyState==4){if(this.stoTimeout){clearTimeout(this.stoTimeout);}
responseContent=this.transport.responseText;response=this.getResponse();var headerContentType=this.getHeader('Content-Type');if(headerContentType==null){headerContentType='';}
if((this.options.evalJS&&headerContentType.indexOf("application/json")>=0)||this.options.evalJS==='force'){try{responseJSON=this.evalJSON(responseContent,this.sanitizeJSON);if(responseJSON){responseContent=response.responseJSON=responseJSON;}}catch(e){this._dispatchException(e);}}
var xjson=this.getHeader("X-JSON");if(xjson&&this.options.evalJS){try{responseJSON=this.evalJSON(xjson,this.sanitizeJSON);responseContent=response.headerJSON=responseJSON;}catch(e){this._dispatchException(e);}}
if(this.transport.responseXML!==null&&response.responseJSON===null&&this.transport.responseXML.xml!==""){responseContent=this.transport.responseXML;}
if(curStatus>=200&&curStatus<300){if(this.options.onSuccess){this.options.onSuccess(response,responseContent);}}else{if(this.options.onFailure){this.options.onFailure(response,responseContent);}}
if(typeof(this.options['on'+curStatus])!='undefined'){this.options['on'+curStatus](response,responseContent);}
if(this.options.onComplete){this.options.onComplete(response,responseContent);}}},_dispatchException:function(e){if(typeof this.options.onException==="function"){this.options.onException(e);}else{throw e;}},request:function(url)
{if(this.transport){if(this.options.method=='get'){this.setParams();}
if(this.options.method=='get'){this.transport.open("GET",this.url,this.options.asynchronous);}else{this.transport.open("POST",this.url,this.options.asynchronous);}
this.setHeaders();if(this.options.onCreate){this.options.onCreate(this.transport);}
if(this.options.timeout&&!isNaN(this.options.timeout)){this.stoTimeout=setTimeout(function(){if(this.options.onTimeout){this.transport.abort();this.options.onTimeout();}}.bindObj(this),(this.options.timeout*1000));}
if(this.options.asynchronous){this.transport.onreadystatechange=function(){this.runStateChange();}.bindObj(this);}
if(this.options.method=='get'){this.transport.send(null);}else{var params;if(this.options.postBody){params=this.options.postBody;}else{if(typeof this.options.parameters==="string"){params=this.options.parameters;}else if(typeof this.options.parameters==="object"){params=this.paramsObjToStr(this.options.parameters);}}
this.transport.send(params);}
if(!this.options.asynchronous){this.runStateChange();}}},isJSON:function(str)
{if(str.length===0||typeof str!=="string"){return false;}
str=str.replace(/\\./g,'@').replace(/"[^"\\\n\r]*"/g,'');return(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);},evalJSON:function(strJSON,sanitize)
{if(!sanitize||this.isJSON(strJSON)){try{if(typeof(JSON)!=="undefined"&&typeof(JSON.parse)!=='undefined'){return JSON.parse(strJSON);}
return eval('('+strJSON+')');}catch(e){throw new Error('ERROR: Bad JSON string...');}}
return false;},debug:function(){}};SAPO.Communication.Ajax.load=function(url,callback){return new SAPO.Communication.Ajax(url,{method:'get',onSuccess:function(response){callback(response.responseText);}});};
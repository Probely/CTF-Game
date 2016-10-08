
(function(window,undefined){'use strict';SAPO.namespace('Dom');if(SAPO.Dom.Css){return;}
var ua=navigator.userAgent.toLowerCase();var isNativeAndroidBrowser=ua.indexOf('android')!==-1&&ua.indexOf('chrome')===-1&&ua.indexOf('safari')!==-1;var isNode=(typeof Node==='object')?function(o){return o instanceof Node;}:function(o){return o&&typeof o==='object'&&typeof o.nodeType==='number'&&typeof o.nodeName==='string';};SAPO.Dom.Css={addRemoveClassName:function(elm,className,addRemState){if(addRemState){return SAPO.Dom.Css.addClassName(elm,className);}
SAPO.Dom.Css.removeClassName(elm,className);},addClassName:function(elm,className){elm=s$(elm);if(elm&&className){if(typeof elm.classList!=="undefined"){elm.classList.add(className);}
else if(!this.hasClassName(elm,className)){elm.className+=(elm.className?' ':'')+className;}}},removeClassName:function(elm,className){elm=s$(elm);if(elm&&className){if(typeof elm.classList!=="undefined"){elm.classList.remove(className);}else{if(typeof elm.className==="undefined"){return false;}
var elmClassName=elm.className,re=new RegExp("(^|\\s+)"+className+"(\\s+|$)");elmClassName=elmClassName.replace(re,' ');elmClassName=elmClassName.replace(/^\s+/,'').replace(/\s+$/,'');elm.className=elmClassName;}}},setClassName:function(elm,className,add){if(add){SAPO.Dom.Css.addClassName(elm,className);}
else{SAPO.Dom.Css.removeClassName(elm,className);}},hasClassName:function(elm,className){elm=s$(elm);if(elm&&className){if(typeof elm.classList!=="undefined"){return elm.classList.contains(className);}
else{if(typeof elm.className==="undefined"){return false;}
var elmClassName=elm.className;if(typeof elmClassName.length==="undefined"){return false;}
if(elmClassName.length>0){if(elmClassName===className){return true;}
else{var re=new RegExp("(^|\\s)"+className+"(\\s|$)");if(re.test(elmClassName)){return true;}}}}}
return false;},blinkClass:function(element,className,timeout,negate){element=s$(element);SAPO.Dom.Css.setClassName(element,className,!negate);setTimeout(function(){SAPO.Dom.Css.setClassName(element,className,negate);},Number(timeout)|100);},toggleClassName:function(elm,className,forceAdd){if(elm&&className){if(typeof elm.classList!=="undefined"){elm=s$(elm);if(elm!==null){elm.classList.toggle(className);}
return true;}}
if(typeof forceAdd!=='undefined'){if(forceAdd===true){this.addClassName(elm,className);}
else if(forceAdd===false){this.removeClassName(elm,className);}}else{if(this.hasClassName(elm,className)){this.removeClassName(elm,className);}
else{this.addClassName(elm,className);}}},setOpacity:function(elm,value){elm=s$(elm);if(elm!==null){var val=1;if(!isNaN(Number(value))){if(value<=0){val=0;}
else if(value<=1){val=value;}
else if(value<=100){val=value/100;}
else{val=1;}}
if(typeof elm.style.opacity!=='undefined'){elm.style.opacity=val;}
else{elm.style.filter="alpha(opacity:"+(val*100|0)+")";}}},_camelCase:function(str){return str?str.replace(/-(\w)/g,function(_,$1){return $1.toUpperCase();}):str;},getStyle:function(elm,style){elm=s$(elm);if(elm!==null){style=style==='float'?'cssFloat':SAPO.Dom.Css._camelCase(style);var value=elm.style[style];if(window.getComputedStyle&&(!value||value==='auto')){var css=getComputedStyle(elm,null);value=css?css[style]:null;}
else if(!value&&elm.currentStyle){value=elm.currentStyle[style];if(value==='auto'&&(style==='width'||style==='height')){value=elm["offset"+style.charAt(0).toUpperCase()+style.slice(1)]+"px";}}
if(style==='opacity'){return value?parseFloat(value,10):1.0;}
else if(style==='borderTopWidth'||style==='borderBottomWidth'||style==='borderRightWidth'||style==='borderLeftWidth'){if(value==='thin'){return'1px';}
else if(value==='medium'){return'3px';}
else if(value==='thick'){return'5px';}}
return value==='auto'?null:value;}},setStyle:function(elm,style){elm=s$(elm);if(elm!==null){if(typeof style==='string'){elm.style.cssText+='; '+style;if(style.indexOf('opacity')!==-1){this.setOpacity(elm,style.match(/opacity:\s*(\d?\.?\d*)/)[1]);}}
else{for(var prop in style){if(style.hasOwnProperty(prop)){if(prop==='opacity'){this.setOpacity(elm,style[prop]);}
else{if(prop==='float'||prop==='cssFloat'){if(typeof elm.style.styleFloat==='undefined'){elm.style.cssFloat=style[prop];}
else{elm.style.styleFloat=style[prop];}}else{elm.style[prop]=style[prop];}}}}}}},show:function(elm,forceDisplayProperty){elm=s$(elm);if(elm!==null){elm.style.display=(forceDisplayProperty)?forceDisplayProperty:'';}},hide:function(elm){elm=s$(elm);if(elm!==null){elm.style.display='none';}},showHide:function(elm,show){elm=s$(elm);if(elm){elm.style.display=show?'':'none';}},toggle:function(elm,forceShow){elm=s$(elm);if(elm!==null){if(typeof forceShow!=='undefined'){if(forceShow===true){this.show(elm);}else{this.hide(elm);}}
else{if(elm.style.display==='none'){this.show(elm);}
else{this.hide(elm);}}}},_getRefTag:function(head){if(head.firstElementChild){return head.firstElementChild;}
for(var child=head.firstChild;child;child=child.nextSibling){if(child.nodeType===1){return child;}}
return null;},appendStyleTag:function(selector,style,options){options=SAPO.extendObj({type:'text/css',force:false},options||{});var styles=document.getElementsByTagName("style"),oldStyle=false,setStyle=true,i,l;for(i=0,l=styles.length;i<l;i++){oldStyle=styles[i].innerHTML;if(oldStyle.indexOf(selector)>=0){setStyle=false;}}
if(setStyle){var defStyle=document.createElement("style"),head=document.getElementsByTagName("head")[0],refTag=false,styleStr='';defStyle.type=options.type;styleStr+=selector+" {";styleStr+=style;styleStr+="} ";if(typeof defStyle.styleSheet!=="undefined"){defStyle.styleSheet.cssText=styleStr;}else{defStyle.appendChild(document.createTextNode(styleStr));}
if(options.force){head.appendChild(defStyle);}else{refTag=this._getRefTag(head);if(refTag){head.insertBefore(defStyle,refTag);}}}},appendStylesheet:function(path,options){options=SAPO.extendObj({media:'screen',type:'text/css',force:false},options||{});var refTag,style=document.createElement("link"),head=document.getElementsByTagName("head")[0];style.media=options.media;style.type=options.type;style.href=path;style.rel="Stylesheet";if(options.force){head.appendChild(style);}
else{refTag=this._getRefTag(head);if(refTag){head.insertBefore(style,refTag);}}},_loadingCSSFiles:{},_loadedCSSFiles:{},appendStylesheetCb:function(url,callback){if(!url){return callback(url);}
if(SAPO.Dom.Css._loadedCSSFiles[url]){return callback(url);}
var cbs=SAPO.Dom.Css._loadingCSSFiles[url];if(cbs){return cbs.push(callback);}
SAPO.Dom.Css._loadingCSSFiles[url]=[callback];var linkEl=document.createElement('link');linkEl.type='text/css';linkEl.rel='stylesheet';linkEl.href=url;var headEl=document.getElementsByTagName('head')[0];headEl.appendChild(linkEl);var innerCb=function(frameEl){var url=this;SAPO.Dom.Css._loadedCSSFiles[url]=true;var callbacks=SAPO.Dom.Css._loadingCSSFiles[url];for(var i=0,f=callbacks.length;i<f;++i){callbacks[i](url);}
delete SAPO.Dom.Css._loadingCSSFiles[url];if(frameEl&&isNode(frameEl)){frameEl.parentNode.removeChild(frameEl);}};if(!isNativeAndroidBrowser){var imgEl=document.createElement('img');imgEl.onerror=innerCb.bindObj(url);imgEl.src=url;}
else{var frameEl=document.createElement('iframe');frameEl.style.display='none';frameEl.onerror=innerCb;frameEl.onload=innerCb.bindObj(url,frameEl);document.body.appendChild(frameEl);}},decToHex:function(dec){var normalizeTo2=function(val){if(val.length===1){val='0'+val;}
val=val.toUpperCase();return val;};if(typeof dec==='object'){var rDec=normalizeTo2(parseInt(dec.r,10).toString(16));var gDec=normalizeTo2(parseInt(dec.g,10).toString(16));var bDec=normalizeTo2(parseInt(dec.b,10).toString(16));return rDec+gDec+bDec;}
else{dec+='';var rgb=dec.match(/\((\d+),\s?(\d+),\s?(\d+)\)/);if(rgb!==null){return normalizeTo2(parseInt(rgb[1],10).toString(16))+
normalizeTo2(parseInt(rgb[2],10).toString(16))+
normalizeTo2(parseInt(rgb[3],10).toString(16));}
else{return normalizeTo2(parseInt(dec,10).toString(16));}}},hexToDec:function(hex){if(hex.indexOf('#')===0){hex=hex.substr(1);}
if(hex.length===6){return{r:parseInt(hex.substr(0,2),16),g:parseInt(hex.substr(2,2),16),b:parseInt(hex.substr(4,2),16)};}
else if(hex.length===3){return{r:parseInt(hex.charAt(0)+hex.charAt(0),16),g:parseInt(hex.charAt(1)+hex.charAt(1),16),b:parseInt(hex.charAt(2)+hex.charAt(2),16)};}
else if(hex.length<=2){return parseInt(hex,16);}},getPropertyFromStylesheet:function(selector,property){var rule=SAPO.Dom.Css.getRuleFromStylesheet(selector);if(rule){return rule.style[property];}
return null;},getPropertyFromStylesheet2:function(selector,property){var rules=SAPO.Dom.Css.getRulesFromStylesheet(selector);rules.forEach(function(rule){var x=rule.style[property];if(x!==null&&x!==undefined){return x;}});return null;},getRuleFromStylesheet:function(selector){var sheet,rules,ri,rf,rule;var s=document.styleSheets;if(!s){return null;}
for(var si=0,sf=document.styleSheets.length;si<sf;++si){sheet=document.styleSheets[si];rules=sheet.rules?sheet.rules:sheet.cssRules;if(!rules){return null;}
for(ri=0,rf=rules.length;ri<rf;++ri){rule=rules[ri];if(!rule.selectorText){continue;}
if(rule.selectorText===selector){return rule;}}}
return null;},getRulesFromStylesheet:function(selector){var res=[];var sheet,rules,ri,rf,rule;var s=document.styleSheets;if(!s){return res;}
for(var si=0,sf=document.styleSheets.length;si<sf;++si){sheet=document.styleSheets[si];rules=sheet.rules?sheet.rules:sheet.cssRules;if(!rules){return null;}
for(ri=0,rf=rules.length;ri<rf;++ri){rule=rules[ri];if(!rule.selectorText){continue;}
if(rule.selectorText===selector){res.push(rule);}}}
return res;},getPropertiesFromRule:function(selector){var rule=this.getRuleFromStylesheet(selector);var props={};var prop,i,f;rule=rule.style.cssText;var parts=rule.split(';');var steps,val,pre,pos;for(i=0,f=parts.length;i<f;++i){if(parts[i].charAt(0)===' '){parts[i]=parts[i].substring(1);}
steps=parts[i].split(':');prop=this._camelCase(steps[0].toLowerCase());val=steps[1];if(val){val=val.substring(1);if(prop==='padding'||prop==='margin'||prop==='borderWidth'){if(prop==='borderWidth'){pre='border';pos='Width';}
else{pre=prop;pos='';}
if(val.indexOf(' ')!==-1){val=val.split(' ');props[pre+'Top'+pos]=val[0];props[pre+'Bottom'+pos]=val[0];props[pre+'Left'+pos]=val[1];props[pre+'Right'+pos]=val[1];}
else{props[pre+'Top'+pos]=val;props[pre+'Bottom'+pos]=val;props[pre+'Left'+pos]=val;props[pre+'Right'+pos]=val;}}
else if(prop==='borderRadius'){if(val.indexOf(' ')!==-1){val=val.split(' ');props.borderTopLeftRadius=val[0];props.borderBottomRightRadius=val[0];props.borderTopRightRadius=val[1];props.borderBottomLeftRadius=val[1];}
else{props.borderTopLeftRadius=val;props.borderTopRightRadius=val;props.borderBottomLeftRadius=val;props.borderBottomRightRadius=val;}}
else{props[prop]=val;}}}
return props;},changeFontSize:function(selector,delta,op,minVal,maxVal){var e;if(typeof selector!=='string'){e='1st argument must be a CSS selector rule.';}
else if(typeof delta!=='number'){e='2nd argument must be a number.';}
else if(op!==undefined&&op!=='+'&&op!=='*'){e='3rd argument must be one of "+", "*".';}
else if(minVal!==undefined&&(typeof minVal!=='number'||minVal<=0)){e='4th argument must be a positive number.';}
else if(maxVal!==undefined&&(typeof maxVal!=='number'||maxVal<maxVal)){e='5th argument must be a positive number greater than minValue.';}
if(e){throw new TypeError(e);}
var val,el,els=SAPO.Dom.Selector.select(selector);if(minVal===undefined){minVal=1;}
op=(op==='*')?function(a,b){return a*b;}:function(a,b){return a+b;};for(var i=0,f=els.length;i<f;++i){el=els[i];val=parseFloat(SAPO.Dom.Css.getStyle(el,'fontSize'));val=op(val,delta);if(val<minVal){continue;}
if(typeof maxVal==='number'&&val>maxVal){continue;}
el.style.fontSize=val+'px';}}};})(window);
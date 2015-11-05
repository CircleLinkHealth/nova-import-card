<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" ng-app> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" ng-app> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" ng-app> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" ng-app> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Blue Button Health Record </title>
    <meta name="description" content="Patient health records in the Blue Button format.">
    <meta name="viewport" content="width=device-width">
    <meta name="author" content="M. Jackson Wilkinson / jackson@jounce.net / @mjacksonw">
    <!-- Injected styles -->
    <style media="screen, projection">
        /* stylesheets/normalize.css */ /*!normalize.css v1.0.1 | MIT License | git.io/normalize */ article,aside,details,figcaption,figure,footer,header,hgroup,nav,section,summary{display:block}audio,canvas,video{display:inline-block;*display:inline;*zoom:1}audio:not([controls]){display:none;height:0}[hidden]{display:none}html{font-size:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}html,button,input,select,textarea{font-family:sans-serif}body{margin:0}a:focus{outline:thin dotted}a:active,a:hover{outline:0}h1{font-size:2em;margin:.67em 0}h2{font-size:1.5em;margin:.83em 0}h3{font-size:1.17em;margin:1em 0}h4{font-size:1em;margin:1.33em 0}h5{font-size:.83em;margin:1.67em 0}h6{font-size:.75em;margin:2.33em 0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:bold}blockquote{margin:1em 40px}dfn{font-style:italic}mark{background:#ff0;color:#000}p,pre{margin:1em 0}code,kbd,pre,samp{font-family:monospace,serif;_font-family:'courier new',monospace;font-size:1em}pre{white-space:pre;white-space:pre-wrap;word-wrap:break-word}q{quotes:none}q:before,q:after{content:'';content:none}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-0.5em}sub{bottom:-0.25em}dl,menu,ol,ul{margin:1em 0}dd{margin:0 0 0 40px}menu,ol,ul{padding:0 0 0 40px}nav ul,nav ol{list-style:none;list-style-image:none}img{border:0;-ms-interpolation-mode:bicubic}svg:not(:root){overflow:hidden}figure{margin:0}form{margin:0}fieldset{border:1px solid #c0c0c0;margin:0 2px;padding:.35em .625em .75em}legend{border:0;padding:0;white-space:normal;*margin-left:-7px}button,input,select,textarea{font-size:100%;margin:0;vertical-align:baseline;*vertical-align:middle}button,input{line-height:normal}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer;*overflow:visible}button[disabled],input[disabled]{cursor:default}input[type="checkbox"],input[type="radio"]{box-sizing:border-box;padding:0;*height:13px;*width:13px}input[type="search"]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}textarea{overflow:auto;vertical-align:top}table{border-collapse:collapse;border-spacing:0}
        /* stylesheets/screen.css */ body{font-family:Helvetica Neue,Arial,Helvetica,sans-serif;color:#333}section.bb-template{width:800px;margin:0 auto;display:none}.panel,div#demographics,div#allergies,div#medications,div#immunizations,div#history,div#labs{padding:50px 0;border-bottom:1px solid #ddd}.panel h1,div#demographics h1,div#allergies h1,div#medications h1,div#immunizations h1,div#history h1,div#labs h1{font-size:30px;margin-bottom:30px}a{color:inherit;text-decoration:none}a:hover{text-decoration:underline}ul.pills{overflow:hidden;*zoom:1;margin:0;padding:10px 0 0 0}ul.pills li{float:left;display:inline-block;padding:2px 7px;margin-right:5px;background:#ddd;-webkit-border-radius:20px;-moz-border-radius:20px;-ms-border-radius:20px;-o-border-radius:20px;border-radius:20px;font-size:12px;border:1px solid #ccc}.listless,nav#primaryNav ul,div#allergies ul,div#medications ul,div#immunizations>ul,div#history>ul,div#labs>ul,div#labs ul.results{overflow:hidden;*zoom:1;list-style-type:none;margin:0;padding:0}.module,div#allergies li,div#medications ul>li,div#immunizations>ul>li{float:left;margin:0 20px 20px 0;padding:20px;width:380px;background:#f8f8f8;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}.module h2,div#allergies li h2,div#medications ul>li h2,div#immunizations>ul>li h2,.module p,div#allergies li p,div#medications ul>li p,div#immunizations>ul>li p{margin-top:0;margin-bottom:2px;font-size:18px}.module p,div#allergies li p,div#medications ul>li p,div#immunizations>ul>li p,.module header small,div#allergies li header small,div#medications ul>li header small,div#immunizations>ul>li header small{font-weight:300;font-size:18px}.container{overflow:hidden;*zoom:1;width:800px;margin:0 auto}nav#primaryNav{position:fixed;top:0;left:0;width:100%;height:50px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;background:#eee;background-image:-webkit-gradient(linear,50% 0,50% 100%,color-stop(0%,#eee),color-stop(100%,#ddd));background-image:-webkit-linear-gradient(top,#eee,#ddd);background-image:-moz-linear-gradient(top,#eee,#ddd);background-image:-o-linear-gradient(top,#eee,#ddd);background-image:linear-gradient(top,#eee,#ddd);-webkit-box-shadow:0 0 15px rgba(0,0,0,0.1);-moz-box-shadow:0 0 15px rgba(0,0,0,0.1);box-shadow:0 0 15px rgba(0,0,0,0.1);z-index:900}nav#primaryNav h1{margin:12px 0;padding:0;width:350px;float:left;font-size:20px;font-weight:normal;color:#bbb}nav#primaryNav ul{float:right}nav#primaryNav ul li{padding:18px 0;float:left;margin-right:15px;font-size:11px;text-transform:uppercase;color:#666}nav#primaryNav ul li:hover{border-bottom:2px solid #aaa}nav#primaryNav ul li a:hover{text-decoration:none}div#demographics{font-size:26px;font-weight:300}div#demographics h1{font-size:50px}div#demographics strong.severe{color:#f8f8f8;background:#c33}div#demographics dl{overflow:hidden;*zoom:1;list-style-type:none;font-size:18px}div#demographics dl li{width:25%;float:left}div#demographics dt{text-transform:lowercase;color:#666}div#demographics dd{font-weight:bold;margin-left:0}div#allergies li.allergy-severe{background:#c33;color:#f8f8f8}div#allergies li.allergy-moderate{background:#e70;color:#f8f8f8}div#medications ul>li{border:1px solid #ddd}div#medications ul>li.odd{clear:left}div#medications ul>li dl{overflow:hidden;*zoom:1;font-size:13px}div#medications ul>li dl li{width:50%;float:left}div#medications ul>li dl dt{font-weight:300;color:#666}div#medications ul>li dl dd{margin:0;font-weight:bold}div#immunizations>ul>li{border:1px solid #ddd}div#history>ul{padding-left:40px;margin-left:20px;border-left:1px solid #ddd;z-index:1}div#history>ul>li:before{content:".";display:block;position:absolute;background:#666;height:35px;width:35px;text-indent:100%;overflow:hidden;margin-left:-60px;z-index:999}div#history>ul>li h2{font-size:18px;font-weight:bold;margin-top:0;padding:6px 0;margin-bottom:20px}div#history>ul>li dl>li{margin-bottom:30px}div#history>ul>li dt{color:#666;font-size:20px;font-weight:300;text-transform:lowercase}div#history>ul>li dd{color:#666;font-size:20px;margin:0;padding:0;font-weight:300}div#history>ul>li dd.head{font-size:24px;color:#333;font-weight:bold}div#history>ul>li dd.head:before{content:".";display:block;position:absolute;background:#666;height:15px;width:15px;text-indent:100%;overflow:hidden;margin-left:-48px;margin-top:10px;z-index:999}div#labs h2 .date{float:right;font-weight:300;color:#666}div#labs ul.results{display:table;width:100%;border:1px solid #ddd;border-right:none;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}div#labs ul.results li{display:table-row}div#labs ul.results li.header span{background:#ddd;font-weight:bold}div#labs ul.results span{display:table-cell;padding:20px;border-right:1px solid #ddd;color:#666}div#labs ul.results span.lab-component{font-weight:bold}div#loader{display:none;width:304px;margin:100px auto;text-align:center;color:#ccc}div#loader #warningGradientOuterBarG{height:38px;width:304px;border:2px solid #eee;overflow:hidden;background-color:#f8f8f8;background-image:-webkit-gradient(linear,50% 0,50% 100%,color-stop(0%,#f8f8f8),color-stop(100%,#eee));background-image:-webkit-linear-gradient(top,#f8f8f8,#eee);background-image:-moz-linear-gradient(top,#f8f8f8,#eee);background-image:-o-linear-gradient(top,#f8f8f8,#eee);background-image:linear-gradient(top,#f8f8f8,#eee)}div#loader .warningGradientBarLineG{background-color:#f8f8f8;float:left;width:27px;height:228px;margin-right:46px;margin-top:-53px;-webkit-transform:rotate(45deg);-moz-transform:rotate(45deg);-ms-transform:rotate(45deg);-o-transform:rotate(45deg);transform:rotate(45deg)}div#loader .warningGradientAnimationG{width:448px;-moz-animation-name:warningGradientAnimationG;-moz-animation-duration:1.3s;-moz-animation-iteration-count:infinite;-moz-animation-timing-function:linear;-webkit-animation-name:warningGradientAnimationG;-webkit-animation-duration:1.3s;-webkit-animation-iteration-count:infinite;-webkit-animation-timing-function:linear;-ms-animation-name:warningGradientAnimationG;-ms-animation-duration:1.3s;-ms-animation-iteration-count:infinite;-ms-animation-timing-function:linear;-o-animation-name:warningGradientAnimationG;-o-animation-duration:1.3s;-o-animation-iteration-count:infinite;-o-animation-timing-function:linear;animation-name:warningGradientAnimationG;animation-duration:1.3s;animation-iteration-count:infinite;animation-timing-function:linear}@-moz-keyframes warningGradientAnimationG{0%{margin-left:-72px}100%{margin-left:0}}@-webkit-keyframes warningGradientAnimationG{0%{margin-left:-72px}100%{margin-left:0}}@-ms-keyframes warningGradientAnimationG{0%{margin-left:-72px}100%{margin-left:0}}@-o-keyframes warningGradientAnimationG{0%{margin-left:-72px}100%{margin-left:0}}@keyframes warningGradientAnimationG{0%{margin-left:-72px}100%{margin-left:0}}

    </style>
    <style media="print">

    </style>
    <!-- Injected scripts -->
    <script>
        /* js/01-modernizr-2.6.2.min.js */ /* Modernizr 2.6.2 (Custom Build) | MIT & BSD - Build: http://modernizr.com/download/#-fontface-backgroundsize-borderimage-borderradius-boxshadow-flexbox-hsla-multiplebgs-opacity-rgba-textshadow-cssanimations-csscolumns-generatedcontent-cssgradients-cssreflections-csstransforms-csstransforms3d-csstransitions-applicationcache-canvas-canvastext-draganddrop-hashchange-history-audio-video-indexeddb-input-inputtypes-localstorage-postmessage-sessionstorage-websockets-websqldatabase-webworkers-geolocation-inlinesvg-smil-svg-svgclippaths-touch-webgl-shiv-mq-cssclasses-addtest-prefixed-teststyles-testprop-testallprops-hasevent-prefixes-domprefixes-load */;window.Modernizr=function(a,b,c){function D(a){j.cssText=a}function E(a,b){return D(n.join(a+";")+(b||""))}function F(a,b){return typeof a===b}function G(a,b){return!!~(""+a).indexOf(b)}function H(a,b){for(var d in a){var e=a[d];if(!G(e,"-")&&j[e]!==c)return b=="pfx"?e:!0}return!1}function I(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:F(f,"function")?f.bind(d||b):f}return!1}function J(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),e=(a+" "+p.join(d+" ")+d).split(" ");return F(b,"string")||F(b,"undefined")?H(e,b):(e=(a+" "+q.join(d+" ")+d).split(" "),I(e,b,c))}function K(){e.input=function(c){for(var d=0,e=c.length;d<e;d++)u[c[d]]=c[d]in k;return u.list&&(u.list=!!b.createElement("datalist")&&!!a.HTMLDataListElement),u}("autocomplete autofocus list placeholder max min multiple pattern required step".split(" ")),e.inputtypes=function(a){for(var d=0,e,f,h,i=a.length;d<i;d++)k.setAttribute("type",f=a[d]),e=k.type!=="text",e&&(k.value=l,k.style.cssText="position:absolute;visibility:hidden;",/^range$/.test(f)&&k.style.WebkitAppearance!==c?(g.appendChild(k),h=b.defaultView,e=h.getComputedStyle&&h.getComputedStyle(k,null).WebkitAppearance!=="textfield"&&k.offsetHeight!==0,g.removeChild(k)):/^(search|tel)$/.test(f)||(/^(url|email)$/.test(f)?e=k.checkValidity&&k.checkValidity()===!1:e=k.value!=l)),t[a[d]]=!!e;return t}("search tel url email datetime date month week time datetime-local number range color".split(" "))}var d="2.6.2",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k=b.createElement("input"),l=":)",m={}.toString,n=" -webkit- -moz- -o- -ms- ".split(" "),o="Webkit Moz O ms",p=o.split(" "),q=o.toLowerCase().split(" "),r={svg:"http://www.w3.org/2000/svg"},s={},t={},u={},v=[],w=v.slice,x,y=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",'<style id="s',h,'">',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},z=function(b){var c=a.matchMedia||a.msMatchMedia;if(c)return c(b).matches;var d;return y("@media "+b+" { #"+h+" { position: absolute; } }",function(b){d=(a.getComputedStyle?getComputedStyle(b,null):b.currentStyle)["position"]=="absolute"}),d},A=function(){function d(d,e){e=e||b.createElement(a[d]||"div"),d="on"+d;var f=d in e;return f||(e.setAttribute||(e=b.createElement("div")),e.setAttribute&&e.removeAttribute&&(e.setAttribute(d,""),f=F(e[d],"function"),F(e[d],"undefined")||(e[d]=c),e.removeAttribute(d))),e=null,f}var a={select:"input",change:"input",submit:"form",reset:"form",error:"img",load:"img",abort:"img"};return d}(),B={}.hasOwnProperty,C;!F(B,"undefined")&&!F(B.call,"undefined")?C=function(a,b){return B.call(a,b)}:C=function(a,b){return b in a&&F(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=w.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(w.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(w.call(arguments)))};return e}),s.flexbox=function(){return J("flexWrap")},s.canvas=function(){var a=b.createElement("canvas");return!!a.getContext&&!!a.getContext("2d")},s.canvastext=function(){return!!e.canvas&&!!F(b.createElement("canvas").getContext("2d").fillText,"function")},s.webgl=function(){return!!a.WebGLRenderingContext},s.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:y(["@media (",n.join("touch-enabled),("),h,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=a.offsetTop===9}),c},s.geolocation=function(){return"geolocation"in navigator},s.postmessage=function(){return!!a.postMessage},s.websqldatabase=function(){return!!a.openDatabase},s.indexedDB=function(){return!!J("indexedDB",a)},s.hashchange=function(){return A("hashchange",a)&&(b.documentMode===c||b.documentMode>7)},s.history=function(){return!!a.history&&!!history.pushState},s.draganddrop=function(){var a=b.createElement("div");return"draggable"in a||"ondragstart"in a&&"ondrop"in a},s.websockets=function(){return"WebSocket"in a||"MozWebSocket"in a},s.rgba=function(){return D("background-color:rgba(150,255,150,.5)"),G(j.backgroundColor,"rgba")},s.hsla=function(){return D("background-color:hsla(120,40%,100%,.5)"),G(j.backgroundColor,"rgba")||G(j.backgroundColor,"hsla")},s.multiplebgs=function(){return D("background:url(https://),url(https://),red url(https://)"),/(url\s*\(.*?){3}/.test(j.background)},s.backgroundsize=function(){return J("backgroundSize")},s.borderimage=function(){return J("borderImage")},s.borderradius=function(){return J("borderRadius")},s.boxshadow=function(){return J("boxShadow")},s.textshadow=function(){return b.createElement("div").style.textShadow===""},s.opacity=function(){return E("opacity:.55"),/^0.55$/.test(j.opacity)},s.cssanimations=function(){return J("animationName")},s.csscolumns=function(){return J("columnCount")},s.cssgradients=function(){var a="background-image:",b="gradient(linear,left top,right bottom,from(#9f9),to(white));",c="linear-gradient(left top,#9f9, white);";return D((a+"-webkit- ".split(" ").join(b+a)+n.join(c+a)).slice(0,-a.length)),G(j.backgroundImage,"gradient")},s.cssreflections=function(){return J("boxReflect")},s.csstransforms=function(){return!!J("transform")},s.csstransforms3d=function(){var a=!!J("perspective");return a&&"webkitPerspective"in g.style&&y("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}",function(b,c){a=b.offsetLeft===9&&b.offsetHeight===3}),a},s.csstransitions=function(){return J("transition")},s.fontface=function(){var a;return y('@font-face {font-family:"font";src:url("https://")}',function(c,d){var e=b.getElementById("smodernizr"),f=e.sheet||e.styleSheet,g=f?f.cssRules&&f.cssRules[0]?f.cssRules[0].cssText:f.cssText||"":"";a=/src/i.test(g)&&g.indexOf(d.split(" ")[0])===0}),a},s.generatedcontent=function(){var a;return y(["#",h,"{font:0/0 a}#",h,':after{content:"',l,'";visibility:hidden;font:3px/1 a}'].join(""),function(b){a=b.offsetHeight>=3}),a},s.video=function(){var a=b.createElement("video"),c=!1;try{if(c=!!a.canPlayType)c=new Boolean(c),c.ogg=a.canPlayType('video/ogg; codecs="theora"').replace(/^no$/,""),c.h264=a.canPlayType('video/mp4; codecs="avc1.42E01E"').replace(/^no$/,""),c.webm=a.canPlayType('video/webm; codecs="vp8, vorbis"').replace(/^no$/,"")}catch(d){}return c},s.audio=function(){var a=b.createElement("audio"),c=!1;try{if(c=!!a.canPlayType)c=new Boolean(c),c.ogg=a.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,""),c.mp3=a.canPlayType("audio/mpeg;").replace(/^no$/,""),c.wav=a.canPlayType('audio/wav; codecs="1"').replace(/^no$/,""),c.m4a=(a.canPlayType("audio/x-m4a;")||a.canPlayType("audio/aac;")).replace(/^no$/,"")}catch(d){}return c},s.localstorage=function(){try{return localStorage.setItem(h,h),localStorage.removeItem(h),!0}catch(a){return!1}},s.sessionstorage=function(){try{return sessionStorage.setItem(h,h),sessionStorage.removeItem(h),!0}catch(a){return!1}},s.webworkers=function(){return!!a.Worker},s.applicationcache=function(){return!!a.applicationCache},s.svg=function(){return!!b.createElementNS&&!!b.createElementNS(r.svg,"svg").createSVGRect},s.inlinesvg=function(){var a=b.createElement("div");return a.innerHTML="<svg/>",(a.firstChild&&a.firstChild.namespaceURI)==r.svg},s.smil=function(){return!!b.createElementNS&&/SVGAnimate/.test(m.call(b.createElementNS(r.svg,"animate")))},s.svgclippaths=function(){return!!b.createElementNS&&/SVGClipPath/.test(m.call(b.createElementNS(r.svg,"clipPath")))};for(var L in s)C(s,L)&&(x=L.toLowerCase(),e[x]=s[L](),v.push((e[x]?"":"no-")+x));return e.input||K(),e.addTest=function(a,b){if(typeof a=="object")for(var d in a)C(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},D(""),i=k=null,function(a,b){function k(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function l(){var a=r.elements;return typeof a=="string"?a.split(" "):a}function m(a){var b=i[a[g]];return b||(b={},h++,a[g]=h,i[h]=b),b}function n(a,c,f){c||(c=b);if(j)return c.createElement(a);f||(f=m(c));var g;return f.cache[a]?g=f.cache[a].cloneNode():e.test(a)?g=(f.cache[a]=f.createElem(a)).cloneNode():g=f.createElem(a),g.canHaveChildren&&!d.test(a)?f.frag.appendChild(g):g}function o(a,c){a||(a=b);if(j)return a.createDocumentFragment();c=c||m(a);var d=c.frag.cloneNode(),e=0,f=l(),g=f.length;for(;e<g;e++)d.createElement(f[e]);return d}function p(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return r.shivMethods?n(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+l().join().replace(/\w+/g,function(a){return b.createElem(a),b.frag.createElement(a),'c("'+a+'")'})+");return n}")(r,b.frag)}function q(a){a||(a=b);var c=m(a);return r.shivCSS&&!f&&!c.hasCSS&&(c.hasCSS=!!k(a,"article,aside,figcaption,figure,footer,header,hgroup,nav,section{display:block}mark{background:#FF0;color:#000}")),j||p(a,c),a}var c=a.html5||{},d=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,e=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,f,g="_html5shiv",h=0,i={},j;(function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",f="hidden"in a,j=a.childNodes.length==1||function(){b.createElement("a");var a=b.createDocumentFragment();return typeof a.cloneNode=="undefined"||typeof a.createDocumentFragment=="undefined"||typeof a.createElement=="undefined"}()}catch(c){f=!0,j=!0}})();var r={elements:c.elements||"abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video",shivCSS:c.shivCSS!==!1,supportsUnknownElements:j,shivMethods:c.shivMethods!==!1,type:"default",shivDocument:q,createElement:n,createDocumentFragment:o};a.html5=r,q(b)}(this,b),e._version=d,e._prefixes=n,e._domPrefixes=q,e._cssomPrefixes=p,e.mq=z,e.hasEvent=A,e.testProp=function(a){return H([a])},e.testAllProps=J,e.testStyles=y,e.prefixed=function(a,b,c){return b?J(a,b,c):J(a,"pfx")},g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+v.join(" "):""),e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};
        /* js/02-jquery-1.9.0.min.js */ /*! jQuery v1.9.0 | (c) 2005, 2012 jQuery Foundation, Inc. | jquery.org/license */(function(e,t){"use strict";function n(e){var t=e.length,n=st.type(e);return st.isWindow(e)?!1:1===e.nodeType&&t?!0:"array"===n||"function"!==n&&(0===t||"number"==typeof t&&t>0&&t-1 in e)}function r(e){var t=Tt[e]={};return st.each(e.match(lt)||[],function(e,n){t[n]=!0}),t}function i(e,n,r,i){if(st.acceptData(e)){var o,a,s=st.expando,u="string"==typeof n,l=e.nodeType,c=l?st.cache:e,f=l?e[s]:e[s]&&s;if(f&&c[f]&&(i||c[f].data)||!u||r!==t)return f||(l?e[s]=f=K.pop()||st.guid++:f=s),c[f]||(c[f]={},l||(c[f].toJSON=st.noop)),("object"==typeof n||"function"==typeof n)&&(i?c[f]=st.extend(c[f],n):c[f].data=st.extend(c[f].data,n)),o=c[f],i||(o.data||(o.data={}),o=o.data),r!==t&&(o[st.camelCase(n)]=r),u?(a=o[n],null==a&&(a=o[st.camelCase(n)])):a=o,a}}function o(e,t,n){if(st.acceptData(e)){var r,i,o,a=e.nodeType,u=a?st.cache:e,l=a?e[st.expando]:st.expando;if(u[l]){if(t&&(r=n?u[l]:u[l].data)){st.isArray(t)?t=t.concat(st.map(t,st.camelCase)):t in r?t=[t]:(t=st.camelCase(t),t=t in r?[t]:t.split(" "));for(i=0,o=t.length;o>i;i++)delete r[t[i]];if(!(n?s:st.isEmptyObject)(r))return}(n||(delete u[l].data,s(u[l])))&&(a?st.cleanData([e],!0):st.support.deleteExpando||u!=u.window?delete u[l]:u[l]=null)}}}function a(e,n,r){if(r===t&&1===e.nodeType){var i="data-"+n.replace(Nt,"-$1").toLowerCase();if(r=e.getAttribute(i),"string"==typeof r){try{r="true"===r?!0:"false"===r?!1:"null"===r?null:+r+""===r?+r:wt.test(r)?st.parseJSON(r):r}catch(o){}st.data(e,n,r)}else r=t}return r}function s(e){var t;for(t in e)if(("data"!==t||!st.isEmptyObject(e[t]))&&"toJSON"!==t)return!1;return!0}function u(){return!0}function l(){return!1}function c(e,t){do e=e[t];while(e&&1!==e.nodeType);return e}function f(e,t,n){if(t=t||0,st.isFunction(t))return st.grep(e,function(e,r){var i=!!t.call(e,r,e);return i===n});if(t.nodeType)return st.grep(e,function(e){return e===t===n});if("string"==typeof t){var r=st.grep(e,function(e){return 1===e.nodeType});if(Wt.test(t))return st.filter(t,r,!n);t=st.filter(t,r)}return st.grep(e,function(e){return st.inArray(e,t)>=0===n})}function p(e){var t=zt.split("|"),n=e.createDocumentFragment();if(n.createElement)for(;t.length;)n.createElement(t.pop());return n}function d(e,t){return e.getElementsByTagName(t)[0]||e.appendChild(e.ownerDocument.createElement(t))}function h(e){var t=e.getAttributeNode("type");return e.type=(t&&t.specified)+"/"+e.type,e}function g(e){var t=nn.exec(e.type);return t?e.type=t[1]:e.removeAttribute("type"),e}function m(e,t){for(var n,r=0;null!=(n=e[r]);r++)st._data(n,"globalEval",!t||st._data(t[r],"globalEval"))}function y(e,t){if(1===t.nodeType&&st.hasData(e)){var n,r,i,o=st._data(e),a=st._data(t,o),s=o.events;if(s){delete a.handle,a.events={};for(n in s)for(r=0,i=s[n].length;i>r;r++)st.event.add(t,n,s[n][r])}a.data&&(a.data=st.extend({},a.data))}}function v(e,t){var n,r,i;if(1===t.nodeType){if(n=t.nodeName.toLowerCase(),!st.support.noCloneEvent&&t[st.expando]){r=st._data(t);for(i in r.events)st.removeEvent(t,i,r.handle);t.removeAttribute(st.expando)}"script"===n&&t.text!==e.text?(h(t).text=e.text,g(t)):"object"===n?(t.parentNode&&(t.outerHTML=e.outerHTML),st.support.html5Clone&&e.innerHTML&&!st.trim(t.innerHTML)&&(t.innerHTML=e.innerHTML)):"input"===n&&Zt.test(e.type)?(t.defaultChecked=t.checked=e.checked,t.value!==e.value&&(t.value=e.value)):"option"===n?t.defaultSelected=t.selected=e.defaultSelected:("input"===n||"textarea"===n)&&(t.defaultValue=e.defaultValue)}}function b(e,n){var r,i,o=0,a=e.getElementsByTagName!==t?e.getElementsByTagName(n||"*"):e.querySelectorAll!==t?e.querySelectorAll(n||"*"):t;if(!a)for(a=[],r=e.childNodes||e;null!=(i=r[o]);o++)!n||st.nodeName(i,n)?a.push(i):st.merge(a,b(i,n));return n===t||n&&st.nodeName(e,n)?st.merge([e],a):a}function x(e){Zt.test(e.type)&&(e.defaultChecked=e.checked)}function T(e,t){if(t in e)return t;for(var n=t.charAt(0).toUpperCase()+t.slice(1),r=t,i=Nn.length;i--;)if(t=Nn[i]+n,t in e)return t;return r}function w(e,t){return e=t||e,"none"===st.css(e,"display")||!st.contains(e.ownerDocument,e)}function N(e,t){for(var n,r=[],i=0,o=e.length;o>i;i++)n=e[i],n.style&&(r[i]=st._data(n,"olddisplay"),t?(r[i]||"none"!==n.style.display||(n.style.display=""),""===n.style.display&&w(n)&&(r[i]=st._data(n,"olddisplay",S(n.nodeName)))):r[i]||w(n)||st._data(n,"olddisplay",st.css(n,"display")));for(i=0;o>i;i++)n=e[i],n.style&&(t&&"none"!==n.style.display&&""!==n.style.display||(n.style.display=t?r[i]||"":"none"));return e}function C(e,t,n){var r=mn.exec(t);return r?Math.max(0,r[1]-(n||0))+(r[2]||"px"):t}function k(e,t,n,r,i){for(var o=n===(r?"border":"content")?4:"width"===t?1:0,a=0;4>o;o+=2)"margin"===n&&(a+=st.css(e,n+wn[o],!0,i)),r?("content"===n&&(a-=st.css(e,"padding"+wn[o],!0,i)),"margin"!==n&&(a-=st.css(e,"border"+wn[o]+"Width",!0,i))):(a+=st.css(e,"padding"+wn[o],!0,i),"padding"!==n&&(a+=st.css(e,"border"+wn[o]+"Width",!0,i)));return a}function E(e,t,n){var r=!0,i="width"===t?e.offsetWidth:e.offsetHeight,o=ln(e),a=st.support.boxSizing&&"border-box"===st.css(e,"boxSizing",!1,o);if(0>=i||null==i){if(i=un(e,t,o),(0>i||null==i)&&(i=e.style[t]),yn.test(i))return i;r=a&&(st.support.boxSizingReliable||i===e.style[t]),i=parseFloat(i)||0}return i+k(e,t,n||(a?"border":"content"),r,o)+"px"}function S(e){var t=V,n=bn[e];return n||(n=A(e,t),"none"!==n&&n||(cn=(cn||st("<iframe frameborder='0' width='0' height='0'/>").css("cssText","display:block !important")).appendTo(t.documentElement),t=(cn[0].contentWindow||cn[0].contentDocument).document,t.write("<!doctype html><html><body>"),t.close(),n=A(e,t),cn.detach()),bn[e]=n),n}function A(e,t){var n=st(t.createElement(e)).appendTo(t.body),r=st.css(n[0],"display");return n.remove(),r}function j(e,t,n,r){var i;if(st.isArray(t))st.each(t,function(t,i){n||kn.test(e)?r(e,i):j(e+"["+("object"==typeof i?t:"")+"]",i,n,r)});else if(n||"object"!==st.type(t))r(e,t);else for(i in t)j(e+"["+i+"]",t[i],n,r)}function D(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match(lt)||[];if(st.isFunction(n))for(;r=o[i++];)"+"===r[0]?(r=r.slice(1)||"*",(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function L(e,n,r,i){function o(u){var l;return a[u]=!0,st.each(e[u]||[],function(e,u){var c=u(n,r,i);return"string"!=typeof c||s||a[c]?s?!(l=c):t:(n.dataTypes.unshift(c),o(c),!1)}),l}var a={},s=e===$n;return o(n.dataTypes[0])||!a["*"]&&o("*")}function H(e,n){var r,i,o=st.ajaxSettings.flatOptions||{};for(r in n)n[r]!==t&&((o[r]?e:i||(i={}))[r]=n[r]);return i&&st.extend(!0,e,i),e}function M(e,n,r){var i,o,a,s,u=e.contents,l=e.dataTypes,c=e.responseFields;for(o in c)o in r&&(n[c[o]]=r[o]);for(;"*"===l[0];)l.shift(),i===t&&(i=e.mimeType||n.getResponseHeader("Content-Type"));if(i)for(o in u)if(u[o]&&u[o].test(i)){l.unshift(o);break}if(l[0]in r)a=l[0];else{for(o in r){if(!l[0]||e.converters[o+" "+l[0]]){a=o;break}s||(s=o)}a=a||s}return a?(a!==l[0]&&l.unshift(a),r[a]):t}function q(e,t){var n,r,i,o,a={},s=0,u=e.dataTypes.slice(),l=u[0];if(e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u[1])for(n in e.converters)a[n.toLowerCase()]=e.converters[n];for(;i=u[++s];)if("*"!==i){if("*"!==l&&l!==i){if(n=a[l+" "+i]||a["* "+i],!n)for(r in a)if(o=r.split(" "),o[1]===i&&(n=a[l+" "+o[0]]||a["* "+o[0]])){n===!0?n=a[r]:a[r]!==!0&&(i=o[0],u.splice(s--,0,i));break}if(n!==!0)if(n&&e["throws"])t=n(t);else try{t=n(t)}catch(c){return{state:"parsererror",error:n?c:"No conversion from "+l+" to "+i}}}l=i}return{state:"success",data:t}}function _(){try{return new e.XMLHttpRequest}catch(t){}}function F(){try{return new e.ActiveXObject("Microsoft.XMLHTTP")}catch(t){}}function O(){return setTimeout(function(){Qn=t}),Qn=st.now()}function B(e,t){st.each(t,function(t,n){for(var r=(rr[t]||[]).concat(rr["*"]),i=0,o=r.length;o>i;i++)if(r[i].call(e,t,n))return})}function P(e,t,n){var r,i,o=0,a=nr.length,s=st.Deferred().always(function(){delete u.elem}),u=function(){if(i)return!1;for(var t=Qn||O(),n=Math.max(0,l.startTime+l.duration-t),r=n/l.duration||0,o=1-r,a=0,u=l.tweens.length;u>a;a++)l.tweens[a].run(o);return s.notifyWith(e,[l,o,n]),1>o&&u?n:(s.resolveWith(e,[l]),!1)},l=s.promise({elem:e,props:st.extend({},t),opts:st.extend(!0,{specialEasing:{}},n),originalProperties:t,originalOptions:n,startTime:Qn||O(),duration:n.duration,tweens:[],createTween:function(t,n){var r=st.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;r>n;n++)l.tweens[n].run(1);return t?s.resolveWith(e,[l,t]):s.rejectWith(e,[l,t]),this}}),c=l.props;for(R(c,l.opts.specialEasing);a>o;o++)if(r=nr[o].call(l,e,c,l.opts))return r;return B(l,c),st.isFunction(l.opts.start)&&l.opts.start.call(e,l),st.fx.timer(st.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always)}function R(e,t){var n,r,i,o,a;for(n in e)if(r=st.camelCase(n),i=t[r],o=e[n],st.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),a=st.cssHooks[r],a&&"expand"in a){o=a.expand(o),delete e[r];for(n in o)n in e||(e[n]=o[n],t[n]=i)}else t[r]=i}function W(e,t,n){var r,i,o,a,s,u,l,c,f,p=this,d=e.style,h={},g=[],m=e.nodeType&&w(e);n.queue||(c=st._queueHooks(e,"fx"),null==c.unqueued&&(c.unqueued=0,f=c.empty.fire,c.empty.fire=function(){c.unqueued||f()}),c.unqueued++,p.always(function(){p.always(function(){c.unqueued--,st.queue(e,"fx").length||c.empty.fire()})})),1===e.nodeType&&("height"in t||"width"in t)&&(n.overflow=[d.overflow,d.overflowX,d.overflowY],"inline"===st.css(e,"display")&&"none"===st.css(e,"float")&&(st.support.inlineBlockNeedsLayout&&"inline"!==S(e.nodeName)?d.zoom=1:d.display="inline-block")),n.overflow&&(d.overflow="hidden",st.support.shrinkWrapBlocks||p.done(function(){d.overflow=n.overflow[0],d.overflowX=n.overflow[1],d.overflowY=n.overflow[2]}));for(r in t)if(o=t[r],Zn.exec(o)){if(delete t[r],u=u||"toggle"===o,o===(m?"hide":"show"))continue;g.push(r)}if(a=g.length){s=st._data(e,"fxshow")||st._data(e,"fxshow",{}),"hidden"in s&&(m=s.hidden),u&&(s.hidden=!m),m?st(e).show():p.done(function(){st(e).hide()}),p.done(function(){var t;st._removeData(e,"fxshow");for(t in h)st.style(e,t,h[t])});for(r=0;a>r;r++)i=g[r],l=p.createTween(i,m?s[i]:0),h[i]=s[i]||st.style(e,i),i in s||(s[i]=l.start,m&&(l.end=l.start,l.start="width"===i||"height"===i?1:0))}}function $(e,t,n,r,i){return new $.prototype.init(e,t,n,r,i)}function I(e,t){var n,r={height:e},i=0;for(t=t?1:0;4>i;i+=2-t)n=wn[i],r["margin"+n]=r["padding"+n]=e;return t&&(r.opacity=r.width=e),r}function z(e){return st.isWindow(e)?e:9===e.nodeType?e.defaultView||e.parentWindow:!1}var X,U,V=e.document,Y=e.location,J=e.jQuery,G=e.$,Q={},K=[],Z="1.9.0",et=K.concat,tt=K.push,nt=K.slice,rt=K.indexOf,it=Q.toString,ot=Q.hasOwnProperty,at=Z.trim,st=function(e,t){return new st.fn.init(e,t,X)},ut=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,lt=/\S+/g,ct=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,ft=/^(?:(<[\w\W]+>)[^>]*|#([\w-]*))$/,pt=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,dt=/^[\],:{}\s]*$/,ht=/(?:^|:|,)(?:\s*\[)+/g,gt=/\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,mt=/"[^"\\\r\n]*"|true|false|null|-?(?:\d+\.|)\d+(?:[eE][+-]?\d+|)/g,yt=/^-ms-/,vt=/-([\da-z])/gi,bt=function(e,t){return t.toUpperCase()},xt=function(){V.addEventListener?(V.removeEventListener("DOMContentLoaded",xt,!1),st.ready()):"complete"===V.readyState&&(V.detachEvent("onreadystatechange",xt),st.ready())};st.fn=st.prototype={jquery:Z,constructor:st,init:function(e,n,r){var i,o;if(!e)return this;if("string"==typeof e){if(i="<"===e.charAt(0)&&">"===e.charAt(e.length-1)&&e.length>=3?[null,e,null]:ft.exec(e),!i||!i[1]&&n)return!n||n.jquery?(n||r).find(e):this.constructor(n).find(e);if(i[1]){if(n=n instanceof st?n[0]:n,st.merge(this,st.parseHTML(i[1],n&&n.nodeType?n.ownerDocument||n:V,!0)),pt.test(i[1])&&st.isPlainObject(n))for(i in n)st.isFunction(this[i])?this[i](n[i]):this.attr(i,n[i]);return this}if(o=V.getElementById(i[2]),o&&o.parentNode){if(o.id!==i[2])return r.find(e);this.length=1,this[0]=o}return this.context=V,this.selector=e,this}return e.nodeType?(this.context=this[0]=e,this.length=1,this):st.isFunction(e)?r.ready(e):(e.selector!==t&&(this.selector=e.selector,this.context=e.context),st.makeArray(e,this))},selector:"",length:0,size:function(){return this.length},toArray:function(){return nt.call(this)},get:function(e){return null==e?this.toArray():0>e?this[this.length+e]:this[e]},pushStack:function(e){var t=st.merge(this.constructor(),e);return t.prevObject=this,t.context=this.context,t},each:function(e,t){return st.each(this,e,t)},ready:function(e){return st.ready.promise().done(e),this},slice:function(){return this.pushStack(nt.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(0>e?t:0);return this.pushStack(n>=0&&t>n?[this[n]]:[])},map:function(e){return this.pushStack(st.map(this,function(t,n){return e.call(t,n,t)}))},end:function(){return this.prevObject||this.constructor(null)},push:tt,sort:[].sort,splice:[].splice},st.fn.init.prototype=st.fn,st.extend=st.fn.extend=function(){var e,n,r,i,o,a,s=arguments[0]||{},u=1,l=arguments.length,c=!1;for("boolean"==typeof s&&(c=s,s=arguments[1]||{},u=2),"object"==typeof s||st.isFunction(s)||(s={}),l===u&&(s=this,--u);l>u;u++)if(null!=(e=arguments[u]))for(n in e)r=s[n],i=e[n],s!==i&&(c&&i&&(st.isPlainObject(i)||(o=st.isArray(i)))?(o?(o=!1,a=r&&st.isArray(r)?r:[]):a=r&&st.isPlainObject(r)?r:{},s[n]=st.extend(c,a,i)):i!==t&&(s[n]=i));return s},st.extend({noConflict:function(t){return e.$===st&&(e.$=G),t&&e.jQuery===st&&(e.jQuery=J),st},isReady:!1,readyWait:1,holdReady:function(e){e?st.readyWait++:st.ready(!0)},ready:function(e){if(e===!0?!--st.readyWait:!st.isReady){if(!V.body)return setTimeout(st.ready);st.isReady=!0,e!==!0&&--st.readyWait>0||(U.resolveWith(V,[st]),st.fn.trigger&&st(V).trigger("ready").off("ready"))}},isFunction:function(e){return"function"===st.type(e)},isArray:Array.isArray||function(e){return"array"===st.type(e)},isWindow:function(e){return null!=e&&e==e.window},isNumeric:function(e){return!isNaN(parseFloat(e))&&isFinite(e)},type:function(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?Q[it.call(e)]||"object":typeof e},isPlainObject:function(e){if(!e||"object"!==st.type(e)||e.nodeType||st.isWindow(e))return!1;try{if(e.constructor&&!ot.call(e,"constructor")&&!ot.call(e.constructor.prototype,"isPrototypeOf"))return!1}catch(n){return!1}var r;for(r in e);return r===t||ot.call(e,r)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},error:function(e){throw Error(e)},parseHTML:function(e,t,n){if(!e||"string"!=typeof e)return null;"boolean"==typeof t&&(n=t,t=!1),t=t||V;var r=pt.exec(e),i=!n&&[];return r?[t.createElement(r[1])]:(r=st.buildFragment([e],t,i),i&&st(i).remove(),st.merge([],r.childNodes))},parseJSON:function(n){return e.JSON&&e.JSON.parse?e.JSON.parse(n):null===n?n:"string"==typeof n&&(n=st.trim(n),n&&dt.test(n.replace(gt,"@").replace(mt,"]").replace(ht,"")))?Function("return "+n)():(st.error("Invalid JSON: "+n),t)},parseXML:function(n){var r,i;if(!n||"string"!=typeof n)return null;try{e.DOMParser?(i=new DOMParser,r=i.parseFromString(n,"text/xml")):(r=new ActiveXObject("Microsoft.XMLDOM"),r.async="false",r.loadXML(n))}catch(o){r=t}return r&&r.documentElement&&!r.getElementsByTagName("parsererror").length||st.error("Invalid XML: "+n),r},noop:function(){},globalEval:function(t){t&&st.trim(t)&&(e.execScript||function(t){e.eval.call(e,t)})(t)},camelCase:function(e){return e.replace(yt,"ms-").replace(vt,bt)},nodeName:function(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()},each:function(e,t,r){var i,o=0,a=e.length,s=n(e);if(r){if(s)for(;a>o&&(i=t.apply(e[o],r),i!==!1);o++);else for(o in e)if(i=t.apply(e[o],r),i===!1)break}else if(s)for(;a>o&&(i=t.call(e[o],o,e[o]),i!==!1);o++);else for(o in e)if(i=t.call(e[o],o,e[o]),i===!1)break;return e},trim:at&&!at.call("\ufeff\u00a0")?function(e){return null==e?"":at.call(e)}:function(e){return null==e?"":(e+"").replace(ct,"")},makeArray:function(e,t){var r=t||[];return null!=e&&(n(Object(e))?st.merge(r,"string"==typeof e?[e]:e):tt.call(r,e)),r},inArray:function(e,t,n){var r;if(t){if(rt)return rt.call(t,e,n);for(r=t.length,n=n?0>n?Math.max(0,r+n):n:0;r>n;n++)if(n in t&&t[n]===e)return n}return-1},merge:function(e,n){var r=n.length,i=e.length,o=0;if("number"==typeof r)for(;r>o;o++)e[i++]=n[o];else for(;n[o]!==t;)e[i++]=n[o++];return e.length=i,e},grep:function(e,t,n){var r,i=[],o=0,a=e.length;for(n=!!n;a>o;o++)r=!!t(e[o],o),n!==r&&i.push(e[o]);return i},map:function(e,t,r){var i,o=0,a=e.length,s=n(e),u=[];if(s)for(;a>o;o++)i=t(e[o],o,r),null!=i&&(u[u.length]=i);else for(o in e)i=t(e[o],o,r),null!=i&&(u[u.length]=i);return et.apply([],u)},guid:1,proxy:function(e,n){var r,i,o;return"string"==typeof n&&(r=e[n],n=e,e=r),st.isFunction(e)?(i=nt.call(arguments,2),o=function(){return e.apply(n||this,i.concat(nt.call(arguments)))},o.guid=e.guid=e.guid||st.guid++,o):t},access:function(e,n,r,i,o,a,s){var u=0,l=e.length,c=null==r;if("object"===st.type(r)){o=!0;for(u in r)st.access(e,n,u,r[u],!0,a,s)}else if(i!==t&&(o=!0,st.isFunction(i)||(s=!0),c&&(s?(n.call(e,i),n=null):(c=n,n=function(e,t,n){return c.call(st(e),n)})),n))for(;l>u;u++)n(e[u],r,s?i:i.call(e[u],u,n(e[u],r)));return o?e:c?n.call(e):l?n(e[0],r):a},now:function(){return(new Date).getTime()}}),st.ready.promise=function(t){if(!U)if(U=st.Deferred(),"complete"===V.readyState)setTimeout(st.ready);else if(V.addEventListener)V.addEventListener("DOMContentLoaded",xt,!1),e.addEventListener("load",st.ready,!1);else{V.attachEvent("onreadystatechange",xt),e.attachEvent("onload",st.ready);var n=!1;try{n=null==e.frameElement&&V.documentElement}catch(r){}n&&n.doScroll&&function i(){if(!st.isReady){try{n.doScroll("left")}catch(e){return setTimeout(i,50)}st.ready()}}()}return U.promise(t)},st.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),function(e,t){Q["[object "+t+"]"]=t.toLowerCase()}),X=st(V);var Tt={};st.Callbacks=function(e){e="string"==typeof e?Tt[e]||r(e):st.extend({},e);var n,i,o,a,s,u,l=[],c=!e.once&&[],f=function(t){for(n=e.memory&&t,i=!0,u=a||0,a=0,s=l.length,o=!0;l&&s>u;u++)if(l[u].apply(t[0],t[1])===!1&&e.stopOnFalse){n=!1;break}o=!1,l&&(c?c.length&&f(c.shift()):n?l=[]:p.disable())},p={add:function(){if(l){var t=l.length;(function r(t){st.each(t,function(t,n){var i=st.type(n);"function"===i?e.unique&&p.has(n)||l.push(n):n&&n.length&&"string"!==i&&r(n)})})(arguments),o?s=l.length:n&&(a=t,f(n))}return this},remove:function(){return l&&st.each(arguments,function(e,t){for(var n;(n=st.inArray(t,l,n))>-1;)l.splice(n,1),o&&(s>=n&&s--,u>=n&&u--)}),this},has:function(e){return st.inArray(e,l)>-1},empty:function(){return l=[],this},disable:function(){return l=c=n=t,this},disabled:function(){return!l},lock:function(){return c=t,n||p.disable(),this},locked:function(){return!c},fireWith:function(e,t){return t=t||[],t=[e,t.slice?t.slice():t],!l||i&&!c||(o?c.push(t):f(t)),this},fire:function(){return p.fireWith(this,arguments),this},fired:function(){return!!i}};return p},st.extend({Deferred:function(e){var t=[["resolve","done",st.Callbacks("once memory"),"resolved"],["reject","fail",st.Callbacks("once memory"),"rejected"],["notify","progress",st.Callbacks("memory")]],n="pending",r={state:function(){return n},always:function(){return i.done(arguments).fail(arguments),this},then:function(){var e=arguments;return st.Deferred(function(n){st.each(t,function(t,o){var a=o[0],s=st.isFunction(e[t])&&e[t];i[o[1]](function(){var e=s&&s.apply(this,arguments);e&&st.isFunction(e.promise)?e.promise().done(n.resolve).fail(n.reject).progress(n.notify):n[a+"With"](this===r?n.promise():this,s?[e]:arguments)})}),e=null}).promise()},promise:function(e){return null!=e?st.extend(e,r):r}},i={};return r.pipe=r.then,st.each(t,function(e,o){var a=o[2],s=o[3];r[o[1]]=a.add,s&&a.add(function(){n=s},t[1^e][2].disable,t[2][2].lock),i[o[0]]=function(){return i[o[0]+"With"](this===i?r:this,arguments),this},i[o[0]+"With"]=a.fireWith}),r.promise(i),e&&e.call(i,i),i},when:function(e){var t,n,r,i=0,o=nt.call(arguments),a=o.length,s=1!==a||e&&st.isFunction(e.promise)?a:0,u=1===s?e:st.Deferred(),l=function(e,n,r){return function(i){n[e]=this,r[e]=arguments.length>1?nt.call(arguments):i,r===t?u.notifyWith(n,r):--s||u.resolveWith(n,r)}};if(a>1)for(t=Array(a),n=Array(a),r=Array(a);a>i;i++)o[i]&&st.isFunction(o[i].promise)?o[i].promise().done(l(i,r,o)).fail(u.reject).progress(l(i,n,t)):--s;return s||u.resolveWith(r,o),u.promise()}}),st.support=function(){var n,r,i,o,a,s,u,l,c,f,p=V.createElement("div");if(p.setAttribute("className","t"),p.innerHTML="  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",r=p.getElementsByTagName("*"),i=p.getElementsByTagName("a")[0],!r||!i||!r.length)return{};o=V.createElement("select"),a=o.appendChild(V.createElement("option")),s=p.getElementsByTagName("input")[0],i.style.cssText="top:1px;float:left;opacity:.5",n={getSetAttribute:"t"!==p.className,leadingWhitespace:3===p.firstChild.nodeType,tbody:!p.getElementsByTagName("tbody").length,htmlSerialize:!!p.getElementsByTagName("link").length,style:/top/.test(i.getAttribute("style")),hrefNormalized:"/a"===i.getAttribute("href"),opacity:/^0.5/.test(i.style.opacity),cssFloat:!!i.style.cssFloat,checkOn:!!s.value,optSelected:a.selected,enctype:!!V.createElement("form").enctype,html5Clone:"<:nav></:nav>"!==V.createElement("nav").cloneNode(!0).outerHTML,boxModel:"CSS1Compat"===V.compatMode,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0,boxSizingReliable:!0,pixelPosition:!1},s.checked=!0,n.noCloneChecked=s.cloneNode(!0).checked,o.disabled=!0,n.optDisabled=!a.disabled;try{delete p.test}catch(d){n.deleteExpando=!1}s=V.createElement("input"),s.setAttribute("value",""),n.input=""===s.getAttribute("value"),s.value="t",s.setAttribute("type","radio"),n.radioValue="t"===s.value,s.setAttribute("checked","t"),s.setAttribute("name","t"),u=V.createDocumentFragment(),u.appendChild(s),n.appendChecked=s.checked,n.checkClone=u.cloneNode(!0).cloneNode(!0).lastChild.checked,p.attachEvent&&(p.attachEvent("onclick",function(){n.noCloneEvent=!1}),p.cloneNode(!0).click());for(f in{submit:!0,change:!0,focusin:!0})p.setAttribute(l="on"+f,"t"),n[f+"Bubbles"]=l in e||p.attributes[l].expando===!1;return p.style.backgroundClip="content-box",p.cloneNode(!0).style.backgroundClip="",n.clearCloneStyle="content-box"===p.style.backgroundClip,st(function(){var r,i,o,a="padding:0;margin:0;border:0;display:block;box-sizing:content-box;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;",s=V.getElementsByTagName("body")[0];s&&(r=V.createElement("div"),r.style.cssText="border:0;width:0;height:0;position:absolute;top:0;left:-9999px;margin-top:1px",s.appendChild(r).appendChild(p),p.innerHTML="<table><tr><td></td><td>t</td></tr></table>",o=p.getElementsByTagName("td"),o[0].style.cssText="padding:0;margin:0;border:0;display:none",c=0===o[0].offsetHeight,o[0].style.display="",o[1].style.display="none",n.reliableHiddenOffsets=c&&0===o[0].offsetHeight,p.innerHTML="",p.style.cssText="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;",n.boxSizing=4===p.offsetWidth,n.doesNotIncludeMarginInBodyOffset=1!==s.offsetTop,e.getComputedStyle&&(n.pixelPosition="1%"!==(e.getComputedStyle(p,null)||{}).top,n.boxSizingReliable="4px"===(e.getComputedStyle(p,null)||{width:"4px"}).width,i=p.appendChild(V.createElement("div")),i.style.cssText=p.style.cssText=a,i.style.marginRight=i.style.width="0",p.style.width="1px",n.reliableMarginRight=!parseFloat((e.getComputedStyle(i,null)||{}).marginRight)),p.style.zoom!==t&&(p.innerHTML="",p.style.cssText=a+"width:1px;padding:1px;display:inline;zoom:1",n.inlineBlockNeedsLayout=3===p.offsetWidth,p.style.display="block",p.innerHTML="<div></div>",p.firstChild.style.width="5px",n.shrinkWrapBlocks=3!==p.offsetWidth,s.style.zoom=1),s.removeChild(r),r=p=o=i=null)}),r=o=u=a=i=s=null,n}();var wt=/(?:\{[\s\S]*\}|\[[\s\S]*\])$/,Nt=/([A-Z])/g;st.extend({cache:{},expando:"jQuery"+(Z+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(e){return e=e.nodeType?st.cache[e[st.expando]]:e[st.expando],!!e&&!s(e)},data:function(e,t,n){return i(e,t,n,!1)},removeData:function(e,t){return o(e,t,!1)},_data:function(e,t,n){return i(e,t,n,!0)},_removeData:function(e,t){return o(e,t,!0)},acceptData:function(e){var t=e.nodeName&&st.noData[e.nodeName.toLowerCase()];return!t||t!==!0&&e.getAttribute("classid")===t}}),st.fn.extend({data:function(e,n){var r,i,o=this[0],s=0,u=null;if(e===t){if(this.length&&(u=st.data(o),1===o.nodeType&&!st._data(o,"parsedAttrs"))){for(r=o.attributes;r.length>s;s++)i=r[s].name,i.indexOf("data-")||(i=st.camelCase(i.substring(5)),a(o,i,u[i]));st._data(o,"parsedAttrs",!0)}return u}return"object"==typeof e?this.each(function(){st.data(this,e)}):st.access(this,function(n){return n===t?o?a(o,e,st.data(o,e)):null:(this.each(function(){st.data(this,e,n)}),t)},null,n,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){st.removeData(this,e)})}}),st.extend({queue:function(e,n,r){var i;return e?(n=(n||"fx")+"queue",i=st._data(e,n),r&&(!i||st.isArray(r)?i=st._data(e,n,st.makeArray(r)):i.push(r)),i||[]):t},dequeue:function(e,t){t=t||"fx";var n=st.queue(e,t),r=n.length,i=n.shift(),o=st._queueHooks(e,t),a=function(){st.dequeue(e,t)};"inprogress"===i&&(i=n.shift(),r--),o.cur=i,i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,a,o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return st._data(e,n)||st._data(e,n,{empty:st.Callbacks("once memory").add(function(){st._removeData(e,t+"queue"),st._removeData(e,n)})})}}),st.fn.extend({queue:function(e,n){var r=2;return"string"!=typeof e&&(n=e,e="fx",r--),r>arguments.length?st.queue(this[0],e):n===t?this:this.each(function(){var t=st.queue(this,e,n);st._queueHooks(this,e),"fx"===e&&"inprogress"!==t[0]&&st.dequeue(this,e)})},dequeue:function(e){return this.each(function(){st.dequeue(this,e)})},delay:function(e,t){return e=st.fx?st.fx.speeds[e]||e:e,t=t||"fx",this.queue(t,function(t,n){var r=setTimeout(t,e);n.stop=function(){clearTimeout(r)}})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,n){var r,i=1,o=st.Deferred(),a=this,s=this.length,u=function(){--i||o.resolveWith(a,[a])};for("string"!=typeof e&&(n=e,e=t),e=e||"fx";s--;)r=st._data(a[s],e+"queueHooks"),r&&r.empty&&(i++,r.empty.add(u));return u(),o.promise(n)}});var Ct,kt,Et=/[\t\r\n]/g,St=/\r/g,At=/^(?:input|select|textarea|button|object)$/i,jt=/^(?:a|area)$/i,Dt=/^(?:checked|selected|autofocus|autoplay|async|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped)$/i,Lt=/^(?:checked|selected)$/i,Ht=st.support.getSetAttribute,Mt=st.support.input;st.fn.extend({attr:function(e,t){return st.access(this,st.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){st.removeAttr(this,e)})},prop:function(e,t){return st.access(this,st.prop,e,t,arguments.length>1)},removeProp:function(e){return e=st.propFix[e]||e,this.each(function(){try{this[e]=t,delete this[e]}catch(n){}})},addClass:function(e){var t,n,r,i,o,a=0,s=this.length,u="string"==typeof e&&e;if(st.isFunction(e))return this.each(function(t){st(this).addClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(lt)||[];s>a;a++)if(n=this[a],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(Et," "):" ")){for(o=0;i=t[o++];)0>r.indexOf(" "+i+" ")&&(r+=i+" ");n.className=st.trim(r)}return this},removeClass:function(e){var t,n,r,i,o,a=0,s=this.length,u=0===arguments.length||"string"==typeof e&&e;if(st.isFunction(e))return this.each(function(t){st(this).removeClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(lt)||[];s>a;a++)if(n=this[a],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(Et," "):"")){for(o=0;i=t[o++];)for(;r.indexOf(" "+i+" ")>=0;)r=r.replace(" "+i+" "," ");n.className=e?st.trim(r):""}return this},toggleClass:function(e,t){var n=typeof e,r="boolean"==typeof t;return st.isFunction(e)?this.each(function(n){st(this).toggleClass(e.call(this,n,this.className,t),t)}):this.each(function(){if("string"===n)for(var i,o=0,a=st(this),s=t,u=e.match(lt)||[];i=u[o++];)s=r?s:!a.hasClass(i),a[s?"addClass":"removeClass"](i);else("undefined"===n||"boolean"===n)&&(this.className&&st._data(this,"__className__",this.className),this.className=this.className||e===!1?"":st._data(this,"__className__")||"")})},hasClass:function(e){for(var t=" "+e+" ",n=0,r=this.length;r>n;n++)if(1===this[n].nodeType&&(" "+this[n].className+" ").replace(Et," ").indexOf(t)>=0)return!0;return!1},val:function(e){var n,r,i,o=this[0];{if(arguments.length)return i=st.isFunction(e),this.each(function(r){var o,a=st(this);1===this.nodeType&&(o=i?e.call(this,r,a.val()):e,null==o?o="":"number"==typeof o?o+="":st.isArray(o)&&(o=st.map(o,function(e){return null==e?"":e+""})),n=st.valHooks[this.type]||st.valHooks[this.nodeName.toLowerCase()],n&&"set"in n&&n.set(this,o,"value")!==t||(this.value=o))});if(o)return n=st.valHooks[o.type]||st.valHooks[o.nodeName.toLowerCase()],n&&"get"in n&&(r=n.get(o,"value"))!==t?r:(r=o.value,"string"==typeof r?r.replace(St,""):null==r?"":r)}}}),st.extend({valHooks:{option:{get:function(e){var t=e.attributes.value;return!t||t.specified?e.value:e.text}},select:{get:function(e){for(var t,n,r=e.options,i=e.selectedIndex,o="select-one"===e.type||0>i,a=o?null:[],s=o?i+1:r.length,u=0>i?s:o?i:0;s>u;u++)if(n=r[u],!(!n.selected&&u!==i||(st.support.optDisabled?n.disabled:null!==n.getAttribute("disabled"))||n.parentNode.disabled&&st.nodeName(n.parentNode,"optgroup"))){if(t=st(n).val(),o)return t;a.push(t)}return a},set:function(e,t){var n=st.makeArray(t);return st(e).find("option").each(function(){this.selected=st.inArray(st(this).val(),n)>=0}),n.length||(e.selectedIndex=-1),n}}},attr:function(e,n,r){var i,o,a,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return e.getAttribute===t?st.prop(e,n,r):(a=1!==s||!st.isXMLDoc(e),a&&(n=n.toLowerCase(),o=st.attrHooks[n]||(Dt.test(n)?kt:Ct)),r===t?o&&a&&"get"in o&&null!==(i=o.get(e,n))?i:(e.getAttribute!==t&&(i=e.getAttribute(n)),null==i?t:i):null!==r?o&&a&&"set"in o&&(i=o.set(e,r,n))!==t?i:(e.setAttribute(n,r+""),r):(st.removeAttr(e,n),t))},removeAttr:function(e,t){var n,r,i=0,o=t&&t.match(lt);if(o&&1===e.nodeType)for(;n=o[i++];)r=st.propFix[n]||n,Dt.test(n)?!Ht&&Lt.test(n)?e[st.camelCase("default-"+n)]=e[r]=!1:e[r]=!1:st.attr(e,n,""),e.removeAttribute(Ht?n:r)},attrHooks:{type:{set:function(e,t){if(!st.support.radioValue&&"radio"===t&&st.nodeName(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(e,n,r){var i,o,a,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return a=1!==s||!st.isXMLDoc(e),a&&(n=st.propFix[n]||n,o=st.propHooks[n]),r!==t?o&&"set"in o&&(i=o.set(e,r,n))!==t?i:e[n]=r:o&&"get"in o&&null!==(i=o.get(e,n))?i:e[n]},propHooks:{tabIndex:{get:function(e){var n=e.getAttributeNode("tabindex");return n&&n.specified?parseInt(n.value,10):At.test(e.nodeName)||jt.test(e.nodeName)&&e.href?0:t}}}}),kt={get:function(e,n){var r=st.prop(e,n),i="boolean"==typeof r&&e.getAttribute(n),o="boolean"==typeof r?Mt&&Ht?null!=i:Lt.test(n)?e[st.camelCase("default-"+n)]:!!i:e.getAttributeNode(n);return o&&o.value!==!1?n.toLowerCase():t},set:function(e,t,n){return t===!1?st.removeAttr(e,n):Mt&&Ht||!Lt.test(n)?e.setAttribute(!Ht&&st.propFix[n]||n,n):e[st.camelCase("default-"+n)]=e[n]=!0,n}},Mt&&Ht||(st.attrHooks.value={get:function(e,n){var r=e.getAttributeNode(n);return st.nodeName(e,"input")?e.defaultValue:r&&r.specified?r.value:t},set:function(e,n,r){return st.nodeName(e,"input")?(e.defaultValue=n,t):Ct&&Ct.set(e,n,r)}}),Ht||(Ct=st.valHooks.button={get:function(e,n){var r=e.getAttributeNode(n);return r&&("id"===n||"name"===n||"coords"===n?""!==r.value:r.specified)?r.value:t},set:function(e,n,r){var i=e.getAttributeNode(r);return i||e.setAttributeNode(i=e.ownerDocument.createAttribute(r)),i.value=n+="","value"===r||n===e.getAttribute(r)?n:t}},st.attrHooks.contenteditable={get:Ct.get,set:function(e,t,n){Ct.set(e,""===t?!1:t,n)}},st.each(["width","height"],function(e,n){st.attrHooks[n]=st.extend(st.attrHooks[n],{set:function(e,r){return""===r?(e.setAttribute(n,"auto"),r):t}})})),st.support.hrefNormalized||(st.each(["href","src","width","height"],function(e,n){st.attrHooks[n]=st.extend(st.attrHooks[n],{get:function(e){var r=e.getAttribute(n,2);return null==r?t:r}})}),st.each(["href","src"],function(e,t){st.propHooks[t]={get:function(e){return e.getAttribute(t,4)}}})),st.support.style||(st.attrHooks.style={get:function(e){return e.style.cssText||t},set:function(e,t){return e.style.cssText=t+""}}),st.support.optSelected||(st.propHooks.selected=st.extend(st.propHooks.selected,{get:function(e){var t=e.parentNode;return t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex),null}})),st.support.enctype||(st.propFix.enctype="encoding"),st.support.checkOn||st.each(["radio","checkbox"],function(){st.valHooks[this]={get:function(e){return null===e.getAttribute("value")?"on":e.value}}}),st.each(["radio","checkbox"],function(){st.valHooks[this]=st.extend(st.valHooks[this],{set:function(e,n){return st.isArray(n)?e.checked=st.inArray(st(e).val(),n)>=0:t}})});var qt=/^(?:input|select|textarea)$/i,_t=/^key/,Ft=/^(?:mouse|contextmenu)|click/,Ot=/^(?:focusinfocus|focusoutblur)$/,Bt=/^([^.]*)(?:\.(.+)|)$/;st.event={global:{},add:function(e,n,r,i,o){var a,s,u,l,c,f,p,d,h,g,m,y=3!==e.nodeType&&8!==e.nodeType&&st._data(e);if(y){for(r.handler&&(a=r,r=a.handler,o=a.selector),r.guid||(r.guid=st.guid++),(l=y.events)||(l=y.events={}),(s=y.handle)||(s=y.handle=function(e){return st===t||e&&st.event.triggered===e.type?t:st.event.dispatch.apply(s.elem,arguments)},s.elem=e),n=(n||"").match(lt)||[""],c=n.length;c--;)u=Bt.exec(n[c])||[],h=m=u[1],g=(u[2]||"").split(".").sort(),p=st.event.special[h]||{},h=(o?p.delegateType:p.bindType)||h,p=st.event.special[h]||{},f=st.extend({type:h,origType:m,data:i,handler:r,guid:r.guid,selector:o,needsContext:o&&st.expr.match.needsContext.test(o),namespace:g.join(".")},a),(d=l[h])||(d=l[h]=[],d.delegateCount=0,p.setup&&p.setup.call(e,i,g,s)!==!1||(e.addEventListener?e.addEventListener(h,s,!1):e.attachEvent&&e.attachEvent("on"+h,s))),p.add&&(p.add.call(e,f),f.handler.guid||(f.handler.guid=r.guid)),o?d.splice(d.delegateCount++,0,f):d.push(f),st.event.global[h]=!0;e=null}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,m=st.hasData(e)&&st._data(e);if(m&&(u=m.events)){for(t=(t||"").match(lt)||[""],l=t.length;l--;)if(s=Bt.exec(t[l])||[],d=g=s[1],h=(s[2]||"").split(".").sort(),d){for(f=st.event.special[d]||{},d=(r?f.delegateType:f.bindType)||d,p=u[d]||[],s=s[2]&&RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;o--;)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&f.teardown.call(e,h,m.handle)!==!1||st.removeEvent(e,d,m.handle),delete u[d])}else for(d in u)st.event.remove(e,d+t[l],n,r,!0);st.isEmptyObject(u)&&(delete m.handle,st._removeData(e,"events"))}},trigger:function(n,r,i,o){var a,s,u,l,c,f,p,d=[i||V],h=n.type||n,g=n.namespace?n.namespace.split("."):[];if(s=u=i=i||V,3!==i.nodeType&&8!==i.nodeType&&!Ot.test(h+st.event.triggered)&&(h.indexOf(".")>=0&&(g=h.split("."),h=g.shift(),g.sort()),c=0>h.indexOf(":")&&"on"+h,n=n[st.expando]?n:new st.Event(h,"object"==typeof n&&n),n.isTrigger=!0,n.namespace=g.join("."),n.namespace_re=n.namespace?RegExp("(^|\\.)"+g.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,n.result=t,n.target||(n.target=i),r=null==r?[n]:st.makeArray(r,[n]),p=st.event.special[h]||{},o||!p.trigger||p.trigger.apply(i,r)!==!1)){if(!o&&!p.noBubble&&!st.isWindow(i)){for(l=p.delegateType||h,Ot.test(l+h)||(s=s.parentNode);s;s=s.parentNode)d.push(s),u=s;u===(i.ownerDocument||V)&&d.push(u.defaultView||u.parentWindow||e)}for(a=0;(s=d[a++])&&!n.isPropagationStopped();)n.type=a>1?l:p.bindType||h,f=(st._data(s,"events")||{})[n.type]&&st._data(s,"handle"),f&&f.apply(s,r),f=c&&s[c],f&&st.acceptData(s)&&f.apply&&f.apply(s,r)===!1&&n.preventDefault();if(n.type=h,!(o||n.isDefaultPrevented()||p._default&&p._default.apply(i.ownerDocument,r)!==!1||"click"===h&&st.nodeName(i,"a")||!st.acceptData(i)||!c||!i[h]||st.isWindow(i))){u=i[c],u&&(i[c]=null),st.event.triggered=h;try{i[h]()}catch(m){}st.event.triggered=t,u&&(i[c]=u)}return n.result}},dispatch:function(e){e=st.event.fix(e);var n,r,i,o,a,s=[],u=nt.call(arguments),l=(st._data(this,"events")||{})[e.type]||[],c=st.event.special[e.type]||{};if(u[0]=e,e.delegateTarget=this,!c.preDispatch||c.preDispatch.call(this,e)!==!1){for(s=st.event.handlers.call(this,e,l),n=0;(o=s[n++])&&!e.isPropagationStopped();)for(e.currentTarget=o.elem,r=0;(a=o.handlers[r++])&&!e.isImmediatePropagationStopped();)(!e.namespace_re||e.namespace_re.test(a.namespace))&&(e.handleObj=a,e.data=a.data,i=((st.event.special[a.origType]||{}).handle||a.handler).apply(o.elem,u),i!==t&&(e.result=i)===!1&&(e.preventDefault(),e.stopPropagation()));return c.postDispatch&&c.postDispatch.call(this,e),e.result}},handlers:function(e,n){var r,i,o,a,s=[],u=n.delegateCount,l=e.target;if(u&&l.nodeType&&(!e.button||"click"!==e.type))for(;l!=this;l=l.parentNode||this)if(l.disabled!==!0||"click"!==e.type){for(i=[],r=0;u>r;r++)a=n[r],o=a.selector+" ",i[o]===t&&(i[o]=a.needsContext?st(o,this).index(l)>=0:st.find(o,this,null,[l]).length),i[o]&&i.push(a);i.length&&s.push({elem:l,handlers:i})}return n.length>u&&s.push({elem:this,handlers:n.slice(u)}),s},fix:function(e){if(e[st.expando])return e;var t,n,r=e,i=st.event.fixHooks[e.type]||{},o=i.props?this.props.concat(i.props):this.props;for(e=new st.Event(r),t=o.length;t--;)n=o[t],e[n]=r[n];return e.target||(e.target=r.srcElement||V),3===e.target.nodeType&&(e.target=e.target.parentNode),e.metaKey=!!e.metaKey,i.filter?i.filter(e,r):e},props:"altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(e,t){return null==e.which&&(e.which=null!=t.charCode?t.charCode:t.keyCode),e}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(e,n){var r,i,o,a=n.button,s=n.fromElement;return null==e.pageX&&null!=n.clientX&&(r=e.target.ownerDocument||V,i=r.documentElement,o=r.body,e.pageX=n.clientX+(i&&i.scrollLeft||o&&o.scrollLeft||0)-(i&&i.clientLeft||o&&o.clientLeft||0),e.pageY=n.clientY+(i&&i.scrollTop||o&&o.scrollTop||0)-(i&&i.clientTop||o&&o.clientTop||0)),!e.relatedTarget&&s&&(e.relatedTarget=s===e.target?n.toElement:s),e.which||a===t||(e.which=1&a?1:2&a?3:4&a?2:0),e}},special:{load:{noBubble:!0},click:{trigger:function(){return st.nodeName(this,"input")&&"checkbox"===this.type&&this.click?(this.click(),!1):t}},focus:{trigger:function(){if(this!==V.activeElement&&this.focus)try{return this.focus(),!1}catch(e){}},delegateType:"focusin"},blur:{trigger:function(){return this===V.activeElement&&this.blur?(this.blur(),!1):t},delegateType:"focusout"},beforeunload:{postDispatch:function(e){e.result!==t&&(e.originalEvent.returnValue=e.result)}}},simulate:function(e,t,n,r){var i=st.extend(new st.Event,n,{type:e,isSimulated:!0,originalEvent:{}});r?st.event.trigger(i,null,t):st.event.dispatch.call(t,i),i.isDefaultPrevented()&&n.preventDefault()}},st.removeEvent=V.removeEventListener?function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n,!1)}:function(e,n,r){var i="on"+n;e.detachEvent&&(e[i]===t&&(e[i]=null),e.detachEvent(i,r))},st.Event=function(e,n){return this instanceof st.Event?(e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||e.returnValue===!1||e.getPreventDefault&&e.getPreventDefault()?u:l):this.type=e,n&&st.extend(this,n),this.timeStamp=e&&e.timeStamp||st.now(),this[st.expando]=!0,t):new st.Event(e,n)},st.Event.prototype={isDefaultPrevented:l,isPropagationStopped:l,isImmediatePropagationStopped:l,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=u,e&&(e.preventDefault?e.preventDefault():e.returnValue=!1)},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=u,e&&(e.stopPropagation&&e.stopPropagation(),e.cancelBubble=!0)},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=u,this.stopPropagation()}},st.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(e,t){st.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,o=e.handleObj;return(!i||i!==r&&!st.contains(r,i))&&(e.type=o.origType,n=o.handler.apply(this,arguments),e.type=t),n}}}),st.support.submitBubbles||(st.event.special.submit={setup:function(){return st.nodeName(this,"form")?!1:(st.event.add(this,"click._submit keypress._submit",function(e){var n=e.target,r=st.nodeName(n,"input")||st.nodeName(n,"button")?n.form:t;r&&!st._data(r,"submitBubbles")&&(st.event.add(r,"submit._submit",function(e){e._submit_bubble=!0}),st._data(r,"submitBubbles",!0))}),t)},postDispatch:function(e){e._submit_bubble&&(delete e._submit_bubble,this.parentNode&&!e.isTrigger&&st.event.simulate("submit",this.parentNode,e,!0))},teardown:function(){return st.nodeName(this,"form")?!1:(st.event.remove(this,"._submit"),t)}}),st.support.changeBubbles||(st.event.special.change={setup:function(){return qt.test(this.nodeName)?(("checkbox"===this.type||"radio"===this.type)&&(st.event.add(this,"propertychange._change",function(e){"checked"===e.originalEvent.propertyName&&(this._just_changed=!0)}),st.event.add(this,"click._change",function(e){this._just_changed&&!e.isTrigger&&(this._just_changed=!1),st.event.simulate("change",this,e,!0)})),!1):(st.event.add(this,"beforeactivate._change",function(e){var t=e.target;qt.test(t.nodeName)&&!st._data(t,"changeBubbles")&&(st.event.add(t,"change._change",function(e){!this.parentNode||e.isSimulated||e.isTrigger||st.event.simulate("change",this.parentNode,e,!0)}),st._data(t,"changeBubbles",!0))}),t)},handle:function(e){var n=e.target;return this!==n||e.isSimulated||e.isTrigger||"radio"!==n.type&&"checkbox"!==n.type?e.handleObj.handler.apply(this,arguments):t},teardown:function(){return st.event.remove(this,"._change"),!qt.test(this.nodeName)}}),st.support.focusinBubbles||st.each({focus:"focusin",blur:"focusout"},function(e,t){var n=0,r=function(e){st.event.simulate(t,e.target,st.event.fix(e),!0)};st.event.special[t]={setup:function(){0===n++&&V.addEventListener(e,r,!0)},teardown:function(){0===--n&&V.removeEventListener(e,r,!0)}}}),st.fn.extend({on:function(e,n,r,i,o){var a,s;if("object"==typeof e){"string"!=typeof n&&(r=r||n,n=t);for(s in e)this.on(s,n,r,e[s],o);return this}if(null==r&&null==i?(i=n,r=n=t):null==i&&("string"==typeof n?(i=r,r=t):(i=r,r=n,n=t)),i===!1)i=l;else if(!i)return this;return 1===o&&(a=i,i=function(e){return st().off(e),a.apply(this,arguments)},i.guid=a.guid||(a.guid=st.guid++)),this.each(function(){st.event.add(this,e,i,r,n)})},one:function(e,t,n,r){return this.on(e,t,n,r,1)},off:function(e,n,r){var i,o;if(e&&e.preventDefault&&e.handleObj)return i=e.handleObj,st(e.delegateTarget).off(i.namespace?i.origType+"."+i.namespace:i.origType,i.selector,i.handler),this;if("object"==typeof e){for(o in e)this.off(o,n,e[o]);return this}return(n===!1||"function"==typeof n)&&(r=n,n=t),r===!1&&(r=l),this.each(function(){st.event.remove(this,e,r,n)})},bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},trigger:function(e,t){return this.each(function(){st.event.trigger(e,t,this)})},triggerHandler:function(e,n){var r=this[0];return r?st.event.trigger(e,n,r,!0):t},hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),st.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(e,t){st.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)},_t.test(t)&&(st.event.fixHooks[t]=st.event.keyHooks),Ft.test(t)&&(st.event.fixHooks[t]=st.event.mouseHooks)}),function(e,t){function n(e){return ht.test(e+"")}function r(){var e,t=[];return e=function(n,r){return t.push(n+=" ")>C.cacheLength&&delete e[t.shift()],e[n]=r}}function i(e){return e[P]=!0,e}function o(e){var t=L.createElement("div");try{return e(t)}catch(n){return!1}finally{t=null}}function a(e,t,n,r){var i,o,a,s,u,l,c,d,h,g;if((t?t.ownerDocument||t:R)!==L&&D(t),t=t||L,n=n||[],!e||"string"!=typeof e)return n;if(1!==(s=t.nodeType)&&9!==s)return[];if(!M&&!r){if(i=gt.exec(e))if(a=i[1]){if(9===s){if(o=t.getElementById(a),!o||!o.parentNode)return n;if(o.id===a)return n.push(o),n}else if(t.ownerDocument&&(o=t.ownerDocument.getElementById(a))&&O(t,o)&&o.id===a)return n.push(o),n}else{if(i[2])return Q.apply(n,K.call(t.getElementsByTagName(e),0)),n;if((a=i[3])&&W.getByClassName&&t.getElementsByClassName)return Q.apply(n,K.call(t.getElementsByClassName(a),0)),n}if(W.qsa&&!q.test(e)){if(c=!0,d=P,h=t,g=9===s&&e,1===s&&"object"!==t.nodeName.toLowerCase()){for(l=f(e),(c=t.getAttribute("id"))?d=c.replace(vt,"\\$&"):t.setAttribute("id",d),d="[id='"+d+"'] ",u=l.length;u--;)l[u]=d+p(l[u]);h=dt.test(e)&&t.parentNode||t,g=l.join(",")}if(g)try{return Q.apply(n,K.call(h.querySelectorAll(g),0)),n}catch(m){}finally{c||t.removeAttribute("id")}}}return x(e.replace(at,"$1"),t,n,r)}function s(e,t){for(var n=e&&t&&e.nextSibling;n;n=n.nextSibling)if(n===t)return-1;return e?1:-1}function u(e){return function(t){var n=t.nodeName.toLowerCase();return"input"===n&&t.type===e}}function l(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function c(e){return i(function(t){return t=+t,i(function(n,r){for(var i,o=e([],n.length,t),a=o.length;a--;)n[i=o[a]]&&(n[i]=!(r[i]=n[i]))})})}function f(e,t){var n,r,i,o,s,u,l,c=X[e+" "];if(c)return t?0:c.slice(0);for(s=e,u=[],l=C.preFilter;s;){(!n||(r=ut.exec(s)))&&(r&&(s=s.slice(r[0].length)||s),u.push(i=[])),n=!1,(r=lt.exec(s))&&(n=r.shift(),i.push({value:n,type:r[0].replace(at," ")}),s=s.slice(n.length));for(o in C.filter)!(r=pt[o].exec(s))||l[o]&&!(r=l[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),s=s.slice(n.length));if(!n)break}return t?s.length:s?a.error(e):X(e,u).slice(0)}function p(e){for(var t=0,n=e.length,r="";n>t;t++)r+=e[t].value;return r}function d(e,t,n){var r=t.dir,i=n&&"parentNode"===t.dir,o=I++;return t.first?function(t,n,o){for(;t=t[r];)if(1===t.nodeType||i)return e(t,n,o)}:function(t,n,a){var s,u,l,c=$+" "+o;if(a){for(;t=t[r];)if((1===t.nodeType||i)&&e(t,n,a))return!0}else for(;t=t[r];)if(1===t.nodeType||i)if(l=t[P]||(t[P]={}),(u=l[r])&&u[0]===c){if((s=u[1])===!0||s===N)return s===!0}else if(u=l[r]=[c],u[1]=e(t,n,a)||N,u[1]===!0)return!0}}function h(e){return e.length>1?function(t,n,r){for(var i=e.length;i--;)if(!e[i](t,n,r))return!1;return!0}:e[0]}function g(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;u>s;s++)(o=e[s])&&(!n||n(o,r,i))&&(a.push(o),l&&t.push(s));return a}function m(e,t,n,r,o,a){return r&&!r[P]&&(r=m(r)),o&&!o[P]&&(o=m(o,a)),i(function(i,a,s,u){var l,c,f,p=[],d=[],h=a.length,m=i||b(t||"*",s.nodeType?[s]:s,[]),y=!e||!i&&t?m:g(m,p,e,s,u),v=n?o||(i?e:h||r)?[]:a:y;if(n&&n(y,v,s,u),r)for(l=g(v,d),r(l,[],s,u),c=l.length;c--;)(f=l[c])&&(v[d[c]]=!(y[d[c]]=f));if(i){if(o||e){if(o){for(l=[],c=v.length;c--;)(f=v[c])&&l.push(y[c]=f);o(null,v=[],l,u)}for(c=v.length;c--;)(f=v[c])&&(l=o?Z.call(i,f):p[c])>-1&&(i[l]=!(a[l]=f))}}else v=g(v===a?v.splice(h,v.length):v),o?o(null,a,v,u):Q.apply(a,v)})}function y(e){for(var t,n,r,i=e.length,o=C.relative[e[0].type],a=o||C.relative[" "],s=o?1:0,u=d(function(e){return e===t},a,!0),l=d(function(e){return Z.call(t,e)>-1},a,!0),c=[function(e,n,r){return!o&&(r||n!==j)||((t=n).nodeType?u(e,n,r):l(e,n,r))}];i>s;s++)if(n=C.relative[e[s].type])c=[d(h(c),n)];else{if(n=C.filter[e[s].type].apply(null,e[s].matches),n[P]){for(r=++s;i>r&&!C.relative[e[r].type];r++);return m(s>1&&h(c),s>1&&p(e.slice(0,s-1)).replace(at,"$1"),n,r>s&&y(e.slice(s,r)),i>r&&y(e=e.slice(r)),i>r&&p(e))}c.push(n)}return h(c)}function v(e,t){var n=0,r=t.length>0,o=e.length>0,s=function(i,s,u,l,c){var f,p,d,h=[],m=0,y="0",v=i&&[],b=null!=c,x=j,T=i||o&&C.find.TAG("*",c&&s.parentNode||s),w=$+=null==x?1:Math.E;for(b&&(j=s!==L&&s,N=n);null!=(f=T[y]);y++){if(o&&f){for(p=0;d=e[p];p++)if(d(f,s,u)){l.push(f);break}b&&($=w,N=++n)}r&&((f=!d&&f)&&m--,i&&v.push(f))}if(m+=y,r&&y!==m){for(p=0;d=t[p];p++)d(v,h,s,u);if(i){if(m>0)for(;y--;)v[y]||h[y]||(h[y]=G.call(l));h=g(h)}Q.apply(l,h),b&&!i&&h.length>0&&m+t.length>1&&a.uniqueSort(l)}return b&&($=w,j=x),v};return r?i(s):s}function b(e,t,n){for(var r=0,i=t.length;i>r;r++)a(e,t[r],n);return n}function x(e,t,n,r){var i,o,a,s,u,l=f(e);if(!r&&1===l.length){if(o=l[0]=l[0].slice(0),o.length>2&&"ID"===(a=o[0]).type&&9===t.nodeType&&!M&&C.relative[o[1].type]){if(t=C.find.ID(a.matches[0].replace(xt,Tt),t)[0],!t)return n;e=e.slice(o.shift().value.length)}for(i=pt.needsContext.test(e)?-1:o.length-1;i>=0&&(a=o[i],!C.relative[s=a.type]);i--)if((u=C.find[s])&&(r=u(a.matches[0].replace(xt,Tt),dt.test(o[0].type)&&t.parentNode||t))){if(o.splice(i,1),e=r.length&&p(o),!e)return Q.apply(n,K.call(r,0)),n;break}}return S(e,l)(r,t,M,n,dt.test(e)),n}function T(){}var w,N,C,k,E,S,A,j,D,L,H,M,q,_,F,O,B,P="sizzle"+-new Date,R=e.document,W={},$=0,I=0,z=r(),X=r(),U=r(),V=typeof t,Y=1<<31,J=[],G=J.pop,Q=J.push,K=J.slice,Z=J.indexOf||function(e){for(var t=0,n=this.length;n>t;t++)if(this[t]===e)return t;return-1},et="[\\x20\\t\\r\\n\\f]",tt="(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",nt=tt.replace("w","w#"),rt="([*^$|!~]?=)",it="\\["+et+"*("+tt+")"+et+"*(?:"+rt+et+"*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|("+nt+")|)|)"+et+"*\\]",ot=":("+tt+")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|"+it.replace(3,8)+")*)|.*)\\)|)",at=RegExp("^"+et+"+|((?:^|[^\\\\])(?:\\\\.)*)"+et+"+$","g"),ut=RegExp("^"+et+"*,"+et+"*"),lt=RegExp("^"+et+"*([\\x20\\t\\r\\n\\f>+~])"+et+"*"),ct=RegExp(ot),ft=RegExp("^"+nt+"$"),pt={ID:RegExp("^#("+tt+")"),CLASS:RegExp("^\\.("+tt+")"),NAME:RegExp("^\\[name=['\"]?("+tt+")['\"]?\\]"),TAG:RegExp("^("+tt.replace("w","w*")+")"),ATTR:RegExp("^"+it),PSEUDO:RegExp("^"+ot),CHILD:RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+et+"*(even|odd|(([+-]|)(\\d*)n|)"+et+"*(?:([+-]|)"+et+"*(\\d+)|))"+et+"*\\)|)","i"),needsContext:RegExp("^"+et+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+et+"*((?:-\\d)?\\d*)"+et+"*\\)|)(?=[^-]|$)","i")},dt=/[\x20\t\r\n\f]*[+~]/,ht=/\{\s*\[native code\]\s*\}/,gt=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,mt=/^(?:input|select|textarea|button)$/i,yt=/^h\d$/i,vt=/'|\\/g,bt=/\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,xt=/\\([\da-fA-F]{1,6}[\x20\t\r\n\f]?|.)/g,Tt=function(e,t){var n="0x"+t-65536;return n!==n?t:0>n?String.fromCharCode(n+65536):String.fromCharCode(55296|n>>10,56320|1023&n)};try{K.call(H.childNodes,0)[0].nodeType}catch(wt){K=function(e){for(var t,n=[];t=this[e];e++)n.push(t);return n}}E=a.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return t?"HTML"!==t.nodeName:!1},D=a.setDocument=function(e){var r=e?e.ownerDocument||e:R;return r!==L&&9===r.nodeType&&r.documentElement?(L=r,H=r.documentElement,M=E(r),W.tagNameNoComments=o(function(e){return e.appendChild(r.createComment("")),!e.getElementsByTagName("*").length}),W.attributes=o(function(e){e.innerHTML="<select></select>";var t=typeof e.lastChild.getAttribute("multiple");return"boolean"!==t&&"string"!==t}),W.getByClassName=o(function(e){return e.innerHTML="<div class='hidden e'></div><div class='hidden'></div>",e.getElementsByClassName&&e.getElementsByClassName("e").length?(e.lastChild.className="e",2===e.getElementsByClassName("e").length):!1}),W.getByName=o(function(e){e.id=P+0,e.innerHTML="<a name='"+P+"'></a><div name='"+P+"'></div>",H.insertBefore(e,H.firstChild);var t=r.getElementsByName&&r.getElementsByName(P).length===2+r.getElementsByName(P+0).length;return W.getIdNotName=!r.getElementById(P),H.removeChild(e),t}),C.attrHandle=o(function(e){return e.innerHTML="<a href='#'></a>",e.firstChild&&typeof e.firstChild.getAttribute!==V&&"#"===e.firstChild.getAttribute("href")})?{}:{href:function(e){return e.getAttribute("href",2)},type:function(e){return e.getAttribute("type")}},W.getIdNotName?(C.find.ID=function(e,t){if(typeof t.getElementById!==V&&!M){var n=t.getElementById(e);return n&&n.parentNode?[n]:[]}},C.filter.ID=function(e){var t=e.replace(xt,Tt);return function(e){return e.getAttribute("id")===t}}):(C.find.ID=function(e,n){if(typeof n.getElementById!==V&&!M){var r=n.getElementById(e);return r?r.id===e||typeof r.getAttributeNode!==V&&r.getAttributeNode("id").value===e?[r]:t:[]}},C.filter.ID=function(e){var t=e.replace(xt,Tt);return function(e){var n=typeof e.getAttributeNode!==V&&e.getAttributeNode("id");return n&&n.value===t}}),C.find.TAG=W.tagNameNoComments?function(e,n){return typeof n.getElementsByTagName!==V?n.getElementsByTagName(e):t}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){for(;n=o[i];i++)1===n.nodeType&&r.push(n);return r}return o},C.find.NAME=W.getByName&&function(e,n){return typeof n.getElementsByName!==V?n.getElementsByName(name):t},C.find.CLASS=W.getByClassName&&function(e,n){return typeof n.getElementsByClassName===V||M?t:n.getElementsByClassName(e)},_=[],q=[":focus"],(W.qsa=n(r.querySelectorAll))&&(o(function(e){e.innerHTML="<select><option selected=''></option></select>",e.querySelectorAll("[selected]").length||q.push("\\["+et+"*(?:checked|disabled|ismap|multiple|readonly|selected|value)"),e.querySelectorAll(":checked").length||q.push(":checked")}),o(function(e){e.innerHTML="<input type='hidden' i=''/>",e.querySelectorAll("[i^='']").length&&q.push("[*^$]="+et+"*(?:\"\"|'')"),e.querySelectorAll(":enabled").length||q.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),q.push(",.*:")})),(W.matchesSelector=n(F=H.matchesSelector||H.mozMatchesSelector||H.webkitMatchesSelector||H.oMatchesSelector||H.msMatchesSelector))&&o(function(e){W.disconnectedMatch=F.call(e,"div"),F.call(e,"[s!='']:x"),_.push("!=",ot)}),q=RegExp(q.join("|")),_=RegExp(_.join("|")),O=n(H.contains)||H.compareDocumentPosition?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)for(;t=t.parentNode;)if(t===e)return!0;return!1},B=H.compareDocumentPosition?function(e,t){var n;return e===t?(A=!0,0):(n=t.compareDocumentPosition&&e.compareDocumentPosition&&e.compareDocumentPosition(t))?1&n||e.parentNode&&11===e.parentNode.nodeType?e===r||O(R,e)?-1:t===r||O(R,t)?1:0:4&n?-1:1:e.compareDocumentPosition?-1:1}:function(e,t){var n,i=0,o=e.parentNode,a=t.parentNode,u=[e],l=[t];if(e===t)return A=!0,0;if(e.sourceIndex&&t.sourceIndex)return(~t.sourceIndex||Y)-(O(R,e)&&~e.sourceIndex||Y);if(!o||!a)return e===r?-1:t===r?1:o?-1:a?1:0;if(o===a)return s(e,t);for(n=e;n=n.parentNode;)u.unshift(n);for(n=t;n=n.parentNode;)l.unshift(n);for(;u[i]===l[i];)i++;return i?s(u[i],l[i]):u[i]===R?-1:l[i]===R?1:0},A=!1,[0,0].sort(B),W.detectDuplicates=A,L):L},a.matches=function(e,t){return a(e,null,null,t)},a.matchesSelector=function(e,t){if((e.ownerDocument||e)!==L&&D(e),t=t.replace(bt,"='$1']"),!(!W.matchesSelector||M||_&&_.test(t)||q.test(t)))try{var n=F.call(e,t);if(n||W.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(r){}return a(t,L,null,[e]).length>0},a.contains=function(e,t){return(e.ownerDocument||e)!==L&&D(e),O(e,t)},a.attr=function(e,t){var n;return(e.ownerDocument||e)!==L&&D(e),M||(t=t.toLowerCase()),(n=C.attrHandle[t])?n(e):M||W.attributes?e.getAttribute(t):((n=e.getAttributeNode(t))||e.getAttribute(t))&&e[t]===!0?t:n&&n.specified?n.value:null},a.error=function(e){throw Error("Syntax error, unrecognized expression: "+e)},a.uniqueSort=function(e){var t,n=[],r=1,i=0;if(A=!W.detectDuplicates,e.sort(B),A){for(;t=e[r];r++)t===e[r-1]&&(i=n.push(r));for(;i--;)e.splice(n[i],1)}return e},k=a.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=k(e)}else if(3===i||4===i)return e.nodeValue}else for(;t=e[r];r++)n+=k(t);return n},C=a.selectors={cacheLength:50,createPseudo:i,match:pt,find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(xt,Tt),e[3]=(e[4]||e[5]||"").replace(xt,Tt),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||a.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&a.error(e[0]),e},PSEUDO:function(e){var t,n=!e[5]&&e[2];return pt.CHILD.test(e[0])?null:(e[4]?e[2]=e[4]:n&&ct.test(n)&&(t=f(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){return"*"===e?function(){return!0}:(e=e.replace(xt,Tt).toLowerCase(),function(t){return t.nodeName&&t.nodeName.toLowerCase()===e})},CLASS:function(e){var t=z[e+" "];return t||(t=RegExp("(^|"+et+")"+e+"("+et+"|$)"))&&z(e,function(e){return t.test(e.className||typeof e.getAttribute!==V&&e.getAttribute("class")||"")})},ATTR:function(e,t,n){return function(r){var i=a.attr(r,e);return null==i?"!="===t:t?(i+="","="===t?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.substr(i.length-n.length)===n:"~="===t?(" "+i+" ").indexOf(n)>-1:"|="===t?i===n||i.substr(0,n.length+1)===n+"-":!1):!0}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,f,p,d,h,g=o!==a?"nextSibling":"previousSibling",m=t.parentNode,y=s&&t.nodeName.toLowerCase(),v=!u&&!s;if(m){if(o){for(;g;){for(f=t;f=f[g];)if(s?f.nodeName.toLowerCase()===y:1===f.nodeType)return!1;h=g="only"===e&&!h&&"nextSibling"}return!0}if(h=[a?m.firstChild:m.lastChild],a&&v){for(c=m[P]||(m[P]={}),l=c[e]||[],d=l[0]===$&&l[1],p=l[0]===$&&l[2],f=d&&m.childNodes[d];f=++d&&f&&f[g]||(p=d=0)||h.pop();)if(1===f.nodeType&&++p&&f===t){c[e]=[$,d,p];break}}else if(v&&(l=(t[P]||(t[P]={}))[e])&&l[0]===$)p=l[1];else for(;(f=++d&&f&&f[g]||(p=d=0)||h.pop())&&((s?f.nodeName.toLowerCase()!==y:1!==f.nodeType)||!++p||(v&&((f[P]||(f[P]={}))[e]=[$,p]),f!==t)););return p-=i,p===r||0===p%r&&p/r>=0}}},PSEUDO:function(e,t){var n,r=C.pseudos[e]||C.setFilters[e.toLowerCase()]||a.error("unsupported pseudo: "+e);return r[P]?r(t):r.length>1?(n=[e,e,"",t],C.setFilters.hasOwnProperty(e.toLowerCase())?i(function(e,n){for(var i,o=r(e,t),a=o.length;a--;)i=Z.call(e,o[a]),e[i]=!(n[i]=o[a])}):function(e){return r(e,0,n)}):r}},pseudos:{not:i(function(e){var t=[],n=[],r=S(e.replace(at,"$1"));return r[P]?i(function(e,t,n,i){for(var o,a=r(e,null,i,[]),s=e.length;s--;)(o=a[s])&&(e[s]=!(t[s]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),!n.pop()}}),has:i(function(e){return function(t){return a(e,t).length>0}}),contains:i(function(e){return function(t){return(t.textContent||t.innerText||k(t)).indexOf(e)>-1}}),lang:i(function(e){return ft.test(e||"")||a.error("unsupported lang: "+e),e=e.replace(xt,Tt).toLowerCase(),function(t){var n;do if(n=M?t.getAttribute("xml:lang")||t.getAttribute("lang"):t.lang)return n=n.toLowerCase(),n===e||0===n.indexOf(e+"-");while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===H},focus:function(e){return e===L.activeElement&&(!L.hasFocus||L.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:function(e){return e.disabled===!1},disabled:function(e){return e.disabled===!0},checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,e.selected===!0},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeName>"@"||3===e.nodeType||4===e.nodeType)return!1;return!0},parent:function(e){return!C.pseudos.empty(e)},header:function(e){return yt.test(e.nodeName)},input:function(e){return mt.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||t.toLowerCase()===e.type)},first:c(function(){return[0]}),last:c(function(e,t){return[t-1]}),eq:c(function(e,t,n){return[0>n?n+t:n]}),even:c(function(e,t){for(var n=0;t>n;n+=2)e.push(n);return e}),odd:c(function(e,t){for(var n=1;t>n;n+=2)e.push(n);return e}),lt:c(function(e,t,n){for(var r=0>n?n+t:n;--r>=0;)e.push(r);return e}),gt:c(function(e,t,n){for(var r=0>n?n+t:n;t>++r;)e.push(r);return e})}};for(w in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})C.pseudos[w]=u(w);for(w in{submit:!0,reset:!0})C.pseudos[w]=l(w);S=a.compile=function(e,t){var n,r=[],i=[],o=U[e+" "];if(!o){for(t||(t=f(e)),n=t.length;n--;)o=y(t[n]),o[P]?r.push(o):i.push(o);o=U(e,v(i,r))}return o},C.pseudos.nth=C.pseudos.eq,C.filters=T.prototype=C.pseudos,C.setFilters=new T,D(),a.attr=st.attr,st.find=a,st.expr=a.selectors,st.expr[":"]=st.expr.pseudos,st.unique=a.uniqueSort,st.text=a.getText,st.isXMLDoc=a.isXML,st.contains=a.contains}(e);var Pt=/Until$/,Rt=/^(?:parents|prev(?:Until|All))/,Wt=/^.[^:#\[\.,]*$/,$t=st.expr.match.needsContext,It={children:!0,contents:!0,next:!0,prev:!0};st.fn.extend({find:function(e){var t,n,r;if("string"!=typeof e)return r=this,this.pushStack(st(e).filter(function(){for(t=0;r.length>t;t++)if(st.contains(r[t],this))return!0}));for(n=[],t=0;this.length>t;t++)st.find(e,this[t],n);return n=this.pushStack(st.unique(n)),n.selector=(this.selector?this.selector+" ":"")+e,n},has:function(e){var t,n=st(e,this),r=n.length;return this.filter(function(){for(t=0;r>t;t++)if(st.contains(this,n[t]))return!0})},not:function(e){return this.pushStack(f(this,e,!1))},filter:function(e){return this.pushStack(f(this,e,!0))},is:function(e){return!!e&&("string"==typeof e?$t.test(e)?st(e,this.context).index(this[0])>=0:st.filter(e,this).length>0:this.filter(e).length>0)},closest:function(e,t){for(var n,r=0,i=this.length,o=[],a=$t.test(e)||"string"!=typeof e?st(e,t||this.context):0;i>r;r++)for(n=this[r];n&&n.ownerDocument&&n!==t&&11!==n.nodeType;){if(a?a.index(n)>-1:st.find.matchesSelector(n,e)){o.push(n);break}n=n.parentNode}return this.pushStack(o.length>1?st.unique(o):o)},index:function(e){return e?"string"==typeof e?st.inArray(this[0],st(e)):st.inArray(e.jquery?e[0]:e,this):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){var n="string"==typeof e?st(e,t):st.makeArray(e&&e.nodeType?[e]:e),r=st.merge(this.get(),n);return this.pushStack(st.unique(r))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),st.fn.andSelf=st.fn.addBack,st.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return st.dir(e,"parentNode")},parentsUntil:function(e,t,n){return st.dir(e,"parentNode",n)},next:function(e){return c(e,"nextSibling")},prev:function(e){return c(e,"previousSibling")},nextAll:function(e){return st.dir(e,"nextSibling")},prevAll:function(e){return st.dir(e,"previousSibling")},nextUntil:function(e,t,n){return st.dir(e,"nextSibling",n)},prevUntil:function(e,t,n){return st.dir(e,"previousSibling",n)},siblings:function(e){return st.sibling((e.parentNode||{}).firstChild,e)},children:function(e){return st.sibling(e.firstChild)},contents:function(e){return st.nodeName(e,"iframe")?e.contentDocument||e.contentWindow.document:st.merge([],e.childNodes)}},function(e,t){st.fn[e]=function(n,r){var i=st.map(this,t,n);return Pt.test(e)||(r=n),r&&"string"==typeof r&&(i=st.filter(r,i)),i=this.length>1&&!It[e]?st.unique(i):i,this.length>1&&Rt.test(e)&&(i=i.reverse()),this.pushStack(i)}}),st.extend({filter:function(e,t,n){return n&&(e=":not("+e+")"),1===t.length?st.find.matchesSelector(t[0],e)?[t[0]]:[]:st.find.matches(e,t)},dir:function(e,n,r){for(var i=[],o=e[n];o&&9!==o.nodeType&&(r===t||1!==o.nodeType||!st(o).is(r));)1===o.nodeType&&i.push(o),o=o[n];return i},sibling:function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n}});var zt="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",Xt=/ jQuery\d+="(?:null|\d+)"/g,Ut=RegExp("<(?:"+zt+")[\\s/>]","i"),Vt=/^\s+/,Yt=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,Jt=/<([\w:]+)/,Gt=/<tbody/i,Qt=/<|&#?\w+;/,Kt=/<(?:script|style|link)/i,Zt=/^(?:checkbox|radio)$/i,en=/checked\s*(?:[^=]|=\s*.checked.)/i,tn=/^$|\/(?:java|ecma)script/i,nn=/^true\/(.*)/,rn=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,on={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],area:[1,"<map>","</map>"],param:[1,"<object>","</object>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:st.support.htmlSerialize?[0,"",""]:[1,"X<div>","</div>"]},an=p(V),sn=an.appendChild(V.createElement("div"));on.optgroup=on.option,on.tbody=on.tfoot=on.colgroup=on.caption=on.thead,on.th=on.td,st.fn.extend({text:function(e){return st.access(this,function(e){return e===t?st.text(this):this.empty().append((this[0]&&this[0].ownerDocument||V).createTextNode(e))},null,e,arguments.length)},wrapAll:function(e){if(st.isFunction(e))return this.each(function(t){st(this).wrapAll(e.call(this,t))});if(this[0]){var t=st(e,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){for(var e=this;e.firstChild&&1===e.firstChild.nodeType;)e=e.firstChild;return e}).append(this)}return this},wrapInner:function(e){return st.isFunction(e)?this.each(function(t){st(this).wrapInner(e.call(this,t))}):this.each(function(){var t=st(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=st.isFunction(e);return this.each(function(n){st(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(){return this.parent().each(function(){st.nodeName(this,"body")||st(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,!0,function(e){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&this.appendChild(e)})},prepend:function(){return this.domManip(arguments,!0,function(e){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&this.insertBefore(e,this.firstChild)})},before:function(){return this.domManip(arguments,!1,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return this.domManip(arguments,!1,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},remove:function(e,t){for(var n,r=0;null!=(n=this[r]);r++)(!e||st.filter(e,[n]).length>0)&&(t||1!==n.nodeType||st.cleanData(b(n)),n.parentNode&&(t&&st.contains(n.ownerDocument,n)&&m(b(n,"script")),n.parentNode.removeChild(n)));return this},empty:function(){for(var e,t=0;null!=(e=this[t]);t++){for(1===e.nodeType&&st.cleanData(b(e,!1));e.firstChild;)e.removeChild(e.firstChild);e.options&&st.nodeName(e,"select")&&(e.options.length=0)}return this},clone:function(e,t){return e=null==e?!1:e,t=null==t?e:t,this.map(function(){return st.clone(this,e,t)})},html:function(e){return st.access(this,function(e){var n=this[0]||{},r=0,i=this.length;if(e===t)return 1===n.nodeType?n.innerHTML.replace(Xt,""):t;if(!("string"!=typeof e||Kt.test(e)||!st.support.htmlSerialize&&Ut.test(e)||!st.support.leadingWhitespace&&Vt.test(e)||on[(Jt.exec(e)||["",""])[1].toLowerCase()])){e=e.replace(Yt,"<$1></$2>");try{for(;i>r;r++)n=this[r]||{},1===n.nodeType&&(st.cleanData(b(n,!1)),n.innerHTML=e);n=0}catch(o){}}n&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(e){var t=st.isFunction(e);return t||"string"==typeof e||(e=st(e).not(this).detach()),this.domManip([e],!0,function(e){var t=this.nextSibling,n=this.parentNode;(n&&1===this.nodeType||11===this.nodeType)&&(st(this).remove(),t?t.parentNode.insertBefore(e,t):n.appendChild(e))})},detach:function(e){return this.remove(e,!0)},domManip:function(e,n,r){e=et.apply([],e);var i,o,a,s,u,l,c=0,f=this.length,p=this,m=f-1,y=e[0],v=st.isFunction(y);if(v||!(1>=f||"string"!=typeof y||st.support.checkClone)&&en.test(y))return this.each(function(i){var o=p.eq(i);v&&(e[0]=y.call(this,i,n?o.html():t)),o.domManip(e,n,r)});if(f&&(i=st.buildFragment(e,this[0].ownerDocument,!1,this),o=i.firstChild,1===i.childNodes.length&&(i=o),o)){for(n=n&&st.nodeName(o,"tr"),a=st.map(b(i,"script"),h),s=a.length;f>c;c++)u=i,c!==m&&(u=st.clone(u,!0,!0),s&&st.merge(a,b(u,"script"))),r.call(n&&st.nodeName(this[c],"table")?d(this[c],"tbody"):this[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,st.map(a,g),c=0;s>c;c++)u=a[c],tn.test(u.type||"")&&!st._data(u,"globalEval")&&st.contains(l,u)&&(u.src?st.ajax({url:u.src,type:"GET",dataType:"script",async:!1,global:!1,"throws":!0}):st.globalEval((u.text||u.textContent||u.innerHTML||"").replace(rn,"")));i=o=null}return this}}),st.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){st.fn[e]=function(e){for(var n,r=0,i=[],o=st(e),a=o.length-1;a>=r;r++)n=r===a?this:this.clone(!0),st(o[r])[t](n),tt.apply(i,n.get());return this.pushStack(i)}}),st.extend({clone:function(e,t,n){var r,i,o,a,s,u=st.contains(e.ownerDocument,e);if(st.support.html5Clone||st.isXMLDoc(e)||!Ut.test("<"+e.nodeName+">")?s=e.cloneNode(!0):(sn.innerHTML=e.outerHTML,sn.removeChild(s=sn.firstChild)),!(st.support.noCloneEvent&&st.support.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||st.isXMLDoc(e)))for(r=b(s),i=b(e),a=0;null!=(o=i[a]);++a)r[a]&&v(o,r[a]);if(t)if(n)for(i=i||b(e),r=r||b(s),a=0;null!=(o=i[a]);a++)y(o,r[a]);else y(e,s);return r=b(s,"script"),r.length>0&&m(r,!u&&b(e,"script")),r=i=o=null,s},buildFragment:function(e,t,n,r){for(var i,o,a,s,u,l,c,f=e.length,d=p(t),h=[],g=0;f>g;g++)if(o=e[g],o||0===o)if("object"===st.type(o))st.merge(h,o.nodeType?[o]:o);else if(Qt.test(o)){for(s=s||d.appendChild(t.createElement("div")),a=(Jt.exec(o)||["",""])[1].toLowerCase(),u=on[a]||on._default,s.innerHTML=u[1]+o.replace(Yt,"<$1></$2>")+u[2],c=u[0];c--;)s=s.lastChild;if(!st.support.leadingWhitespace&&Vt.test(o)&&h.push(t.createTextNode(Vt.exec(o)[0])),!st.support.tbody)for(o="table"!==a||Gt.test(o)?"<table>"!==u[1]||Gt.test(o)?0:s:s.firstChild,c=o&&o.childNodes.length;c--;)st.nodeName(l=o.childNodes[c],"tbody")&&!l.childNodes.length&&o.removeChild(l);for(st.merge(h,s.childNodes),s.textContent="";s.firstChild;)s.removeChild(s.firstChild);s=d.lastChild}else h.push(t.createTextNode(o));for(s&&d.removeChild(s),st.support.appendChecked||st.grep(b(h,"input"),x),g=0;o=h[g++];)if((!r||-1===st.inArray(o,r))&&(i=st.contains(o.ownerDocument,o),s=b(d.appendChild(o),"script"),i&&m(s),n))for(c=0;o=s[c++];)tn.test(o.type||"")&&n.push(o);return s=null,d},cleanData:function(e,n){for(var r,i,o,a,s=0,u=st.expando,l=st.cache,c=st.support.deleteExpando,f=st.event.special;null!=(o=e[s]);s++)if((n||st.acceptData(o))&&(i=o[u],r=i&&l[i])){if(r.events)for(a in r.events)f[a]?st.event.remove(o,a):st.removeEvent(o,a,r.handle);l[i]&&(delete l[i],c?delete o[u]:o.removeAttribute!==t?o.removeAttribute(u):o[u]=null,K.push(i))}}});var un,ln,cn,fn=/alpha\([^)]*\)/i,pn=/opacity\s*=\s*([^)]*)/,dn=/^(top|right|bottom|left)$/,hn=/^(none|table(?!-c[ea]).+)/,gn=/^margin/,mn=RegExp("^("+ut+")(.*)$","i"),yn=RegExp("^("+ut+")(?!px)[a-z%]+$","i"),vn=RegExp("^([+-])=("+ut+")","i"),bn={BODY:"block"},xn={position:"absolute",visibility:"hidden",display:"block"},Tn={letterSpacing:0,fontWeight:400},wn=["Top","Right","Bottom","Left"],Nn=["Webkit","O","Moz","ms"];st.fn.extend({css:function(e,n){return st.access(this,function(e,n,r){var i,o,a={},s=0;if(st.isArray(n)){for(i=ln(e),o=n.length;o>s;s++)a[n[s]]=st.css(e,n[s],!1,i);return a}return r!==t?st.style(e,n,r):st.css(e,n)},e,n,arguments.length>1)},show:function(){return N(this,!0)},hide:function(){return N(this)},toggle:function(e){var t="boolean"==typeof e;return this.each(function(){(t?e:w(this))?st(this).show():st(this).hide()})}}),st.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=un(e,"opacity");return""===n?"1":n}}}},cssNumber:{columnCount:!0,fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":st.support.cssFloat?"cssFloat":"styleFloat"},style:function(e,n,r,i){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var o,a,s,u=st.camelCase(n),l=e.style;if(n=st.cssProps[u]||(st.cssProps[u]=T(l,u)),s=st.cssHooks[n]||st.cssHooks[u],r===t)return s&&"get"in s&&(o=s.get(e,!1,i))!==t?o:l[n];if(a=typeof r,"string"===a&&(o=vn.exec(r))&&(r=(o[1]+1)*o[2]+parseFloat(st.css(e,n)),a="number"),!(null==r||"number"===a&&isNaN(r)||("number"!==a||st.cssNumber[u]||(r+="px"),st.support.clearCloneStyle||""!==r||0!==n.indexOf("background")||(l[n]="inherit"),s&&"set"in s&&(r=s.set(e,r,i))===t)))try{l[n]=r}catch(c){}}},css:function(e,n,r,i){var o,a,s,u=st.camelCase(n);return n=st.cssProps[u]||(st.cssProps[u]=T(e.style,u)),s=st.cssHooks[n]||st.cssHooks[u],s&&"get"in s&&(o=s.get(e,!0,r)),o===t&&(o=un(e,n,i)),"normal"===o&&n in Tn&&(o=Tn[n]),r?(a=parseFloat(o),r===!0||st.isNumeric(a)?a||0:o):o},swap:function(e,t,n,r){var i,o,a={};for(o in t)a[o]=e.style[o],e.style[o]=t[o];i=n.apply(e,r||[]);for(o in t)e.style[o]=a[o];return i}}),e.getComputedStyle?(ln=function(t){return e.getComputedStyle(t,null)},un=function(e,n,r){var i,o,a,s=r||ln(e),u=s?s.getPropertyValue(n)||s[n]:t,l=e.style;return s&&(""!==u||st.contains(e.ownerDocument,e)||(u=st.style(e,n)),yn.test(u)&&gn.test(n)&&(i=l.width,o=l.minWidth,a=l.maxWidth,l.minWidth=l.maxWidth=l.width=u,u=s.width,l.width=i,l.minWidth=o,l.maxWidth=a)),u}):V.documentElement.currentStyle&&(ln=function(e){return e.currentStyle},un=function(e,n,r){var i,o,a,s=r||ln(e),u=s?s[n]:t,l=e.style;return null==u&&l&&l[n]&&(u=l[n]),yn.test(u)&&!dn.test(n)&&(i=l.left,o=e.runtimeStyle,a=o&&o.left,a&&(o.left=e.currentStyle.left),l.left="fontSize"===n?"1em":u,u=l.pixelLeft+"px",l.left=i,a&&(o.left=a)),""===u?"auto":u}),st.each(["height","width"],function(e,n){st.cssHooks[n]={get:function(e,r,i){return r?0===e.offsetWidth&&hn.test(st.css(e,"display"))?st.swap(e,xn,function(){return E(e,n,i)}):E(e,n,i):t},set:function(e,t,r){var i=r&&ln(e);return C(e,t,r?k(e,n,r,st.support.boxSizing&&"border-box"===st.css(e,"boxSizing",!1,i),i):0)}}}),st.support.opacity||(st.cssHooks.opacity={get:function(e,t){return pn.test((t&&e.currentStyle?e.currentStyle.filter:e.style.filter)||"")?.01*parseFloat(RegExp.$1)+"":t?"1":""},set:function(e,t){var n=e.style,r=e.currentStyle,i=st.isNumeric(t)?"alpha(opacity="+100*t+")":"",o=r&&r.filter||n.filter||"";n.zoom=1,(t>=1||""===t)&&""===st.trim(o.replace(fn,""))&&n.removeAttribute&&(n.removeAttribute("filter"),""===t||r&&!r.filter)||(n.filter=fn.test(o)?o.replace(fn,i):o+" "+i)}}),st(function(){st.support.reliableMarginRight||(st.cssHooks.marginRight={get:function(e,n){return n?st.swap(e,{display:"inline-block"},un,[e,"marginRight"]):t}}),!st.support.pixelPosition&&st.fn.position&&st.each(["top","left"],function(e,n){st.cssHooks[n]={get:function(e,r){return r?(r=un(e,n),yn.test(r)?st(e).position()[n]+"px":r):t}}})}),st.expr&&st.expr.filters&&(st.expr.filters.hidden=function(e){return 0===e.offsetWidth&&0===e.offsetHeight||!st.support.reliableHiddenOffsets&&"none"===(e.style&&e.style.display||st.css(e,"display"))},st.expr.filters.visible=function(e){return!st.expr.filters.hidden(e)}),st.each({margin:"",padding:"",border:"Width"},function(e,t){st.cssHooks[e+t]={expand:function(n){for(var r=0,i={},o="string"==typeof n?n.split(" "):[n];4>r;r++)i[e+wn[r]+t]=o[r]||o[r-2]||o[0];return i}},gn.test(e)||(st.cssHooks[e+t].set=C)});var Cn=/%20/g,kn=/\[\]$/,En=/\r?\n/g,Sn=/^(?:submit|button|image|reset)$/i,An=/^(?:input|select|textarea|keygen)/i;st.fn.extend({serialize:function(){return st.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=st.prop(this,"elements");return e?st.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!st(this).is(":disabled")&&An.test(this.nodeName)&&!Sn.test(e)&&(this.checked||!Zt.test(e))}).map(function(e,t){var n=st(this).val();return null==n?null:st.isArray(n)?st.map(n,function(e){return{name:t.name,value:e.replace(En,"\r\n")}}):{name:t.name,value:n.replace(En,"\r\n")}}).get()}}),st.param=function(e,n){var r,i=[],o=function(e,t){t=st.isFunction(t)?t():null==t?"":t,i[i.length]=encodeURIComponent(e)+"="+encodeURIComponent(t)};if(n===t&&(n=st.ajaxSettings&&st.ajaxSettings.traditional),st.isArray(e)||e.jquery&&!st.isPlainObject(e))st.each(e,function(){o(this.name,this.value)});else for(r in e)j(r,e[r],n,o);return i.join("&").replace(Cn,"+")};var jn,Dn,Ln=st.now(),Hn=/\?/,Mn=/#.*$/,qn=/([?&])_=[^&]*/,_n=/^(.*?):[ \t]*([^\r\n]*)\r?$/gm,Fn=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,On=/^(?:GET|HEAD)$/,Bn=/^\/\//,Pn=/^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,Rn=st.fn.load,Wn={},$n={},In="*/".concat("*");try{Dn=Y.href}catch(zn){Dn=V.createElement("a"),Dn.href="",Dn=Dn.href}jn=Pn.exec(Dn.toLowerCase())||[],st.fn.load=function(e,n,r){if("string"!=typeof e&&Rn)return Rn.apply(this,arguments);var i,o,a,s=this,u=e.indexOf(" ");return u>=0&&(i=e.slice(u,e.length),e=e.slice(0,u)),st.isFunction(n)?(r=n,n=t):n&&"object"==typeof n&&(o="POST"),s.length>0&&st.ajax({url:e,type:o,dataType:"html",data:n}).done(function(e){a=arguments,s.html(i?st("<div>").append(st.parseHTML(e)).find(i):e)}).complete(r&&function(e,t){s.each(r,a||[e.responseText,t,e])}),this},st.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){st.fn[t]=function(e){return this.on(t,e)}}),st.each(["get","post"],function(e,n){st[n]=function(e,r,i,o){return st.isFunction(r)&&(o=o||i,i=r,r=t),st.ajax({url:e,type:n,dataType:o,data:r,success:i})}}),st.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Dn,type:"GET",isLocal:Fn.test(jn[1]),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":In,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":e.String,"text html":!0,"text json":st.parseJSON,"text xml":st.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?H(H(e,st.ajaxSettings),t):H(st.ajaxSettings,e)},ajaxPrefilter:D(Wn),ajaxTransport:D($n),ajax:function(e,n){function r(e,n,r,s){var l,f,v,b,T,N=n;2!==x&&(x=2,u&&clearTimeout(u),i=t,a=s||"",w.readyState=e>0?4:0,r&&(b=M(p,w,r)),e>=200&&300>e||304===e?(p.ifModified&&(T=w.getResponseHeader("Last-Modified"),T&&(st.lastModified[o]=T),T=w.getResponseHeader("etag"),T&&(st.etag[o]=T)),304===e?(l=!0,N="notmodified"):(l=q(p,b),N=l.state,f=l.data,v=l.error,l=!v)):(v=N,(e||!N)&&(N="error",0>e&&(e=0))),w.status=e,w.statusText=(n||N)+"",l?g.resolveWith(d,[f,N,w]):g.rejectWith(d,[w,N,v]),w.statusCode(y),y=t,c&&h.trigger(l?"ajaxSuccess":"ajaxError",[w,p,l?f:v]),m.fireWith(d,[w,N]),c&&(h.trigger("ajaxComplete",[w,p]),--st.active||st.event.trigger("ajaxStop")))}"object"==typeof e&&(n=e,e=t),n=n||{};var i,o,a,s,u,l,c,f,p=st.ajaxSetup({},n),d=p.context||p,h=p.context&&(d.nodeType||d.jquery)?st(d):st.event,g=st.Deferred(),m=st.Callbacks("once memory"),y=p.statusCode||{},v={},b={},x=0,T="canceled",w={readyState:0,getResponseHeader:function(e){var t;if(2===x){if(!s)for(s={};t=_n.exec(a);)s[t[1].toLowerCase()]=t[2];t=s[e.toLowerCase()]}return null==t?null:t},getAllResponseHeaders:function(){return 2===x?a:null},setRequestHeader:function(e,t){var n=e.toLowerCase();return x||(e=b[n]=b[n]||e,v[e]=t),this},overrideMimeType:function(e){return x||(p.mimeType=e),this},statusCode:function(e){var t;if(e)if(2>x)for(t in e)y[t]=[y[t],e[t]];else w.always(e[w.status]);return this},abort:function(e){var t=e||T;return i&&i.abort(t),r(0,t),this}};if(g.promise(w).complete=m.add,w.success=w.done,w.error=w.fail,p.url=((e||p.url||Dn)+"").replace(Mn,"").replace(Bn,jn[1]+"//"),p.type=n.method||n.type||p.method||p.type,p.dataTypes=st.trim(p.dataType||"*").toLowerCase().match(lt)||[""],null==p.crossDomain&&(l=Pn.exec(p.url.toLowerCase()),p.crossDomain=!(!l||l[1]===jn[1]&&l[2]===jn[2]&&(l[3]||("http:"===l[1]?80:443))==(jn[3]||("http:"===jn[1]?80:443)))),p.data&&p.processData&&"string"!=typeof p.data&&(p.data=st.param(p.data,p.traditional)),L(Wn,p,n,w),2===x)return w;c=p.global,c&&0===st.active++&&st.event.trigger("ajaxStart"),p.type=p.type.toUpperCase(),p.hasContent=!On.test(p.type),o=p.url,p.hasContent||(p.data&&(o=p.url+=(Hn.test(o)?"&":"?")+p.data,delete p.data),p.cache===!1&&(p.url=qn.test(o)?o.replace(qn,"$1_="+Ln++):o+(Hn.test(o)?"&":"?")+"_="+Ln++)),p.ifModified&&(st.lastModified[o]&&w.setRequestHeader("If-Modified-Since",st.lastModified[o]),st.etag[o]&&w.setRequestHeader("If-None-Match",st.etag[o])),(p.data&&p.hasContent&&p.contentType!==!1||n.contentType)&&w.setRequestHeader("Content-Type",p.contentType),w.setRequestHeader("Accept",p.dataTypes[0]&&p.accepts[p.dataTypes[0]]?p.accepts[p.dataTypes[0]]+("*"!==p.dataTypes[0]?", "+In+"; q=0.01":""):p.accepts["*"]);for(f in p.headers)w.setRequestHeader(f,p.headers[f]);if(p.beforeSend&&(p.beforeSend.call(d,w,p)===!1||2===x))return w.abort();T="abort";for(f in{success:1,error:1,complete:1})w[f](p[f]);if(i=L($n,p,n,w)){w.readyState=1,c&&h.trigger("ajaxSend",[w,p]),p.async&&p.timeout>0&&(u=setTimeout(function(){w.abort("timeout")},p.timeout));try{x=1,i.send(v,r)}catch(N){if(!(2>x))throw N;r(-1,N)}}else r(-1,"No Transport");return w},getScript:function(e,n){return st.get(e,t,n,"script")},getJSON:function(e,t,n){return st.get(e,t,n,"json")}}),st.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/(?:java|ecma)script/},converters:{"text script":function(e){return st.globalEval(e),e}}}),st.ajaxPrefilter("script",function(e){e.cache===t&&(e.cache=!1),e.crossDomain&&(e.type="GET",e.global=!1)}),st.ajaxTransport("script",function(e){if(e.crossDomain){var n,r=V.head||st("head")[0]||V.documentElement;return{send:function(t,i){n=V.createElement("script"),n.async=!0,e.scriptCharset&&(n.charset=e.scriptCharset),n.src=e.url,n.onload=n.onreadystatechange=function(e,t){(t||!n.readyState||/loaded|complete/.test(n.readyState))&&(n.onload=n.onreadystatechange=null,n.parentNode&&n.parentNode.removeChild(n),n=null,t||i(200,"success"))},r.insertBefore(n,r.firstChild)},abort:function(){n&&n.onload(t,!0)}}}});var Xn=[],Un=/(=)\?(?=&|$)|\?\?/;st.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Xn.pop()||st.expando+"_"+Ln++;return this[e]=!0,e}}),st.ajaxPrefilter("json jsonp",function(n,r,i){var o,a,s,u=n.jsonp!==!1&&(Un.test(n.url)?"url":"string"==typeof n.data&&!(n.contentType||"").indexOf("application/x-www-form-urlencoded")&&Un.test(n.data)&&"data");return u||"jsonp"===n.dataTypes[0]?(o=n.jsonpCallback=st.isFunction(n.jsonpCallback)?n.jsonpCallback():n.jsonpCallback,u?n[u]=n[u].replace(Un,"$1"+o):n.jsonp!==!1&&(n.url+=(Hn.test(n.url)?"&":"?")+n.jsonp+"="+o),n.converters["script json"]=function(){return s||st.error(o+" was not called"),s[0]},n.dataTypes[0]="json",a=e[o],e[o]=function(){s=arguments},i.always(function(){e[o]=a,n[o]&&(n.jsonpCallback=r.jsonpCallback,Xn.push(o)),s&&st.isFunction(a)&&a(s[0]),s=a=t}),"script"):t});var Vn,Yn,Jn=0,Gn=e.ActiveXObject&&function(){var e;for(e in Vn)Vn[e](t,!0)};st.ajaxSettings.xhr=e.ActiveXObject?function(){return!this.isLocal&&_()||F()}:_,Yn=st.ajaxSettings.xhr(),st.support.cors=!!Yn&&"withCredentials"in Yn,Yn=st.support.ajax=!!Yn,Yn&&st.ajaxTransport(function(n){if(!n.crossDomain||st.support.cors){var r;return{send:function(i,o){var a,s,u=n.xhr();if(n.username?u.open(n.type,n.url,n.async,n.username,n.password):u.open(n.type,n.url,n.async),n.xhrFields)for(s in n.xhrFields)u[s]=n.xhrFields[s];n.mimeType&&u.overrideMimeType&&u.overrideMimeType(n.mimeType),n.crossDomain||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest");try{for(s in i)u.setRequestHeader(s,i[s])}catch(l){}u.send(n.hasContent&&n.data||null),r=function(e,i){var s,l,c,f,p;try{if(r&&(i||4===u.readyState))if(r=t,a&&(u.onreadystatechange=st.noop,Gn&&delete Vn[a]),i)4!==u.readyState&&u.abort();else{f={},s=u.status,p=u.responseXML,c=u.getAllResponseHeaders(),p&&p.documentElement&&(f.xml=p),"string"==typeof u.responseText&&(f.text=u.responseText);try{l=u.statusText}catch(d){l=""}s||!n.isLocal||n.crossDomain?1223===s&&(s=204):s=f.text?200:404}}catch(h){i||o(-1,h)}f&&o(s,l,f,c)},n.async?4===u.readyState?setTimeout(r):(a=++Jn,Gn&&(Vn||(Vn={},st(e).unload(Gn)),Vn[a]=r),u.onreadystatechange=r):r()},abort:function(){r&&r(t,!0)}}}});var Qn,Kn,Zn=/^(?:toggle|show|hide)$/,er=RegExp("^(?:([+-])=|)("+ut+")([a-z%]*)$","i"),tr=/queueHooks$/,nr=[W],rr={"*":[function(e,t){var n,r,i=this.createTween(e,t),o=er.exec(t),a=i.cur(),s=+a||0,u=1,l=20;if(o){if(n=+o[2],r=o[3]||(st.cssNumber[e]?"":"px"),"px"!==r&&s){s=st.css(i.elem,e,!0)||n||1;do u=u||".5",s/=u,st.style(i.elem,e,s+r);while(u!==(u=i.cur()/a)&&1!==u&&--l)}i.unit=r,i.start=s,i.end=o[1]?s+(o[1]+1)*n:n}return i}]};st.Animation=st.extend(P,{tweener:function(e,t){st.isFunction(e)?(t=e,e=["*"]):e=e.split(" ");for(var n,r=0,i=e.length;i>r;r++)n=e[r],rr[n]=rr[n]||[],rr[n].unshift(t)},prefilter:function(e,t){t?nr.unshift(e):nr.push(e)}}),st.Tween=$,$.prototype={constructor:$,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||"swing",this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(st.cssNumber[n]?"":"px")},cur:function(){var e=$.propHooks[this.prop];return e&&e.get?e.get(this):$.propHooks._default.get(this)},run:function(e){var t,n=$.propHooks[this.prop];return this.pos=t=this.options.duration?st.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):$.propHooks._default.set(this),this}},$.prototype.init.prototype=$.prototype,$.propHooks={_default:{get:function(e){var t;return null==e.elem[e.prop]||e.elem.style&&null!=e.elem.style[e.prop]?(t=st.css(e.elem,e.prop,"auto"),t&&"auto"!==t?t:0):e.elem[e.prop]},set:function(e){st.fx.step[e.prop]?st.fx.step[e.prop](e):e.elem.style&&(null!=e.elem.style[st.cssProps[e.prop]]||st.cssHooks[e.prop])?st.style(e.elem,e.prop,e.now+e.unit):e.elem[e.prop]=e.now}}},$.propHooks.scrollTop=$.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},st.each(["toggle","show","hide"],function(e,t){var n=st.fn[t];st.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(I(t,!0),e,r,i)}}),st.fn.extend({fadeTo:function(e,t,n,r){return this.filter(w).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=st.isEmptyObject(e),o=st.speed(t,n,r),a=function(){var t=P(this,st.extend({},e),o);a.finish=function(){t.stop(!0)},(i||st._data(this,"finish"))&&t.stop(!0)};return a.finish=a,i||o.queue===!1?this.each(a):this.queue(o.queue,a)},stop:function(e,n,r){var i=function(e){var t=e.stop;delete e.stop,t(r)};return"string"!=typeof e&&(r=n,n=e,e=t),n&&e!==!1&&this.queue(e||"fx",[]),this.each(function(){var t=!0,n=null!=e&&e+"queueHooks",o=st.timers,a=st._data(this);if(n)a[n]&&a[n].stop&&i(a[n]);else for(n in a)a[n]&&a[n].stop&&tr.test(n)&&i(a[n]);for(n=o.length;n--;)o[n].elem!==this||null!=e&&o[n].queue!==e||(o[n].anim.stop(r),t=!1,o.splice(n,1));(t||!r)&&st.dequeue(this,e)})},finish:function(e){return e!==!1&&(e=e||"fx"),this.each(function(){var t,n=st._data(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=st.timers,a=r?r.length:0;for(n.finish=!0,st.queue(this,e,[]),i&&i.cur&&i.cur.finish&&i.cur.finish.call(this),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;a>t;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}}),st.each({slideDown:I("show"),slideUp:I("hide"),slideToggle:I("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){st.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),st.speed=function(e,t,n){var r=e&&"object"==typeof e?st.extend({},e):{complete:n||!n&&t||st.isFunction(e)&&e,duration:e,easing:n&&t||t&&!st.isFunction(t)&&t};return r.duration=st.fx.off?0:"number"==typeof r.duration?r.duration:r.duration in st.fx.speeds?st.fx.speeds[r.duration]:st.fx.speeds._default,(null==r.queue||r.queue===!0)&&(r.queue="fx"),r.old=r.complete,r.complete=function(){st.isFunction(r.old)&&r.old.call(this),r.queue&&st.dequeue(this,r.queue)},r},st.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2}},st.timers=[],st.fx=$.prototype.init,st.fx.tick=function(){var e,n=st.timers,r=0;for(Qn=st.now();n.length>r;r++)e=n[r],e()||n[r]!==e||n.splice(r--,1);n.length||st.fx.stop(),Qn=t},st.fx.timer=function(e){e()&&st.timers.push(e)&&st.fx.start()},st.fx.interval=13,st.fx.start=function(){Kn||(Kn=setInterval(st.fx.tick,st.fx.interval))},st.fx.stop=function(){clearInterval(Kn),Kn=null},st.fx.speeds={slow:600,fast:200,_default:400},st.fx.step={},st.expr&&st.expr.filters&&(st.expr.filters.animated=function(e){return st.grep(st.timers,function(t){return e===t.elem}).length}),st.fn.offset=function(e){if(arguments.length)return e===t?this:this.each(function(t){st.offset.setOffset(this,e,t)});var n,r,i={top:0,left:0},o=this[0],a=o&&o.ownerDocument;if(a)return n=a.documentElement,st.contains(n,o)?(o.getBoundingClientRect!==t&&(i=o.getBoundingClientRect()),r=z(a),{top:i.top+(r.pageYOffset||n.scrollTop)-(n.clientTop||0),left:i.left+(r.pageXOffset||n.scrollLeft)-(n.clientLeft||0)}):i},st.offset={setOffset:function(e,t,n){var r=st.css(e,"position");"static"===r&&(e.style.position="relative");var i,o,a=st(e),s=a.offset(),u=st.css(e,"top"),l=st.css(e,"left"),c=("absolute"===r||"fixed"===r)&&st.inArray("auto",[u,l])>-1,f={},p={};c?(p=a.position(),i=p.top,o=p.left):(i=parseFloat(u)||0,o=parseFloat(l)||0),st.isFunction(t)&&(t=t.call(e,n,s)),null!=t.top&&(f.top=t.top-s.top+i),null!=t.left&&(f.left=t.left-s.left+o),"using"in t?t.using.call(e,f):a.css(f)}},st.fn.extend({position:function(){if(this[0]){var e,t,n={top:0,left:0},r=this[0];return"fixed"===st.css(r,"position")?t=r.getBoundingClientRect():(e=this.offsetParent(),t=this.offset(),st.nodeName(e[0],"html")||(n=e.offset()),n.top+=st.css(e[0],"borderTopWidth",!0),n.left+=st.css(e[0],"borderLeftWidth",!0)),{top:t.top-n.top-st.css(r,"marginTop",!0),left:t.left-n.left-st.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){for(var e=this.offsetParent||V.documentElement;e&&!st.nodeName(e,"html")&&"static"===st.css(e,"position");)e=e.offsetParent;return e||V.documentElement})}}),st.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(e,n){var r=/Y/.test(n);st.fn[e]=function(i){return st.access(this,function(e,i,o){var a=z(e);return o===t?a?n in a?a[n]:a.document.documentElement[i]:e[i]:(a?a.scrollTo(r?st(a).scrollLeft():o,r?o:st(a).scrollTop()):e[i]=o,t)},e,i,arguments.length,null)}}),st.each({Height:"height",Width:"width"},function(e,n){st.each({padding:"inner"+e,content:n,"":"outer"+e},function(r,i){st.fn[i]=function(i,o){var a=arguments.length&&(r||"boolean"!=typeof i),s=r||(i===!0||o===!0?"margin":"border");return st.access(this,function(n,r,i){var o;return st.isWindow(n)?n.document.documentElement["client"+e]:9===n.nodeType?(o=n.documentElement,Math.max(n.body["scroll"+e],o["scroll"+e],n.body["offset"+e],o["offset"+e],o["client"+e])):i===t?st.css(n,r,s):st.style(n,r,i,s)},n,a?i:t,a,null)}})}),e.jQuery=e.$=st,"function"==typeof define&&define.amd&&define.amd.jQuery&&define("jquery",[],function(){return st})})(window);//@ sourceMappingURL=jquery.min.map
        /* js/03-swig.min.js */ /* swig.js v0.13.5 | Copyright (c) 2010-2011 Paul Armstrong, Dusko Jordanovski | https://github.com/paularmstrong/swig/ */ (function(){var str="{{ a }}",splitter;if(str.split(/(\{\{.*?\}\})/).length===0){splitter=function(str,separator,limit){if(Object.prototype.toString.call(separator)!=="[object RegExp]"){return splitter._nativeSplit.call(str,separator,limit)}var output=[],lastLastIndex=0,flags=(separator.ignoreCase?"i":"")+(separator.multiline?"m":"")+(separator.sticky?"y":""),separator2,match,lastIndex,lastLength;separator=RegExp(separator.source,flags+"g");str=str.toString();if(!splitter._compliantExecNpcg){separator2=RegExp("^"+separator.source+"$(?!\\s)",flags)}if(limit===undefined||limit<0){limit=Infinity}else{limit=Math.floor(+limit);if(!limit){return[]}}function fixExec(){var i=1;for(i;i<arguments.length-2;i+=1){if(arguments[i]===undefined){match[i]=undefined}}}match=separator.exec(str);while(match){lastIndex=match.index+match[0].length;if(lastIndex>lastLastIndex){output.push(str.slice(lastLastIndex,match.index));if(!splitter._compliantExecNpcg&&match.length>1){match[0].replace(separator2,fixExec)}if(match.length>1&&match.index<str.length){Array.prototype.push.apply(output,match.slice(1))}lastLength=match[0].length;lastLastIndex=lastIndex;if(output.length>=limit){break}}if(separator.lastIndex===match.index){separator.lastIndex+=1}match=separator.exec(str)}if(lastLastIndex===str.length){if(lastLength||!separator.test("")){output.push("")}}else{output.push(str.slice(lastLastIndex))}return output.length>limit?output.slice(0,limit):output};splitter._compliantExecNpcg=/()??/.exec("")[1]===undefined;splitter._nativeSplit=String.prototype.split;String.prototype.split=function(separator,limit){return splitter(this,separator,limit)}}})();swig=function(){var swig={},dateformat={},filters={},helpers={},parser={},tags={};(function(){var root=this;var previousUnderscore=root._;var breaker={};var ArrayProto=Array.prototype,ObjProto=Object.prototype,FuncProto=Function.prototype;var push=ArrayProto.push,slice=ArrayProto.slice,concat=ArrayProto.concat,toString=ObjProto.toString,hasOwnProperty=ObjProto.hasOwnProperty;var nativeForEach=ArrayProto.forEach,nativeMap=ArrayProto.map,nativeReduce=ArrayProto.reduce,nativeReduceRight=ArrayProto.reduceRight,nativeFilter=ArrayProto.filter,nativeEvery=ArrayProto.every,nativeSome=ArrayProto.some,nativeIndexOf=ArrayProto.indexOf,nativeLastIndexOf=ArrayProto.lastIndexOf,nativeIsArray=Array.isArray,nativeKeys=Object.keys,nativeBind=FuncProto.bind;var _=function(obj){if(obj instanceof _)return obj;if(!(this instanceof _))return new _(obj);this._wrapped=obj};if(typeof exports!=="undefined"){if(typeof module!=="undefined"&&module.exports){exports=module.exports=_}exports._=_}else{root._=_}_.VERSION="1.4.3";var each=_.each=_.forEach=function(obj,iterator,context){if(obj==null)return;if(nativeForEach&&obj.forEach===nativeForEach){obj.forEach(iterator,context)}else if(obj.length===+obj.length){for(var i=0,l=obj.length;i<l;i++){if(iterator.call(context,obj[i],i,obj)===breaker)return}}else{for(var key in obj){if(_.has(obj,key)){if(iterator.call(context,obj[key],key,obj)===breaker)return}}}};_.map=_.collect=function(obj,iterator,context){var results=[];if(obj==null)return results;if(nativeMap&&obj.map===nativeMap)return obj.map(iterator,context);each(obj,function(value,index,list){results[results.length]=iterator.call(context,value,index,list)});return results};var reduceError="Reduce of empty array with no initial value";_.reduce=_.foldl=_.inject=function(obj,iterator,memo,context){var initial=arguments.length>2;if(obj==null)obj=[];if(nativeReduce&&obj.reduce===nativeReduce){if(context)iterator=_.bind(iterator,context);return initial?obj.reduce(iterator,memo):obj.reduce(iterator)}each(obj,function(value,index,list){if(!initial){memo=value;initial=true}else{memo=iterator.call(context,memo,value,index,list)}});if(!initial)throw new TypeError(reduceError);return memo};_.reduceRight=_.foldr=function(obj,iterator,memo,context){var initial=arguments.length>2;if(obj==null)obj=[];if(nativeReduceRight&&obj.reduceRight===nativeReduceRight){if(context)iterator=_.bind(iterator,context);return initial?obj.reduceRight(iterator,memo):obj.reduceRight(iterator)}var length=obj.length;if(length!==+length){var keys=_.keys(obj);length=keys.length}each(obj,function(value,index,list){index=keys?keys[--length]:--length;if(!initial){memo=obj[index];initial=true}else{memo=iterator.call(context,memo,obj[index],index,list)}});if(!initial)throw new TypeError(reduceError);return memo};_.find=_.detect=function(obj,iterator,context){var result;any(obj,function(value,index,list){if(iterator.call(context,value,index,list)){result=value;return true}});return result};_.filter=_.select=function(obj,iterator,context){var results=[];if(obj==null)return results;if(nativeFilter&&obj.filter===nativeFilter)return obj.filter(iterator,context);each(obj,function(value,index,list){if(iterator.call(context,value,index,list))results[results.length]=value});return results};_.reject=function(obj,iterator,context){return _.filter(obj,function(value,index,list){return!iterator.call(context,value,index,list)},context)};_.every=_.all=function(obj,iterator,context){iterator||(iterator=_.identity);var result=true;if(obj==null)return result;if(nativeEvery&&obj.every===nativeEvery)return obj.every(iterator,context);each(obj,function(value,index,list){if(!(result=result&&iterator.call(context,value,index,list)))return breaker});return!!result};var any=_.some=_.any=function(obj,iterator,context){iterator||(iterator=_.identity);var result=false;if(obj==null)return result;if(nativeSome&&obj.some===nativeSome)return obj.some(iterator,context);each(obj,function(value,index,list){if(result||(result=iterator.call(context,value,index,list)))return breaker});return!!result};_.contains=_.include=function(obj,target){if(obj==null)return false;if(nativeIndexOf&&obj.indexOf===nativeIndexOf)return obj.indexOf(target)!=-1;return any(obj,function(value){return value===target})};_.invoke=function(obj,method){var args=slice.call(arguments,2);return _.map(obj,function(value){return(_.isFunction(method)?method:value[method]).apply(value,args)})};_.pluck=function(obj,key){return _.map(obj,function(value){return value[key]})};_.where=function(obj,attrs){if(_.isEmpty(attrs))return[];return _.filter(obj,function(value){for(var key in attrs){if(attrs[key]!==value[key])return false}return true})};_.max=function(obj,iterator,context){if(!iterator&&_.isArray(obj)&&obj[0]===+obj[0]&&obj.length<65535){return Math.max.apply(Math,obj)}if(!iterator&&_.isEmpty(obj))return-Infinity;var result={computed:-Infinity,value:-Infinity};each(obj,function(value,index,list){var computed=iterator?iterator.call(context,value,index,list):value;computed>=result.computed&&(result={value:value,computed:computed})});return result.value};_.min=function(obj,iterator,context){if(!iterator&&_.isArray(obj)&&obj[0]===+obj[0]&&obj.length<65535){return Math.min.apply(Math,obj)}if(!iterator&&_.isEmpty(obj))return Infinity;var result={computed:Infinity,value:Infinity};each(obj,function(value,index,list){var computed=iterator?iterator.call(context,value,index,list):value;computed<result.computed&&(result={value:value,computed:computed})});return result.value};_.shuffle=function(obj){var rand;var index=0;var shuffled=[];each(obj,function(value){rand=_.random(index++);shuffled[index-1]=shuffled[rand];shuffled[rand]=value});return shuffled};var lookupIterator=function(value){return _.isFunction(value)?value:function(obj){return obj[value]}};_.sortBy=function(obj,value,context){var iterator=lookupIterator(value);return _.pluck(_.map(obj,function(value,index,list){return{value:value,index:index,criteria:iterator.call(context,value,index,list)}}).sort(function(left,right){var a=left.criteria;var b=right.criteria;if(a!==b){if(a>b||a===void 0)return 1;if(a<b||b===void 0)return-1}return left.index<right.index?-1:1}),"value")};var group=function(obj,value,context,behavior){var result={};var iterator=lookupIterator(value||_.identity);each(obj,function(value,index){var key=iterator.call(context,value,index,obj);behavior(result,key,value)});return result};_.groupBy=function(obj,value,context){return group(obj,value,context,function(result,key,value){(_.has(result,key)?result[key]:result[key]=[]).push(value)})};_.countBy=function(obj,value,context){return group(obj,value,context,function(result,key){if(!_.has(result,key))result[key]=0;result[key]++})};_.sortedIndex=function(array,obj,iterator,context){iterator=iterator==null?_.identity:lookupIterator(iterator);var value=iterator.call(context,obj);var low=0,high=array.length;while(low<high){var mid=low+high>>>1;iterator.call(context,array[mid])<value?low=mid+1:high=mid}return low};_.toArray=function(obj){if(!obj)return[];if(_.isArray(obj))return slice.call(obj);if(obj.length===+obj.length)return _.map(obj,_.identity);return _.values(obj)};_.size=function(obj){if(obj==null)return 0;return obj.length===+obj.length?obj.length:_.keys(obj).length};_.first=_.head=_.take=function(array,n,guard){if(array==null)return void 0;return n!=null&&!guard?slice.call(array,0,n):array[0]};_.initial=function(array,n,guard){return slice.call(array,0,array.length-(n==null||guard?1:n))};_.last=function(array,n,guard){if(array==null)return void 0;if(n!=null&&!guard){return slice.call(array,Math.max(array.length-n,0))}else{return array[array.length-1]}};_.rest=_.tail=_.drop=function(array,n,guard){return slice.call(array,n==null||guard?1:n)};_.compact=function(array){return _.filter(array,_.identity)};var flatten=function(input,shallow,output){each(input,function(value){if(_.isArray(value)){shallow?push.apply(output,value):flatten(value,shallow,output)}else{output.push(value)}});return output};_.flatten=function(array,shallow){return flatten(array,shallow,[])};_.without=function(array){return _.difference(array,slice.call(arguments,1))};_.uniq=_.unique=function(array,isSorted,iterator,context){if(_.isFunction(isSorted)){context=iterator;iterator=isSorted;isSorted=false}var initial=iterator?_.map(array,iterator,context):array;var results=[];var seen=[];each(initial,function(value,index){if(isSorted?!index||seen[seen.length-1]!==value:!_.contains(seen,value)){seen.push(value);results.push(array[index])}});return results};_.union=function(){return _.uniq(concat.apply(ArrayProto,arguments))};_.intersection=function(array){var rest=slice.call(arguments,1);return _.filter(_.uniq(array),function(item){return _.every(rest,function(other){return _.indexOf(other,item)>=0})})};_.difference=function(array){var rest=concat.apply(ArrayProto,slice.call(arguments,1));return _.filter(array,function(value){return!_.contains(rest,value)})};_.zip=function(){var args=slice.call(arguments);var length=_.max(_.pluck(args,"length"));var results=new Array(length);for(var i=0;i<length;i++){results[i]=_.pluck(args,""+i)}return results};_.object=function(list,values){if(list==null)return{};var result={};for(var i=0,l=list.length;i<l;i++){if(values){result[list[i]]=values[i]}else{result[list[i][0]]=list[i][1]}}return result};_.indexOf=function(array,item,isSorted){if(array==null)return-1;var i=0,l=array.length;if(isSorted){if(typeof isSorted=="number"){i=isSorted<0?Math.max(0,l+isSorted):isSorted}else{i=_.sortedIndex(array,item);return array[i]===item?i:-1}}if(nativeIndexOf&&array.indexOf===nativeIndexOf)return array.indexOf(item,isSorted);for(;i<l;i++)if(array[i]===item)return i;return-1};_.lastIndexOf=function(array,item,from){if(array==null)return-1;var hasIndex=from!=null;if(nativeLastIndexOf&&array.lastIndexOf===nativeLastIndexOf){return hasIndex?array.lastIndexOf(item,from):array.lastIndexOf(item)}var i=hasIndex?from:array.length;while(i--)if(array[i]===item)return i;return-1};_.range=function(start,stop,step){if(arguments.length<=1){stop=start||0;start=0}step=arguments[2]||1;var len=Math.max(Math.ceil((stop-start)/step),0);var idx=0;var range=new Array(len);while(idx<len){range[idx++]=start;start+=step}return range};var ctor=function(){};_.bind=function(func,context){var args,bound;if(func.bind===nativeBind&&nativeBind)return nativeBind.apply(func,slice.call(arguments,1));if(!_.isFunction(func))throw new TypeError;args=slice.call(arguments,2);return bound=function(){if(!(this instanceof bound))return func.apply(context,args.concat(slice.call(arguments)));ctor.prototype=func.prototype;var self=new ctor;ctor.prototype=null;var result=func.apply(self,args.concat(slice.call(arguments)));if(Object(result)===result)return result;return self}};_.bindAll=function(obj){var funcs=slice.call(arguments,1);if(funcs.length==0)funcs=_.functions(obj);each(funcs,function(f){obj[f]=_.bind(obj[f],obj)});return obj};_.memoize=function(func,hasher){var memo={};hasher||(hasher=_.identity);return function(){var key=hasher.apply(this,arguments);return _.has(memo,key)?memo[key]:memo[key]=func.apply(this,arguments)}};_.delay=function(func,wait){var args=slice.call(arguments,2);return setTimeout(function(){return func.apply(null,args)},wait)};_.defer=function(func){return _.delay.apply(_,[func,1].concat(slice.call(arguments,1)))};_.throttle=function(func,wait){var context,args,timeout,result;var previous=0;var later=function(){previous=new Date;timeout=null;result=func.apply(context,args)};return function(){var now=new Date;var remaining=wait-(now-previous);context=this;args=arguments;if(remaining<=0){clearTimeout(timeout);timeout=null;previous=now;result=func.apply(context,args)}else if(!timeout){timeout=setTimeout(later,remaining)}return result}};_.debounce=function(func,wait,immediate){var timeout,result;return function(){var context=this,args=arguments;var later=function(){timeout=null;if(!immediate)result=func.apply(context,args)};var callNow=immediate&&!timeout;clearTimeout(timeout);timeout=setTimeout(later,wait);if(callNow)result=func.apply(context,args);return result}};_.once=function(func){var ran=false,memo;return function(){if(ran)return memo;ran=true;memo=func.apply(this,arguments);func=null;return memo}};_.wrap=function(func,wrapper){return function(){var args=[func];push.apply(args,arguments);return wrapper.apply(this,args)}};_.compose=function(){var funcs=arguments;return function(){var args=arguments;for(var i=funcs.length-1;i>=0;i--){args=[funcs[i].apply(this,args)]}return args[0]}};_.after=function(times,func){if(times<=0)return func();return function(){if(--times<1){return func.apply(this,arguments)}}};_.keys=nativeKeys||function(obj){if(obj!==Object(obj))throw new TypeError("Invalid object");var keys=[];for(var key in obj)if(_.has(obj,key))keys[keys.length]=key;return keys};_.values=function(obj){var values=[];for(var key in obj)if(_.has(obj,key))values.push(obj[key]);return values};_.pairs=function(obj){var pairs=[];for(var key in obj)if(_.has(obj,key))pairs.push([key,obj[key]]);return pairs};_.invert=function(obj){var result={};for(var key in obj)if(_.has(obj,key))result[obj[key]]=key;return result};_.functions=_.methods=function(obj){var names=[];for(var key in obj){if(_.isFunction(obj[key]))names.push(key)}return names.sort()};_.extend=function(obj){each(slice.call(arguments,1),function(source){if(source){for(var prop in source){obj[prop]=source[prop]}}});return obj};_.pick=function(obj){var copy={};var keys=concat.apply(ArrayProto,slice.call(arguments,1));each(keys,function(key){if(key in obj)copy[key]=obj[key]});return copy};_.omit=function(obj){var copy={};var keys=concat.apply(ArrayProto,slice.call(arguments,1));for(var key in obj){if(!_.contains(keys,key))copy[key]=obj[key]}return copy};_.defaults=function(obj){each(slice.call(arguments,1),function(source){if(source){for(var prop in source){if(obj[prop]==null)obj[prop]=source[prop]}}});return obj};_.clone=function(obj){if(!_.isObject(obj))return obj;return _.isArray(obj)?obj.slice():_.extend({},obj)};_.tap=function(obj,interceptor){interceptor(obj);return obj};var eq=function(a,b,aStack,bStack){if(a===b)return a!==0||1/a==1/b;if(a==null||b==null)return a===b;if(a instanceof _)a=a._wrapped;if(b instanceof _)b=b._wrapped;var className=toString.call(a);if(className!=toString.call(b))return false;switch(className){case"[object String]":return a==String(b);case"[object Number]":return a!=+a?b!=+b:a==0?1/a==1/b:a==+b;case"[object Date]":case"[object Boolean]":return+a==+b;case"[object RegExp]":return a.source==b.source&&a.global==b.global&&a.multiline==b.multiline&&a.ignoreCase==b.ignoreCase}if(typeof a!="object"||typeof b!="object")return false;var length=aStack.length;while(length--){if(aStack[length]==a)return bStack[length]==b}aStack.push(a);bStack.push(b);var size=0,result=true;if(className=="[object Array]"){size=a.length;result=size==b.length;if(result){while(size--){if(!(result=eq(a[size],b[size],aStack,bStack)))break}}}else{var aCtor=a.constructor,bCtor=b.constructor;if(aCtor!==bCtor&&!(_.isFunction(aCtor)&&aCtor instanceof aCtor&&_.isFunction(bCtor)&&bCtor instanceof bCtor)){return false}for(var key in a){if(_.has(a,key)){size++;if(!(result=_.has(b,key)&&eq(a[key],b[key],aStack,bStack)))break}}if(result){for(key in b){if(_.has(b,key)&&!size--)break}result=!size}}aStack.pop();bStack.pop();return result};_.isEqual=function(a,b){return eq(a,b,[],[])};_.isEmpty=function(obj){if(obj==null)return true;if(_.isArray(obj)||_.isString(obj))return obj.length===0;for(var key in obj)if(_.has(obj,key))return false;return true};_.isElement=function(obj){return!!(obj&&obj.nodeType===1)};_.isArray=nativeIsArray||function(obj){return toString.call(obj)=="[object Array]"};_.isObject=function(obj){return obj===Object(obj)};each(["Arguments","Function","String","Number","Date","RegExp"],function(name){_["is"+name]=function(obj){return toString.call(obj)=="[object "+name+"]"}});if(!_.isArguments(arguments)){_.isArguments=function(obj){return!!(obj&&_.has(obj,"callee"))}}if(typeof/./!=="function"){_.isFunction=function(obj){return typeof obj==="function"}}_.isFinite=function(obj){return isFinite(obj)&&!isNaN(parseFloat(obj))};_.isNaN=function(obj){return _.isNumber(obj)&&obj!=+obj};_.isBoolean=function(obj){return obj===true||obj===false||toString.call(obj)=="[object Boolean]"};_.isNull=function(obj){return obj===null};_.isUndefined=function(obj){return obj===void 0};_.has=function(obj,key){return hasOwnProperty.call(obj,key)};_.noConflict=function(){root._=previousUnderscore;return this};_.identity=function(value){return value};_.times=function(n,iterator,context){var accum=Array(n);for(var i=0;i<n;i++)accum[i]=iterator.call(context,i);return accum};_.random=function(min,max){if(max==null){max=min;min=0}return min+(0|Math.random()*(max-min+1))};var entityMap={escape:{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","/":"&#x2F;"}};entityMap.unescape=_.invert(entityMap.escape);var entityRegexes={escape:new RegExp("["+_.keys(entityMap.escape).join("")+"]","g"),unescape:new RegExp("("+_.keys(entityMap.unescape).join("|")+")","g")};_.each(["escape","unescape"],function(method){_[method]=function(string){if(string==null)return"";return(""+string).replace(entityRegexes[method],function(match){return entityMap[method][match]})}});_.result=function(object,property){if(object==null)return null;var value=object[property];return _.isFunction(value)?value.call(object):value};_.mixin=function(obj){each(_.functions(obj),function(name){var func=_[name]=obj[name];_.prototype[name]=function(){var args=[this._wrapped];push.apply(args,arguments);return result.call(this,func.apply(_,args))}})};var idCounter=0;_.uniqueId=function(prefix){var id=""+ ++idCounter;return prefix?prefix+id:id};_.templateSettings={evaluate:/<%([\s\S]+?)%>/g,interpolate:/<%=([\s\S]+?)%>/g,escape:/<%-([\s\S]+?)%>/g};var noMatch=/(.)^/;var escapes={"'":"'","\\":"\\","\r":"r","\n":"n","   ":"t","\u2028":"u2028","\u2029":"u2029"};var escaper=/\\|'|\r|\n|\t|\u2028|\u2029/g;_.template=function(text,data,settings){settings=_.defaults({},settings,_.templateSettings);var matcher=new RegExp([(settings.escape||noMatch).source,(settings.interpolate||noMatch).source,(settings.evaluate||noMatch).source].join("|")+"|$","g");var index=0;var source="__p+='";text.replace(matcher,function(match,escape,interpolate,evaluate,offset){source+=text.slice(index,offset).replace(escaper,function(match){return"\\"+escapes[match]});if(escape){source+="'+\n((__t=("+escape+"))==null?'':_.escape(__t))+\n'"}if(interpolate){source+="'+\n((__t=("+interpolate+"))==null?'':__t)+\n'"}if(evaluate){source+="';\n"+evaluate+"\n__p+='"}index=offset+match.length;return match});source+="';\n";if(!settings.variable)source="with(obj||{}){\n"+source+"}\n";source="var __t,__p='',__j=Array.prototype.join,"+"print=function(){__p+=__j.call(arguments,'');};\n"+source+"return __p;\n";try{var render=new Function(settings.variable||"obj","_",source)}catch(e){e.source=source;throw e}if(data)return render(data,_);var template=function(data){return render.call(this,data,_)};template.source="function("+(settings.variable||"obj")+"){\n"+source+"}";return template};_.chain=function(obj){return _(obj).chain()};var result=function(obj){return this._chain?_(obj).chain():obj};_.mixin(_);each(["pop","push","reverse","shift","sort","splice","unshift"],function(name){var method=ArrayProto[name];_.prototype[name]=function(){var obj=this._wrapped;method.apply(obj,arguments);if((name=="shift"||name=="splice")&&obj.length===0)delete obj[0];return result.call(this,obj)}});each(["concat","join","slice"],function(name){var method=ArrayProto[name];_.prototype[name]=function(){return result.call(this,method.apply(this._wrapped,arguments))}});_.extend(_.prototype,{chain:function(){this._chain=true;return this},value:function(){return this._wrapped}})}).call(this);(function(exports){var config={allowErrors:false,autoescape:true,cache:true,encoding:"utf8",filters:filters,root:"/",tags:tags,extensions:{},tzOffset:0},_config=_.extend({},config),CACHE={};exports.init=function(options){CACHE={};_config=_.extend({},config,options);_config.filters=_.extend(filters,options.filters);_config.tags=_.extend(tags,options.tags);dateformat.defaultTZOffset=_config.tzOffset};function TemplateError(error){return{render:function(){return"<pre>"+error.stack+"</pre>"}}}function createRenderFunc(code){return new Function("_context","_parents","_filters","_","_ext",["_parents = _parents ? _parents.slice() : [];","_context = _context || {};","var j = _parents.length,",'  _output = "",',"  _this = this;","while (j--) {","   if (_parents[j] === this.id) {",'     return "Circular import of template " + this.id + " in " + _parents[_parents.length-1];',"   }","}","_parents.push(this.id);",code,"return _output;"].join(""))}function createTemplate(data,id){var template={compileFile:exports.compileFile,blocks:{},type:parser.TEMPLATE,id:id},tokens,code,render;if(_config.allowErrors){tokens=parser.parse.call(template,data,_config.tags,_config.autoescape)}else{try{tokens=parser.parse.call(template,data,_config.tags,_config.autoescape)}catch(e){return new TemplateError(e)}}template.tokens=tokens;code=parser.compile.call(template);if(code!==false){render=createRenderFunc(code)}else{render=function(_context,_parents,_filters,_,_ext){template.tokens=tokens;code=parser.compile.call(template,"",_context);var fn=createRenderFunc(code);return fn.call(this,_context,_parents,_filters,_,_ext)}}template.render=function(context,parents){if(_config.allowErrors){return render.call(this,context,parents,_config.filters,_,_config.extensions)}try{return render.call(this,context,parents,_config.filters,_,_config.extensions)}catch(e){return new TemplateError(e)}};return template}function getTemplate(source,options){var key=options.filename||source;if(_config.cache||options.cache){if(!CACHE.hasOwnProperty(key)){CACHE[key]=createTemplate(source,key)}return CACHE[key]}return createTemplate(source,key)}exports.compileFile=function(filepath,forceAllowErrors){var tpl,get;if(_config.cache&&CACHE.hasOwnProperty(filepath)){return CACHE[filepath]}if(typeof window!=="undefined"){throw new TemplateError({stack:"You must pre-compile all templates in-browser. Use `swig.compile(template);`."})}get=function(){var excp,getSingle,c;getSingle=function(prefix){var file=/^\//.test(filepath)||/^.:/.test(filepath)?filepath:prefix+"/"+filepath,data;try{data=fs.readFileSync(file,config.encoding);tpl=getTemplate(data,{filename:filepath})}catch(e){excp=e}};if(typeof _config.root==="string"){getSingle(_config.root)}if(_config.root instanceof Array){c=0;while(tpl===undefined&&c<_config.root.length){getSingle(_config.root[c]);c=c+1}}if(tpl===undefined){throw excp}};if(_config.allowErrors||forceAllowErrors){get()}else{try{get()}catch(error){tpl=new TemplateError(error)}}return tpl};exports.compile=function(source,options){var tmpl=getTemplate(source,options||{});return function(source,options){return tmpl.render(source,options)}}})(swig);(function(exports){var KEYWORDS=/^(Array|ArrayBuffer|Boolean|Date|Error|eval|EvalError|Function|Infinity|Iterator|JSON|Math|Namespace|NaN|Number|Object|QName|RangeError|ReferenceError|RegExp|StopIteration|String|SyntaxError|TypeError|undefined|uneval|URIError|XML|XMLList|break|case|catch|continue|debugger|default|delete|do|else|finally|for|function|if|in|instanceof|new|return|switch|this|throw|try|typeof|var|void|while|with)(?=(\.|$))/;exports.isStringLiteral=function(string){if(typeof string!=="string"){return false}var first=string.substring(0,1),last=string.charAt(string.length-1,1),teststr;if(first===last&&(first==="'"||first==='"')){teststr=string.substr(1,string.length-2).split("").reverse().join("");if(first==="'"&&/'(?!\\)/.test(teststr)||last==='"'&&/"(?!\\)/.test(teststr)){throw new Error("Invalid string literal. Unescaped quote ("+string[0]+") found.")}return true}return false};exports.isLiteral=function(string){var literal=false;if(/^\d+([.]\d+)?$/.test(string)){literal=true}else if(exports.isStringLiteral(string)){literal=true}return literal};exports.isValidName=function(string){return typeof string==="string"&&string.substr(0,2)!=="__"&&/^([$A-Za-z_]+[$A-Za-z_0-9]*)(\.?([$A-Za-z_]+[$A-Za-z_0-9]*))*$/.test(string)&&!KEYWORDS.test(string)};exports.isValidShortName=function(string){return string.substr(0,2)!=="__"&&/^[$A-Za-z_]+[$A-Za-z_0-9]*$/.test(string)&&!KEYWORDS.test(string)};exports.isValidBlockName=function(string){return/^[A-Za-z]+[A-Za-z_0-9]*$/.test(string)};function stripWhitespace(input){return input.replace(/^\s+|\s+$/g,"")}exports.stripWhitespace=stripWhitespace;function filterVariablePath(props){var filtered=[],literal="",i=0;for(i;i<props.length;i+=1){if(props[i]&&props[i].charAt(0)!==props[i].charAt(props[i].length-1)&&(props[i].indexOf('"')===0||props[i].indexOf("'")===0)){literal=props[i];continue}if(props[i]==="."&&literal){literal+=".";continue}if(props[i].indexOf('"')===props[i].length-1||props[i].indexOf("'")===props[i].length-1){literal+=props[i];filtered.push(literal);literal=""}else{filtered.push(props[i])}}return _.compact(filtered)}function check(variable,context){if(_.isArray(variable)){return"(true)"}variable=variable.replace(/^this/,"_this.__currentContext");if(exports.isLiteral(variable)){return"(true)"}var props=variable.split(/(\.|\[|\])/),chain="",output=[],inArr=false,prevDot=false;if(typeof context==="string"&&context.length){props.unshift(context)}props=_.reject(props,function(val){return val===""});props=filterVariablePath(props);_.each(props,function(prop){if(prop==="."){prevDot=true;return}if(prop==="["){inArr=true;return}if(prop==="]"){inArr=false;return}if(!chain){chain=prop}else if(inArr){if(!exports.isStringLiteral(prop)){if(prevDot){output[output.length-1]=_.last(output).replace(/\] !== "undefined"$/,"_"+prop+'] !== "undefined"');chain=chain.replace(/\]$/,"_"+prop+"]");return}chain+="[___"+prop+"]"}else{chain+="["+prop+"]"}}else{chain+="."+prop}prevDot=false;output.push("typeof "+chain+' !== "undefined"')});return"("+output.join(" && ")+")"}exports.check=check;exports.escapeVarName=function(variable,context){if(_.isArray(variable)){_.each(variable,function(val,key){variable[key]=exports.escapeVarName(val,context)});return variable}variable=variable.replace(/^this/,"_this.__currentContext");if(exports.isLiteral(variable)){return variable}if(typeof context==="string"&&context.length){variable=context+"."+variable}var chain="",props=variable.split(/(\.|\[|\])/),inArr=false,prevDot=false;props=_.reject(props,function(val){return val===""});props=filterVariablePath(props);_.each(props,function(prop){if(prop==="."){prevDot=true;return}if(prop==="["){inArr=true;return}if(prop==="]"){inArr=false;return}if(!chain){chain=prop}else if(inArr){if(!exports.isStringLiteral(prop)){if(prevDot){chain=chain.replace(/\]$/,"_"+prop+"]")}else{chain+="[___"+prop+"]"}}else{chain+="["+prop+"]"}}else{chain+="."+prop}prevDot=false});return chain};exports.wrapMethod=function(variable,filter,context){var output="(function () {\n",args;variable=variable||'""';if(!filter){return variable}args=filter.args.split(",");args=_.map(args,function(value){var varname,stripped=value.replace(/^\s+|\s+$/g,"");try{varname="__"+parser.parseVariable(stripped).name.replace(/\W/g,"_")}catch(e){return value}if(exports.isValidName(stripped)){output+=exports.setVar(varname,parser.parseVariable(stripped));return varname}return value});args=args&&args.length?args.join(","):'""';output+="return ";output+=context?context+'["':"";output+=filter.name;output+=context?'"]':"";output+=".call(this";output+=args.length?", "+args:"";output+=");\n";return output+"})()"};exports.wrapFilter=function(variable,filter){var output="",args="";variable=variable||'""';if(!filter){return variable}if(filters.hasOwnProperty(filter.name)){args=filter.args?variable+", "+filter.args:variable;output+=exports.wrapMethod(variable,{name:filter.name,args:args},"_filters")}else{throw new Error('Filter "'+filter.name+'" not found')}return output};exports.wrapFilters=function(variable,filters,context,escape){var output=exports.escapeVarName(variable,context);if(filters&&filters.length>0){_.each(filters,function(filter){switch(filter.name){case"raw":escape=false;return;case"e":case"escape":escape=filter.args||escape;return;default:output=exports.wrapFilter(output,filter,"_filters");break}})}output=output||'""';if(escape){output="_filters.escape.call(this, "+output+", "+escape+")"}return output};exports.setVar=function(varName,argument){var out="",props,output,inArr;if(/\[/.test(argument.name)){props=argument.name.split(/(\[|\])/);output=[];inArr=false;_.each(props,function(prop){if(prop===""){return}if(prop==="["){inArr=true;return}if(prop==="]"){inArr=false;return}if(inArr&&!exports.isStringLiteral(prop)){out+=exports.setVar("___"+prop.replace(/\W/g,"_"),{name:prop,filters:[],escape:true})}})}out+="var "+varName+' = "";\n'+"if ("+check(argument.name,"_context")+") {\n"+"  "+varName+" = "+exports.wrapFilters(argument.name,argument.filters,"_context",argument.escape)+";\n"+"} else if ("+check(argument.name)+") {\n"+"  "+varName+" = "+exports.wrapFilters(argument.name,argument.filters,null,argument.escape)+";\n"+"}\n";if(argument.filters.length){out+=" else if (true) {\n";out+="  "+varName+" = "+exports.wrapFilters("",argument.filters,null,argument.escape)+";\n";out+="}\n"}return out};exports.parseIfArgs=function(args,parser){var operators=["==","<",">","!=","<=",">=","===","!==","&&","||","in","and","or","mod","%"],errorString="Bad if-syntax in `{% if "+args.join(" ")+" %}...",startParen=/^\(+/,endParen=/\)+$/,tokens=[],prevType,last,closing=0;_.each(args,function(value,index){var endsep=0,startsep=0,operand;if(startParen.test(value)){startsep=value.match(startParen)[0].length;closing+=startsep;value=value.replace(startParen,"");while(startsep){startsep-=1;tokens.push({type:"separator",value:"("})}}if(/^\![^=]/.test(value)||value==="not"){if(value==="not"){value=""}else{value=value.substr(1)}tokens.push({type:"operator",value:"!"})}if(endParen.test(value)&&value.indexOf("(")===-1){if(!closing){throw new Error(errorString)}endsep=value.match(endParen)[0].length;value=value.replace(endParen,"");closing-=endsep}if(value==="in"){last=tokens.pop();prevType="inindex"}else if(_.indexOf(operators,value)!==-1){if(prevType==="operator"){throw new Error(errorString)}value=value.replace("and","&&").replace("or","||").replace("mod","%");tokens.push({value:value});prevType="operator"}else if(value!==""){if(prevType==="value"){throw new Error(errorString)}operand=parser.parseVariable(value);if(prevType==="inindex"){tokens.push({preout:last.preout+exports.setVar("__op"+index,operand),value:"(((_.isArray(__op"+index+") || typeof __op"+index+' === "string") && _.indexOf(__op'+index+", "+last.value+") !== -1) || (typeof __op"+index+' === "object" && '+last.value+" in __op"+index+"))"});last=null}else{tokens.push({preout:exports.setVar("__op"+index,operand),value:"__op"+index})}prevType="value"}while(endsep){endsep-=1;tokens.push({type:"separator",value:")"})}});if(closing>0){throw new Error(errorString)}return tokens}})(helpers);(function(exports){var _months={full:["January","February","March","April","May","June","July","August","September","October","November","December"],abbr:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]},_days={full:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],abbr:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],alt:{"-1":"Yesterday",0:"Today",1:"Tomorrow"}};exports.defaultTZOffset=0;exports.DateZ=function(){var members={"default":["getUTCDate","getUTCDay","getUTCFullYear","getUTCHours","getUTCMilliseconds","getUTCMinutes","getUTCMonth","getUTCSeconds","toISOString","toGMTString","toUTCString","valueOf","getTime"],z:["getDate","getDay","getFullYear","getHours","getMilliseconds","getMinutes","getMonth","getSeconds","getYear","toDateString","toLocaleDateString","toLocaleTimeString"],string:["toLocaleString","toString","toTimeString"],zSet:["setDate","setFullYear","setHours","setMilliseconds","setMinutes","setMonth","setSeconds","setTime","setYear"],set:["setUTCDate","setUTCFullYear","setUTCHours","setUTCMilliseconds","setUTCMinutes","setUTCMonth","setUTCSeconds"],"static":["UTC","parse"]},d=this,i;d.date=d.dateZ=arguments.length>1?new Date(Date.UTC.apply(Date,arguments)+(new Date).getTimezoneOffset()*6e4):arguments.length===1?new Date(new Date(arguments["0"])):new Date;d.timezoneOffset=d.dateZ.getTimezoneOffset();function zeroPad(i){return i<10?"0"+i:i}function _toTZString(){var hours=zeroPad(Math.floor(Math.abs(d.timezoneOffset)/60)),minutes=zeroPad(Math.abs(d.timezoneOffset)-hours*60),prefix=d.timezoneOffset<0?"+":"-",abbr=d.tzAbbreviation===undefined?"":" ("+d.tzAbbreviation+")";return"GMT"+prefix+hours+minutes+abbr}_.each(members.z,function(name){d[name]=function(){return d.dateZ[name]()}});_.each(members.string,function(name){d[name]=function(){return d.dateZ[name].apply(d.dateZ,[]).replace(/GMT[+\-]\\d{4} \\(([a-zA-Z]{3,4})\\)/,_toTZString())}});_.each(members["default"],function(name){d[name]=function(){return d.date[name]()}});_.each(members["static"],function(name){d[name]=function(){return Date[name].apply(Date,arguments)}});_.each(members.zSet,function(name){d[name]=function(){d.dateZ[name].apply(d.dateZ,arguments);d.date=new Date(d.dateZ.getTime()-d.dateZ.getTimezoneOffset()*6e4+d.timezoneOffset*6e4);return d}});_.each(members.set,function(name){d[name]=function(){d.date[name].apply(d.date,arguments);d.dateZ=new Date(d.date.getTime()+d.date.getTimezoneOffset()*6e4-d.timezoneOffset*6e4);return d}});if(exports.defaultTZOffset){this.setTimezoneOffset(exports.defaultTZOffset)}};exports.DateZ.prototype={getTimezoneOffset:function(){return this.timezoneOffset},setTimezoneOffset:function(offset,abbr){this.timezoneOffset=offset;if(abbr){this.tzAbbreviation=abbr}this.dateZ=new Date(this.date.getTime()+this.date.getTimezoneOffset()*6e4-this.timezoneOffset*6e4);return this}};exports.d=function(input){return(input.getDate()<10?"0":"")+input.getDate()};exports.D=function(input){return _days.abbr[input.getDay()]};exports.j=function(input){return input.getDate()};exports.l=function(input){return _days.full[input.getDay()]};exports.N=function(input){var d=input.getDay();return d>=1?d+1:7};exports.S=function(input){var d=input.getDate();return d%10===1&&d!==11?"st":d%10===2&&d!==12?"nd":d%10===3&&d!==13?"rd":"th"};exports.w=function(input){return input.getDay()};exports.z=function(input,offset,abbr){var year=input.getFullYear(),e=new exports.DateZ(year,input.getMonth(),input.getDate(),12,0,0),d=new exports.DateZ(year,0,1,12,0,0);e.setTimezoneOffset(offset,abbr);d.setTimezoneOffset(offset,abbr);return Math.round((e-d)/864e5)};exports.W=function(input){var target=new Date(input.valueOf()),dayNr=(input.getDay()+6)%7,fThurs;target.setDate(target.getDate()-dayNr+3);fThurs=target.valueOf();target.setMonth(0,1);if(target.getDay()!==4){target.setMonth(0,1+(4-target.getDay()+7)%7)}return 1+Math.ceil((fThurs-target)/6048e5)};exports.F=function(input){return _months.full[input.getMonth()]};exports.m=function(input){return(input.getMonth()<9?"0":"")+(input.getMonth()+1)};exports.M=function(input){return _months.abbr[input.getMonth()]};exports.n=function(input){return input.getMonth()+1};exports.t=function(input){return 32-new Date(input.getFullYear(),input.getMonth(),32).getDate()};exports.L=function(input){return new Date(input.getFullYear(),1,29).getDate()===29};exports.o=function(input){var target=new Date(input.valueOf());target.setDate(target.getDate()-(input.getDay()+6)%7+3);return target.getFullYear()};exports.Y=function(input){return input.getFullYear()};exports.y=function(input){return input.getFullYear().toString().substr(2)};exports.a=function(input){return input.getHours()<12?"am":"pm"};exports.A=function(input){return input.getHours()<12?"AM":"PM"};exports.B=function(input){var hours=input.getUTCHours(),beats;hours=hours===23?0:hours+1;beats=Math.abs(((hours*60+input.getUTCMinutes())*60+input.getUTCSeconds())/86.4).toFixed(0);return"000".concat(beats).slice(beats.length)};exports.g=function(input){var h=input.getHours();return h===0?12:h>12?h-12:h};exports.G=function(input){return input.getHours()};exports.h=function(input){var h=input.getHours();return(h<10||12<h&&22>h?"0":"")+(h<12?h:h-12)};exports.H=function(input){var h=input.getHours();return(h<10?"0":"")+h};exports.i=function(input){var m=input.getMinutes();return(m<10?"0":"")+m};exports.s=function(input){var s=input.getSeconds();return(s<10?"0":"")+s};exports.O=function(input){var tz=input.getTimezoneOffset();return(tz<0?"-":"+")+(tz/60<10?"0":"")+Math.abs(tz/60)+"00"};exports.Z=function(input){return input.getTimezoneOffset()*60};exports.c=function(input){return input.toISOString()};exports.r=function(input){return input.toUTCString()};exports.U=function(input){return input.getTime()/1e3}})(dateformat);(function(exports){exports.add=function(input,addend){if(_.isArray(input)&&_.isArray(addend)){return input.concat(addend)}if(typeof input==="object"&&typeof addend==="object"){return _.extend(input,addend)}if(_.isNumber(input)&&_.isNumber(addend)){return input+addend}return input+addend};exports.addslashes=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.addslashes(value)});return input}return input.replace(/\\/g,"\\\\").replace(/\'/g,"\\'").replace(/\"/g,'\\"')};exports.capitalize=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.capitalize(value)});return input}return input.toString().charAt(0).toUpperCase()+input.toString().substr(1).toLowerCase()};exports.date=function(input,format,offset,abbr){var l=format.length,date=new dateformat.DateZ(input),cur,i=0,out="";if(offset){date.setTimezoneOffset(offset,abbr)}for(i;i<l;i+=1){cur=format.charAt(i);if(dateformat.hasOwnProperty(cur)){out+=dateformat[cur](date,offset,abbr)}else{out+=cur}}return out};exports["default"]=function(input,def){return typeof input!=="undefined"&&(input||typeof input==="number")?input:def};exports.escape=exports.e=function(input,type){type=type||"html";if(typeof input==="string"){if(type==="js"){var i=0,code,out="";input=input.replace(/\\/g,"\\u005C");for(i;i<input.length;i+=1){code=input.charCodeAt(i);if(code<32){code=code.toString(16).toUpperCase();code=code.length<2?"0"+code:code;out+="\\u00"+code}else{out+=input[i]}}return out.replace(/&/g,"\\u0026").replace(/</g,"\\u003C").replace(/>/g,"\\u003E").replace(/\'/g,"\\u0027").replace(/"/g,"\\u0022").replace(/\=/g,"\\u003D").replace(/-/g,"\\u002D").replace(/;/g,"\\u003B")}return input.replace(/&(?!amp;|lt;|gt;|quot;|#39;)/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}return input};exports.first=function(input){if(typeof input==="object"&&!_.isArray(input)){return""}if(typeof input==="string"){return input.substr(0,1)}return _.first(input)};exports.join=function(input,separator){if(_.isArray(input)){return input.join(separator)}if(typeof input==="object"){var out=[];_.each(input,function(value,key){out.push(value)});return out.join(separator)}return input};exports.json_encode=function(input,indent){return JSON.stringify(input,null,indent||0)};exports.last=function(input){if(typeof input==="object"&&!_.isArray(input)){return""}if(typeof input==="string"){return input.charAt(input.length-1)}return _.last(input)};exports.length=function(input){if(typeof input==="object"){return _.keys(input).length}return input.length};exports.lower=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.lower(value)});return input}return input.toString().toLowerCase()};exports.replace=function(input,search,replacement,flags){var r=new RegExp(search,flags);return input.replace(r,replacement)};exports.reverse=function(input){if(_.isArray(input)){return input.reverse()}return input};exports.striptags=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.striptags(value)});return input}return input.toString().replace(/(<([^>]+)>)/gi,"")};exports.title=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.title(value)});return input}return input.toString().replace(/\w\S*/g,function(str){return str.charAt(0).toUpperCase()+str.substr(1).toLowerCase()})};exports.uniq=function(input){return _.uniq(input)};exports.upper=function(input){if(typeof input==="object"){_.each(input,function(value,key){input[key]=exports.upper(value)});return input}return input.toString().toUpperCase()};exports.url_encode=function(input){return encodeURIComponent(input)};exports.url_decode=function(input){return decodeURIComponent(input)}})(filters);(function(exports){var variableRegexp=/^\{\{[^\r]*?\}\}$/,logicRegexp=/^\{%[^\r]*?%\}$/,commentRegexp=/^\{#[^\r]*?#\}$/,TEMPLATE=exports.TEMPLATE=0,LOGIC_TOKEN=1,VAR_TOKEN=2;exports.TOKEN_TYPES={TEMPLATE:TEMPLATE,LOGIC:LOGIC_TOKEN,VAR:VAR_TOKEN};function getMethod(input){return helpers.stripWhitespace(input).match(/^[\w\.]+/)[0]}function doubleEscape(input){return input.replace(/\\/g,"\\\\")}function getArgs(input){return doubleEscape(helpers.stripWhitespace(input).replace(/^[\w\.]+\(|\)$/g,""))}function getContextVar(varName,context){var a=varName.split(".");while(a.length){context=context[a.splice(0,1)[0]]}return context}function getTokenArgs(token,parts){parts=_.map(parts,doubleEscape);var i=0,l=parts.length,arg,ender,out=[];function concat(from,ending){var end=new RegExp("\\"+ending+"$"),i=from,out="";while(!end.test(out)&&i<parts.length){out+=" "+parts[i];parts[i]=null;i+=1}if(!end.test(out)){throw new Error("Malformed arguments "+out+" sent to tag.")}return out.replace(/^ /,"")}for(i;i<l;i+=1){arg=parts[i];if(arg===null||/^\s+$/.test(arg)){continue}if(/^\"/.test(arg)&&!/\"[\]\}]?$/.test(arg)||/^\'/.test(arg)&&!/\'[\]\}]?$/.test(arg)||/^\{/.test(arg)&&!/\}$/.test(arg)||/^\[/.test(arg)&&!/\]$/.test(arg)){switch(arg.substr(0,1)){case"'":ender="'";break;case'"':ender='"';break;case"[":ender="]";break;case"{":ender="}";break}out.push(concat(i,ender));continue}out.push(arg)}return out}function findSubBlocks(topToken,blocks){_.each(topToken.tokens,function(token,index){if(token.name==="block"){blocks[token.args[0]]=token;findSubBlocks(token,blocks)}})}function getParentBlock(token){var block;if(token.parentBlock){block=token.parentBlock}else if(token.parent){block=getParentBlock(_.last(token.parent))}return block}exports.parseVariable=function(token,escape){if(!token){return{type:null,name:"",filters:[],escape:escape}}var filters=[],parts=token.replace(/^\{\{\s*|\s*\}\}$/g,"").split("|"),varname=parts.shift(),args=null,part;if(/\(/.test(varname)){args=getArgs(varname.replace(/^\w+\./,""));varname=getMethod(varname)}_.each(parts,function(part,i){if(part&&(/^[\w\.]+\(/.test(part)||/\)$/.test(part))&&!/^[\w\.]+\([^\)]*\)$/.test(part)){parts[i]+=parts[i+1]?"|"+parts[i+1]:"";parts[i+1]=false}});parts=_.without(parts,false);_.each(parts,function(part){var filter_name=getMethod(part);if(/\(/.test(part)){filters.push({name:filter_name,args:getArgs(part)})}else{filters.push({name:filter_name,args:""})}});return{type:VAR_TOKEN,name:varname,args:args,filters:filters,escape:escape}};exports.parse=function(data,tags,autoescape){var rawtokens=helpers.stripWhitespace(data).split(/(\{%[^\r]*?%\}|\{\{.*?\}\}|\{#[^\r]*?#\})/),escape=!!autoescape,last_escape=escape,stack=[[]],index=0,i=0,j=rawtokens.length,token,parts,tagname,lines=1,curline=1,newlines=null,lastToken,rawStart=/^\{\% *raw *\%\}/,rawEnd=/\{\% *endraw *\%\}$/,inRaw=false,stripAfter=false,stripBefore=false,stripStart=false,stripEnd=false;for(i;i<j;i+=1){token=rawtokens[i];curline=lines;newlines=token.match(/\n/g);stripAfter=false;stripBefore=false;stripStart=false;stripEnd=false;if(newlines){lines+=newlines.length}if(inRaw!==false&&!rawEnd.test(token)){inRaw+=token;continue}if(token.length===0||commentRegexp.test(token)){continue}else if(/^\s+$/.test(token)){token=token.replace(/ +/," ").replace(/\n+/,"\n")}else if(variableRegexp.test(token)){token=exports.parseVariable(token,escape)}else if(logicRegexp.test(token)){if(rawEnd.test(token)){token=inRaw+token.replace(rawEnd,"");inRaw=false;stack[index].push(token);continue}if(rawStart.test(token)){inRaw=token.replace(rawStart,"");continue}parts=token.replace(/^\{%\s*|\s*%\}$/g,"").split(" ");if(parts[0]==="-"){stripBefore=true;parts.shift()}tagname=parts.shift();if(_.last(parts)==="-"){stripAfter=true;parts.pop()}if(index>0&&/^end/.test(tagname)){lastToken=_.last(stack[stack.length-2]);if("end"+lastToken.name===tagname){if(lastToken.name==="autoescape"){escape=last_escape}lastToken.strip.end=stripBefore;lastToken.strip.after=stripAfter;stack.pop();index-=1;continue}throw new Error('Expected end tag for "'+lastToken.name+'", but found "'+tagname+'" at line '+lines+".")}if(!tags.hasOwnProperty(tagname)){throw new Error("Unknown logic tag at line "+lines+': "'+tagname+'".')}if(tagname==="autoescape"){last_escape=escape;escape=!parts.length||parts[0]==="true"?parts.length>=2?parts[1]:true:false}token={type:LOGIC_TOKEN,line:curline,name:tagname,compile:tags[tagname],parent:_.uniq(stack[stack.length-2]||[]),strip:{before:stripBefore,after:stripAfter,start:false,end:false}};token.args=getTokenArgs(token,parts);if(tags[tagname].ends){token.strip.after=false;token.strip.start=stripAfter;stack[index].push(token);stack.push(token.tokens=[]);index+=1;continue}}stack[index].push(token)}if(inRaw!==false){throw new Error('Missing expected end tag for "raw" on line '+curline+".")}if(index!==0){lastToken=_.last(stack[stack.length-2]);throw new Error('Missing end tag for "'+lastToken.name+'" that was opened on line '+lastToken.line+".")}return stack[index]};function precompile(indent,context){var filepath,extendsHasVar,preservedTokens=[];if(this.type===TEMPLATE){_.each(this.tokens,function(token,index){if(!extendsHasVar){if(token.name==="extends"){filepath=token.args[0];if(!helpers.isStringLiteral(filepath)){if(!context){extendsHasVar=true;return}filepath='"'+getContextVar(filepath,context)+'"'}if(!helpers.isStringLiteral(filepath)||token.args.length>1){throw new Error("Extends tag on line "+token.line+" accepts exactly one string literal as an argument.")}if(index>0){throw new Error('Extends tag must be the first tag in the template, but "extends" found on line '+token.line+".")}token.template=this.compileFile(filepath.replace(/['"]/g,""),true);this.parent=token.template;this.blocks=_.extend({},this.parent.blocks,this.blocks)}else if(token.name==="block"){var blockname=token.args[0],parentBlockIndex;if(!helpers.isValidBlockName(blockname)||token.args.length!==1){throw new Error('Invalid block tag name "'+blockname+'" on line '+token.line+".")}this.blocks[blockname]=token;findSubBlocks(token,this.blocks);if(this.parent){token.parentBlock=this.parent.blocks[blockname];parentBlockIndex=_.indexOf(this.parent.tokens,this.parent.blocks[blockname]);if(parentBlockIndex>=0){this.parent.tokens[parentBlockIndex]=token}}}else if(token.type===LOGIC_TOKEN){preservedTokens.push(token)}}},this);if(extendsHasVar){return false}if(this.parent&&this.parent.tokens){this.tokens=preservedTokens.concat(this.parent.tokens)}}}exports.compile=function compile(indent,context,template){var code="",wrappedInMethod,blockname,parentBlock;indent=indent||"";if(this.type===TEMPLATE){template=this}if(!this.blocks){this.blocks={}}if(precompile.call(this,indent,context)===false){return false}_.each(this.tokens,function(token,index){var name,key,args,prev,next;if(typeof token==="string"){prev=this.tokens[index-1];next=this.tokens[index+1];if(prev&&prev.strip&&prev.strip.after){token=token.replace(/^\s+/,"")}if(next&&next.strip&&next.strip.before){token=token.replace(/\s+$/,"")}code+='_output += "'+doubleEscape(token).replace(/\n/g,"\\n").replace(/\r/g,"\\r").replace(/"/g,'\\"')+'";\n';return code}if(typeof token!=="object"){return}if(token.type===VAR_TOKEN){name=token.name.replace(/\W/g,"_");key=helpers.isLiteral(name)?'["'+name+'"]':"."+name;args=token.args&&token.args.length?token.args:"";code+='if (typeof _context !== "undefined" && typeof _context'+key+' === "function") {\n';wrappedInMethod=helpers.wrapMethod("",{name:name,args:args},"_context");code+='  _output = (typeof _output === "undefined") ? '+wrappedInMethod+": _output + "+wrappedInMethod+";\n";if(helpers.isValidName(name)){code+="} else if (typeof "+name+' === "function") {\n';wrappedInMethod=helpers.wrapMethod("",{name:name,args:args});code+='  _output = (typeof _output === "undefined") ? '+wrappedInMethod+": _output + "+wrappedInMethod+";\n"}code+="} else {\n";code+=helpers.setVar("__"+name,token);code+='  _output = (typeof _output === "undefined") ? __'+name+": _output + __"+name+";\n";code+="}\n"}if(token.type!==LOGIC_TOKEN){return}if(token.name==="block"){blockname=token.args[0];if(!template.blocks.hasOwnProperty(blockname)){throw new Error('Unrecognized nested block.  Block "'+blockname+'" at line '+token.line+' of "'+template.id+'" is not in template block list.')}code+=compile.call(template.blocks[token.args[0]],indent+"  ",context,template)}else if(token.name==="parent"){parentBlock=getParentBlock(token);if(!parentBlock){throw new Error("No parent block found for parent tag at line "+token.line+".")}code+=compile.call(parentBlock,indent+"  ",context)}else if(token.hasOwnProperty("compile")){if(token.strip.start&&token.tokens.length&&typeof token.tokens[0]==="string"){token.tokens[0]=token.tokens[0].replace(/^\s+/,"")}if(token.strip.end&&token.tokens.length&&typeof _.last(token.tokens)==="string"){token.tokens[token.tokens.length-1]=_.last(token.tokens).replace(/\s+$/,"")}code+=token.compile(indent+"  ",exports)}else{code+=compile.call(token,indent+"  ",context)}},this);return code}})(parser);tags["autoescape"]=function(){module={};module.exports=function(indent,parser){return parser.compile.apply(this,[indent])};module.exports.ends=true;return module.exports}();tags["block"]=function(){module={};module.exports={ends:true};return module.exports}();tags["else"]=function(){module={};module.exports=function(indent,parser){var last=_.last(this.parent).name,thisArgs=_.clone(this.args),ifarg,args,out;if(last==="for"){if(thisArgs.length){throw new Error('"else" tag cannot accept arguments in the "for" context.')}return"} if (__loopLength === 0) {\n"}if(last!=="if"){throw new Error('Cannot call else tag outside of "if" or "for" context.')}ifarg=thisArgs.shift();args=helpers.parseIfArgs(thisArgs,parser);out="";if(ifarg){out+="} else if (\n";out+="  (function () {\n";_.each(args,function(token){if(token.hasOwnProperty("preout")&&token.preout){out+=token.preout+"\n"}});out+="return (\n";_.each(args,function(token){out+=token.value+" "});out+=");\n";out+="  })()\n";out+=") {\n";return out}return indent+"\n} else {\n"};return module.exports}();tags["extends"]=function(){module={};module.exports={};return module.exports}();tags["filter"]=function(){module={};module.exports=function(indent,parser){var thisArgs=_.clone(this.args),name=thisArgs.shift(),args=thisArgs.length?thisArgs.join(", "):"",value="(function () {\n";value+='  var _output = "";\n';value+=parser.compile.apply(this,[indent+"  "])+"\n";value+="  return _output;\n";value+="})()\n";return"_output += "+helpers.wrapFilter(value.replace(/\n/g,""),{name:name,args:args})+";\n"};module.exports.ends=true;return module.exports}();tags["for"]=function(){module={};module.exports=function(indent,parser){var thisArgs=_.clone(this.args),operand1=thisArgs[0],operator=thisArgs[1],operand2=parser.parseVariable(thisArgs[2]),out="",loopShared;indent=indent||"";if(typeof operator!=="undefined"&&operator!=="in"){throw new Error('Invalid syntax in "for" tag')}if(!helpers.isValidShortName(operand1)){throw new Error("Invalid arguments ("+operand1+') passed to "for" tag')}if(!helpers.isValidName(operand2.name)){throw new Error("Invalid arguments ("+operand2.name+') passed to "for" tag')}operand1=helpers.escapeVarName(operand1);loopShared="loop.index = __loopIndex + 1;\n"+"loop.index0 = __loopIndex;\n"+"loop.revindex = __loopLength - loop.index0;\n"+"loop.revindex0 = loop.revindex - 1;\n"+"loop.first = (__loopIndex === 0);\n"+"loop.last = (__loopIndex === __loopLength - 1);\n"+'_context["'+operand1+'"] = __loopIter[loop.key];\n'+parser.compile.apply(this,[indent+"   "]);out="(function () {\n"+"  var loop = {}, __loopKey, __loopIndex = 0, __loopLength = 0, __keys = [],"+'    __ctx_operand = _context["'+operand1+'"],\n'+"    loop_cycle = function() {\n"+"      var args = _.toArray(arguments), i = loop.index0 % args.length;\n"+"      return args[i];\n"+"    };\n"+helpers.setVar("__loopIter",operand2)+"  else {\n"+"    return;\n"+"  }\n"+"  if (_.isArray(__loopIter)) {\n"+"    __loopIndex = 0; __loopLength = __loopIter.length;\n"+"    for (; __loopIndex < __loopLength; __loopIndex += 1) {\n"+"       loop.key = __loopIndex;\n"+loopShared+"    }\n"+'  } else if (typeof __loopIter === "object") {\n'+"    __keys = _.keys(__loopIter);\n"+"    __loopLength = __keys.length;\n"+"    __loopIndex = 0;\n"+"    for (; __loopIndex < __loopLength; __loopIndex += 1) {\n"+"       loop.key = __keys[__loopIndex];\n"+loopShared+"    }\n"+"  }\n"+'  _context["'+operand1+'"] = __ctx_operand;\n'+"})();\n";return out};module.exports.ends=true;return module.exports}();tags["if"]=function(){module={};module.exports=function(indent,parser){var thisArgs=_.clone(this.args),args=helpers.parseIfArgs(thisArgs,parser),out="(function () {\n";_.each(args,function(token){if(token.hasOwnProperty("preout")&&token.preout){out+=token.preout+"\n"}});out+="\nif (\n";_.each(args,function(token){out+=token.value+" "});out+=") {\n";out+=parser.compile.apply(this,[indent+"  "]);out+="\n}\n";out+="})();\n";return out};module.exports.ends=true;return module.exports}();tags["import"]=function(){module={};module.exports=function(indent,parser){if(this.args.length!==3){}var thisArgs=_.clone(this.args),file=thisArgs[0],as=thisArgs[1],name=thisArgs[2],out="";if(!helpers.isLiteral(file)&&!helpers.isValidName(file)){throw new Error('Invalid attempt to import "'+file+'".')}if(as!=="as"){throw new Error('Invalid syntax {% import "'+file+'" '+as+" "+name+" %}")}out+="_.extend(_context, (function () {\n";out+='var _context = {}, __ctx = {}, _output = "";\n'+helpers.setVar("__template",parser.parseVariable(file))+"_this.compileFile(__template).render(__ctx, _parents);\n"+"_.each(__ctx, function (item, key) {\n"+'  if (typeof item === "function") {\n'+'    _context["'+name+'_" + key] = item;\n'+"  }\n"+"});\n"+"return _context;\n";out+="})());\n";return out};return module.exports}();tags["include"]=function(){module={};module.exports=function(indent,parser){var args=_.clone(this.args),template=args.shift(),context="_context",ignore=false,out="",ctx;indent=indent||"";if(!helpers.isLiteral(template)&&!helpers.isValidName(template)){throw new Error("Invalid arguments passed to 'include' tag.")}if(args.length){if(_.last(args)==="only"){context="{}";args.pop()}if(args.length>1&&args[0]==="ignore"&args[1]==="missing"){args.shift();args.shift();ignore=true}if(args.length&&args[0]!=="with"){throw new Error("Invalid arguments passed to 'include' tag.")}if(args[0]==="with"){args.shift();if(!args.length){throw new Error("Context for 'include' tag not provided, but expected after 'with' token.")}ctx=args.shift();context='_context["'+ctx+'"] || '+ctx}}out="(function () {\n"+helpers.setVar("__template",parser.parseVariable(template))+"\n"+"  var includeContext = "+context+";\n";if(ignore){out+="try {\n"}out+='  if (typeof __template === "string") {\n';out+="    _output += _this.compileFile(__template).render(includeContext, _parents);\n";out+="  }\n";if(ignore){out+="} catch (e) {}\n"}out+="})();\n";return out};return module.exports}();tags["macro"]=function(){module={};module.exports=function(indent,parser){var thisArgs=_.clone(this.args),macro=thisArgs.shift(),args="",out="";if(thisArgs.length){args=JSON.stringify(thisArgs).replace(/^\[|\'|\"|\]$/g,"")}out+="_context."+macro+" = function ("+args+") {\n";out+='  var _output = "";\n';out+=parser.compile.apply(this,[indent+"  "]);out+="  return _output;\n";out+="};\n";return out};module.exports.ends=true;return module.exports}();tags["parent"]=function(){module={};module.exports={};return module.exports}();tags["raw"]=function(){module={};module.exports={ends:true};return module.exports}();tags["set"]=function(){module={};module.exports=function(indent,parser){var thisArgs=_.clone(this.args),varname=helpers.escapeVarName(thisArgs.shift(),"_context"),value;if(thisArgs.shift()!=="="){throw new Error('Invalid token "'+thisArgs[1]+'" in {% set '+thisArgs[0]+' %}. Missing "=".')}value=thisArgs[0];if(helpers.isLiteral(value)||/^\{|^\[/.test(value)||value==="true"||value==="false"){return" "+varname+" = "+value+";"}value=parser.parseVariable(value);return" "+varname+" = "+"(function () {\n"+"  var _output;\n"+parser.compile.apply({tokens:[value]},[indent])+"\n"+"  return _output;\n"+"})();\n"};return module.exports}();return swig}();
        /* js/04-bluebutton-0.0.10.js */ var Core=function(){var ElementWrapper=function(el){return{el:el,template:template,tag:tag,elsByTag:elsByTag,attr:attr,val:val,isEmpty:isEmpty}};var parseXML=function(data){if(!data||typeof data!=="string"){console.log("BB Error: XML data is not a string");return null;}
            var xml,tmp;if(window.DOMParser){parser=new DOMParser();xml=parser.parseFromString(data,"text/xml");}else{try{xml=new ActiveXObject("Microsoft.XMLDOM");xml.async="false";xml.loadXML(data);}catch(e){console.log("BB ActiveX Exception: Could not parse XML");}}
            if(!xml||!xml.documentElement||xml.getElementsByTagName("parsererror").length){console.log("BB Error: Could not parse XML");return null;}
            return wrapElement(xml);};var wrapElement=function(el){if(el.length){var els=[];for(var i=0;i<el.length;i++){els.push(ElementWrapper(el[i]));}
            return els;}else{return ElementWrapper(el);}};var emptyEl=function(){var el=document.createElement('empty');return wrapElement(el);};var tagAttrVal=function(el,tag,attr,value){el=el.getElementsByTagName(tag);for(var i=0;i<el.length;i++){if(el[i].getAttribute(attr)===value){return el[i];}}};var template=function(templateId){var el=tagAttrVal(this.el,'templateId','root',templateId);if(!el){return emptyEl();}else{return wrapElement(el.parentNode);}};var tag=function(tag){var el=this.el.getElementsByTagName(tag)[0];if(!el){return emptyEl();}else{return wrapElement(el);}};var elsByTag=function(tag){return wrapElement(this.el.getElementsByTagName(tag));};var attr=function(attr){if(!this.el){return null;}
            return this.el.getAttribute(attr);};var val=function(){if(!this.el){return null;}
            try{return this.el.childNodes[0].nodeValue;}catch(e){return null;}};var isEmpty=function(){if(this.el.tagName.toLowerCase()=='empty'){return true;}else{return false;}};var parseDate=function(str){if(!str||typeof str!=="string"){console.log("Error: date is not a string");return null;}
            var year=str.substr(0,4);var month=str.substr(4,2);var day=str.substr(6,2);return new Date(year,month,day);};var trim=function(o){var y;for(var x in o){y=o[x];if(y===null){delete o[x];}
            if(y instanceof Object)y=trim(y);}
            return o;}
            return{parseXML:parseXML,parseDate:parseDate,trim:trim};}();var Allergies=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date_range:{start:raw[i].start_date,end:raw[i].end_date},name:raw[i].name,code:raw[i].code,code_system:raw[i].code_system,code_system_name:raw[i].code_system_name,status:raw[i].status,severity:raw[i].severity,reaction:{name:raw[i].reaction_name,code:raw[i].reaction_code,code_system:raw[i].reaction_code_system},reaction_type:{name:raw[i].reaction_type_name,code:raw[i].reaction_type_code,code_system:raw[i].reaction_code_system,code_system_name:raw[i].reaction_type_code_system_name},allergen:{name:raw[i].allergen_name,code:raw[i].allergen_code,code_system:raw[i].allergen_code_system,code_system_name:raw[i].allergen_code_system_name}});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.6.1');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=parseDate(el.tag('low').attr('value')),end_date=parseDate(el.tag('high').attr('value'));el=entry.template('2.16.840.1.113883.10.20.22.4.7').tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.7').tag('value');var reaction_type_name=el.attr('displayName'),reaction_type_code=el.attr('code'),reaction_type_code_system=el.attr('codeSystem'),reaction_type_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.9').tag('value');var reaction_name=el.attr('displayName'),reaction_code=el.attr('code'),reaction_code_system=el.attr('codeSystem');el=entry.template('2.16.840.1.113883.10.20.22.4.8').tag('value');var severity=el.attr('displayName');el=entry.tag('participant').tag('code');var allergen_name=el.attr('displayName'),allergen_code=el.attr('code'),allergen_code_system=el.attr('codeSystem'),allergen_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.28').tag('value');var status=el.attr('displayName');data.push({name:name,start_date:start_date,end_date:end_date,code:code,code_system:code_system,code_system_name:code_system_name,reaction_type_name:reaction_type_name,reaction_type_code:reaction_type_code,reaction_type_code_system:reaction_type_code_system,reaction_type_code_system_name:reaction_type_code_system_name,reaction_name:reaction_name,reaction_code:reaction_code,reaction_code_system:reaction_code_system,severity:severity,allergen_name:allergen_name,allergen_code:allergen_code,allergen_code_system:allergen_code_system,allergen_code_system_name:allergen_code_system_name});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.3.88.11.83.102');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=el.tag('low').attr('value'),end_date=el.tag('high').attr('value');el=entry.template('2.16.840.1.113883.10.20.1.28').tag('code');var name=el.tag('originalText').val(),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.1.54').tag('value');var reaction_type_name=el.attr('displayName'),reaction_type_code=el.attr('code'),reaction_type_code_system=el.attr('codeSystem'),reaction_type_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.1.54').tag('value');var reaction_name=el.attr('displayName'),reaction_code=el.attr('code'),reaction_code_system=el.attr('codeSystem');el=entry.template('2.16.840.1.113883.10.20.1.55').tag('value');var severity=el.attr('displayName');el=entry.tag('participant').tag('code');var allergen_name=entry.tag('participant').tag('name').val(),allergen_code=el.attr('code'),allergen_code_system=el.attr('codeSystem'),allergen_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.28').tag('value');var status=el.attr('displayName');data.push({name:name,start_date:start_date,end_date:end_date,code:code,code_system:code_system,code_system_name:code_system_name,reaction_type_name:reaction_type_name,reaction_type_code:reaction_type_code,reaction_type_code_system:reaction_type_code_system,reaction_type_code_system_name:reaction_type_code_system_name,reaction_name:reaction_name,reaction_code:reaction_code,reaction_code_system:reaction_code_system,severity:severity,allergen_name:allergen_name,allergen_code:allergen_code,allergen_code_system:allergen_code_system,allergen_code_system_name:allergen_code_system_name});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Demographics=function(){var parseDate=Core.parseDate;var process=function(source,type){var data;switch(type){case'ccda':data=processCCDA(source);break;case'va_c32':data=processVAC32(source);break;case'json':return processJSON(source);break;}
            return{name:{prefix:data.prefix,given:data.given,family:data.family},dob:data.dob,gender:data.gender,marital_status:data.marital_status,address:{street:data.street,city:data.city,state:data.state,zip:data.zip,country:data.country},phone:{home:data.home,work:data.work,mobile:data.mobile},email:data.email,language:data.language,race:data.race,ethnicity:data.ethnicity,religion:data.religion,birthplace:{state:data.birthplace_state,zip:data.birthplace_zip,country:data.birthplace_country},guardian:{name:{given:data.guardian_given,family:data.guardian_family},relationship:data.guardian_relationship,address:{street:data.guardian_street,city:data.guardian_city,state:data.guardian_state,zip:data.guardian_zip,country:data.guardian_country},phone:{home:data.guardian_home}},provider:{organization:data.provider_organization,phone:data.provider_phone,address:{street:data.provider_street,city:data.provider_city,state:data.provider_state,zip:data.provider_zip,country:data.provider_country}}};};var processCCDA=function(xmlDOM){var data={},el,els,patient;el=xmlDOM.template('2.16.840.1.113883.10.20.22.1.1');patient=el.tag('patientRole');el=patient.tag('patient').tag('name');data.prefix=el.tag('prefix').val();els=el.elsByTag('given');data.given=[];for(var i=0;i<els.length;i++){data.given.push(els[i].val());}
            data.family=el.tag('family').val();el=patient.tag('patient');data.dob=parseDate(el.tag('birthTime').attr('value'));data.gender=el.tag('administrativeGenderCode').attr('displayName');data.marital_status=el.tag('maritalStatusCode').attr('displayName');el=patient.tag('addr');els=el.elsByTag('streetAddressLine');data.street=[];for(var i=0;i<els.length;i++){data.street.push(els[i].val());}
            data.city=el.tag('city').val();data.state=el.tag('state').val();data.zip=el.tag('postalCode').val();data.country=el.tag('country').val();el=patient.tag('telecom');data.home=el.attr('value');data.work=null;data.mobile=null;data.email=null;data.language=patient.tag('languageCommunication').tag('languageCode').attr('code');data.race=patient.tag('raceCode').attr('displayName');data.ethnicity=patient.tag('ethnicGroupCode').attr('displayName');data.religion=patient.tag('religiousAffiliationCode').attr('displayName');el=patient.tag('birthplace');data.birthplace_state=el.tag('state').val();data.birthplace_zip=el.tag('postalCode').val();data.birthplace_country=el.tag('country').val();el=patient.tag('guardian');data.guardian_relationship=el.tag('code').attr('displayName');data.guardian_home=el.tag('telecom').attr('value');el=el.tag('guardianPerson');els=el.elsByTag('given');data.guardian_given=[];for(var i=0;i<els.length;i++){data.guardian_given.push(els[i].val());}
            data.guardian_family=el.tag('family').val();el=patient.tag('guardian').tag('addr');els=el.elsByTag('streetAddressLine');data.guardian_street=[];for(var i=0;i<els.length;i++){data.guardian_street.push(els[i].val());}
            data.guardian_city=el.tag('city').val();data.guardian_state=el.tag('state').val();data.guardian_zip=el.tag('postalCode').val();data.guardian_country=el.tag('country').val();el=patient.tag('providerOrganization');data.provider_organization=el.tag('name').val();data.provider_phone=el.tag('telecom').attr('value');els=el.elsByTag('streetAddressLine');data.provider_street=[];for(var i=0;i<els.length;i++){data.provider_street.push(els[i].val());}
            data.provider_city=el.tag('city').val();data.provider_state=el.tag('state').val();data.provider_zip=el.tag('postalCode').val();data.provider_country=el.tag('country').val();return data;};var processVAC32=function(xmlDOM){var data={},el,els,patient;el=xmlDOM.template('1.3.6.1.4.1.19376.1.5.3.1.1.1');patient=el.tag('patientRole');el=patient.tag('patient').tag('name');data.prefix=el.tag('prefix').val();els=el.elsByTag('given');data.given=[];for(var i=0;i<els.length;i++){data.given.push(els[i].val());}
            data.family=el.tag('family').val();el=patient.tag('patient');data.dob=parseDate(el.tag('birthTime').attr('value'));data.gender=el.tag('administrativeGenderCode').attr('displayName');data.marital_status=el.tag('maritalStatusCode').attr('displayName');el=patient.tag('addr');els=el.elsByTag('streetAddressLine');data.street=[];for(var i=0;i<els.length;i++){data.street.push(els[i].val());}
            data.city=el.tag('city').val();data.state=el.tag('state').val();data.zip=el.tag('postalCode').val();data.country=el.tag('country').val();el=patient.tag('telecom');data.home=el.attr('value');data.work=null;data.mobile=null;data.email=null;data.language=patient.tag('languageCommunication').tag('languageCode').attr('code');data.race=patient.tag('raceCode').attr('displayName');data.ethnicity=patient.tag('ethnicGroupCode').attr('displayName');data.religion=patient.tag('religiousAffiliationCode').attr('displayName');el=patient.tag('birthplace');data.birthplace_state=el.tag('state').val();data.birthplace_zip=el.tag('postalCode').val();data.birthplace_country=el.tag('country').val();el=patient.tag('guardian');data.guardian_relationship=el.tag('code').attr('displayName');data.guardian_home=el.tag('telecom').attr('value');el=el.tag('guardianPerson');els=el.elsByTag('given');data.guardian_given=[];for(var i=0;i<els.length;i++){data.guardian_given.push(els[i].val());}
            data.guardian_family=el.tag('family').val();el=patient.tag('guardian').tag('addr');els=el.elsByTag('streetAddressLine');data.guardian_street=[];for(var i=0;i<els.length;i++){data.guardian_street.push(els[i].val());}
            data.guardian_city=el.tag('city').val();data.guardian_state=el.tag('state').val();data.guardian_zip=el.tag('postalCode').val();data.guardian_country=el.tag('country').val();el=patient.tag('providerOrganization');data.provider_organization=el.tag('name').val();data.provider_phone=el.tag('telecom').attr('value');els=el.elsByTag('streetAddressLine');data.provider_street=[];for(var i=0;i<els.length;i++){data.provider_street.push(els[i].val());}
            data.provider_city=el.tag('city').val();data.provider_state=el.tag('state').val();data.provider_zip=el.tag('postalCode').val();data.provider_country=el.tag('country').val();return data;};var processJSON=function(json){return{};};return{process:process};}();var Encounters=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date:raw[i].date,name:raw[i].name,code:raw[i].code,code_system:raw[i].code_system,code_system_name:raw[i].code_system_name,code_system_version:raw[i].code_system_version,finding:{name:raw[i].finding_name,code:raw[i].finding_code,code_system:raw[i].finding_code_system},translation:{name:raw[i].translation_name,code:raw[i].translation_code,code_system:raw[i].translation_code_system,code_system_name:raw[i].translation_code_system_name},performer:{name:raw[i].performer_name,code:raw[i].performer_code,code_system:raw[i].performer_code_system,code_system_name:raw[i].performer_code_system_name},location:{organization:raw[i].organization,street:raw[i].street,city:raw[i].city,state:raw[i].state,zip:raw[i].zip,country:raw[i].country}});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,els,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.22')
            if(el.isEmpty()){el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.22.1');}
            entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];var date=parseDate(entry.tag('effectiveTime').attr('value'));el=entry.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName'),code_system_version=el.attr('codeSystemVersion');el=entry.tag('value');var finding_name=el.attr('displayName'),finding_code=el.attr('code'),finding_code_system=el.attr('codeSystem');el=entry.tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('performer').tag('code');var performer_name=el.attr('displayName'),performer_code=el.attr('code'),performer_code_system=el.attr('codeSystem'),performer_code_system_name=el.attr('codeSystemName');el=entry.tag('participant');var organization=el.tag('code').attr('displayName');els=el.elsByTag('streetAddressLine');street=[];for(var j=0;j<els.length;j++){street.push(els[j].val());}
                var city=el.tag('city').val(),state=el.tag('state').val(),zip=el.tag('postalCode').val(),country=el.tag('country').val();data.push({date:date,name:name,code:code,code_system:code_system,code_system_name:code_system_name,code_system_version:code_system_version,finding_name:finding_name,finding_code:finding_code,finding_code_system:finding_code_system,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,performer_name:performer_name,performer_code_system:performer_code_system,performer_code_system_name:performer_code_system_name,organization:organization,street:street,city:city,state:state,zip:zip,country:country});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,els,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.1.3');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];var date=parseDate(entry.tag('effectiveTime').tag('low').attr('value'));el=entry.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName'),code_system_version=el.attr('codeSystemVersion');el=entry.tag('value');var finding_name=el.attr('displayName'),finding_code=el.attr('code'),finding_code_system=el.attr('codeSystem');el=entry.tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('performer').tag('code');var performer_name=el.attr('displayName'),performer_code=el.attr('code'),performer_code_system=el.attr('codeSystem'),performer_code_system_name=el.attr('codeSystemName');el=entry.tag('participant');var organization=el.tag('code').attr('displayName');els=el.elsByTag('streetAddressLine');street=[];for(var j=0;j<els.length;j++){street.push(els[j].val());}
            var city=el.tag('city').val(),state=el.tag('state').val(),zip=el.tag('postalCode').val(),country=el.tag('country').val();data.push({date:date,name:name,code:code,code_system:code_system,code_system_name:code_system_name,code_system_version:code_system_version,finding_name:finding_name,finding_code:finding_code,finding_code_system:finding_code_system,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,performer_name:performer_name,performer_code_system:performer_code_system,performer_code_system_name:performer_code_system_name,organization:organization,street:street,city:city,state:state,zip:zip,country:country});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Immunizations=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date:raw[i].date,product:{name:raw[i].product_name,code:raw[i].product_code,code_system:raw[i].product_code_system,code_system_name:raw[i].product_code_system_name,translation:{name:raw[i].translation_name,code:raw[i].translation_code,code_system:raw[i].translation_code_system,code_system_name:raw[i].translation_code_system_name}},route:{name:raw[i].route_name,code:raw[i].route_code,code_system:raw[i].route_code_system,code_system_name:raw[i].route_code_system_name},instructions:raw[i].instructions_text,education_type:{name:raw[i].education_name,code:raw[i].education_code,code_system:raw[i].education_code_system}});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.2.1')
            if(el.isEmpty()){el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.2');}
            entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var date=parseDate(el.attr('value'));el=entry.template('2.16.840.1.113883.10.20.22.4.54').tag('code');var product_name=el.attr('displayName'),product_code=el.attr('code'),product_code_system=el.attr('codeSystem'),product_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.54').tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('routeCode');var route_name=el.attr('displayName'),route_code=el.attr('code'),route_code_system=el.attr('codeSystem'),route_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.20');var instructions_text=el.tag('text').val();el=el.tag('code');var education_name=el.attr('displayName'),education_code=el.attr('code'),education_code_system=el.attr('codeSystem');data.push({date:date,product_name:product_name,product_code:product_code,product_code_system:product_code_system,product_code_system_name:product_code_system_name,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,route_name:route_name,route_code:route_code,route_code_system:route_code_system,route_code_system_name:route_code_system_name,instructions_text:instructions_text,education_name:education_name,education_code:education_code,education_code_system:education_code_system});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.1.6');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var date=parseDate(el.attr('value'));el=entry.template('2.16.840.1.113883.10.20.1.53').tag('code');var product_name=el.attr('displayName'),product_code=el.attr('code'),product_code_system=el.attr('codeSystem'),product_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.54').tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('routeCode');var route_name=el.attr('displayName'),route_code=el.attr('code'),route_code_system=el.attr('codeSystem'),route_code_system_name=el.attr('codeSystemName');el=entry.template('2.16.840.1.113883.10.20.22.4.20');var instructions_text=el.tag('text').val();el=el.tag('code');var education_name=el.attr('displayName'),education_code=el.attr('code'),education_code_system=el.attr('codeSystem');data.push({date:date,product_name:product_name,product_code:product_code,product_code_system:product_code_system,product_code_system_name:product_code_system_name,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,route_name:route_name,route_code:route_code,route_code_system:route_code_system,route_code_system_name:route_code_system_name,instructions_text:instructions_text,education_name:education_name,education_code:education_code,education_code_system:education_code_system});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Labs=function(){var parseDate=Core.parseDate;var process=function(source,type){var panels,data=[];switch(type){case'ccda':panels=processCCDA(source);break;case'va_c32':panels=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<panels.length;i++){var p=panels[i];var panel={name:p.name,code:p.code,code_system:p.code_system,code_system_name:p.code_system_name}
                var results=[];for(var j=0;j<p.results.length;j++){var r=p.results[j];results.push({date:r.date,name:r.name,value:r.value,unit:r.unit,code:r.code,code_system:r.code_system,code_system_name:r.code_system_name,reference:{low:r.reference_low,high:r.reference_high}});}
                panel.results=results;data.push(panel);}
            return data;};var processCCDA=function(xmlDOM){var data=[],results_data=[],el,entries,entry,results,result;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.3.1');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('code');var panel_name=el.attr('displayName'),panel_code=el.attr('code'),panel_code_system=el.attr('codeSystem'),panel_code_system_name=el.attr('codeSystemName');results=entry.elsByTag('component');for(var j=0;j<results.length;j++){result=results[j];var date=parseDate(result.tag('effectiveTime').attr('value'));el=result.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=result.tag('value');var value=parseInt(el.attr('value')),unit=el.attr('unit');reference_low=null;reference_high=null;results_data.push({date:date,name:name,value:value,unit:unit,code:code,code_system:code_system,code_system_name:code_system_name,reference_low:reference_low,reference_high:reference_high});}
            data.push({name:panel_name,code:panel_code,code_system:panel_code_system,code_system_name:panel_code_system_name,results:results_data});}
            return data;};var processVAC32=function(xmlDOM){var data=[],results_data=[],el,entries,entry,results,result;el=xmlDOM.template('2.16.840.1.113883.10.20.1.14');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('code');var panel_name=el.attr('displayName'),panel_code=el.attr('code'),panel_code_system=el.attr('codeSystem'),panel_code_system_name=el.attr('codeSystemName');results=entry.elsByTag('component');for(var j=0;j<results.length;j++){result=results[j];var date=parseDate(result.tag('effectiveTime').attr('value'));el=result.tag('code');var name=el.tag('originalText').val(),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=result.tag('value');var value=parseInt(el.attr('value')),unit=el.attr('unit');reference_low=null;reference_high=null;results_data.push({date:date,name:name,value:value,unit:unit,code:code,code_system:code_system,code_system_name:code_system_name,reference_low:reference_low,reference_high:reference_high});}
            data.push({name:panel_name,code:panel_code,code_system:panel_code_system,code_system_name:panel_code_system_name,results:results_data});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Medications=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date_range:{start:raw[i].start_date,end:raw[i].end_date},product:{name:raw[i].product_name,code:raw[i].product_code,code_system:raw[i].product_code_system,translation:{name:raw[i].translation_name,code:raw[i].translation_code,code_system:raw[i].translation_code_system,code_system_name:raw[i].translation_code_system_name}},dose_quantity:{value:raw[i].dose_value,unit:raw[i].dose_unit},rate_quantity:{value:raw[i].rate_quantity_value,unit:raw[i].rate_quantity_unit},precondition:{name:raw[i].precondition_name,code:raw[i].precondition_code,code_system:raw[i].precondition_code_system},reason:{name:raw[i].reason_name,code:raw[i].reason_code,code_system:raw[i].reason_code_system},route:{name:raw[i].route_name,code:raw[i].route_code,code_system:raw[i].route_code_system,code_system_name:raw[i].route_code_system_name},vehicle:{name:raw[i].vehicle_name,code:raw[i].vehicle_code,code_system:raw[i].vehicle_code_system,code_system_name:raw[i].vehicle_code_system_name},administration:{name:raw[i].administration_name,code:raw[i].administration_code,code_system:raw[i].administration_code_system,code_system_name:raw[i].administration_code_system_name},prescriber:{organization:raw[i].prescriber_organization,person:raw[i].prescriber_person}});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.1.1');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=parseDate(el.tag('low').attr('value')),end_date=parseDate(el.tag('high').attr('value'));el=entry.tag('manufacturedProduct').tag('code');var product_name=el.attr('displayName'),product_code=el.attr('code'),product_code_system=el.attr('codeSystem');el=entry.tag('manufacturedProduct').tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('doseQuantity');var dose_value=el.attr('value'),dose_unit=el.attr('unit');el=entry.tag('rateQuantity');var rate_quantity_value=el.attr('value'),rate_quantity_unit=el.attr('unit');el=entry.tag('precondition').tag('value');var precondition_name=el.attr('displayName'),precondition_code=el.attr('code'),precondition_code_system=el.attr('codeSystem'),el=entry.template('2.16.840.1.113883.10.20.22.4.19').tag('value');var reason_name=el.attr('displayName'),reason_code=el.attr('code'),reason_code_system=el.attr('codeSystem');el=entry.tag('routeCode')
            var route_name=el.attr('displayName'),route_code=el.attr('code'),route_code_system=el.attr('codeSystem'),route_code_system_name=el.attr('codeSystemName');el=entry.tag('participant').tag('code');var vehicle_name=el.attr('displayName'),vehicle_code=el.attr('code'),vehicle_code_system=el.attr('codeSystem'),vehicle_code_system_name=el.attr('codeSystemName');el=entry.tag('administrationUnitCode');var administration_name=el.attr('displayName'),administration_code=el.attr('code'),administration_code_system=el.attr('codeSystem'),administration_code_system_name=el.attr('codeSystemName');el=entry.tag('performer');var prescriber_organization=el.tag('name').val(),prescriber_person=null;data.push({start_date:start_date,end_date:end_date,product_name:product_name,product_code:product_code,product_code_system:product_code_system,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,dose_value:dose_value,dose_unit:dose_unit,rate_quantity_value:rate_quantity_value,rate_quantity_unit:rate_quantity_unit,precondition_name:precondition_name,precondition_code:precondition_code,precondition_code_system:precondition_code_system,reason_name:reason_name,reason_code:reason_code,reason_code_system:reason_code_system,route_name:route_name,route_code:route_code,route_code_system:route_code_system,route_code_system_name:route_code_system_name,vehicle_name:vehicle_name,vehicle_code:vehicle_code,vehicle_code_system:vehicle_code_system,vehicle_code_system_name:vehicle_code_system_name,administration_name:administration_name,administration_code:administration_code,administration_code_system:administration_code_system,administration_code_system_name:administration_code_system_name,prescriber_organization:prescriber_organization,prescriber_person:prescriber_person});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.3.88.11.83.112');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=parseDate(el.tag('low').attr('value')),end_date=parseDate(el.tag('high').attr('value'));el=entry.tag('manufacturedProduct').tag('code');var product_name=el.attr('displayName'),product_code=el.attr('code'),product_code_system=el.attr('codeSystem');el=entry.tag('manufacturedProduct').tag('translation');var translation_name=el.attr('displayName'),translation_code=el.attr('code'),translation_code_system=el.attr('codeSystem'),translation_code_system_name=el.attr('codeSystemName');el=entry.tag('doseQuantity');var dose_value=el.attr('value'),dose_unit=el.attr('unit');el=entry.tag('rateQuantity');var rate_quantity_value=el.attr('value'),rate_quantity_unit=el.attr('unit');el=entry.tag('precondition').tag('value');var precondition_name=el.attr('displayName'),precondition_code=el.attr('code'),precondition_code_system=el.attr('codeSystem'),el=entry.template('2.16.840.1.113883.10.20.22.4.19').tag('value');var reason_name=el.attr('displayName'),reason_code=el.attr('code'),reason_code_system=el.attr('codeSystem');el=entry.tag('routeCode')
            var route_name=el.attr('displayName'),route_code=el.attr('code'),route_code_system=el.attr('codeSystem'),route_code_system_name=el.attr('codeSystemName');el=entry.tag('participant').tag('code');var vehicle_name=el.attr('displayName'),vehicle_code=el.attr('code'),vehicle_code_system=el.attr('codeSystem'),vehicle_code_system_name=el.attr('codeSystemName');el=entry.tag('administrationUnitCode');var administration_name=el.attr('displayName'),administration_code=el.attr('code'),administration_code_system=el.attr('codeSystem'),administration_code_system_name=el.attr('codeSystemName');el=entry.tag('performer');var prescriber_organization=el.tag('name').val(),prescriber_person=null;data.push({start_date:start_date,end_date:end_date,product_name:product_name,product_code:product_code,product_code_system:product_code_system,translation_name:translation_name,translation_code:translation_code,translation_code_system:translation_code_system,translation_code_system_name:translation_code_system_name,dose_value:dose_value,dose_unit:dose_unit,rate_quantity_value:rate_quantity_value,rate_quantity_unit:rate_quantity_unit,precondition_name:precondition_name,precondition_code:precondition_code,precondition_code_system:precondition_code_system,reason_name:reason_name,reason_code:reason_code,reason_code_system:reason_code_system,route_name:route_name,route_code:route_code,route_code_system:route_code_system,route_code_system_name:route_code_system_name,vehicle_name:vehicle_name,vehicle_code:vehicle_code,vehicle_code_system:vehicle_code_system,vehicle_code_system_name:vehicle_code_system_name,administration_name:administration_name,administration_code:administration_code,administration_code_system:administration_code_system,administration_code_system_name:administration_code_system_name,prescriber_organization:prescriber_organization,prescriber_person:prescriber_person});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Problems=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date_range:{start:raw[i].start_date,end:raw[i].end_date},name:raw[i].name,status:raw[i].status,age:raw[i].age,code:raw[i].code,code_system:raw[i].code_system});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.5.1')
            if(el.isEmpty()){el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.5');}
            entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=parseDate(el.tag('low').attr('value')),end_date=parseDate(el.tag('high').attr('value'));el=entry.template('2.16.840.1.113883.10.20.22.4.4').tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem');el=entry.template('2.16.840.1.113883.10.20.22.4.6');var status=el.tag('value').attr('displayName');el=entry.template('2.16.840.1.113883.10.20.22.4.31');var age=parseInt(el.tag('value').attr('value'));data.push({start_date:start_date,end_date:end_date,name:name,code:code,code_system:code_system,status:status,age:age});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.1.11');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var start_date=parseDate(el.tag('low').attr('value')),end_date=parseDate(el.tag('high').attr('value'));el=entry.template('2.16.840.1.113883.10.20.1.28').tag('code');var name=el.tag('originalText').val(),code=el.attr('code'),code_system=el.attr('codeSystem');el=entry.template('2.16.840.1.113883.10.20.22.4.6');var status=el.tag('value').attr('displayName');el=entry.template('2.16.840.1.113883.10.20.22.4.31');var age=parseInt(el.tag('value').attr('value'));data.push({start_date:start_date,end_date:end_date,name:name,code:code,code_system:code_system,status:status,age:age});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Procedures=function(){var parseDate=Core.parseDate;var process=function(source,type){var raw,data=[];switch(type){case'ccda':raw=processCCDA(source);break;case'va_c32':raw=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<raw.length;i++){data.push({date:raw[i].date,name:raw[i].name,code:raw[i].code,code_system:raw[i].code_system,specimen:{name:raw[i].specimen_name,code:raw[i].specimen_code,code_system:raw[i].specimen_code_system},performer:{organization:raw[i].organization,street:raw[i].street,city:raw[i].city,state:raw[i].state,zip:raw[i].zip,country:raw[i].country,phone:raw[i].phone},device:{name:raw[i].device_name,code:raw[i].device_code,code_system:raw[i].device_code_system}});}
            return data;};var processCCDA=function(xmlDOM){var data=[],el,els,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.7.1')
            if(el.isEmpty()){el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.7');}
            entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var date=parseDate(el.attr('value'));el=entry.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem');var specimen_name=null,specimen_code=null,specimen_code_system=null;el=entry.tag('performer').tag('addr');var organization=el.tag('name').val(),phone=el.tag('telecom').attr('value');els=el.elsByTag('streetAddressLine');street=[];for(var j=0;j<els.length;j++){street.push(els[j].val());}
                var city=el.tag('city').val(),state=el.tag('state').val(),zip=el.tag('postalCode').val(),country=el.tag('country').val();el=entry.tag('participant').tag('code');var device_name=el.attr('displayName'),device_code=el.attr('code'),device_code_system=el.attr('codeSystem');data.push({date:date,name:name,code:code,code_system:code_system,specimen_name:specimen_name,specimen_code:specimen_code,specimen_code_system:specimen_code_system,organization:organization,phone:phone,street:street,city:city,state:state,zip:zip,country:country,device_name:device_name,device_code:device_code,device_code_system:device_code_system});}
            return data;};var processVAC32=function(xmlDOM){var data=[],el,els,entries,entry;el=xmlDOM.template('2.16.840.1.113883.10.20.1.12');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var date=parseDate(el.tag('low').attr('value'));el=entry.tag('code');var name=el.tag('originalText').val(),code=el.attr('code'),code_system=el.attr('codeSystem');var specimen_name=null,specimen_code=null,specimen_code_system=null;el=entry.tag('performer').tag('addr');var organization=el.tag('name').val(),phone=el.tag('telecom').attr('value');els=el.elsByTag('streetAddressLine');street=[];for(var j=0;j<els.length;j++){street.push(els[j].val());}
            var city=el.tag('city').val(),state=el.tag('state').val(),zip=el.tag('postalCode').val(),country=el.tag('country').val();el=entry.tag('participant').tag('code');var device_name=el.attr('displayName'),device_code=el.attr('code'),device_code_system=el.attr('codeSystem');data.push({date:date,name:name,code:code,code_system:code_system,specimen_name:specimen_name,specimen_code:specimen_code,specimen_code_system:specimen_code_system,organization:organization,phone:phone,street:street,city:city,state:state,zip:zip,country:country,device_name:device_name,device_code:device_code,device_code_system:device_code_system});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var Vitals=function(){var parseDate=Core.parseDate;var process=function(source,type){var entries,data=[];switch(type){case'ccda':entries=processCCDA(source);break;case'va_c32':entries=processVAC32(source);break;case'json':return processJSON(source);break;}
            for(var i=0;i<entries.length;i++){var e=entries[i];var entry={date:e.date}
                var results=[];for(var j=0;j<e.results.length;j++){var r=e.results[j];results.push({name:r.name,code:r.code,code_system:r.code_system,code_system_name:r.code_system_name,value:r.value,unit:r.unit});}
                entry.results=results;data.push(entry);}
            return data;};var processCCDA=function(xmlDOM){var data=[],results_data=[],el,entries,entry,results,result;el=xmlDOM.template('2.16.840.1.113883.10.20.22.2.4.1');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var entry_date=parseDate(el.attr('value'));results=entry.elsByTag('component');for(var j=0;j<results.length;j++){result=results[j];el=result.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=result.tag('value');var value=parseInt(el.attr('value')),unit=el.attr('unit');results_data.push({name:name,code:code,code_system:code_system,code_system_name:code_system_name,value:value,unit:unit});}
            data.push({date:entry_date,results:results_data});}
            return data;};var processVAC32=function(xmlDOM){var data=[],results_data=[],el,entries,entry,results,result;el=xmlDOM.template('2.16.840.1.113883.10.20.1.16');entries=el.elsByTag('entry');for(var i=0;i<entries.length;i++){entry=entries[i];el=entry.tag('effectiveTime');var date=parseDate(el.attr('value'));results=entry.elsByTag('component');for(var j=0;j<results.length;j++){result=results[j];el=result.tag('code');var name=el.attr('displayName'),code=el.attr('code'),code_system=el.attr('codeSystem'),code_system_name=el.attr('codeSystemName');el=result.tag('value');var value=parseInt(el.attr('value')),unit=el.attr('unit');results_data.push({name:name,code:code,code_system:code_system,code_system_name:code_system_name,value:value,unit:unit});}
            data.push({date:date,results:results_data});}
            return data;};var processJSON=function(json){return{};};return{process:process};}();var BlueButton=function(source){var xmlDOM=null,type='',data={};var addMethods=function(objects){for(var i=0;i<objects.length;i++){objects[i].json=function(){return JSON.stringify(this,null,2)};};};var doc=function(){return data.document};var allergies=function(){return data.allergies};var demographics=function(){return data.demographics};var encounters=function(){return data.encounters};var immunizations=function(){return data.immunizations};var labs=function(){return data.labs};var medications=function(){return data.medications};var problems=function(){return data.problems};var procedures=function(){return data.procedures};var vitals=function(){return data.vitals};source=source.replace(/^\s+|\s+$/g,'');if(source.substr(0,5)=="<?xml"){xmlDOM=Core.parseXML(source);if(xmlDOM.template('1.3.6.1.4.1.19376.1.5.3.1.1.1').isEmpty()){type='ccda';}else{type='va_c32';}
data.document={type:type};data.allergies=Allergies.process(xmlDOM,type);data.demographics=Demographics.process(xmlDOM,type);data.encounters=Encounters.process(xmlDOM,type);data.immunizations=Immunizations.process(xmlDOM,type);data.labs=Labs.process(xmlDOM,type);data.medications=Medications.process(xmlDOM,type);data.problems=Problems.process(xmlDOM,type);data.procedures=Procedures.process(xmlDOM,type);data.vitals=Vitals.process(xmlDOM,type);addMethods([data,data.document,data.allergies,data.demographics,data.encounters,data.immunizations,data.labs,data.medications,data.problems,data.procedures,data.vitals]);}else{type='json';try{var json=JSON.parse(source);}catch(e){console.log("BB Exception: Could not parse JSON");}
data.document={type:type};data.allergies=Allergies.process(json,type);data.demographics=Demographics.process(json,type);data.encounters=Encounters.process(json,type);data.immunizations=Immunizations.process(json,type);data.labs=Labs.process(json,type);data.medications=Medications.process(json,type);data.problems=Problems.process(json,type);data.procedures=Procedures.process(json,type);data.vitals=Vitals.process(json,type);}
return{data:data,xmlDOM:xmlDOM,document:doc,allergies:allergies,demographics:demographics,encounters:encounters,immunizations:immunizations,labs:labs,medications:medications,problems:problems,procedures:procedures,vitals:vitals};};window.BlueButton=BlueButton;        /* js/05-bbclear.js */ function isInt(input){return parseInt(input,10)%1===0;}
var filters={isolanguage:function(input){if(input.length>=2){code=input.substr(0,2);return isoLangs[code];}else{return input;}},since_days:function(input,days){batch=[];today=new Date();target_date=new Date(today.setDate(today.getDate()-days));for(var k in input){if(isInt(k)){if(input[k].effective_time&&input[k].effective_time.low&&input[k].effective_time.low>target_date){batch.push(input[k]);}else if(input[k].date&&input[k].date>target_date){batch.push(input[k]);}}}
return batch;},strict_length:function(input){return input.length;},fallback:function(input,output){return input?input:output;},age:function(date){today=new Date();ms=today-date;years=ms/(1000*60*60*24*365);return Math.floor(years);},related_by_date:function(input,kind){var date,batch;var list=[];if(kind=='encounters'){batch=bb.encounters();}else if(kind=='procedures'){batch=bb.procedures();}else if(kind=='problems'){batch=bb.problems();}else if(kind=='immunizations'){batch=bb.immunizations();}else if(kind=='medications'){batch=bb.medications();return[];}else if(kind=='labs'){batch=[];for(var m in bb.labs()){for(var l in bb.labs()[m].results){batch.push(bb.labs()[m].results[l]);}}}
if(input.date){if(input.date instanceof Date){dates=[input.date.toDateString()];}else{dates=[input.date_range.start.toDateString(),input.date_range.end.toDateString()];}
for(var k in batch){if(typeof k=="number"){target=batch[k];if(target.date instanceof Date){target_date=[target.date.toDateString()];}else{target_dates=[target.date_range.start.toDateString,target.date_range.end.toDateString()];}
if(filters.intersects(dates,target_dates).length>0){list.push(target);}}}}
return list;},intersects:function(input,comparand){return input.filter(function(n){if($.inArray(n,comparand)==-1){return false;}
return true;});},group:function(list,key){var keys=key.split(".");var val,keyList=[],groupedList=[];for(i=0;i<list.length;i++){val=list[i];for(var k in keys){val=val[keys[k]];}
if($.inArray(val,keyList)<0){keyList.push(val);}}
for(var j in keyList){var item={};item={grouper:keyList[j],list:[]};for(var h=0;h<list.length;h++){val=list[h];for(var m in keys){val=val[keys[m]];}
if(val==item.grouper){item.list.push(list[h]);}}
groupedList.push(item);}
return groupedList;},slice:function(input,start,end){return end?input.slice(start,end):input.slice(start);},format_phone:function(input){if(input.match(/(^\+)/g)){return input;}else{numbers=input.replace(/\D/g,'');numbers=numbers.replace(/(^1)/g,'');number=[numbers.substr(0,3),numbers.substr(3,3),numbers.substr(6,4)];return number.join('.');}},format_unit:function(input){if(input){if(input.match(/10\+\d\//g)){base=input.split('/')[0].split('+')[0];exp=input.split('/')[0].split('+')[1];unit=input.split('/')[1];str=base+"<sup>"+exp+"</sup>/"+unit;return str;}else if(input=='1'||input==1){return"";}else{return input;}}
return input;},full_name:function(input){if(typeof input.given=='undefined'){return"John Doe";}
if(input.given===null){if(input.family===null){return"Unknown";}else{return input.family;}}
var name,first_given,other_given,names=input.given.slice(0);if(names instanceof Array){first_given=names.splice(0,1);other_given=names.join(" ");}else{first_given=names;}
name=first_given;name=input.call_me?name+" \""+input.call_me+"\"":name;name=(other_given)?name+" "+other_given:name;name=name+" "+input.family;return name;},display_name:function(input){if(input.given instanceof Array){return input.call_me?input.call_me:input.given[0];}else{return input.call_me?input.call_me:input.given;}},gender_pronoun:function(input,possessive,absolute){if(input=="female"){return possessive?(absolute?"hers":"her"):"she";}else{return possessive?"his":"he";}},max_severity:function(input){var i,mild=0,moderate=0,severe=0,exists=0;if(input.severity){if(input.severity.match(/severe/i)){severe++;}else if(input.severity.match(/moderate/i)){moderate++;}else if(input.severity.match(/mild/i)){mild++;}else{exists++;}}else{for(i in input){if(isInt(i)){if(input[i].severity){if(input[i].severity.match(/severe/i)){severe++;}else if(input[i].severity.match(/moderate/i)){moderate++;}else if(input[i].severity.match(/mild/i)){mild++;}else{exists++;}}else{exists++;}}}}
if(severe){return severe>1?"multiple severe":"severe";}else if(moderate){return moderate>1?"multiple moderate":"moderate";}else if(mild){return mild>1?"multiple mild":"mild";}else{return exists===0?"no":exists>1?"multiple":"";}}};function init_template(){swig.init({allowErrors:true,autoescape:true,cache:true,encoding:'utf8',filters:filters,tags:{},extensions:{},tzOffset:0});template=swig.compile($(".bb-template").html());renderedHtml=template({bb:bb,demographics:bb.demographics(),allergies:bb.allergies(),encounters:bb.encounters(),immunizations:bb.immunizations(),labs:bb.labs(),medications:bb.medications(),problems:bb.problems(),procedures:bb.procedures(),vitals:bb.vitals()});$(".bb-template").html(renderedHtml);$("#loader").fadeOut(function(){$(".bb-template").fadeIn();});}
function scrollToElement(element){$('html,body').animate({scrollTop:element.offset().top},'slow');}
$(function(){$("#loader").fadeIn(function(){text=$.text($("script#xmlBBData"));bb=BlueButton(text);init_template();});$(document).on('click','nav a',function(){destination=$(this).attr('href');scrollToElement($(destination));return false;});});isoLangs={"ab":"Abkhaz","aa":"Afar","af":"Afrikaans","ak":"Akan","sq":"Albanian","am":"Amharic","ar":"Arabic","an":"Aragonese","hy":"Armenian","as":"Assamese","av":"Avaric","ae":"Avestan","ay":"Aymara","az":"Azerbaijani","bm":"Bambara","ba":"Bashkir","eu":"Basque","be":"Belarusian","bn":"Bengali","bh":"Bihari","bi":"Bislama","bs":"Bosnian","br":"Breton","bg":"Bulgarian","my":"Burmese","ca":"Catalan","ch":"Chamorro","ce":"Chechen","ny":"Chichewa","zh":"Chinese","cv":"Chuvash","kw":"Cornish","co":"Corsican","cr":"Cree","hr":"Croatian","cs":"Czech","da":"Danish","dv":"Divehi","nl":"Dutch","en":"English","eo":"Esperanto","et":"Estonian","ee":"Ewe","fo":"Faroese","fj":"Fijian","fi":"Finnish","fr":"French","ff":"Fula","gl":"Galician","ka":"Georgian","de":"German","el":"Greek, Modern","gn":"Guarani","gu":"Gujarati","ht":"Haitian","ha":"Hausa","he":"Hebrew (modern)","hz":"Herero","hi":"Hindi","ho":"Hiri Motu","hu":"Hungarian","ia":"Interlingua","id":"Indonesian","ie":"Interlingue","ga":"Irish","ig":"Igbo","ik":"Inupiaq","io":"Ido","is":"Icelandic","it":"Italian","iu":"Inuktitut","ja":"Japanese","jv":"Javanese","kl":"Greenlandic","kn":"Kannada","kr":"Kanuri","ks":"Kashmiri","kk":"Kazakh","km":"Khmer","ki":"Kikuyu","rw":"Kinyarwanda","ky":"Kirghiz","kv":"Komi","kg":"Kongo","ko":"Korean","ku":"Kurdish","kj":"Kwanyama","la":"Latin","lb":"Luxembourgish","lg":"Luganda","li":"Limburgish","ln":"Lingala","lo":"Lao","lt":"Lithuanian","lu":"Luba-Katanga","lv":"Latvian","gv":"Manx","mk":"Macedonian","mg":"Malagasy","ms":"Malay","ml":"Malayalam","mt":"Maltese","mi":"Maori","mr":"Marathi","mh":"Marshallese","mn":"Mongolian","na":"Nauru","nv":"Navajo","nb":"Norwegian Bokmal","nd":"North Ndebele","ne":"Nepali","ng":"Ndonga","nn":"Norwegian Nynorsk","no":"Norwegian","ii":"Nuosu","nr":"South Ndebele","oc":"Occitan","oj":"Ojibwe","cu":"Old Church Slavonic","om":"Oromo","or":"Oriya","os":"Ossetian","pa":"Panjabi","pi":"Pali","fa":"Persian","pl":"Polish","ps":"Pashto","pt":"Portuguese","qu":"Quechua","rm":"Romansh","rn":"Kirundi","ro":"Romanian","ru":"Russian","sa":"Sanskrit","sc":"Sardinian","sd":"Sindhi","se":"Northern Sami","sm":"Samoan","sg":"Sango","sr":"Serbian","gd":"Gaelic","sn":"Shona","si":"Sinhalese","sk":"Slovak","sl":"Slovene","so":"Somali","st":"Southern Sotho","es":"Spanish","su":"Sundanese","sw":"Swahili","ss":"Swati","sv":"Swedish","ta":"Tamil","te":"Telugu","tg":"Tajik","th":"Thai","ti":"Tigrinya","bo":"Tibetan,","tk":"Turkmen","tl":"Tagalog","tn":"Tswana","to":"Tonga","tr":"Turkish","ts":"Tsonga","tt":"Tatar","tw":"Twi","ty":"Tahitian","ug":"Uighur","uk":"Ukrainian","ur":"Urdu","uz":"Uzbek","ve":"Venda","vi":"Vietnamese","vo":"Volapuk","wa":"Walloon","cy":"Welsh","wo":"Wolof","fy":"Western Frisian","xh":"Xhosa","yi":"Yiddish","yo":"Yoruba","za":"Zhuang"};
        </script>
    </head>
    <body>
        <section class="bb-template">
            <nav id="primaryNav">
                <div class="container">
                    <h1>Blue Button Health Record</h1>
                    <ul>
                        <li><a href="#demographics">Profile</a></li>
                        <li><a href="#allergies">Allergies</a></li>
                        <li><a href="#medications">Medications</a></li>
                        <li><a href="#immunizations">Immunizations</a></li>
                        <li><a href="#history">History</a></li>
                        <li><a href="#labs">Labs</a></li>
                    </ul>
                </div>
            </nav>
            <div id="demographics" class="panel">
                <h1>{{demographics.name|full_name}}</h1>
                <p class="narrative">
                    <span class="general">
                        <strong>{{demographics.name|display_name}}</strong> is a {% if demographics.dob %}<strong>{{demographics.dob|age}}</strong> year old{% endif %}
                        <strong>{% if demographics.race %}{{demographics.race}} {% endif %}{% if demographics.marital_status %}{{demographics.marital_status|lower}} {% endif %}{{demographics.gender|lower}}</strong>
                        {% if demographics.religion or demographics.language %}who {% if demographics.religion %}is <strong>{{demographics.religion}}</strong>{% if demographics.language %} and {% endif %}{% endif %}{% if demographics.language %}speaks <strong>{{demographics.language|isolanguage|title}}</strong>{% endif %}{% endif %}.
                    </span>
                    <span class="allergies">
                        {{demographics.gender|gender_pronoun|title}} has <strong class="{{allergies|max_severity}}">{{allergies|max_severity}} allergies</strong>.
                    </span>
                    <span class="yearReview">
                        In the past year, {{demographics.gender|gender_pronoun}}
                        <span id="yearReviewEncounters">
                            {% if encounters|since_days(365)|strict_length == 0 %}
                                did not have medical encounters
                            {% else %}
                                had <strong>medical encounters</strong>
                            {% endif %}
                        </span> and has <span id="yearReviewMedications">
                            {% if medications|since_days(365)|strict_length == 0 %}
                                not had any medications prescribed.
                            {% else %}
                                been <strong>prescribed medications</strong>.
                            {% endif %}
                        </span>
                    </span>
                </p>
                <dl id="demographicsExtras">
                    <li>
                        <dt>Birthday</dt>
                        <dd>{{demographics.dob|date("F j, Y")}}</dd>
                    </li>
                    <li>
                        <dt>Address</dt>
                        {% if demographics.address.street|length == 2 %}
                            {% for line in demographics.address.street %}
                            <dd>{{line}}</dd>
                            {% endfor %}
                        {% else %}
                        <dd>{{demographics.address.street}}</dd>
                        {% endif %}
                        <dd>{{demographics.address.city}}, {{demographics.address.state}} {{demographics.address.zip}}</dd>
                    </li>
                    <li>
                        <dt>Telephone</dt>
                        {% for number in demographics.phone %}
                            {% if number %}<dd class="phone-{{loop.key}}">{{loop.key|slice(0,1)}}: <a href="{{number}}">{{number|format_phone}}</a></dd>{% endif %}
                        {% else %}
                            <dd>No known number</dd>
                        {% endfor %}
                    </li>
                    {% if demographics.guardian and demographics.guardian.name.family %}<li>
                        <dt>{{demographics.guardian.relationship|fallback("Guardian")}}</dt>
                        <dd>{{demographics.guardian.name|full_name}}</dd>
                        {% for number in demographics.guardian.phone %}
                            {% if number %}<dd class="phone-{{loop.key}}">{{loop.key|slice(0,1)}}: <a href="{{number}}">{{number|format_phone}}</a></dd>{% endif %}
                        {% else %}
                            <dd>No known number</dd>
                        {% endfor %}
                    </li>{% endif %}
                </dl>
            </div>
            <div id="allergies" class="panel">
                <h1>Allergies</h1>
                {% for allergy in allergies %}
                    {% if loop.first %}<ul>{% endif %}
                    <li class="allergy-{{allergy|max_severity}}">
                        <h2>{{allergy.allergen.name}}</h2>
                        {% if allergy.severity %}<p>{{allergy.severity}}</p>{% endif %}
                        {% if allergy.reaction.name %}<p>Causes {{allergy.reaction.name|lower}}</p>{% endif %}
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known allergies</p>
                {% endfor %}
            </div>
            <div id="medications" class="panel">
                <h1>Medication History</h1>
                {% for med in medications %}
                    {% if loop.first %}<ul>{% endif %}
                    <li class="{{loop.cycle('odd', 'even')}}">
                        <header>
                            <h2>{{med.product.name}}</h2>
                            {% if med.administration.name %}<small>{{med.administration.name|title}}</small>{% endif %}
                            {% if med.reason.name %}<small>for {{med.reason.name}}</small>{% endif %}
                        </header>

                        <dl class="footer">
                            {% if med.prescriber.organization or med.prescriber.person %}<li>
                                <dt>Prescriber</dt>
                                {% if med.prescriber.organization %}<dd>{{med.prescriber.organization}}</dd>{% endif %}
                                {% if med.prescriber.person %}<dd>{{med.prescriber.person}}</dd>{% endif %}
                            </li>{% endif %}
                            {% if med.date_range.start or med.date_range.end %}<li>
                                <dt>Date</dt>
                                <dd>
                                    {% if med.date_range.start %}{{med.date_range.start|date('M j, Y')}}{% endif %}
                                    {% if med.date_range.end %}&ndash; {{med.date_range.end|date('M j, Y')}}{% endif %}
                                </dd>
                            </li>{% endif %}
                        </dl>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known medications</p>
                {% endfor %}
            </div>
            <div id="immunizations" class="panel">
                <h1>Immunizations</h1>
                {% for group in immunizations|group('product.name') %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>{{group.grouper}}</h2>
                        {% for item in group.list %}
                            {% if loop.first %}<ul class="pills">{% endif %}
                            <li>{{item.date|date('M j, Y')}}</li>
                            {% if loop.last %}</ul>{% endif %}
                        {% endfor %}
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known immunizations</p>
                {% endfor %}
            </div>
            <div id="history" class="panel">
                <h1>Medical History</h1>
                {% for encounter in encounters %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>{{encounter.date|date('M j, Y')}}</h2>
                        <dl>
                            <li>
                                <dt>Encounter</dt>
                                <dd class="head">{{encounter.name|fallback("Unknown Visit")|title}}</dd>
                                {% if encounter.finding.name %}<dd>Finding: {{encounter.finding.name}}</dd>{% endif %}
                            </li>
                            {% for problem in encounter|related_by_date('problems') %}
                                <li>
                                    <dt>Problem</dt>
                                    <dd class="head">{{problem.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for procedure in encounter|related_by_date('procedures') %}
                                <li>
                                    <dt>Procedure</dt>
                                    <dd class="head">{{procedure.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for medication in encounter|related_by_date('medications') %}
                                <li>
                                    <dt>Medication</dt>
                                    <dd class="head">{{medication.product.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for immunization in encounter|related_by_date('immunizations') %}
                                <li>
                                    <dt>Immunization</dt>
                                    <dd class='head'>{{immunization.product.name}}</dd>
                                </li>
                            {% endfor %}
                        </dl>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known past encounters</p>
                {% endfor %}
            </div>
            <div id="labs" class="panel">
                <h1>Lab Results</h1>
                {% for panel in labs %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>
                            <span class="date">{{panel.results[0].date|date('M j, Y')}}</span>
                            {{panel.name|fallback("Laboratory Panel")}}
                        </h2>
                        <ul class="results">
                            <li class="header">
                                <span class="lab-component">Component</span>
                                <span class="lab-value">Value</span>
                                <span class="lab-low">Low</span>
                                <span class="lab-high">High</span>
                            </li>
                            {% for result in panel.results %}
                                <li>
                                    <span class="lab-component">{{result.name}}</span>
                                    <span class="lab-value">{{result.value|fallback("Unknown")}}{% if result.unit %} {{result.unit|format_unit|raw}}{% endif %}</span>
                                    <span class="lab-low">{% if result.reference.low %}{{result.reference.low}}{% endif %}</span>
                                    <span class="lab-high">{% if result.reference.high %}{{result.reference.high}}{% endif %}</span>
                                </li>
                            {% endfor %}
                        </ul>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% endfor %}
            </div>
        </section>
        <div id="loader">
            <div id="warningGradientOuterBarG">
                <div id="warningGradientFrontBarG" class="warningGradientAnimationG">
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                </div>
            </div>
            <p>Reticulating splines...</p>
        </div>
    </body>
</html>
<script style="display: none;" id="xmlBBData" type="text/plain">
<?xml version="1.0"?>
            <?xml-stylesheet type="text/xsl" href="CDA.xsl"?>
                <!--
  Title: US_Realm_Header_Template

  Revision History:
   01/31/2011 bam created
    07/29/2011 RWM modified
    11/26/2011 RWM modified
    08/12/2012 RWM modified
    09/12/2012 BNR(Dragon) modified

 -->
            <ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="urn:hl7-org:v3 ../../../CDA%20R2/cda-schemas-and-samples/infrastructure/cda/CDA.xsd"
            xmlns="urn:hl7-org:v3"
            xmlns:cda="urn:hl7-org:v3"
            xmlns:sdtc="urn:hl7-org:sdtc">

                    <!--
********************************************************
  CDA Header
********************************************************
  -->
                    <!-- CONF 16791 -->
                <realmCode
            code="US"/>

                    <!-- CONF 5361 -->
                <typeId
            root="2.16.840.1.113883.1.3"
            extension="POCD_HD000040"/>

                    <!-- US General Header Template -->
                    <!-- CONF 5252 -->
                <templateId
            root="2.16.840.1.113883.10.20.22.1.1"/>
                    <!-- *** Note:  The next templateId, code and title will differ depending on what type of document is being sent. *** -->
                    <!-- conforms to the document specific requirements  -->
                <templateId
            root="2.16.840.1.113883.10.20.22.1.2"/>

                    <!-- CONF 5363 -->
                <id
            extension="Test CCDA"
            root="1.1.1.1.1.1.1.1.1"/>

                    <!-- CONF 5253 "CCD document" -->
                <code
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            code="34133-9"
            displayName="Summarization of Episode Note"/>

                    <!-- CONF 5254 -->
                <title>Community Health and Hospitals: Health Summary</title>

                <!-- CONF 5256 -->
            <effectiveTime
            value="20120912000000-0000"/>

                    <!-- 5259 -->
                <confidentialityCode
            code="N"
            codeSystem="2.16.840.1.113883.5.25"/>

                    <!-- 5372 -->
                <languageCode
            code="en-US"/>

                    <!-- CONF 5266 -->
                <recordTarget>

                    <!-- CONF 5267 -->
                <patientRole>

                    <!-- CONF 5268-->
                <id
            extension="1"
            root="2.16.840.1.113883.4.6"/>
                    <!-- Fake ID using HL7 example OID. -->

                    <!-- Patient SSN recorded as an ID -->
                <id
            extension="123-101-5230"
            root="2.16.840.1.113883.4.1"/>

                    <!-- CONF 5271 -->
                <addr
            use="HP">
                    <!-- HP is "primary home" from codeSystem 2.16.840.1.113883.5.1119 -->
                <streetAddressLine>1357 Amber Drive</streetAddressLine>
            <city>Beaverton</city>
            <state>OR</state>
            <postalCode>97006</postalCode>
            <country>US</country>
                <!-- US is "United States" from ISO 3166-1 Country Codes: 1.0.3166.1 -->
            </addr>

                <!-- CONF 5280 -->
            <telecom
            value="tel:(816)276-6909"
            use="HP"/>
                    <!-- HP is "primary home" from HL7 AddressUse 2.16.840.1.113883.5.1119 -->

                    <!-- CONF 5283 -->
                <patient>

                    <!-- CONF 5284 -->
                <name
            use="L">
                    <!-- L is "Legal" from HL7 EntityNameUse 2.16.840.1.113883.5.45 -->
                <given>Myra</given>
                    <!-- CL is "Call me" from HL7 EntityNamePartQualifier 2.16.840.1.113883.5.43 -->
                <family>Jones</family>
                </name>
                <administrativeGenderCode
            code="F"
            codeSystem="2.16.840.1.113883.5.1"
            displayName="Female"/>
                <birthTime
            value="19470501"/>

                <maritalStatusCode
            code="M"
            displayName="Married"
            codeSystem="2.16.840.1.113883.5.2"
            codeSystemName="MaritalStatusCode"/>
                <religiousAffiliationCode
            code="1013"
            displayName="Christian (non-Catholic, non-specific)"
            codeSystemName="HL7 Religious Affiliation "
            codeSystem="2.16.840.1.113883.5.1076"/>

                    <!-- Need to Fix the Race Code to be from the OMB Standards -->
                <raceCode
            code="2106-3"
            displayName="White"
            codeSystem="2.16.840.1.113883.6.238"
            codeSystemName="Race and Ethnicity - CDC"/>
                <ethnicGroupCode
            code="2186-5"
            displayName="Not Hispanic or Latino"
            codeSystem="2.16.840.1.113883.6.238"
            codeSystemName="Race and Ethnicity - CDC"/>

                <guardian>
                <code
            code="GPARNT"
            displayName="Grandfather"
            codeSystem="2.16.840.1.113883.5.111"
            codeSystemName="HL7 Role code"/>
                <addr
            use="HP">
                    <!-- HP is "primary home" from codeSystem 2.16.840.1.113883.5.1119 -->
                <streetAddressLine>1357 Amber Drive</streetAddressLine>
            <city>Beaverton</city>
            <state>OR</state>
            <postalCode>97006</postalCode>
            <country>US</country>
                <!-- US is "United States" from ISO 3166-1 Country Codes: 1.0.3166.1 -->
            </addr>
            <telecom
            value="tel:(816)276-6909"
            use="HP"/>
                <guardianPerson>
                <name>
                <given>Ralph</given>
                <family>Jones</family>
                </name>
                </guardianPerson>
                </guardian>
                <birthplace>
                <place>
                <addr>
                <city>Beaverton</city>
                <state>OR</state>
                <postalCode>97006</postalCode>
                <country>US</country>
                </addr>
                </place>
                </birthplace>

                    <!-- FIX  the Code System to be 639.2 -->
                <languageCommunication>
                <languageCode
            code="eng"/>
                <modeCode
            code="ESP"
            displayName="Expressed spoken"
            codeSystem="2.16.840.1.113883.5.60"
            codeSystemName="LanguageAbilityMode"/>
                <preferenceInd
            value="true"/>
                </languageCommunication>
                </patient>
                <providerOrganization>
                <id
            root="1.1.1.1.1.1.1.1.4"/>
                <name>Community Health and Hospitals</name>
            <telecom
            use="WP"
            value="tel: 555-555-5000"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </providerOrganization>
            </patientRole>
            </recordTarget>

            <author>
            <time
            value="20050813000000+0500"/>
                <assignedAuthor>
                <id
            extension="111111"
            root="2.16.840.1.113883.4.6"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="tel:555-555-1002"/>
                <assignedPerson>
                <name>
                <prefix>Dr</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedAuthor>
                </author>
                <dataEnterer>
                <assignedEntity>
                <id
            root="2.16.840.1.113883.4.6"
            extension="999999943252"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="tel:555-555-1002"/>
                <assignedPerson>
                <name>
                <given>Mary</given>
                <family>McDonald</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </dataEnterer>
                <informant>
                <assignedEntity>
                <id
            extension="KP00017"
            root="2.16.840.1.113883.19.5"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="tel:555-555-1002"/>
                <assignedPerson>
                <name>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </informant>
                <informant>
                <relatedEntity
            classCode="PRS">
                    <!-- classCode PRS represents a person with personal relationship with the patient. -->
                <code
            code="SPS"
            displayName="SPOUSE"
            codeSystem="2.16.840.1.113883.1.11.19563"
            codeSystemName="Personal Relationship Role Type Value Set"/>
                <relatedPerson>
                <name>
                <given>Frank</given>
                <family>Jones</family>
                </name>
                </relatedPerson>
                </relatedEntity>
                </informant>
                <custodian>
                <assignedCustodian>
                <representedCustodianOrganization>
                <id
            extension="99999999"
            root="2.16.840.1.113883.4.6"/>
                <name>Community Health and Hospitals</name>
            <telecom
            value="tel: 555-555-1002"
            use="WP"/>
                <addr
            use="WP">
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </representedCustodianOrganization>
            </assignedCustodian>
            </custodian>
            <informationRecipient>
            <intendedRecipient>
            <informationRecipient>
            <name>
            <given>Henry</given>
            <family>Seven</family>
            </name>
            </informationRecipient>
            <receivedOrganization>
            <name>Community Health and Hospitals</name>
            </receivedOrganization>
            </intendedRecipient>
            </informationRecipient>
            <legalAuthenticator>
            <time
            value="20120813"/>
                <signatureCode
            code="S"/>
                <assignedEntity>
                <id
            extension="999999999"
            root="2.16.840.1.113883.4.6"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="tel:555-555-1002"/>
                <assignedPerson>
                <name>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </legalAuthenticator>
                <authenticator>
                <time
            value="20120813"/>
                <signatureCode
            code="S"/>
                <assignedEntity>
                <id
            extension="999999999"
            root="2.16.840.1.113883.4.6"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="tel:555-555-1002"/>
                <assignedPerson>
                <name>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </authenticator>
                <participant typeCode="IND">
                <associatedEntity classCode="PRS">
                <code
            code="GPARNT"
            displayName="Grand Parent"
            codeSystem="2.16.840.1.113883.1.11.19563"
            codeSystemName="Personal Relationship Role Type Value Set"/>
                <addr
            use="HP">
                    <!-- HP is "primary home" from codeSystem 2.16.840.1.113883.5.1119 -->
                <streetAddressLine>1357 Amber Drive</streetAddressLine>
            <city>Beaverton</city>
            <state>OR</state>
            <postalCode>97006</postalCode>
            <country>US</country>
                <!-- US is "United States" from ISO 3166-1 Country Codes: 1.0.3166.1 -->
            </addr>
            <telecom value='tel:(555)555-2006' use='WP'/>
                <associatedPerson>
                <name>
                <prefix>Mr.</prefix>
                <given>Ralph</given>
                <family>Jones</family>
                </name>
                </associatedPerson>
                </associatedEntity>
                </participant>
                <participant typeCode="IND">
                <associatedEntity classCode="PRS">
                <code
            code="SPS"
            displayName="SPOUSE"
            codeSystem="2.16.840.1.113883.1.11.19563"
            codeSystemName="Personal Relationship Role Type Value Set"/>
                <addr
            use="HP">
                    <!-- HP is "primary home" from codeSystem 2.16.840.1.113883.5.1119 -->
                <streetAddressLine>1357 Amber Drive</streetAddressLine>
            <city>Beaverton</city>
            <state>OR</state>
            <postalCode>97006</postalCode>
            <country>US</country>
                <!-- US is "United States" from ISO 3166-1 Country Codes: 1.0.3166.1 -->
            </addr>
            <telecom value='tel:(555)555-2006' use='WP'/>
                <associatedPerson>
                <name>
                <prefix>Mr.</prefix>
                <given>Frank</given>
                <family>Jones</family>
                </name>
                </associatedPerson>
                </associatedEntity>
                </participant>
                <documentationOf
            typeCode="DOC">
                <serviceEvent
            classCode="PCPR">
                <code
            code="233604007"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED-CT"
            displayName="Pnuemonia"/>
                <effectiveTime>
                <low
            value="20120806"/>
                <high
            value="20120813"/>

                </effectiveTime>
                <performer
            typeCode="PRF">
                <functionCode
            code="PP"
            displayName="Primary Care Provider"
            codeSystem="2.16.840.1.113883.12.443"
            codeSystemName="Provider Role">
                <originalText>Primary Care Provider</originalText>
            </functionCode>
            <time>
            <low
            value="20120806"/>
                <high
            value="20120813"/>
                </time>
                <assignedEntity>
                <id
            extension="PseudoMD-1"
            root="2.16.840.1.113883.4.6"/>
                <code
            code="208D00000X"
            displayName="General Practice"
            codeSystemName="Provider Codes"
            codeSystem="2.16.840.1.113883.6.101"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            value="tel:+1-555-555-5000"
            use="WP"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1393"/>
                <name>Community Health and Hospitals</name>
            <telecom
            value="tel:+1-555-555-5000"
            use="WP"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </representedOrganization>
            </assignedEntity>
            </performer>
            <performer
            typeCode="PRF">
                <functionCode
            code="PP"
            displayName="Primary Care Provider"
            codeSystem="2.16.840.1.113883.12.443"
            codeSystemName="Provider Role">
                <originalText>Primary Care Provider</originalText>
            </functionCode>
            <time>
            <low
            value="20120806"/>
                <high
            value="20120813"/>
                </time>
                <assignedEntity>
                <id
            extension="PseudoMD-3"
            root="2.16.840.1.113883.4.6"/>
                <code
            code="208D00000X"
            displayName="General Practice"
            codeSystemName="Provider Codes"
            codeSystem="2.16.840.1.113883.6.101"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            value="tel:+1-555-555-5000"
            use="HP"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1393"/>
                <name>Community Health and Hospitals</name>
            <telecom
            value="tel:+1-555-555-5000"
            use="HP"/>
                <addr>
                <streetAddressLine>1002 Healthcare Drive </streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </representedOrganization>
            </assignedEntity>
            </performer>
            </serviceEvent>
            </documentationOf>

            <componentOf>
            <encompassingEncounter>
            <id extension="1" root="2.16.840.1.113883.4.6"/>
                <code
            code="233604007"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED-CT"
            displayName="Pnuemonia"/>
                <effectiveTime>
                <low
            value="20120806"/>
                <high
            value="20120813"/>
                </effectiveTime>
                <responsibleParty>
                <assignedEntity>
                <id root="2.16.840.1.113883.4.6"/>
                <assignedPerson>
                <name>
                <prefix>Dr</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </responsibleParty>
                <encounterParticipant typeCode="ATND">
                <assignedEntity>
                <id root="2.16.840.1.113883.4.6"/>
                <assignedPerson>
                <name>
                <prefix>Dr</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedEntity>
                </encounterParticipant>
                <location>
                <healthCareFacility>
                <id root="2.16.840.1.113883.4.6"/>
                </healthCareFacility>
                </location>
                </encompassingEncounter>
                </componentOf>
                    <!-- ********************************************************
     CDA Body
     ******************************************************** -->
                <component>
                <structuredBody>
                    <!-- *********************** -->

                    <!--
********************************************************
Allergies, Adverse Reactions, Alerts
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.6.1"/>
                    <!-- Alerts section template -->
                <code
            code="48765-2"
            codeSystem="2.16.840.1.113883.6.1"/>
                <title>ALLERGIES, ADVERSE REACTIONS, ALERTS</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Substance</th>
                <th>Reaction</th>
                <th>Severity</th>
                <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td>Penicillin G benzathine</td>
            <td><content
            ID="reaction1">Hives</content></td>
            <td><content
            ID="severity1">Moderate to severe</content></td>
            <td>Inactive</td>
            </tr>
            <tr>
            <td>Codeine</td>
            <td><content
            ID="reaction2">Shortness of Breath</content></td>
            <td><content
            ID="severity2">Moderate</content></td>
            <td>Active</td>
            </tr>
            <tr>
            <td>Aspirin</td>
            <td><content
            ID="reaction3">Hives</content></td>
            <td><content
            ID="severity3">Mild to moderate</content></td>
            <td>Active</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.30"/>
                    <!-- ** Allergy problem act ** -->
                <id
            root="36e3e930-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="48765-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Allergies, adverse reactions, alerts"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20070501">
                <low
            value="20070501"/>
                <high
            value="20120806"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- allergy observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.7"/>
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low nullFlavor="UNK"/>
                <high  value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="419511003"
            displayName="Propensity to adverse reaction to drug"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT">
                <originalText>
                <reference
            value="#reaction1"/>
                </originalText>
                </value>
                <participant
            typeCode="CSM">
                <participantRole
            classCode="MANU">
                <playingEntity
            classCode="MMAT">
                <code
            code="7982"
            displayName="Penicillin G benzathine"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm">
                <originalText>
                <reference
            value="#reaction1"/>
                </originalText>
                </code>
                </playingEntity>
                </participantRole>
                </participant>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.28"/>
                    <!-- Allergy status observation template -->
                <code
            code="33999-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Status"/>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CE"
            code="73425007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Inactive"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="MFST"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.9"/>
                    <!-- Reaction observation template -->
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a64"/>
                <code
            nullFlavor="NA"/>
                <text>
                <reference
            value="#reaction1"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20070501"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="247472004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Hives"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.8"/>
                    <!-- ** Severity observation template ** -->
                <code xsi:type="CE"
            code="SEV"
            displayName="Severity Observation"
            codeSystem="2.16.840.1.113883.5.4"
            codeSystemName="ActCode"/>
                <text>
                <reference
            value="#severity1"/>
                </text>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CD"
            code="371924009"
            displayName="Moderate to severe"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                </observation>
                </entryRelationship>
                </observation>
                </entryRelationship>
                </act>
                </entry>
                <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.30"/>
                    <!-- ** Allergy problem act ** -->
                <id
            root="36e3e930-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="48765-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Allergies, adverse reactions, alerts"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20060501">
                <low
            value="20060501"/>
                <high
            value="20120806"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- allergy observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.7"/>
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20060501"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="419511003"
            displayName="Propensity to adverse reaction to drug"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT">
                <originalText>
                <reference
            value="#reaction2"/>
                </originalText>
                </value>
                <participant
            typeCode="CSM">
                <participantRole
            classCode="MANU">
                <playingEntity
            classCode="MMAT">
                <code
            code="2670"
            displayName="Codeine"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm">
                <originalText>
                <reference
            value="#reaction2"/>
                </originalText>
                </code>
                </playingEntity>
                </participantRole>
                </participant>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.28"/>
                    <!-- Allergy status observation template -->
                <code
            code="33999-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Status"/>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CE"
            code="55561003"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Active"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="MFST"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.9"/>
                    <!-- Reaction observation template -->
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a64"/>
                <code
            nullFlavor="NA"/>
                <text>
                <reference
            value="#reaction2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20060501"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="267036007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Shortness of Breath"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.8"/>
                    <!-- ** Severity observation template ** -->
                <code xsi:type="CE"
            code="SEV"
            displayName="Severity Observation"
            codeSystem="2.16.840.1.113883.5.4"
            codeSystemName="ActCode"/>
                <text>
                <reference
            value="#severity2"/>
                </text>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CD"
            code="6736007"
            displayName="Moderate"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                </observation>
                </entryRelationship>
                </observation>
                </entryRelationship>
                </act>
                </entry>
                <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.30"/>
                    <!-- ** Allergy problem act ** -->
                <id
            root="36e3e930-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="48765-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Allergies, adverse reactions, alerts"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20080501">
                <low
            value="20080501"/>
                <high
            value="20120806"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- allergy observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.7"/>
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a66"/>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20080501"/>
                <high value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="419511003"
            displayName="Propensity to adverse reaction to drug"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT">
                <originalText>
                <reference
            value="#reaction3"/>
                </originalText>
                </value>
                <participant
            typeCode="CSM">
                <participantRole
            classCode="MANU">
                <playingEntity
            classCode="MMAT">
                <code
            code="1191"
            displayName="Aspirin"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm">
                <originalText>
                <reference
            value="#reaction3"/>
                </originalText>
                </code>
                </playingEntity>
                </participantRole>
                </participant>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.28"/>
                    <!-- Allergy status observation template -->
                <code
            code="33999-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Status"/>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CE"
            code="55561003"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Active"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="MFST"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.9"/>
                    <!-- Reaction observation template -->
                <id
            root="4adc1020-7b14-11db-9fe1-0800200c9a64"/>
                <code
            nullFlavor="NA"/>
                <text>
                <reference
            value="#reaction3"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20080501"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="247472004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Hives"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.8"/>
                    <!-- ** Severity observation template ** -->
                <code xsi:type="CE"
            code="SEV"
            displayName="Severity Observation"
            codeSystem="2.16.840.1.113883.5.4"
            codeSystemName="ActCode"/>
                <text>
                <reference
            value="#severity3"/>
                </text>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CD"
            code="371923003"
            displayName="Mild to moderate"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                </observation>
                </entryRelationship>
                </observation>
                </entryRelationship>
                </act>
                </entry>
                </section>
                </component>

                    <!--
********************************************************
ENCOUNTERS
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.22.1"/>
                    <!-- Encounters Section - required entries -->
                <code
            code="46240-8"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="History of encounters"/>
                <title>ENCOUNTERS</title>
                <text>
                <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Encounter</th>
                <th>Performer</th>
                <th>Location</th>
                <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td>
                <content
            ID="Encounter1"/> Pnuemonia</td>
                <td>Dr Henry Seven</td>
            <td>Community Health and Hospitals</td>
            <td>20120806</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <encounter
            classCode="ENC"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.49"/>
                    <!-- Encounter Activities -->
                    <!--  ********  Encounter activity template   ******** -->
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4de8"/>
                <code
            code="99222"
            displayName="InPatient Admission"
            codeSystemName="CPT"
            codeSystem="2.16.840.1.113883.6.12"
            codeSystemVersion="4">
                <originalText>Mild Fever<reference
            value="#Encounter1"/>
                </originalText>
                </code>
                <effectiveTime
            value="20120806"/>
                <performer>
                <assignedEntity>
                <id
            root="2a620155-9d11-439e-92a3-5d9815ff4de8"/>
                <code
            code="59058001"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="General Physician"/>
                </assignedEntity>
                </performer>
                <participant
            typeCode="LOC">
                <participantRole
            classCode="SDLOC">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.32"/>
                    <!-- Service Delivery Location template -->
                <code
            code="1160-1"
            codeSystem="2.16.840.1.113883.6.259"
            codeSystemName="HealthcareServiceLocation"
            displayName="Urgent Care Center"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            nullFlavor="UNK"/>
                <playingEntity
            classCode="PLC">
                <name>Community Health and Hospitals</name>
            </playingEntity>
            </participantRole>
            </participant>
            <entryRelationship
            typeCode="RSON">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.19"/>
                <id
            root="db734647-fc99-424c-a864-7e3cda82e703"
            extension="45665"/>
                <code
            code="404684003"
            displayName="Finding"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="233604007"
            displayName="Pneumonia"
            codeSystem="2.16.840.1.113883.6.96"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ" inversionInd="false">
                <act classCode="ACT" moodCode="EVN">

                    <!--Encounter diagnosis act -->
                <templateId root="2.16.840.1.113883.10.20.22.4.80"/>

                <id root="5a784260-6856-4f38-9638-80c751aff2fb"/>
                <code xsi:type="CE"
            code="29308-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="ENCOUNTER DIAGNOSIS"/>
                <statusCode code="active"/>
                <effectiveTime>
                <low value="20120806"/>
                </effectiveTime>
                <entryRelationship typeCode="SUBJ" inversionInd="false">
                <observation classCode="OBS" moodCode="EVN" negationInd="false">
                <templateId root="2.16.840.1.113883.10.20.22.4.4"/>
                    <!-- Problem Observation -->
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="233604007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Pneumonia"/>
                </observation>
                </entryRelationship>
                </act>
                </entryRelationship>
                </encounter>
                </entry>
                </section>
                </component>

                    <!--
********************************************************
IMMUNIZATIONS
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.2.1"/>
                    <!-- Entries Required -->
                    <!--  ********  Immunizations section template   ******** -->
                <code
            code="11369-6"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="History of immunizations"/>
                <title>IMMUNIZATIONS</title>
                <text><content
            ID="immunSect"/>
                <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Vaccine</th>
                <th>Date</th>
                <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td><content
            ID="immun2"/>Influenza virus vaccine, IM</td>
            <td>May 2012</td>
            <td>Completed</td>
            </tr>
            <tr>
            <td><content
            ID="immun4"/>Tetanus and diphtheria toxoids, IM</td>
            <td>Apr 2012</td>
            <td>Completed</td>
            </tr>
            <tr>
            <td><content
            ID="immun6"/>Influenza virus vaccine, IM</td>
            <td>Aug 2012</td>
            <td>Declined</td>
            </tr>
            </tbody>
            </table>
            </text>

            <entry
            typeCode="DRIV">
                <substanceAdministration
            classCode="SBADM"
            moodCode="EVN"
            negationInd="false">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.52"/>
                    <!--  ********   Immunization activity template    ******** -->
                <id
            root="e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92"/>
                <text>
                <reference
            value="#immun2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS"
            value="20120510"/>
                <routeCode
            code="C28161"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="National Cancer Institute (NCI) Thesaurus"
            displayName="Intramuscular injection"/>
                <doseQuantity
            value="50"
            unit="mcg"/>
                <consumable>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.54"/>
                    <!--  ********   Immunization Medication Information    ******** -->
                <manufacturedMaterial>
                <code
            code="88"
            codeSystem="2.16.840.1.113883.6.59"
            displayName="Influenza virus vaccine"
            codeSystemName="CVX">
                <originalText>Influenza virus vaccine</originalText>
            <translation
            code="111"
            displayName="influenza, live, intranasal"
            codeSystemName="CVX"
            codeSystem="2.16.840.1.113883.6.59"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Health LS - Immuno Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </consumable>
            <entryRelationship
            typeCode="SUBJ" inversionInd="true">
                <act
            classCode="ACT"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.20"/>
                    <!-- ** Instructions Template ** -->
                <code xsi:type="CE"
            code="171044003"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="immunization education"/>
                <text><reference
            value="#immunSect"/>Possible flu-like symptoms for three days.</text>
            <statusCode
            code="completed"/>
                </act>
                </entryRelationship>
                </substanceAdministration>
                </entry>
                <entry
            typeCode="DRIV">
                <substanceAdministration
            classCode="SBADM"
            moodCode="EVN"
            negationInd="false">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.52"/>
                    <!--  ********   Immunization activity template    ******** -->
                <id
            root="e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92"/>
                <text>
                <reference
            value="#immun4"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS"
            value="20120401"/>
                <routeCode
            code="C28161"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="National Cancer Institute (NCI) Thesaurus"
            displayName="Intramuscular injection"/>
                <doseQuantity
            value="50"
            unit="mcg"/>
                <consumable>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.54"/>
                    <!--  ********   Immunization Medication Information    ******** -->
                <manufacturedMaterial>
                <code
            code="103"
            codeSystem="2.16.840.1.113883.6.59"
            displayName="Tetanus and diphtheria toxoids - preservative free"
            codeSystemName="CVX">
                <originalText>Tetanus and diphtheria toxoids - preservative free</originalText>
            <translation
            code="09"
            displayName="Tetanus and diphtheria toxoids - preservative free"
            codeSystemName="CVX"
            codeSystem="2.16.840.1.113883.6.59"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Health LS - Immuno Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </consumable>
            <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <act
            classCode="ACT"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.20"/>
                    <!-- ** Instructions Template ** -->
                <code xsi:type="CE"
            code="171044003"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="immunization education"/>
                <text><reference
            value="#immunSect"/>Possible flu-like symptoms for three days.</text>
            <statusCode
            code="completed"/>
                </act>
                </entryRelationship>
                </substanceAdministration>
                </entry>
                <entry
            typeCode="DRIV">
                <substanceAdministration
            classCode="SBADM"
            moodCode="EVN"
            negationInd="true">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.52"/>
                    <!--  ********   Immunization activity template    ******** -->
                <id
            root="e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92"/>
                <text>
                <reference
            value="#immun6"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS"
            value="20120603"/>
                <routeCode
            code="C28161"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="National Cancer Institute (NCI) Thesaurus"
            displayName="Intramuscular injection"/>
                <doseQuantity
            value="50"
            unit="mcg"/>
                <consumable>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.54"/>
                    <!--  ********   Immunization Medication Information    ******** -->
                <manufacturedMaterial>
                <code
            code="88"
            codeSystem="2.16.840.1.113883.6.59"
            displayName="Influenza virus vaccine"
            codeSystemName="CVX">
                <originalText>Influenza virus vaccine</originalText>
            <translation
            code="111"
            displayName="influenza, live, intranasal"
            codeSystemName="CVX"
            codeSystem="2.16.840.1.113883.6.59"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Health LS - Immuno Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </consumable>
            </substanceAdministration>
            </entry>
            </section>
            </component>


                <!--
********************************************************
MEDICATIONS
********************************************************
-->
            <component>
            <section>
            <templateId
            root="2.16.840.1.113883.10.20.22.2.1.1"/>
                <code
            code="10160-0"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="HISTORY OF MEDICATION USE"/>
                <title>Medications</title>
                <text>
                <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Medication</th>
                <th>Directions</th>
                <th>Start Date</th>
            <th>Status</th>
            <th>Indications</th>
            <th>Fill Instructions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td><content
            ID="Med1">Albuterol 0.09 MG/ACTUAT inhalant solution</content></td>
            <td>0.09 MG/ACTUAT inhalant solution, 2 puffs once</td>
            <td>20120806</td>
            <td>Active</td>
            <td>Pneumonia (233604007 SNOMED CT)</td>
            <td><content ID="FillIns">Generic Substitition Allowed</content></td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <substanceAdministration
            classCode="SBADM"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.16"/>
                    <!-- ** MEDICATION ACTIVITY -->
                <id
            root="cdbd33f0-6cde-11db-9fe1-0800200c9a66"/>
                <text>
                <reference
            value="#Med1"/>0.09 MG/ACTUAT inhalant solution, 2 puffs </text>
            <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS">
                <low
            value="20120806"/>
                <high
            value="20120813"/>
                </effectiveTime>
                <effectiveTime
            xsi:type="PIVL_TS"
            institutionSpecified="true"
            operator="A">
                <period
            value="12"
            unit="h"/>
                </effectiveTime>
                <routeCode
            code="C38216"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="NCI Thesaurus"
            displayName="RESPIRATORY (INHALATION)"/>
                <doseQuantity
            value="0.09"
            unit="mg/actuat"/>
                <rateQuantity
            value="90"
            unit="ml/min"/>
                <administrationUnitCode
            code="C42944"
            displayName="INHALANT"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="NCI Thesaurus"/>
                <consumable>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="573621"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="Albuterol 0.09 MG/ACTUAT inhalant solution">
                <originalText><reference
            value="#Med1"/></originalText>
                <translation
            code="573621"
            displayName="Proventil 0.09 MG/ACTUAT inhalant solution"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </consumable>
            <performer>
            <assignedEntity>
            <id
            nullFlavor="NI"/>
                <addr
            nullFlavor="UNK"/>
                <telecom
            nullFlavor="UNK"/>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1393"/>
                <name>Community Health and Hospitals</name>
            <telecom
            nullFlavor="UNK"/>
                <addr
            nullFlavor="UNK"/>
                </representedOrganization>
                </assignedEntity>
                </performer>
                <participant
            typeCode="CSM">
                <participantRole
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.24"/>
                <code
            code="412307009"
            displayName="drug vehicle"
            codeSystem="2.16.840.1.113883.6.96"/>
                <playingEntity
            classCode="MMAT">
                <code
            code="324049"
            displayName="Aerosol"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                <name>Aerosol</name>
                </playingEntity>
                </participantRole>
                </participant>
                <entryRelationship
            typeCode="RSON">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.19"/>
                <id
            root="db734647-fc99-424c-a864-7e3cda82e703"
            extension="45665"/>
                <code
            code="404684003"
            displayName="Finding"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low nullFlavor="UNK" />
                <high value="20120813"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="233604007"
            displayName="Pneumonia"
            codeSystem="2.16.840.1.113883.6.96"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <supply
            classCode="SPLY"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.17"/>
                <id
            nullFlavor="NI"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS">
                <low
            value="20120806"/>
                <high
            value="20120813"/>
                </effectiveTime>
                <repeatNumber
            value="1"/>
                <quantity
            value="75"/>
                <product>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="573621"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="Albuterol 0.09 MG/ACTUAT inhalant solution">
                <originalText><reference
            value="#Med1"/></originalText>
                <translation
            code="573621"
            displayName="Albuterol 0.09 MG/ACTUAT inhalant solution"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </product>
            <performer>
            <assignedEntity>
            <id
            extension="2981823"
            root="2.16.840.1.113883.19.5.9999.456"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </assignedEntity>
            </performer>
            <author>
            <time
            nullFlavor="UNK"/>
                <assignedAuthor>
                <id
            root="2a620155-9d11-439e-92b3-5d9815fe4de8"/>
                <addr
            nullFlavor="UNK"/>
                <telecom
            nullFlavor="UNK"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedAuthor>
                </author>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <act
            classCode="ACT"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.20"/>
                    <!-- ** Instructions Template ** -->
                <code xsi:type="CE"
            code="409073007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="instruction"/>
                <text><reference
            value="#FillIns"/>label in spanish</text>
                <statusCode
            code="completed"/>
                </act>
                </entryRelationship>
                </supply>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <supply
            classCode="SPLY"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.18"/>
                    <!-- ** Medication Dispense Template ** -->
                <id
            root="1.2.3.4.56789.1"
            extension="cb734647-fc99-424c-a864-7e3cda82e704"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120806"/>
                <repeatNumber
            value="1"/>
                <product>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="573621"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="Albuterol 0.09 MG/ACTUAT inhalant solution">
                <originalText><reference
            value="#Med1"/></originalText>
                <translation
            code="573621"
            displayName="Albuterol 0.09 MG/ACTUAT inhalant solution"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </product>
            <performer>
            <time
            nullFlavor="UNK"/>
                <assignedEntity>
                <id
            root="2.16.840.1.113883.19.5.9999.456"
            extension="2981823"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            nullFlavor="UNK"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1393"/>
                <name>Community Health and Hospitals</name>
            <telecom
            nullFlavor="UNK"/>
                <addr
            nullFlavor="UNK"/>
                </representedOrganization>
                </assignedEntity>
                </performer>
                </supply>
                </entryRelationship>
                <precondition
            typeCode="PRCN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.25"/>
                <criterion>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <value
            xsi:type="CE"
            code="56018004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Wheezing"/>
                </criterion>
                </precondition>
                </substanceAdministration>
                </entry>
                </section>
                </component>



                    <!--
********************************************************
CARE PLAN
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.10"/>
                    <!--  **** Plan of Care section template  **** -->
                <code
            code="18776-5"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Treatment plan"/>
                <title>CARE PLAN</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Planned Activity</th>
            <th>Planned Date</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td>Consultation with Dr George Potomac for Asthma</td>
                                                        <td>20120820</td>
                                                        </tr>
                                                        <tr>
                                                        <td>Chest X-ray</td>
            <td>20120826</td>
            </tr>
            <tr>
            <td>Sputum Culture</td>
            <td>20120820</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry>
            <encounter
            moodCode="INT"
            classCode="ENC">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.40"/>
                    <!--  ****  Plan of Care Activity Encounter template  **** -->
                <id
            root="9a6d1bac-17d3-4195-89a4-1121bc809b4d"/>
                <code
            code="99241"
            displayName="Office consultation - 15 minutes"
            codeSystemName="CPT"
            codeSystem="2.16.840.1.113883.6.12"/>
                <effectiveTime>
                <center value="20120820"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ" inversionInd="true">
                <act
            classCode="ACT"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.20"/>
                    <!-- ** Instructions Template ** -->
                <code xsi:type="CE"
            code="409073007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="instruction"/>
                <text>Follow up with Dr George Potomac for Asthma</text>
                                                           <statusCode
                code="completed"/>
                    </act>
                    </entryRelationship>
                    </encounter>
                    </entry>
                    <entry>
                    <procedure
            moodCode="RQO"
            classCode="PROC">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.41"/>
                    <!-- ** Plan of Care Activity Procedure template ** -->
                <id
            root="9a6d1bac-17d3-4195-89c4-1121bc809b5a"/>
                <code
            code="168731009"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Chest X-Ray"
            codeSystemName="SNOMED-CT"/>
                <statusCode
            code="new"/>
                <effectiveTime>
                <center
            value="20120826"/>
                </effectiveTime>
                </procedure>
                </entry>
                <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="RQO">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.44"/>
                    <!--   Plan of Care Activity Observation template   -->
                <id
            root="9a6d1bac-17d3-4195-89a4-1121bc809b4a"/>
                <code
            code="624-7"
            codeSystem="2.16.840.1.113883.6.1"
            displayName="Sputum Culture"/>
                <statusCode
            code="new"/>
                <effectiveTime>
                <center
            value="20120820"/>
                </effectiveTime>
                </observation>
                </entry>
                </section>
                </component>

                    <!--
********************************************************
HOSPITAL DISCHARGE MEDICATIONS
********************************************************
      -->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.11.1"/>
                    <!-- Entries Required -->
                    <!-- Hospital Discharge Summary templateId -->
                <code
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            code="10183-2"
            displayName=" HOSPITAL DISCHARGE MEDICATIONS "/>
                <title>HOSPITAL DISCHARGE MEDICATIONS</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Medication</th>
                <th>Directions</th>
                <th>Start Date</th>
            <th>Status</th>
            <th>Indications</th>
            <th>Fill Instructions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td><content ID="DM">120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler</content></td>
            <td><content ID="DM1">0.11 MG/ACTUAT Metered Dose Once Daily</content></td>
            <td>20120813</td>
            <td>Active</td>
            <td>Bronchitis (32398004 SNOMED CT)</td>
            <td><content ID="FillIns_DM">Generic Substitition Allowed</content></td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                    <!-- Discharge Medication Entry -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.35"/>
                <id
            root="5a784260-6856-4f38-9638-80c751aff2fb"/>
                <code
            code="10183-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Discharge medication"/>
                <statusCode
            code="active"/>
                <effectiveTime>
                <low value="20120813"/>
                <high value="20120813"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <substanceAdministration
            classCode="SBADM"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.16"/>
                    <!-- ** MEDICATION ACTIVITY -->
                <id
            root="cdbd33f0-6cde-11db-9fe1-0800200c9a66"/>
                <text>
                <reference
            value="#DM1"/>0.11 MG/ACTUAT Metered Dose Once Daily </text>
            <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS">
                <low value="20120813"/>
                <high value="20120813"/>
                </effectiveTime>
                <effectiveTime
            xsi:type="PIVL_TS"
            institutionSpecified="true"
            operator="A">
                <period
            value="24"
            unit="h"/>
                </effectiveTime>
                <routeCode
            code="C38216"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="NCI Thesaurus"
            displayName="RESPIRATORY (INHALATION)"/>
                <doseQuantity
            value="1"
            unit="mg/actuat"/>
                <rateQuantity
            value="110"
            unit="ml/min"/>
                <maxDoseQuantity
            nullFlavor="UNK">
                <numerator
            nullFlavor="UNK"/>
                <denominator
            nullFlavor="UNK"/>
                </maxDoseQuantity>
                <administrationUnitCode
            code="C42944"
            displayName="INHALANT"
            codeSystem="2.16.840.1.113883.3.26.1.1"
            codeSystemName="NCI Thesaurus"/>
                <consumable>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="896001"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler">
                <originalText><reference
            value="#DM"/></originalText>
                <translation
            code="896001"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </consumable>
            <performer>
            <assignedEntity>
            <id
            nullFlavor="NI"/>
                <addr
            nullFlavor="UNK"/>
                <telecom
            nullFlavor="UNK"/>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1395"/>
                <name>Community Health and Hospitals</name>
            <telecom
            nullFlavor="UNK"/>
                <addr
            nullFlavor="UNK"/>
                </representedOrganization>
                </assignedEntity>
                </performer>
                <participant
            typeCode="CSM">
                <participantRole
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.24"/>
                <code
            code="412307009"
            displayName="drug vehicle"
            codeSystem="2.16.840.1.113883.6.96"/>
                <playingEntity
            classCode="MMAT">
                <code
            code="324049"
            displayName="Aerosol"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                <name>Aerosol</name>
                </playingEntity>
                </participantRole>
                </participant>
                <entryRelationship
            typeCode="RSON">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.19"/>
                <id
            root="db734647-fc99-424c-a864-7e3cda82e703"
            extension="45665"/>
                <code
            code="404684003"
            displayName="Finding"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20110113"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="32398004"
            displayName="Bronchitis"
            codeSystem="2.16.840.1.113883.6.96"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <supply
            classCode="SPLY"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.17"/>
                <id
            nullFlavor="NI"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            xsi:type="IVL_TS">
                <low
            value="20120813"/>
                <high
            nullFlavor="UNK"/>
                </effectiveTime>
                <repeatNumber
            value="1"/>
                <quantity
            value="75"/>
                <product>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="896001"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler">
                <originalText><reference
            value="#DM"/></originalText>
                <translation
            code="896001"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </product>
            <performer>
            <assignedEntity>
            <id
            extension="2981825"
            root="2.16.840.1.113883.19.5.9999.456"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            </assignedEntity>
            </performer>
            <author>
            <time
            nullFlavor="UNK"/>
                <assignedAuthor>
                <id
            root="2a620155-9d11-439e-92b3-5d9815fe4de8"/>
                <addr
            nullFlavor="UNK"/>
                <telecom
            nullFlavor="UNK"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                </assignedAuthor>
                </author>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <act
            classCode="ACT"
            moodCode="INT">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.20"/>
                    <!-- ** Instructions Template ** -->
                <code xsi:type="CE"
            code="409073007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="instruction"/>
                <text><reference
            value="#FillIns_DM"/>Generic Substitution Allowed</text>
            <statusCode
            code="completed"/>
                </act>
                </entryRelationship>
                </supply>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <supply
            classCode="SPLY"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.18"/>
                    <!-- ** Medication Dispense Template ** -->
                <id
            root="1.2.3.4.56789.1"
            extension="cb734647-fc99-424c-a864-7e3cda82e704"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120813"/>
                <repeatNumber
            value="1"/>
                <quantity
            value="75"/>
                <product>
                <manufacturedProduct
            classCode="MANU">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.23"/>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4ee8"/>
                <manufacturedMaterial>
                <code
            code="896001"
            codeSystem="2.16.840.1.113883.6.88"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler">
                <originalText><reference
            value="#DM"/></originalText>
                <translation
            code="896001"
            displayName="120 ACTUAT Fluticasone propionate 0.11 MG/ACTUAT Metered Dose Inhaler"
            codeSystem="2.16.840.1.113883.6.88"
            codeSystemName="RxNorm"/>
                </code>
                </manufacturedMaterial>
                <manufacturerOrganization>
                <name>Medication Factory Inc.</name>
            </manufacturerOrganization>
            </manufacturedProduct>
            </product>
            <performer>
            <time
            nullFlavor="UNK"/>
                <assignedEntity>
                <id
            root="2.16.840.1.113883.19.5.9999.456"
            extension="2981825"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            nullFlavor="UNK"/>
                <assignedPerson>
                <name>
                <prefix>Dr.</prefix>
                <given>Henry</given>
                <family>Seven</family>
                </name>
                </assignedPerson>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5.9999.1395"/>
                <name>Community Health and Hospitals</name>
            <telecom
            nullFlavor="UNK"/>
                <addr
            nullFlavor="UNK"/>
                </representedOrganization>
                </assignedEntity>
                </performer>
                </supply>
                </entryRelationship>
                <precondition
            typeCode="PRCN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.25"/>
                <criterion>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <value
            xsi:type="CE"
            code="56018004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Wheezing"/>
                </criterion>
                </precondition>
                </substanceAdministration>
                </entryRelationship>
                </act>
                </entry>
                </section>
                </component>


                    <!--
********************************************************
REASON FOR REFERRAL
********************************************************
-->
                <component>
                <section>
                <templateId
            root="1.3.6.1.4.1.19376.1.5.3.1.3.1"/>
                    <!-- ** Reason for Referral Section Template ** -->
                <code
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            code="42349-1"
            displayName="REASON FOR REFERRAL"/>
                <title>REASON FOR REFERRAL</title>
            <text>
            <paragraph>Follow up with Dr George Potomac for Asthma</paragraph>
                                                            </text>
                                                            </section>
                                                            </component>
                                                                <!--
********************************************************
PROBLEM LIST
********************************************************
-->
                                                            <component>
                                                            <section>
                                                            <templateId
                root="2.16.840.1.113883.10.20.22.2.5.1"/>
                    <code
            code="11450-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="PROBLEM LIST"/>
                <title>PROBLEMS</title>
                <text><content
            ID="problems"/>
                <list
            listType="ordered">
                <item><content
            ID="problem1">Pneumonia : Status - Resolved</content></item>
            <item><content
            ID="problem2">Asthma : Status - Active</content></item>
            </list>
            </text>
            <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                    <!-- Problem act template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.3"/>
                <id
            root="ec8a6ff8-ed4b-4f7e-82c3-e98e58b45de7"/>
                <code
            code="CONC"
            codeSystem="2.16.840.1.113883.5.6"
            displayName="Concern"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                <high
            value="20120806"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.4"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#problem1"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="233604007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Pneumonia"/>
                <entryRelationship
            typeCode="REFR">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.68"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#problem1"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="233604007"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Pneumonia"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.31"/>
                    <!--    Age observation template   -->
                <code
            code="445518008"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Age At Onset"/>
                <statusCode
            code="completed"/>
                <value
            xsi:type="PQ"
            value="65"
            unit="a"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.5"/>
                    <!-- Health status observation template -->
                <code xsi:type="CE"
            code="11323-3"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Health status"/>
                <text><reference
            value="#problems"/></text>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CD"
            code="162467007"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="Symptom Free"/>
                </observation>
                </entryRelationship>
                <entryRelationship typeCode="REFR">
                <observation classCode="OBS" moodCode="EVN">
                    <!-- Status observation template -->
                <templateId root="2.16.840.1.113883.10.20.22.4.6"/>
                <code xsi:type="CE"
            code="33999-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Status"/>
                <text><reference
            value="#problem2"/></text>
                <statusCode code="completed"/>
                <value xsi:type="CD"
            code="413322009"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="Resolved"/>
                </observation>
                </entryRelationship>
                </observation>
                </entryRelationship>
                </act>
                </entry>
                <entry
            typeCode="DRIV">
                <act
            classCode="ACT"
            moodCode="EVN">
                    <!-- Problem act template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.3"/>
                <id
            root="ec8a6ff8-ed4b-4f7e-82c3-e98e58b45de7"/>
                <code
            code="CONC"
            codeSystem="2.16.840.1.113883.5.6"
            displayName="Concern"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20070103"/>
                <high
            value="20120806"/>
                </effectiveTime>
                <entryRelationship
            typeCode="SUBJ">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.4"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#problem2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20070103"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="195967001"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Asthma"/>
                <entryRelationship
            typeCode="REFR">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.68"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#problem2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20120806"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="195967001"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Asthma"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="SUBJ"
            inversionInd="true">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.31"/>
                    <!--    Age observation template   -->
                <code
            code="445518008"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Age At Onset"/>
                <statusCode
            code="completed"/>
                <value
            xsi:type="PQ"
            value="65"
            unit="a"/>
                </observation>
                </entryRelationship>
                <entryRelationship
            typeCode="REFR">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.5"/>
                    <!-- Health status observation template -->
                <code xsi:type="CE"
            code="11323-3"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Health status"/>
                <text><reference
            value="#problems"/></text>
                <statusCode
            code="completed"/>
                <value
            xsi:type="CD"
            code="162467007"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="Symptom Free"/>
                </observation>
                </entryRelationship>
                <entryRelationship typeCode="REFR">
                <observation classCode="OBS" moodCode="EVN">
                    <!-- Status observation template -->
                <templateId root="2.16.840.1.113883.10.20.22.4.6"/>
                <code xsi:type="CE"
            code="33999-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Status"/>
                <text><reference
            value="#problem2"/></text>
                <statusCode code="completed"/>
                <value xsi:type="CD"
            code="55561003"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="Active"/>
                </observation>
                </entryRelationship>
                </observation>
                </entryRelationship>
                </act>
                </entry>
                </section>
                </component>
                    <!--
********************************************************
PROCEDURES
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.7.1"/>
                    <!-- Procedures section template -->
                <code
            code="47519-4"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="HISTORY OF PROCEDURES"/>
                <title>PROCEDURES</title>
                <text>
                <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Procedure</th>
                <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td>
                <content ID="Proc2">Chest X-Ray</content>
            </td>
            <td>8/7/2012</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <procedure
            classCode="PROC"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.14"/>
                    <!-- Procedure Activity Observation -->
                <id
            extension="123456789"
            root="2.16.840.1.113883.19"/>
                <code
            code="168731009"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Chest X-Ray"
            codeSystemName="SNOMED-CT">
                <originalText>
                <reference
            value="#Proc2"/>
                </originalText>
                </code>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120807"/>
                <priorityCode
            code="CR"
            codeSystem="2.16.840.1.113883.5.7"
            codeSystemName="ActPriority"
            displayName="Callback results"/>
                <methodCode
            nullFlavor="UNK"/>
                <targetSiteCode
            code="82094008"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"
            displayName="Lower Respiratory Tract Structure"/>
                <performer>
                <assignedEntity>
                <id
            root="2.16.840.1.113883.19.5"
            extension="1234"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            use="WP"
            value="(555)555-555-1234"/>
                <representedOrganization>
                <id
            root="2.16.840.1.113883.19.5"/>
                <name>Community Health and Hospitals</name>
            <telecom
            nullFlavor="UNK"/>
                <addr
            nullFlavor="UNK"/>
                </representedOrganization>
                </assignedEntity>
                </performer>
                <participant
            typeCode="LOC">
                <participantRole
            classCode="SDLOC">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.32"/>
                    <!-- Service Delivery Location template -->
                <code
            code="1160-1"
            codeSystem="2.16.840.1.113883.6.259"
            codeSystemName="HealthcareServiceLocation"
            displayName="Urgent Care Center"/>
                <addr>
                <streetAddressLine>1002 Healthcare Dr</streetAddressLine>
            <city>Portland</city>
            <state>OR</state>
            <postalCode>97266</postalCode>
            <country>US</country>
            </addr>
            <telecom
            nullFlavor="UNK"/>
                <playingEntity
            classCode="PLC">
                <name>Community Health and Hospitals</name>
            </playingEntity>
            </participantRole>
            </participant>
            </procedure>
            </entry>
            </section>
            </component>

                <!--
********************************************************
FUNCTIONAL and COGNITIVE STATUS
********************************************************
-->
            <component>
            <section>
            <templateId
            root="2.16.840.1.113883.10.20.22.2.14"/>
                    <!--  ******** Functional status section template   ******** -->
                <code
            code="47420-5"
            codeSystem="2.16.840.1.113883.6.1"/>
                <title>FUNCTIONAL STATUS</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Functional Condition</th>
            <th>Effective Dates</th>
            <th>Condition Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td>
            <content ID="fs1">Dependence on cane</content></td>
            <td>2008</td>
            <td>Active</td>
            </tr>
            <tr>
            <td>
            <content ID="fs2">Memory impairment</content>
            </td>
            <td>2008</td>
            <td>Active</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.68"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code xsi:type="CE"
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#fs1"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20081101"/>
                <high nullFlavor="UNK"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="105504002"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Dependence on Cane"/>
                </observation>
                </entry>
                <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.68"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code xsi:type="CE"
            code="409586006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Complaint"/>
                <text>
                <reference
            value="#fs2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20081101"/>
                <high nullFlavor="UNK" />

                </effectiveTime>
                <value
            xsi:type="CD"
            code="386807006"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Memory Impairment"/>
                </observation>
                </entry>
                <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Cognitive Status Problem observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.73"/>
                <id
            root="ab1791b0-5c71-11db-b0de-0800200c9a66"/>
                <code xsi:type="CE"
            code="373930000"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Cognitive Function Finding"/>
                <text>
                <reference
            value="#fs2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20081101"/>
                <high nullFlavor="UNK"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="48167000"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Amnesia"/>
                </observation>
                </entry>
                </section>
                </component>

                    <!--
********************************************************
RESULTS
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.3.1"/>
                    <!-- Entries Required -->
                <code
            code="30954-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="RESULTS"/>
                <title>RESULTS</title>
                <text>
                <table>
                <tbody>
                <tr>
                <td
            colspan="2">LABORATORY INFORMATION</td>
            </tr>
            <tr>
            <td
            colspan="2">Chemistries and drug levels</td>
            </tr>
            <tr>
            <td><content
            ID="result1">HGB (M 13-18 g/dl; F 12-16 g/dl)</content></td>
            <td>13.2</td>
            </tr>
            <tr>
            <td><content
            ID="result2">WBC (4.3-10.8 10+3/ul)</content></td>
            <td>6.7</td>
            </tr>
            <tr>
            <td><content
            ID="result3">PLT (135-145 meq/l)</content></td>
            <td>123 (L)</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <organizer
            classCode="BATTERY"
            moodCode="EVN">
                    <!-- Result organizer template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.1"/>
                <id
            root="7d5a02b0-67a4-11db-bd13-0800200c9a66"/>
                <code xsi:type="CE"
            code="43789009"
            displayName="CBC WO DIFFERENTIAL"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED CT"/>
                <statusCode
            code="completed"/>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Result observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.2"/>
                <id
            root="107c2dc0-67a5-11db-bd13-0800200c9a66"/>
                <code xsi:type="CE"
            code="30313-1"
            displayName="HGB"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"> </code>
                <text>
                <reference
            value="#result1"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120810"/>
                <value
            xsi:type="PQ"
            value="10.2"
            unit="g/dl"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                <methodCode/>
                <targetSiteCode/>
                <author>
                <time/>
                <assignedAuthor>
                <id
            root="2a620155-9d11-439e-92b3-5d9816ff4de8"/>
                </assignedAuthor>
                </author>
                <referenceRange>
                <observationRange>
                <text>M 13-18 g/dl; F 12-16 g/dl</text>
            </observationRange>
            </referenceRange>
            </observation>
            </component>
            <component>
            <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Result observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.2"/>
                <id
            root="107c2dc0-67a5-11db-bd13-0800200c9a66"/>
                <code xsi:type="CE"
            code="33765-9"
            displayName="WBC"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"> </code>
                <text>
                <reference
            value="#result2"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120810"/>
                <value
            xsi:type="PQ"
            value="12.3"
            unit="10+3/ul"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                <methodCode/>
                <targetSiteCode/>
                <author>
                <time/>
                <assignedAuthor>
                <id
            root="2a620154-9d11-439e-92b3-5d9815ff4de8"/>
                </assignedAuthor>
                </author>
                <referenceRange>
                <observationRange>
                <value
            xsi:type="IVL_PQ">
                <low
            value="4.3"
            unit="10+3/ul"/>
                <high
            value="10.8"
            unit="10+3/ul"/>
                </value>
                </observationRange>
                </referenceRange>
                </observation>
                </component>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Result observation template -->
                <templateId
            root="2.16.840.1.113883.10.20.22.4.2"/>
                <id
            root="107c2dc0-67a5-11db-bd13-0800200c9a66"/>
                <code xsi:type="CE"
            code="26515-7"
            displayName="PLT"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"> </code>
                <text>
                <reference
            value="#result3"/>
                </text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120810"/>
                <value
            xsi:type="PQ"
            value="123"
            unit="10+3/ul"/>
                <interpretationCode
            code="L"
            codeSystem="2.16.840.1.113883.5.83"/>
                <methodCode/>
                <targetSiteCode/>
                <author>
                <time/>
                <assignedAuthor>
                <id
            root="2a620155-9d11-439e-92b3-5d9815ff4de8"/>
                </assignedAuthor>
                </author>
                <referenceRange>
                <observationRange>
                <value
            xsi:type="IVL_PQ">
                <low
            value="150"
            unit="10+3/ul"/>
                <high
            value="350"
            unit="10+3/ul"/>
                </value>
                </observationRange>
                </referenceRange>
                </observation>
                </component>
                </organizer>
                </entry>
                </section>
                </component>
                    <!--
********************************************************
SOCIAL HISTORY
********************************************************
-->
                <component>
                    <!--   Social History ******** -->
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.17"/>
                    <!--  ********  Social history section template   ******** -->
                <code
            code="29762-2"
            codeSystem="2.16.840.1.113883.6.1"
            displayName="Social History"/>
                <title>SOCIAL HISTORY</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th>Social History Element</th>
            <th>Description</th>
            <th>Effective Dates</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td>
            <content
            ID="soc1"/> smoking</td>
                <td>Former Smoker (1 pack per day</td>
            <td>20050501 to 20110227</td>
            </tr>
            <tr>
            <td>
            <content
            ID="soc2"/> smoking</td>
                <td>Current Everyday Smoker 2 packs per day</td>
            <td>20110227 - today</td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                    <!-- Smoking status observation template -->
                <templateId
            root="2.16.840.1.113883.10.22.4.78"/>
                <id
            extension="123456789"
            root="2.16.840.1.113883.19"/>
                <code
            code="ASSERTION"
            codeSystem="2.16.840.1.113883.5.4"/>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20050501"/>
                <high
            value="20110227"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="8517006"
            displayName="Former smoker"
            codeSystem="2.16.840.1.113883.6.96"/>
                </observation>
                </entry>
                <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.38"/>
                    <!--  ********  Social history observation template   ******** -->
                <id
            root="9b56c25d-9104-45ee-9fa4-e0f3afaa01c1"/>
                <code
            code="230056004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Cigarette smoking">
                <originalText>
                <reference
            value="#soc1"/>
                </originalText>
                </code>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20050501"/>
                <high
            value="20110227"/>
                </effectiveTime>
                <value
            xsi:type="ST">1 pack per day</value>
            </observation>
            </entry>
            <entry
            typeCode="DRIV">
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.38"/>
                    <!--  ********  Social history observation template   ******** -->
                <id
            root="45efb604-7049-4a2e-ad33-d38556c9636c"/>
                <code
            code="230056004"
            codeSystem="2.16.840.1.113883.6.96"
            displayName="Cigarette smoking">
                <originalText>
                <reference
            value="#soc2"/>
                </originalText>
                </code>
                <statusCode
            code="completed"/>
                <effectiveTime>
                <low
            value="20110227"/>
                <high nullFlavor="UNK"/>
                </effectiveTime>
                <value
            xsi:type="CD"
            code="449868002"
            displayName="Current Everyday Smoker"
            codeSystem="2.16.840.1.113883.6.96"/>
                </observation>
                </entry>
                </section>
                </component>
                    <!--
********************************************************
VITAL SIGNS
********************************************************
-->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.4.1"/>
                <code
            code="8716-3"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="VITAL SIGNS"/>
                <title>VITAL SIGNS</title>
            <text>
            <table
            border="1"
            width="100%">
                <thead>
                <tr>
                <th
            align="right">Date / Time: </th>
            <th>Nov 1, 2011</th>
            <th>August 6, 2012</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <th
            align="left">Height</th>
                <td>
                <content
            ID="vit1">69 inches</content></td>
            <td>
            <content
            ID="vit2">69 inches</content></td>
            </tr>
            <tr>
            <th
            align="left">Weight</th>
                <td>
                <content
            ID="vit3">189 lbs</content></td>
            <td>
            <content
            ID="vit4">194 lbs</content></td>
            </tr>
            <tr>
            <th
            align="left">Blood Pressure</th>
            <td><content
            ID="vit5">132/86 mmHg</content></td>
            <td><content
            ID="vit6">145/88 mmHg</content></td>
            </tr>
            </tbody>
            </table>
            </text>
            <entry
            typeCode="DRIV">
                <organizer
            classCode="CLUSTER"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.26"/>
                    <!-- Vital signs organizer template -->
                <id
            root="c6f88320-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="46680005"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED -CT"
            displayName="Vital signs"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20111101"/>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="8302-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Height"/>
                <text><reference
            value="#vit1"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20081101"/>
                <value
            xsi:type="PQ"
            value="69"
            unit="in"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="3141-9"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Patient Body Weight - Measured"/>
                <text><reference
            value="#vit4"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20111101"/>
                <value
            xsi:type="PQ"
            value="189"
            unit="lbs"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="8480-6"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Intravascular Systolic"/>
                <text><reference
            value="#vit5"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20111101"/>
                <value
            xsi:type="PQ"
            value="132"
            unit="mm[Hg]"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                </organizer>
                </entry>
                <entry
            typeCode="DRIV">
                <organizer
            classCode="CLUSTER"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.26"/>
                    <!-- Vital signs organizer template -->
                <id
            root="c6f88320-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="46680005"
            codeSystem="2.16.840.1.113883.6.96"
            codeSystemName="SNOMED -CT"
            displayName="Vital signs"/>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120806"/>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="8302-2"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Height"/>
                <text><reference
            value="#vit2"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120806"/>
                <value
            xsi:type="PQ"
            value="69"
            unit="in"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="3141-9"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Patient Body Weight - Measured"/>
                <text><reference
            value="#vit4"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120806"/>
                <value
            xsi:type="PQ"
            value="194"
            unit="lbs"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                <component>
                <observation
            classCode="OBS"
            moodCode="EVN">
                <templateId
            root="2.16.840.1.113883.10.20.22.4.27"/>
                    <!-- Vital Sign Observation template -->
                <id
            root="c6f88321-67ad-11db-bd13-0800200c9a66"/>
                <code
            code="8480-6"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="Intravascular Systolic"/>
                <text><reference
            value="#vit6"/></text>
                <statusCode
            code="completed"/>
                <effectiveTime
            value="20120806"/>
                <value
            xsi:type="PQ"
            value="145"
            unit="mm[Hg]"/>
                <interpretationCode
            code="N"
            codeSystem="2.16.840.1.113883.5.83"/>
                </observation>
                </component>
                </organizer>
                </entry>
                </section>
                </component>

                    <!--
************************************
HOSPITAL DISCHARGE INSTRUCTIONS
************************************
         -->
                <component>
                <section>
                <templateId
            root="2.16.840.1.113883.10.20.22.2.41"/>
                <code
            code="8653-8"
            codeSystem="2.16.840.1.113883.6.1"
            codeSystemName="LOINC"
            displayName="HOSPITAL DISCHARGE INSTRUCTIONS"/>
                <title>HOSPITAL DISCHARGE INSTRUCTIONS</title>
            <text>
            <content>
            Ms. Jones, you have been seen by Dr. Henry Seven at Local Community Hospital from August 8th until August 13th 2012. You are currently being discharged from Local Community Hospital. Dr. Seven has provided the following instructions to you at this time; should you have any questions please contact a member of your healthcare team prior to discharge. If you have left the hospital and have questions, please contact Dr. Seven at 555-555-1002.
            Instructions:
                </content>
            <list
            listType="ordered">
                <item>Take all medications as prescribed.</item>
            <item>Please monitor your peak flows. If your peak flows drop to 50% of normal, call my office immediately or return to the Emergency Room.</item>
            <item>If you experience any of the following symptoms, call my office immediately or return to the Emergency Room:
                <list listType="ordered">
                <item>Shortness of Breath</item>
            <item>Dizziness or Light-headedness</item>
            <item>Fever, chills, or diffuse body aches</item>
            <item>Pain or redness at the site of any previous intravenous catheter</item>
            <item>Any other unusual problem</item>
            </list>
            </item>
            </list>
            </text>
            </section>
            </component>
                <!--
************************************
PATIENT INSTRUCTIONS
************************************
         -->
            <component>
            <section>
            <templateId root="2.16.840.1.113883.10.20.22.2.45"/>
                <code code="69730-0" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="Instructions"/>
                <title>Instructions</title>
                <entry>
                <act classCode="ACT" moodCode="INT">
                <templateId root="2.16.840.1.113883.10.20.22.4.20"/>
                <code code="423564006" codeSystem="2.16.840.1.113883.11.20.9.34" codeSystemName="patientEducationType" displayName="ProviderInstructions"/>
                <text>diet and exercise counseling provided during visit</text>
            <effectiveTime>
            <low value="20120813"/>
                <high value="20120813"/>
                </effectiveTime>
                <statusCode code="completed"/>
                </act>
                </entry>
                <entry>
                <act classCode="ACT" moodCode="INT">
                <templateId root="2.16.840.1.113883.10.20.22.4.20"/>
                <code code="423564006" codeSystem="2.16.840.1.113883.11.20.9.34" codeSystemName="patientEducationType" displayName="ProviderInstructions"/>
                <text>resources and instructions provided during visit</text>
            <effectiveTime>
            <low value="20120813"/>
                <high value="20120813"/>
                </effectiveTime>
                <statusCode code="completed"/>
                </act>
                </entry>
                </section>
                </component>

                </structuredBody>
                </component>
                </ClinicalDocument>

    </script>

/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.com/yui/license.html
version: 3.3.0
build: 3167
*/
YUI.add("resize-constrain",function(A){var k=A.Lang,S=k.isBoolean,j=k.isNumber,G=k.isString,Q=A.Resize.capitalize,E=function(Y){return(Y instanceof A.Node);},f=function(Y){return parseFloat(Y)||0;},B="borderBottomWidth",Z="borderLeftWidth",b="borderRightWidth",F="borderTopWidth",d="border",R="bottom",T="con",J="constrain",N="host",O="left",M="maxHeight",H="maxWidth",C="minHeight",g="minWidth",i="node",X="offsetHeight",a="offsetWidth",c="preserveRatio",P="region",e="resizeConstrained",h="right",K="tickX",I="tickY",U="top",D="width",L="view",W="viewportRegion";function V(){V.superclass.constructor.apply(this,arguments);}A.mix(V,{NAME:e,NS:T,ATTRS:{constrain:{setter:function(Y){if(Y&&(E(Y)||G(Y)||Y.nodeType)){Y=A.one(Y);}return Y;}},minHeight:{value:15,validator:j},minWidth:{value:15,validator:j},maxHeight:{value:Infinity,validator:j},maxWidth:{value:Infinity,validator:j},preserveRatio:{value:false,validator:S},tickX:{value:false},tickY:{value:false}}});A.extend(V,A.Plugin.Base,{constrainSurrounding:null,initializer:function(){var Y=this,l=Y.get(N);l.delegate.dd.plug(A.Plugin.DDConstrained,{tickX:Y.get(K),tickY:Y.get(I)});l.after("resize:align",A.bind(Y._handleResizeAlignEvent,Y));l.on("resize:start",A.bind(Y._handleResizeStartEvent,Y));},_checkConstrain:function(m,v,n){var s=this,r,o,p,u,t=s.get(N),Y=t.info,l=s.constrainSurrounding.border,q=s._getConstrainRegion();if(q){r=Y[m]+Y[n];o=q[v]-f(l[Q(d,v,D)]);if(r>=o){Y[n]-=(r-o);}p=Y[m];u=q[m]+f(l[Q(d,m,D)]);if(p<=u){Y[m]+=(u-p);Y[n]-=(u-p);}}},_checkHeight:function(){var Y=this,m=Y.get(N),o=m.info,l=(Y.get(M)+m.totalVSurrounding),n=(Y.get(C)+m.totalVSurrounding);Y._checkConstrain(U,R,X);if(o.offsetHeight>l){m._checkSize(X,l);}if(o.offsetHeight<n){m._checkSize(X,n);}},_checkRatio:function(){var y=this,r=y.get(N),x=r.info,n=r.originalInfo,q=n.offsetWidth,z=n.offsetHeight,p=n.top,AA=n.left,t=n.bottom,w=n.right,m=function(){return(x.offsetWidth/q);},o=function(){return(x.offsetHeight/z);},s=r.changeHeightHandles,Y,AB,u,v,l,AC;if(y.get(J)&&r.changeHeightHandles&&r.changeWidthHandles){u=y._getConstrainRegion();AB=y.constrainSurrounding.border;Y=(u.bottom-f(AB[B]))-t;v=AA-(u.left+f(AB[Z]));l=(u.right-f(AB[b]))-w;AC=p-(u.top+f(AB[F]));if(r.changeLeftHandles&&r.changeTopHandles){s=(AC<v);}else{if(r.changeLeftHandles){s=(Y<v);}else{if(r.changeTopHandles){s=(AC<l);}else{s=(Y<l);}}}}if(s){x.offsetWidth=q*o();y._checkWidth();x.offsetHeight=z*m();}else{x.offsetHeight=z*m();y._checkHeight();x.offsetWidth=q*o();}if(r.changeTopHandles){x.top=p+(z-x.offsetHeight);}if(r.changeLeftHandles){x.left=AA+(q-x.offsetWidth);}A.each(x,function(AE,AD){if(j(AE)){x[AD]=Math.round(AE);}});},_checkRegion:function(){var Y=this,l=Y.get(N),m=Y._getConstrainRegion();return A.DOM.inRegion(null,m,true,l.info);},_checkWidth:function(){var Y=this,n=Y.get(N),o=n.info,m=(Y.get(H)+n.totalHSurrounding),l=(Y.get(g)+n.totalHSurrounding);Y._checkConstrain(O,h,a);if(o.offsetWidth<l){n._checkSize(a,l);}if(o.offsetWidth>m){n._checkSize(a,m);}},_getConstrainRegion:function(){var Y=this,m=Y.get(N),l=m.get(i),o=Y.get(J),n=null;if(o){if(o==L){n=l.get(W);}else{if(E(o)){n=o.get(P);}else{n=o;}}}return n;},_handleResizeAlignEvent:function(m){var Y=this,l=Y.get(N);Y._checkHeight();Y._checkWidth();if(Y.get(c)){Y._checkRatio();}if(Y.get(J)&&!Y._checkRegion()){l.info=l.lastInfo;}},_handleResizeStartEvent:function(m){var Y=this,n=Y.get(J),l=Y.get(N);Y.constrainSurrounding=l._getBoxSurroundingInfo(n);}});A.namespace("Plugin");A.Plugin.ResizeConstrained=V;},"3.3.0",{requires:["resize-base","plugin"],skinnable:false});
/*! EasyRecipe 3.2.2929 Copyright (c) 2015 BoxHill LLC */
!function(e){function n(e,t){var i,s,a,r,o,l,u,c,E,d,f,A,R,b,g,T,P,h,p,C,I,x,G,S,v,y=0,z=1,D=2,N=0,O="",_="<!-- START REPEAT ",k="<!-- START INCLUDEIF ",U="<!-- END INCLUDEIF ",F=/<!-- START INCLUDEIF (!?)([_a-z][_0-9a-z]{0,19}) -->/i,L=/^#([_a-z][_0-9a-z]{0,19})#/im,m=/<!-- START REPEAT ([_a-zA-Z][_0-9a-zA-Z]{0,19}) -->/m,w="undefined";for(i=e,t=t||{};;){if(o=i.length,l=i.indexOf("#",N),-1!==l&&(o=l,u=y),c=i.indexOf(_,N),-1!==c&&o>c&&(o=c,u=z),E=i.indexOf(k,N),-1!==E&&o>E&&(o=E,u=D),o===i.length)return O+i.substr(N);switch(d=o-N,O+=i.substr(N,d),N=o,u){case D:if(a=i.substr(N,44),r=F.exec(a),null===r)break;if(f=r[1],A="!"!==f,R=r[2],b=U+f+R+" -->",g=b.length,T=i.indexOf(b),-1===T){N++;break}P=typeof t[R]!==w&&t[R]!==!1,P===A?(h="<!-- START INCLUDEIF "+f+R+" -->",p=h.length,i=i.substr(0,N)+i.substr(N+p,T-N-p)+i.substr(T+g)):i=i.substr(0,N)+i.substr(T+g);break;case y:if(a=i.substr(N,22),r=L.exec(a),null===r){O+="#",N++;continue}if(C=r[1],""!==t[C]&&!t[C]){O+="#"+C+"#",N+=C.length+2;continue}O+=t[C],N+=C.length+2;break;case z:if(a=i.substr(N,45),r=m.exec(a),null===r){O+="<",N++;continue}if(I=r[1],!(t[I]&&t[I]instanceof Array)){O+="<",N++;continue}if(N+=I.length+22,x=i.indexOf("<!-- END REPEAT "+I+" -->",N),-1===x){O+="<!-- START REPEAT "+I+" -->";continue}for(G=x-N,S=i.substr(N,G),v=t[I],s=0;s<v.length;s++)O+=n(S,v[s]);N+=I.length+G+20}}}function t(t){var i,l,u,c,E;c=o.exec(t.target.id),null!==c&&(E=c[1],u=s[E],i=e("#divERGPAContainer"+E),0===i.length&&(l=n(a.guestTemplate,u),i=e(l),i.attr("id","divERGPAContainer"+E),e(".spnERGPAClose",i).button(),e(".spnERGPAClose",i).on("click",function(){i.dialog("close")}),i.dialog({title:"Guest Post Details",autoOpen:!1,width:550,resizable:!1,dialogClass:"divERGPAContainer"}),i.parent(".ui-dialog").appendTo(r)),i.dialog("open"))}var i,s,a,r,o=/^ERGP_(\d+)$/;e(function(){r=e("body .easyrecipeUI:first-child"),0===r.length&&(r=e('<div class="easyrecipeUI" />').prependTo("body")),i=e("#divERGPAContainer"),a=EASYRECIPE,s=e.parseJSON(EASYRECIPE.guestPosters),e(".ERGPAPoster").on("click",t)})}(jQuery);
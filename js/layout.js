function moveSection(name,dir) {
    var divs = document.getElementsByTagName("div");
    var x=[];
    var re=new RegExp("\\bsection\\b");
    var div = null;
    var divpos = -1;
    for(var i=0;i<divs.length;i++) {
	if(re.test(divs[i].className)) {
	    x.push(divs[i]);
	    if(name==divs[i].getAttribute("id")) {
		div = divs[i];
		divpos = x.length-1;
	    }
	}
    }
    if(dir=="top") {
	if(divpos<=0) return;
	div.parentNode.removeChild(div);
	x[0].parentNode.insertBefore(div, x[0]);
    }
    else if(dir=="up") {
	if(divpos<=0) return;
	var prev = div.previousSibling;
	div.parentNode.removeChild(div);
	prev.parentNode.insertBefore(div, prev);
    }
    else if(dir=="down") {
	if(divpos>=x.length-1) return;
	var next = div.nextSibling.nextSibling;
	var parent = div.parentNode;
	div.parentNode.removeChild(div);
	if(next==null)
	    parent.appendChild(div);
	else
	    next.parentNode.insertBefore(div, next);
    }
    else if(dir=="bottom") {
	if(divpos>=x.length-1) return;
	var parent = div.parentNode;
	parent.removeChild(div);
	parent.appendChild(div);
    }
}

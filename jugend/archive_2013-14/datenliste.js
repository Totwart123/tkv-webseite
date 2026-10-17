//Datenliste V1.0
//2
//,0,960,
var jsdata_count=1;
var jsdata_path="datenliste.js";

//ViennaSoft Datenlisten JavaScript V1.8 Copyright www.viennasoft.at

vs_p=new Array();
var jsdata_id=2;
//var vs_datastart=2;

var show_start = false;
var vs_wz = 1;
var vs_timeout_id = 0;


function initArray() {this.length=initArray.arguments.length;
for (var i=0;i < this.length; i++) {
this[i+1]=initArray.arguments[i];}}


function vs_addwk(vs_id,anzahl,flags) {
		if(parent.frames.length==0){
			alert("Der Warenkorb ist derzeit nicht verfügbar.");
			return;
		}
		
		vs_id=vs_id+1;

		if(vs_id==0){
			vs_id=jsdata_id;
		}

		var vswk_id=0;

		for (i=1; i<parent.vswk_count+1; i++){
			var wk_ok=0;
			for (j=1; j<vs_p[vs_id].length+1; j++){
				if (parent.vswk[i][j]!=vs_p[vs_id][j]){wk_ok=1;}
			}

			if (parent.vswk[i][j]!=jsdata_path){wk_ok=1;}

			if (wk_ok==0){
				anzahl=parent.vswk[i][j+3];
				vswk_id=i;
			}			
		}


		if (anzahl==0 | vswk_id>0){
			anzahl=prompt("Geben Sie die gewünschte Anzahl ein.",anzahl); 
			anzahl=parseInt(anzahl);
			
			if(isNaN(anzahl) == true){
				return;
			}
			if (anzahl==0){
				if (vswk_id>0){
					vs_deletewk(vswk_id)
					return;
				}
				else{
					return;
				} 
			}
		}

	
		if (vswk_id==0){
			parent.vswk_count=parent.vswk_count+1
			vswk_id=parent.vswk_count;
	
			parent.vswk[vswk_id]=new Array();
			parent.vswk[vswk_id].length=vs_p[vs_id].length+4;
			

			for (i=1; i<vs_p[vs_id].length+1; i++){
				parent.vswk[vswk_id][i]=vs_p[vs_id][i];
			}			
		}



		parent.vswk[vswk_id][vs_p[vs_id].length+1]=jsdata_path;

		var str=self.location.href;
		inn=str.indexOf('?');
		if (inn>0){
			str=str.substring(0,inn);
		}
		parent.vswk[vswk_id][vs_p[vs_id].length+2]=str;

		parent.vswk[vswk_id][vs_p[vs_id].length+3]=vs_id;
		parent.vswk[vswk_id][vs_p[vs_id].length+4]=anzahl;
	}

function vs_removewk(wkid) {
		for (i=1; i<parent.vswk_count+1; i++){	
			if(i>wkid){
				var eintr=parent.vswk[1].length;
				for (j=1; j < eintr; j++){
					parent.vswk[i-1][j]=parent.vswk[i][j]
				}
			}
		}
		parent.vswk_count=parent.vswk_count-1;
		location.href=location.href;
	}

function vs_clearwk() {
		if(parent.frames.length!=0){
			parent.vswk_count=0;
			location.href=location.href;
		}
	}

function vs_editwk(wkid,vs_site,vs_target,vs_flags){

          if (parent.inhalt){

              if (parent.inhalt.location.href==parent.vswk[wkid][parent.vswk[wkid].length-3]){
                  parent.inhalt.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length-2]);
                  parent.inhalt.jsdata_showelements(0);

              }
              else{
                  parent.inhalt.location=parent.vswk[wkid][parent.vswk[wkid].length-3]+'?'+parent.vswk[wkid][parent.vswk[wkid].length-2];
                  //parent.inhalt.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length]);
                  //parent.inhalt.jsdata_showelements(0);
			
              }
          }
          else{
              var vs_wndbild = vs_open_window('inhalt.htm?'+parent.vswk[wkid][parent.vswk[wkid].length],'wndbild','')
                  vs_wndbild.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length]);
                  vs_wndbild.jsdata_showelements(0);
          }
      }

function vs_preiswk(wkid) {
		var summe = 0;
		if(parent.frames.length!=0){
			var vsjs_anzahlspalte=parent.vswk[1].length-1;
			var vsjs_preisspalte=vs_get_colpos("preis");

			if (vsjs_preisspalte==-1){
				return "0,00";
			}

			summe = parent.vswk[wkid][vsjs_preisspalte] * parent.vswk[wkid][vsjs_anzahlspalte];
		}
		return vs_convert_zahl_preis(summe);
	}


function vs_summewk() {
		var vsjs_preisspalte=vs_get_colpos("preis");
		var summe = 0;
		if(parent.frames.length!=0){
			if (parent.vswk_count==0){
				return "0,00";
			}
			if (vsjs_preisspalte==-1){
				return "0,00";
			}
			var vsjs_anzahlspalte=parent.vswk[1].length-1;
	
			for (n=1; n<parent.vswk_count+1; n++){	
				summe = summe + parent.vswk[n][vsjs_preisspalte] * parent.vswk[n][vsjs_anzahlspalte];		
			}
		}
		return vs_convert_zahl_preis(summe);
	}


function vs_inhaltwk() {
		var inhalt="";
		if(parent.frames.length==0){
			alert("Der Warenkorb ist derzeit nicht verfügbar.");
			return "Der Warenkorb ist nicht verfügbar.";
		}
		
		if(parent.vswk_count==0){
			alert("Es befinden sich keine Produkte im Warenkorb.");
			return "Der Warenkorb ist leer.";
		}
		
		for (i=1; i<parent.vswk_count+1; i++){
			var wk_ok=0;
			inhalt = inhalt + "\n\rArtikel " + i + "\n\r";
			inhalt = inhalt + "Menge = " + parent.vswk[i][parent.vswk[i].length-1] + "\n\r";

			for (j=3; j<parent.vswk[i].length-4; j++){
				if (vs_p[1][j]!="bild"){
					inhalt = inhalt + vs_p[0][j] + " = " + parent.vswk[i][j] + "\n\r";
				}
			}
		}

		return inhalt;
	}
	   
function vs_write_selectvalues(wert) {
		var eintr = wert.split("|");
		for (var j = 0; j < eintr.length; j++){
			document.write("<option value='"+eintr[j]+"'>"+eintr[j]+"</option>");
		}
  	}
	
function vs_get_selectvalues(ziel,wert) {
		for (var j = ziel.length; j > 0;){
			ziel.options[--j] = null
		}
		
		var eintr = wert.split("|");
		for (var j = 0; j < eintr.length; j++){
			ziel.options[ziel.length] = new Option(eintr[j], eintr[j])
		}
  	}


function vs_set_selectvalues(id,cname,akteintr,wk) {
		cpos = vs_get_colpos(cname);
		if (wk==true){ 
			wert1=parent.vswk[id][cpos];
		}else{
			wert1=vs_p[id][cpos];
		}
		
		var eintr = wert1.split("|");
		wert=akteintr;
		for (var j = 0; j < eintr.length; j++){
			if(akteintr!=eintr[j]){
				wert=wert+"|"+eintr[j];
			}
		}

		if (wk==true){ 
			parent.vswk[id][cpos]=wert;
		}else{
			vs_p[id][cpos]=wert;
		}
		return false;
  	}


function vsjs_settextvalue(id,cname,wert){
		cpos = vs_get_colpos(cname);
		vs_p[id][cpos]=wert;
		return false;
      }


function vsjs_setwktextvalue(id,cname,wert){
		cpos = vs_get_colpos(cname);
		parent.vswk[id][cpos]=wert;
		return false;
      }


function vs_convert_zahl_preis(zahl) {
		var preis = "0,00";
		if(isNaN(parseFloat(zahl))==false){
			preis = parseFloat(zahl);	
			preis=(Math.round(parseFloat(preis)*100)/100);
			inn=(""+preis).indexOf(".",0);
			if(inn != -1){
				sub1=(""+preis).substring(0,inn);
				sub2=(""+preis).substring(inn+1,(""+zahl).length);
				if(sub2.length==0){sub2=sub2+"00"};
				if(sub2.length==1){sub2=sub2+"0"};
				preis = sub1 + "," + sub2;
			}
			else{
				preis = preis + ",00";
			}
		}
		return preis;
	}

	
function vs_get_colpos(cname){
      	for (n=1; n<vs_p[0].length+1; n++){
      		if (vs_p[0][n]==cname){
      			return n;	
      		}
      	}
      	return -1;
      }




function vsjs_suche_alles(){
	   if(parent.frames.length!=0){
		parent.vssel_count=0;
		vs_setid(1);
		vsjs_showall();
	   }
	}


function vsjs_suche_begriff(begriff){
		var sn = (""+begriff).toLowerCase();
		if (sn==""){
			vsjs_suche_alles()
			return;
		}

		if(parent.frames.length!=0){
			parent.vssel_count=0;
			for (i=1; i<jsdata_count+1; i++){
				var found=0;
				window.status="Suche nach '" + sn + "' in Datensatz " + i + " von " + jsdata_count;
				for (j=1; j<vs_p[i].length+1; j++){
					if ((""+vs_p[i][j]).toLowerCase().indexOf(sn,0) >= 0){
						found++;
					}
				}
				if (found > 0){
					parent.vssel_count++;
					if(parent.vssel.length<parent.vssel_count){parent.vssel.length=parent.vssel_count};
					parent.vssel[parent.vssel_count]=i;
				}
			}
			window.status="Suche nach " + sn + " abgeschlossen. " + parent.vssel_count + " Treffer"
			
			//datenanzeige aktualisieren
			if (parent.vssel_count==0){
				vs_setid(1);
			}
			else{
				parent.vssel_id=1;
				vs_setid(parent.vssel[parent.vssel_id]);
			}
			vsjs_showall();
		}
		else{
			for (i=1; i<jsdata_count+1; i++){
				var found=0;
				window.status="Suche nach '" + sn + "' in Datensatz " + i + " von " + jsdata_count;
				for (j=1; j<vs_p[i].length+1; j++){
					if ((""+vs_p[i][j]).toLowerCase().indexOf(sn,0) >= 0){
						found++;
					}
				}
				if (found > 0){
					window.status="";
					vs_setid(i);
					jsdata_showelements(0);
					return;
				}
			}
			window.status="Suche nach " + sn + " abgeschlossen. 0 Treffer"
		}
      }

function vsjs_suche_spalte_begriff(cname,begriff){
		if(begriff==""){
			vsjs_suche_alles();
			return;
		}
		var cpos = vs_get_colpos(cname);
		var sn = (""+begriff).toLowerCase();

		if(parent.frames.length!=0){
			parent.vssel_count=0;
				for (i=1; i<jsdata_count+1; i++){
					var found=0;
					window.status="Suche nach '" + sn + "' in Datensatz " + i + " von " + jsdata_count;
					if ((""+vs_p[i][cpos]).toLowerCase().indexOf(sn,0) >= 0){
						parent.vssel_count++;
						if(parent.vssel.length<parent.vssel_count){parent.vssel.length=parent.vssel_count};
						parent.vssel[parent.vssel_count]=i;
					}
				}
	
			window.status="Suche nach " + sn + " abgeschlossen. " + parent.vssel_count + " Treffer";
	
			//datenanzeige aktualisieren
			if (parent.vssel_count==0){
				vs_setid(1);
			}
			else{
				parent.vssel_id=1;
				vs_setid(parent.vssel[parent.vssel_id]);
			}
			vsjs_showall();
	   	}
		else{
			for (i=1; i<jsdata_count+1; i++){
				var found=0;
				window.status="Suche nach '" + sn + "' in Datensatz " + i + " von " + jsdata_count;
				if ((""+vs_p[i][cpos]).toLowerCase().indexOf(sn,0) >= 0){
					window.status="";
					vs_setid(i);
					jsdata_showelements(0);
					return;
				}
			}
		}
	}


function vsjs_showall(){
		if(parent.frames.length!=0){
			for (j=0; j<parent.frames.length; j++){
				if(parent.frames[j].jsdata_reload){
					parent.frames[j].location.reload();
				}
				else{
					if(parent.frames[j].jsdata_show){
						parent.frames[j].jsdata_id=jsdata_id;
						parent.frames[j].jsdata_showelements(0);
					}
				}
			}
		}
		else{
			jsdata_showelements(0);
		}
	}



function vs_reload(id){
	if (jsdata_reload==true){
		if (id!=-1){
		    str=self.location.href;
			inn=str.indexOf('?');
			if (inn>0){
				str=str.substring(0,inn);
			}
			self.location.href = str + "?" + jsdata_id;
		}
	}
}


function vs_getidurl(vs_id){
    str=self.location.href;
    inn=str.indexOf('?');
    if (inn>0){
        vs_id=parseInt(str.substring(inn+1,str.length));
    }
    var vs_startid = vs_id;			
    return vs_id;
}

function vs_getvalue(vs_spalte,vs_add) {
	vs_id = jsdata_id + vs_add
	if (vs_id > jsdata_count){
		vs_id = vs_id - jsdata_count + 1
	}
	if (vs_id < 2){
		vs_id = vs_id + jsdata_count + 1
	}
	if (vs_id > jsdata_count) {
		vs_id = jsdata_count
	}
	var vs_spalteid = 0;
	for(var i = 1; i < vs_p[0].length+1; i++){
		if (vs_p[0][i]==vs_spalte){
			vs_spalteid = i;
		}
	}
	return(vs_p[vs_id][vs_spalteid]);
}

function vs_getvalue2(vs_spalteid,vs_id) {

	if (vs_id > jsdata_count){
		vs_id = vs_id - jsdata_count + 1
	}
	if (vs_id < 2){
		vs_id = vs_id + jsdata_count + 1
	}
	if (vs_id > jsdata_count) {
		vs_id = jsdata_count
	}
	return(vs_p[vs_id][vs_spalteid]);
}


function vs_setid(vs_newid) {
	jsdata_id = vs_newid;
	if (jsdata_id < 2) {
		jsdata_id = 2
	}
	if (jsdata_id > jsdata_count) {
		jsdata_id = jsdata_count
	}
}


function vs_getid(vs_add) {
	if(parent.frames.length!=0){
		if(parent.vssel_count>0){

			if (vs_add > parent.vssel_count){
				vs_add = parent.vssel_count; 
			}

			if (parent.vssel_id + vs_add > parent.vssel_count){
				vs_add = vs_add - parent.vssel_count;
			}

			return parent.vssel[parent.vssel_id + vs_add];
		}
	}

	vs_id = jsdata_id + vs_add
	if (vs_id > jsdata_count){
		vs_id = vs_id - jsdata_count + 1;
	}
	if (vs_id < 2){
		vs_id = vs_id + jsdata_count - 1;
	}
	if (vs_id > jsdata_count) {
		vs_id = jsdata_count
	}
	return vs_id;
}


function vs_addid(vs_add) {
	if(parent.frames.length!=0){
		if(parent.vssel_count>0){
			if (vs_add > parent.vssel_count){
				vs_add = parent.vssel_count; 
			}
			parent.vssel_id = parent.vssel_id + vs_add;
			if (parent.vssel_id > parent.vssel_count){
				parent.vssel_id = parent.vssel_id - parent.vssel_count; 
			}
			if (parent.vssel_id == 0){
				parent.vssel_id = 1; 
			}
			jsdata_id = parent.vssel[parent.vssel_id];
			return;
		}
	}
	
	vs_id = jsdata_id + vs_add
	if (vs_id > jsdata_count){
		vs_id = vs_id - jsdata_count + 1;
	}
	if (vs_id < 2){
		vs_id = vs_id + jsdata_count - 1;
	}
	if (vs_id > jsdata_count) {
		vs_id = jsdata_count;
	}
	jsdata_id = vs_id;
}



function vs_zeigebild(vs_add) {
    vs_add=vs_add+1;
    vs_id = vs_getid(vs_add);
    if (parent.inhalt){
        if (parent.inhalt.jsdata_id){
            parent.inhalt.vs_setid(vs_id);
            parent.inhalt.jsdata_showelements(0);
        }
        else{
            parent.inhalt.location='inhalt.htm?'+vs_id;
            parent.inhalt.vs_setid(vs_id);
            parent.inhalt.jsdata_showelements(0);
        }
      }
    else{
        var vs_wndbild = vs_open_window('inhalt.htm?'+vs_id,'wndbild','')
            vs_wndbild.vs_setid(vs_id);
            vs_wndbild.jsdata_showelements(0);
    }
}

function vs_zeigedatensatz(vs_id,vs_site,vs_target,vs_flags) {
	//vs_id=vs_id+1;
	if (vs_site==""){
		vs_site="inhalt.htm";
	}
	if (vs_target==""){
		vs_target="inhalt";
	}

	if (parent.inhalt){
            parent.inhalt.location=vs_site + '?' + (vs_id + 1);
            //parent.inhalt.vs_setid(vs_id+1);
            //parent.inhalt.jsdata_showelements(0);
      }
    	else{
        var vs_wndbild = vs_open_window(vs_site+'?' + (vs_id + 1),'wndbild','')
            //vs_wndbild.vs_setid(vs_id+1);
            //vs_wndbild.jsdata_showelements(0);
    	}
}

function vs_editwk(wkid){

          if (parent.inhalt){

              if (parent.inhalt.location.href==parent.vswk[wkid][parent.vswk[wkid].length-3]){
                  parent.inhalt.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length-2]);
                  parent.inhalt.jsdata_showelements(0);

              }
              else{
                  parent.inhalt.location=parent.vswk[wkid][parent.vswk[wkid].length-3]+'?'+parent.vswk[wkid][parent.vswk[wkid].length-2];
                  //parent.inhalt.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length]);
                  //parent.inhalt.jsdata_showelements(0);
			
              }
          }
          else{
              var vs_wndbild = vs_open_window('inhalt.htm?'+parent.vswk[wkid][parent.vswk[wkid].length],'wndbild','')
                  vs_wndbild.vs_setid(parent.vswk[wkid][parent.vswk[wkid].length]);
                  vs_wndbild.jsdata_showelements(0);
          }
      }

function vs_startshow(vs_wartezeit) {
	if (show_start == true){return;}
	show_start = true;
	vs_wz = vs_wartezeit;
	vs_addid(1);
	jsdata_showelements(0);
	vs_timeout_id = self.setTimeout('vs_nextshow(vs_wz)', vs_wartezeit * 1000)
}

function vs_nextshow(vs_wartezeit) {
	vs_wz = vs_wartezeit;
	vs_addid(1);
	jsdata_showelements(0);
	vs_timeout_id = self.setTimeout('vs_nextshow(vs_wz)', vs_wartezeit * 1000)
}

function vs_stopshow() {
	show_start = false;
	self.clearTimeout (vs_timeout_id)
}

//Datenliste
vs_p[0]= new initArray("Spaltenname","quellbild");
vs_p[1]= new initArray("Spaltenart","quellbild");



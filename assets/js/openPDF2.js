var crenauxNormal = [5,13,21];
var crenauxVendredi = [5,13,20];
var crenauxSamedi = [5,17,20];
var ongletsFils = Array();

function open_pdf_presse(e){
	var dossiers="";
	var url ="";
	tabTypeProd = Array('injection','micro_chaine','ilot_packaging');
	tabTypeDoc = Array('Incident_Qualite','Nouvelle_Information');


tabTypeProd.forEach(function(currentValue,index,arr){
	dossiers=currentValue+'/';
	tabTypeDoc.forEach(function(currentValue2,index2,arr2){
		dossiersTMP=dossiers+currentValue2;
		url = "http://slanwp0167/docAuPoste/assets/pdf/"+dossiersTMP+'/'+$(e).attr('href')+".pdf";
		//var url= "http://slanwp0081/direct.aspx?usersid=22&token=3MkBzI%2Bm9u1ftgcuqzGXbw5lFU62edPQ4tnxjTCsGfYKhA1Qqs6LvQ%3D%3D&module=doc"+$(e).attr('href')"&dossier=2";
		//url= "http://vivaldi_ruitz/retr_doc.aspx?code="+$(e).attr('href')"&dossier=2";
		var http = new XMLHttpRequest();
		http.open('HEAD', url, false);
		http.send();
		if (http.status == 404){
			// l'address n'a pas aboutie
		}
		else{
			return open_a_window(url);
		}
	});

});
return false;
}

/*Pour avoir l'adresse du document stocké sur Vivaldi, changer ici si changement de serveur*/
function open_pdf_vivaldi(e){
	console.log(e.textContent);
	/*Exeption pour ces types. chercher dans le dossier pdf du serveur*/
	if( e.textContent == "7. CVB" ||
		e.textContent == "9. Houssage" ||
		e.textContent == "8. Ctrl final" ||
		e.textContent == "2. Ctrl - Jupe street" ||
		e.textContent == "2. Ctrl - Enjoliveur latéraux" ||
		e.textContent == "2. Ctrl - Peaux centrales et latérales" ||
		e.textContent == "1. Déchargement - Enjoliveur lateraux" ||
		e.textContent == "1. Déchargement - Jupe street" ||
		e.textContent == "1. Déchargement - Grille Supérieure V1-V2" ||
		e.textContent == "1. Déchargement - Peaux centrales et latérales" ||
		e.textContent == "D41 - Equipe Verte - ASS" ||
		e.textContent == "D41 - Equipe Rouge - ASS" ||
		e.textContent == "D41 - Equipe Bleue - ASS" ||
		e.textContent == "10. Mise en rack" ||
		e.textContent == "" ||
		e.textContent == "4. Préparation de la grille inférieure" ||
		e.textContent == "3. Vissage des DRL" ||
		e.textContent == "2. Poinçonnage et soudures des peaux centrales" ||
		e.textContent == "1. Soudure des peaux laterales" ||
		e.textContent == "Défauthèque - D41" ||
		e.textContent == "Défauthèque - T91_T92" ||
		e.textContent == "Défauthèque - P87" ||
		e.textContent == "Défauthèque - P8MV" ||
		e.textContent == "Défauthèque - T9MV" ||
		e.textContent == "4. Transstockeur" ||
		e.textContent == "2. Ctrl - Peau & Softnose" ||
		e.textContent == "3. Assemblage -  cadre de grille sup" ||
		e.textContent == "2. Ctrl - Peau & Softnose" ||
		e.textContent == "2. Ctrl - Grilles inférieures latérales" ||
		e.textContent == "2. Ctrl - Grilles de calandre latérales " ||
		e.textContent == "1.Déchargement - Grilles de calandre latérales" ||
		e.textContent == "1. Déchargement - Peau & Softnose" ||
		e.textContent == "1 - Déchargement -  Grilles inférieures latérales" ||
		e.textContent == "7 - Houssage" ||
		e.textContent == "6 - Ctrl final" ||
		e.textContent == "5 - CVB" ||
		e.textContent == "4 - Soudure Jupe" ||
		e.textContent == "3 - CVH" ||
		e.textContent == "2 - Carrousel" ||
		e.textContent == "1 - Transstockeur" ||
		e.textContent == "Matrice ILUO INTERIMS" ||
		e.textContent == "Matrice ILUO PO" ||
		e.textContent == "MOP - P8MV" ||
		e.textContent == "MOP - T9MV" ||
		e.textContent == "MOP - D41" ||
		e.textContent == "P8MV - Equipe Verte - ASS" ||
		e.textContent == "P8MV - Equipe Rouge - ASS" ||
		e.textContent == "P8MV - Equipe Bleue - ASS" ||
		e.textContent == "SC_T9MV_HDG_01" ||
		e.textContent == "SC_T9MV_HDG_02" ||
		e.textContent == "SC_T9MV_EDG_01" ||
		e.textContent == "SC_T9MV_EDG_02" ||
		e.textContent == "SC_T92_PAN_02" ||
		e.textContent == "SC_T92_PAN_01" ||
		e.textContent == "SC_T91_PAN_02" ||
		e.textContent == "SC_T91_PAN_01" ||
		e.textContent == "SC_CR_P87_02" ||
		e.textContent == "SC_CR_P87_01" ||
		e.textContent == "OP7" ||
		e.textContent == "OP6" ||
		e.textContent == "OP5" ||
		e.textContent == "OP4" ||
		e.textContent == "OP3" ||
		e.textContent == "OP2" ||
		e.textContent == "6. CVH" ||
		e.textContent == "P8MV - Equipe Verte - DR" ||
		e.textContent == "PDCA" ||
		e.textContent == "5. Préparation de la grille supérieure V1 et V2" ||
		e.textContent == "UAPM_PRESSE_AUTO_HUILE_12TONNES" ||
		e.textContent == "P8MV - Equipe Rouge - DR" ||
		e.textContent == "P8MV - Equipe Bleue - DR" ||
		e.textContent == "Bisous" ||
		e.textContent == "Working Instruction N1" ||
		e.textContent == "UAPM_PRESSE_AUTO_CODIPRO" ||
		e.textContent == "Emplacement étiquette traçabilité" ||
		e.textContent == "Matrice ILUO" ||
		e.textContent == "FS" ||
		e.textContent == "Matrice DRR" ||
		e.textContent == "Tableau de marche" ||
		e.textContent == "Tableau Diversite" ||
		e.textContent == "T9 MV OP6" ||
		e.textContent == "T9 MV OP5 HDG" ||
		e.textContent == "T9 MV OP5 EDG" ||
		e.textContent == "T9 MV OP4 HDG" ||
		e.textContent == "T9 MV OP4 EDG" ||
		e.textContent == "T9 MV OP3 HDG" ||
		e.textContent == "T9 MV OP3 EDG" ||
		e.textContent == "T9 MV OP2" ||
		e.textContent == "T9 MV OP1" ||
		e.textContent == "DEFAUTHEQUE" ||
		e.textContent == "FS - PR_CR_P87_01" ||
		e.textContent == "FS - PR_CR_P87_02" ||
		e.textContent == "FS - PR_CR_P87_03" ||
		e.textContent == "FS - PR_CR_P87_03 - Préparation" ||
		e.textContent == "PR_CR_P87_03 - Préparation" ||
		e.textContent == "PR_CR_P87_03" ||
		e.textContent == "PR_CR_P87_02" ||
		e.textContent == "PR_CR_P87_01" ||
		e.textContent == "SWI-SQP" ||
		e.textContent == "FS - SQP" ||
		e.textContent == "ILUO TSK PO" ||
		e.textContent == "ILUO TSK INTERIM" ||
		e.textContent == "ILUO DR INTERIM" ||
		e.textContent == "ILUO DR PO" ||
		e.textContent == "Panneaux" ||
		e.textContent == "P84" ||
		e.textContent == "T9MV" ||
		e.textContent == "A94" ||
		e.textContent == "P87" ||
		e.textContent == "Planning Test de FARNSWORTH" ||
		e.textContent == "Suivi mesures LUX DR" ||
		e.textContent == "Gestion des non-conformes" ||
		e.textContent == "FS - REPRISE" ||
		e.textContent == "FS - PEIN_STO_P84_Enjo" ||
		e.textContent == "FS - PEIN_DECH_P84" ||
		e.textContent == "FS - PEIN_CTRL_P84" ||
		e.textContent == "FS - PEIN_CTRL_ENJO_P84" ||
		e.textContent == "FS - PEIN_Bandeaux" ||
		e.textContent == "FS - DEVIDOIR_REP" ||
		e.textContent == "FS - DECH_T9MVx_02" ||
		e.textContent == "FS - DECH_T9MVx_01" ||
		e.textContent == "FS - DECH_P87_02" ||
		e.textContent == "FS - DECH_P87_01" ||
		e.textContent == "FS - DECH_H9x_02" ||
		e.textContent == "FS - DECH_H9x_01" ||
		e.textContent == "DECH_P87_02" ||
		e.textContent == "DECH_P87_01" ||
		e.textContent == "DECH_T9MVx_02" ||
		e.textContent == "DECH_T9MVx_01" ||
		e.textContent == "DECH_H9x_02" ||
		e.textContent == "DECH_H9x_01" ||
		e.textContent == "DEVIDOIR_REP" ||
		e.textContent == "REPRISE" ||
		e.textContent == "Equipe Rouge" ||
		e.textContent == "Equipe Verte" ||
		e.textContent == "Equipe Bleue" ||
		e.textContent == "Enregistrement de formation" ||
		e.textContent == "T9MV HDG DECOUPE" ||
		e.textContent == "T9MV HDG 01" ||
		e.textContent == "T9MV EDG IMANY SANS DECOUPE" ||
		e.textContent == "T9MV EDG DECOUPE" ||
		e.textContent == "T9MV EDG 01" ||
		e.textContent == "Tableau de marche" ||
		e.textContent == "OK demarrage" ||
		e.textContent == "SQP" ||
		e.textContent == "Tableau Diversite" ||
		e.textContent == "Working Instruction HDG" ||
		e.textContent == "Working Instruction EDG" ||
		e.textContent == "Almaflex HDG" ||
		e.textContent == "Almaflex EDG" ||
		e.textContent == "Transstockeur" ||
		e.textContent == "Almaflex" ||
		e.textContent == "Matrice Verte" ||
		e.textContent == "Matrice Rouge" ||
		e.textContent == "Matrice Bleue" ||
		e.textContent == "Working Instruction" ||
		e.textContent == "SWI P54 Absorbeur" ||
		e.textContent == "SWI P54 ENJO DRL" ||
		e.textContent == "SWI P54 CVB" ||
		e.textContent == "SWI P54 CVH 04" ||
		e.textContent == "Défauthèque - P54" ||
                e.textContent == "SWI P54 mise en rack" ||
		e.textContent == "SWI P54 CVH 05" ||
		e.textContent == "SWI P54 Caméra NV" ||
		e.textContent == "SWI P54 GP12" ||
       	        e.textContent == "SWI P54 Contrôle final" ||
		e.textContent == "SWI P54 Kitting" ||
		e.textContent == "ILUO P54 Rouge" ||
		e.textContent == "ILUO P54 Bleue" ||
                e.textContent == "ILUO P54 Verte" ||
		e.textContent == "ILUO Enjoliveurs P51 Verte" ||
		e.textContent == "ILUO Enjoliveurs P51 Rouge" ||
		e.textContent == "ILUO Enjoliveurs P51 Bleue" ||
		e.textContent == "Enjoliveurs P51 - Equipe Bleue" ||
		e.textContent == "Enjoliveurs P51 - Equipe Rouge" ||
		e.textContent == "Enjoliveurs P51 - Equipe Verte"

		) {
return open_pdf_presse(e);
	}else{
		var url = "http://slanwp0167/docAuPoste/utilisateur/openPDF2/"+$(e).attr('href');
		//var url= "http://slanwp0081/";
		//var url= "http://vivaldi_ruitz/retr_doc.aspx?code="+$(e).attr('href')"&dossier=2";
		return open_a_window(url);
	}

}

/*function UrlExists(url) {
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    if (http.status != 404)
        //  do something
    else
        window.location.reload();
}*/

function open_a_window(url)
{

   var tt = window.open(url);

ongletsFils.push(tt);

	 var today = new Date();
   var now = Date.now();
	 var d = today.getDay();//0 dimanche 1 lundi
   var day = today.getDate();//le jour 1-31
   var year = today.getFullYear();
   var month = today.getMonth();//0-11
  var hour = today.getHours();
//  var minute = today.getUTCMinutes();
var next = -1;
var crenaux = crenauxNormal;
if(d==5){
crenaux = crenauxVendredi;
}
else if (d==6) {
	crenaux = crenauxSamedi;
}

/*Pour choisire la fin de creneau qui vien just aprés*/
  for (var i = 0; i < crenaux.length; i++) {
    next = crenaux[i];
    if(next>hour){
      i+=crenaux.length;
    }
    else if (i==crenaux.length-1) {
      next=crenaux[0];
    }
  }

	 var fin = new Date(year, month, day, next, 0, 0);
console.log(now,fin,fin-now);
if(fin-now<0){

  fin=Date.parse(fin)+(1000*60*60*24); //decaller au lendemain
}
console.log(now,fin,fin-now);

//Regler une action a la fermeture de la page parente
window.onbeforeunload = function(){
	ongletsFils.forEach(function(onglet) {
		console.log(onglet);
  	onglet.close();
	});
};

/*Action à la fin du "contarbour"*/
setTimeout(function(){
     tt.close();
		  location.reload();
}, fin-now);
   return false;


}

// JavaScript Document
let $grillainstrumento=$('#grillainstrumento');
let $resultado=$('#resultado');
let vector=new Array(-1,-1,-1,-1,-1,-1,-1) // Este array representa a las cuerdas, tiene siete elementos para corresponder la posición con el número de la cuerda, así la posición 0 es solo "decorativa"

$grillainstrumento.on('click','div div',function(){
	let ide = $(this).attr('id');
	let cide = ide.substr(1,1);
	let tide = ide.substr(3,2);
	let a;
	for(a=1;a<7;a++) {
		if(a==cide) {
			if(vector[a]>-1) {
				if(vector[a]!=tide) {
					marcardesmarcar(-1,a,vector[a]); //desmarca la última nota de la cuerda pulsada que es diferente a la actual
					$('#c'+a+"t"+vector[a]).text("");
					marcardesmarcar(1,cide,tide); //marca la nota de la cuerda actual
					vector[a]=tide;
				}
				else {
					marcardesmarcar(-1,cide,tide); //desmarca la nota de la cuerda pulsada anteriormente;
					$('#c'+a+"t"+vector[a]).text("");
					vector[a]=-1;
				}				
			}
			else {
				marcardesmarcar(1,cide,tide); //marca la nota de cuerda seleccionada
				vector[a]=tide;
			}
		}
	}
	$.ajax({	
		url: "php/acordesmanual.php",
		type: "POST",
		dataType: 'json',
		data: {'arreglo' : vector},
		success: function (resul)
		{
			for(p=1;p<7;p++) {
				$('#c'+p+'t'+vector[p]).text(resul.nomnot[p]);
			}
			let nora=resul.nraiz;
			let acor=resul.mresu[0];
			if(resul.largo>1) {
				$resultado.text(nora+acor);
			}
			else {
				$resultado.text(nora);
			}
			console.log(resul.mrela);
			console.log(resul.largo+"____"+resul.lar);
			console.log(resul.mresu);
		}
	});
	//console.log(vector);
});

//Funciones
function marcardesmarcar(a,b,c) {
	if(a==-1) {
		if(c==0) {
			$('#'+"c"+b+"t"+c).removeClass("cpna").addClass("cxn");
		}
		else {
			$('#'+"c"+b+"t"+c).removeClass("cpnp").addClass("cxn");
		}
	}
	else {
		if(c==0) {
			$('#'+"c"+b+"t"+c).removeClass("cxn").addClass("cpna");
		}
		else {
			$('#'+"c"+b+"t"+c).removeClass("cxn").addClass("cpnp");
		}
	}
}

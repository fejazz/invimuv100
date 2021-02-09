<?php 
$conect = mysqli_connect("localhost", "genprogcl", "cvr4Def502", "genprogcl_bdato"); ?>
<?php 
//sort
header("Content-Type: text/html;charset=utf-8");
header('Content-Type: application/json'); 
mysqli_set_charset($conect, "utf8");

$idarray=$_POST['arreglo']; //el arreglo enviado desde js
$textonota=array('Do','Do#','Re','Re#','Mi','Fa','Fa#','Sol','Sol#','La','La#','Si'); //arreglo con el nombre de las notas (se puede extender luego a un array de 2 dimensiones que incluyendo la nomenclatura en ingles y la salida en bemol)
$notastexto=array(); //arreglo que contendrá el texto de la nota pulsada en cada cuerda
$afinacionnota=array(-1,4,11,7,2,9,4); // la afinación de nota inicial de cada cuerda (se puede extender luego en un array con tope para afinación inferior y superior de cada cuerda)
$afinacionoctava=array(-1,4,3,3,3,2,2); // la octava de la nota inicial en cada cuerda (con extensión al igual que el array anterior)
$grillanota=array(); //el arreglo de las notas en la grilla
$grillaoctava=array(); //el arreglo de las octavas de las notas en la grilla
//los array a continuación sirven para cumplir el ciclo de trasladar la relación a inversiones
$mat1=array(); $mat2=array(); $mat3=array(); $mat4=array(); 

//El ciclo for constructor de la grilla del instrumento ( la idea es que este ciclo solo se ejecute al inicio o cuando la aplicación detecte que el usuario cambio de afinación o de instrumento)
for($i=1;$i<7;$i++) {
	$grillanota[$i]=array();
	$grillaoctava[$i]=array();
	for($j=0;$j<7;$j++) {
		if($j==0) {
			$grillanota[$i][$j]=$afinacionnota[$i];
			$grillaoctava[$i][$j]=$afinacionoctava[$i];
		}
		else {
			$grillanota[$i][$j]=($afinacionnota[$i]+$j)%12;
			if($grillanota[$i][$j]==0) {
				$grillaoctava[$i][$j]=$grillaoctava[$i][$j-1]+1;
			}
			else {
				$grillaoctava[$i][$j]=$grillaoctava[$i][$j-1];
			}
		}
	}
}
//El ciclo for que asigna el texto de la nota pulsada en cada cuerda y guarda valores para establecer la tónica
$vnotoct=array(); //arreglo que contendrá el valor de la nota en relación a su octava
$vnota=array(); //arreglo que contiene la nota de la cuerda como número
for($k=1;$k<7;$k++) {
	if($idarray[$k]!=-1) {
		$notastexto[$k]=$textonota[$grillanota[$k][$idarray[$k]]];
		$vnotoct[$k]=(10*$grillanota[$k][$idarray[$k]])+(1000*$grillaoctava[$k][$idarray[$k]]);
		$vnota[$k]=$grillanota[$k][$idarray[$k]];
	}
	else {
		$notastexto[$k]='';
		$vnotoct[$k]=99999;
		$vnota[$k]=-1;
	}
}

$notaraiz=min($vnotoct); //se identífica la nota más baja en cuanto a octava (segun la fórmula aplicada)
$notaraiznum=round(100*($notaraiz/1000-floor($notaraiz/1000))); //se extrae el valor numérico de la nota
$nr=$notaraiznum*1; //se asegura de tener un valor numerico...
$nrtex=$textonota[$nr]; //Se extrae el texto de la nota "tonica" (no se considera aún inversiones)

//Funciones para la traslación del arreglo de entrada a relación interválica
$mat1[0]=0; //Se declara la raíz de la relacion interválica
// El ciclo for a continuación evalua que la cuerda este pulsada y además compara a todas las notas que son distintas a la raíz y les asigna la relación de intervalo que tienen con la tónica o raíz
for($t=1;$t<7;$t++) {
	if($vnota[$t]!=-1) {
		if($vnota[$t]<$nr) {
			$mat1[$t]=(12+$vnota[$t])-$nr;
		}
		if($vnota[$t]>$nr) {
			$mat1[$t]=$vnota[$t]-$nr;
		}
	}
}
sort($mat1); //se ordena el arreglo mat1 para que quede de menor a mayor
$largo=count($mat1); //se establece el largo "total" de las relaciones encontradas la que puede venir con repeticiones
$mat2[0]=0; //este arreglo es el que va a contener la relación definitiva sin duplicación
$lar=0; //se establece un contador para el largo definitivo
//El ciclo for a continuación evalua que las relaciones de intervalo con la tónica no se repitan.
for($a=0;$a<$largo;$a++) {
	if($a>0) {
		if($mat1[$a]!=$mat1[$a-1]) {
			$mat2[$lar]=$mat1[$a];
			$lar=$lar+1;
		}
	}
}
//El siguiente ciclo for es el que construye todas las relaciones de inversión, incluida la original y la llevan a un array bidimensional trasladando para cada inversión la tónica y generando una nueva relación de intervalo para cada nota como raíz respecto del resto
for($r=0;$r<$lar+1;$r++) {
	$mat3[$r]=array();
	for($w=0;$w<$lar+1;$w++) {
		if($r==0) {
			$mat3[0][$w]=$mat2[$w];
		}
		else {
			if($mat2[$r]==$mat2[$w]) {
				$mat3[$r][$w]=0;
			}
			else {
				if($mat2[$w]<$mat2[$r]) {
					$mat3[$r][$w]=(12+$mat2[$w])-$mat2[$r];
				}
				if($mat2[$w]>$mat2[$r]) {
					$mat3[$r][$w]=$mat2[$w]-$mat2[$r];
				}
			}
		}
	}
	sort($mat3[$r]); //se ordena de menor a mayor cada uno de los arreglos que contienen las inversiones y la relación original
}

$aco=array(); //se crea un arreglo que contendrá la relación interválica como salida de texto ejemplo 0-3-7-10. Además se lleva la relación interválica de mat3 a una cadena o string para mat4, la que luego servirá para comparar con base de datos
for($e=0;$e<$lar+1;$e++)
{
	for($f=0;$f<$lar+1;$f++) {
		$mat4[$e].=numatex($mat3[$e][$f]);
		if($f>0) {
			$aco[$e].="-".$mat3[$e][$f];
		}
	}
}

$njera1=-1; //se establece nivel de jerarquía -1
$limite=2; //se establece límite para ciclo Do-While (la idea es que no busque posibilidades de acordes de 6 notas si solo se están pulsadando 3 por ejemplo donde solo tendrá que recorrer 68 filas)
if($lar+1==2) { $limite=13; }
if($lar+1==3) { $limite=68; }
if($lar+1==4) { $limite=233; }
if($lar+1==5) { $limite=563; }
if($lar+1==6) { $limite=1025; }
if($lar+1==7) { $limite=1487; }
$query_smaco = "SELECT * FROM ivm_aco_man WHERE id<'$limite'" or die("Error..." . mysqli_error($conect));
$smaco = $conect->query($query_smaco);
$row_smaco = mysqli_fetch_assoc($smaco);
$totalRows_smaco = mysqli_num_rows($smaco);
do 
{
	if($lar+1>2)
	{
		for($q=0;$q<$lar+1;$q++) {
			//se compara la cadena o string resultante de la relación con la base de datos se asigna la jerarquía y el nombre del acorde (A esta operación aún le falta depuración y depende de como se manejará el marco de referencia y la muestra de inversiones)
			if($mat4[$q]==$row_smaco['cad']) { 
				$njera1=$row_smaco['jer1']; 
				if($row_smaco['nom1']!="") { 
				$aco[$q]=$row_smaco['nom1']; 
				} 
				else { 
					if($row_smaco['nom2']!="") { 
						$aco[$q]=$row_smaco['nom2']; 
					}
				}
			}
		}		
	}
} while ($row_smaco = mysqli_fetch_assoc($smaco));
//Funciones auxiliares de conversión de numero a texto y de texto a número
function numatex($ent)
{
	if($ent==-1) { return "a"; } if($ent==0) { return "b"; } if($ent==1) { return "c"; } if($ent==2) { return "d"; } if($ent==3) { return "e"; } if($ent==4) { return "f"; } if($ent==5) { return "g"; } if($ent==6) { return "h"; } if($ent==7) { return "i"; } if($ent==8) { return "j"; } if($ent==9) { return "k"; } if($ent==10) { return "l"; } if($ent==11) { return "m"; } if($ent==12) { return "n"; } if($ent==13) { return "o"; } if($ent==14) { return "p"; } if($ent==15) { return "q"; } if($ent==16) { return "r"; } if($ent==17) { return "s"; } if($ent==18) { return "t"; } if($ent==19) { return "u"; } if($ent==20) { return "v"; } if($ent==21) { return "w"; }
}
function texanum($ent1)
{
	if($ent1=="a") { return -1; } if($ent1=="b") { return 0; } if($ent1=="c") { return 1; } if($ent1=="d") { return 2; } if($ent1=="e") { return 3; } if($ent1=="f") { return 4; } if($ent1=="g") { return 5; } if($ent1=="h") { return 6; } if($ent1=="i") { return 7; } if($ent1=="j") { return 8; } if($ent1=="k") { return 9; } if($ent1=="l") { return 10; } if($ent1=="m") { return 11; } if($ent1=="n") { return 12; } if($ent1=="o") { return 13; } if($ent1=="p") { return 14; } if($ent1=="q") { return 15; } if($ent1=="r") { return 16; } if($ent1=="s") { return 17; } if($ent1=="t") { return 18; } if($ent1=="u") { return 19; } if($ent1=="v") { return 20; }if($ent1=="w") { return 21; }
}

$mat1v="hola";
$ftonre=array('nomnot'=>$notastexto,'nraiz'=>$nrtex,'mresu'=>$aco,'largo'=>$largo,'lar'=>$lar,'mrela'=>$mat1);
echo json_encode($ftonre);
?>
<?php
mysqli_close($conect);
?>
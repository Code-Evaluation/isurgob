<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use \yii\widgets\Pjax;
use \yii\bootstrap\Modal;
use yii\jui\DatePicker;
use app\utils\db\Fecha;
use app\utils\db\utb;
use yii\bootstrap\Alert;
use yii\web\Session;
use yii\data\ArrayDataProvider;

/**
 * Forma que se dibuja cuando se quiere dar de Alta una DJ.
 * Recibo:
 * 			=> $model -> Modelo
 * 			=> $dataProvider -> Datos para la grilla
 *			=> $consulta es una variable que:
 *			 		=> $consulta == 1 => El formulario se dibuja en el index
 *			  		=> $consulta == 0 => El formulario se dibuja en el create
 *			  		=> $consulta == 3 => El formulario se dibuja en el update
 *			  		=> $consulta == 2 => El formulario se dibuja en el delete
 *
 *			=> $fiscaliza -> Por defecto es 0. Se obtiene de la variable en sesión "FiscalizaActiva"
 */

$session = new Session;

$session->open();

$fiscaliza = $session->get('FiscalizaActiva',0);

$session->close();

$form = ActiveForm::begin([
	'id'=>'frmAltaDDJJ',
	//'action'=>['view'],
	]);


 echo Html::input('hidden','txFiscaliza',$fiscaliza,['id'=>'txRubroFiscaliza']);

 //INICIO Bloque actualiza los códigos de objeto
Pjax::begin(['id' => 'ObjNombre']);

	$objeto_id = Yii::$app->request->post('objeto_id','');
	$trib = Yii::$app->request->post('trib',0);

	if ($trib != 0)
	{
		$tobj = utb::getTTrib($trib);

		//Completo el nombre del objeto en caso de que no se ingrese completo
		if (strlen($objeto_id) < 8 && $objeto_id != '')
		{
			$objeto_id = utb::GetObjeto((int)$tobj,(int)$objeto_id);
			echo '<script>$("#ddjj_txObjetoID").val("'.$objeto_id.'")</script>';
		}

		if (utb::GetTObj($objeto_id) == $tobj)
		{
			$objeto_nom = utb::getNombObj("'".$objeto_id."'");

			echo '<script>$("#ddjj_txObjetoNom").val("'.$objeto_nom.'")</script>';

			//Pongo el foco en anio
			?>
			<script>
			$("#PjaxObjBusAvbuscaDDJJ").on("pjax:end",function() {

				$("#ddjj_txAnio").focus();
				$("#PjaxObjBusAvbuscaDDJJ").off("pjax:end");
			});
			</script>
			<?php

		} else
		{
			echo '<script>$("#ddjj_txObjetoID").val("")</script>';
			echo '<script>$("#ddjj_txObjetoNom").val("")</script>';
		}

		$subcta = utb::getCampo('trib','trib_id = ' . $trib,'uso_subcta');

		//Habilitar sucursal si trib.uso_subcta = 1
		if ($subcta == 1)
			echo '<script>$("#ddjj_txSucursal").removeAttr("readOnly");</script>';
		else
			echo '<script>$("#ddjj_txSucursal").attr("readOnly",true);</script>';

		//Actualiza el tipo de objeto para la búsqueda de objeto
		?>
		<script>
		$("#ObjNombre").on("pjax:end",function() {

				$.pjax.reload({
					container:"#PjaxObjBusAvbuscaDDJJ",
					data:{
						tobjeto:<?=$tobj?>,
					},
					method:"POST"
				});
				$("#ObjNombre").off("pjax:end");
			});
		</script>
		<?php
		echo '<script>$(document).ready(function() {});</script>';

	}

Pjax::end();
//FIN Bloque actualiza los códigos de objeto

?>

<style>
.form-panel
{
	margin-right: 0px;
}
</style>

<div id="ddjj_info" style="margin-right: 15px">

<div class="form-panel" style="padding-right:8px;padding-bottom: 8px">

<table border="0px">
	<tr>
		<td width="50px"><label>Tributo:</label></td>
		<td width="308">

			<?= Html::hiddenInput('dlTrib', $model->trib_id, ['id' => 'ddjj_trib']); ?>
			<?= Html::dropDownList('dlTrib', null, utb::getAux('trib','trib_id','nombre',0,"tipo = 2 AND dj_tribprinc = trib_id AND est = 'A'"), [
				'id'=>'ddjj_dlTrib',
				'style' => 'width:100%',
				'class' => 'form-control',

				'onchange'=>'$("#ddjj_trib").val($(this).val()); $.pjax.reload({container:"#ObjNombre",data:{objeto_id:$("#ddjj_txObjetoID").val(),trib:$(this).val()},method:"POST"})'
			]); ?>
		</td>
		<td width="10px"></td>
		<td width="40px"><label>Orden:</label></td>
		<td><?= Html::input('text','txOrden',null,['id'=>'ddjj_txOrden','class'=>'form-control','style'=>'width:113px;text-align:center','readOnly'=>true]) ?></td>
	</tr>
</table>

<table >
	<tr>
		<td width="50px"><label>Objeto:</label></td>
		<td><?= Html::input('text','txObjetoID',$model->obj_id,['id'=>'ddjj_txObjetoID','class'=>'form-control','style'=>'width:70px;text-align:center',
							'onchange'=>'$.pjax.reload({container:"#ObjNombre",data:{objeto_id:$(this).val(),trib:$("#ddjj_dlTrib").val()},method:"POST"})']); ?>
		</td>
		<td>
		<!-- botón de búsqueda modal -->
			<?php
			//INICIO Modal Busca Objeto
			Modal::begin([
			'id' => 'BuscaObj',
			'header' => '<h2 style="font-family:Helvetica Neue, Helvetica, Arial, sans-serif;">Buscar Objeto</h2>',
			'size' => 'modal-lg',
			'toggleButton' => [
				'label' => '<i class="glyphicon glyphicon-search"></i>',
				'class' => 'bt-buscar',
				'id'=>'btnDomParcela'
			],
			'closeButton' => [
			  'label' => '<b>X</b>',
			  'class' => 'btn btn-danger btn-sm pull-right',
			],
			 ]);

			echo $this->render('//objeto/objetobuscarav',[
										'idpx' => 'buscaDDJJ',
										'id' => 'ddjjaltaBuscar',
										'txCod' => 'ddjj_txObjetoID',
										'txNom' => 'ddjj_txObjetoNom',
										'selectorModal' => '#BuscaObj',
					        		]);

			Modal::end();
			//FIN Modal Busca Objeto

			?>

			<!-- fin de botón de búsqueda modal -->
		</td>
		<td width="210px" colspan="2"><?= Html::input('text','txObjetoNom',utb::getNombObj("'".$model->obj_id."'"),['id'=>'ddjj_txObjetoNom','class'=>'form-control','style'=>'width:210px;text-align:left','readOnly'=>true]) ?></td>
		<td width="10px"></td>
		<td width="40px"><label>Suc:</label></td>
		<td>
			<?= Html::input('text','txSucursal',null,['id'=>'ddjj_txSucursal','class'=>'form-control','style'=>'width:30px;text-align:center','readOnly'=>true]) ?>
		</td>
		<td width="10px"></td>
		<td>
			<label>Año:</label>
			<?= Html::input('text','txAño',null,['id'=>'ddjj_txAnio','class'=>'form-control','style'=>'width:40px;text-align:center','onkeypress'=>'return justNumbers(event)','maxlength'=>4]) ?>
		</td>
		<td width="10px"></td>
		<td>
			<label>Cuota:</label>
			<?= Html::input('text','txCuota',null,['id'=>'ddjj_txCuota','class'=>'form-control','style'=>'width:40px;text-align:center','onkeypress'=>'return justNumbers(event)','maxlength'=>2]) ?>
		</td>
	</tr>
</table>

<table border="0">
	<tr>
		<td width="90px"><label>Presentación:</label></td>
		<td>

			<?php
				Pjax::begin(['id'=>'PjaxFchPresentacion']);

					$fecha = Yii::$app->request->post('fecha',Fecha::getDiaActual());

					$fecha = Fecha::usuarioToDatePicker($fecha);

					echo DatePicker::widget([
							'id' => 'ddjj_fchpresentacion',
							'name' => 'fchpresentacion',
							'dateFormat' => 'dd/MM/yyyy',
							'options' => ['class' => 'form-control'.(utb::getExisteProceso(3333) == 1 ? '' : ' solo-lectura'), 'style' => 'width:80px;text-align:center',
								'tabindex'=>(utb::getExisteProceso(3333) == 1 ? '' : '-1')],
							'value' => $fecha,
						]);

				Pjax::end();
						?>
		</td>
		<td width="32px"></td>
		<td align="left"><label>Vencimiento:</label></td>
		<td width="80px">
			<?= DatePicker::widget([	'id' => 'ddjj_fchvencimiento',
												'name' => 'fchvencimiento',
												'dateFormat' => 'dd/MM/yyyy',
												'options' => ['class' => 'form-control solo-lectura','tabindex'=>'-1', 'style' => 'width:80px'],
												'value' => '',
											]);	?>
		</td>
		<td width="10px"></td>
		<td width="40px"><label>Tipo:</label></td>
		<td width="110px">
			<?php $tipos= utb::getAux('ddjj_tipo','cod','nombre',0,($fiscaliza == 0 ? "cod in (1,2)" : "cod in (1,2,3)")); ?>
			<?= Html::hiddenInput('dlTipo', key($tipos), ['id' => 'ddjj_tipo']); ?>
			<?= Html::dropDownList('dlTipo', null, $tipos,
			[	'style' => 'width:100%',
				'class' => 'form-control',
				'id'=>'ddjj_dlTipo',
				'onchange' => '$("#ddjj_tipo").val($(this).val());'
			]); ?>
		</td>
		<td width="55px"></td>
		<td align="right"><?= Html::button('<span class="glyphicon glyphicon-play"></span>',['id'=>'btCargar1','class' => 'btn btn-success','onclick'=>'btCargar(1,[],[],[], 0)']); ?></td>
	</tr>
</table>
</div>

<?php

	ActiveForm::end();

//INICIO Bloque cargar datos
Pjax::begin(['id'=>'DDJJ_cargarDatos']);

	$error = '';

	if (isset($_POST['trib']))
	{

		//Si se presiona el botón Cargar, no debería haber datos en sesión, por lo que se borran
		Yii::$app->session->set( 'DDJJArregloRubros', [] ); //Arreglo de Rubros
		Yii::$app->session->set( 'DDJJArregloItems', [] );
		Yii::$app->session->set( 'DDJJBaseTotal', '' );
		Yii::$app->session->set( 'DDJJMontoTotal', '' );
		Yii::$app->session->set( 'DDJJRubrosAndTributos', [] );	//Arreglo para mostrar en la grilla

		//Obtengo los datos
		$trib 				= Yii::$app->request->post( 'trib', 0 );
		$objeto_id 			= Yii::$app->request->post( 'objeto_id', '' );
		$objeto_nom 		= Yii::$app->request->post( 'objeto_nom', '' );
		$anio 				= Yii::$app->request->post( 'anio', 0 );
		$cuota 				= Yii::$app->request->post( 'cuota', 0 );
		$tipo_nom 			= Yii::$app->request->post( 'tipo', '' );
		$fchpresenta 		= Yii::$app->request->post( 'fchpresenta', '' );
		$fchvencimiento 	= Yii::$app->request->post( 'fchvencimiento' , '' );
		$verificaPeriodo 	= Yii::$app->request->post( 'verificaPeriodo', 0 );
		$base 				= Yii::$app->request->post( 'base', [] );
		$cant 				= Yii::$app->request->post( 'cant', [] );
		$codigo 			= Yii::$app->request->post( 'codigo', [] );
		$calculaDDJJ 		= Yii::$app->request->post( 'calculaDDJJ', 0 );
		$aplicaBonificacion = Yii::$app->request->post( 'aplicaBonificacion', 0 );

		$tobj = utb::getTTrib($trib);

		//Calculo el vencimiento
		$venc = $model->getFechaVenc( $trib, $anio, $cuota, $objeto_id );

		if ( $error == '' && $venc == '' )
			$error = "No se encuentra definido el vencimiento del período ingresado.";

		//Obtengo el tipo de comercio
		$tipo_nom = utb::getCampo('comer',"obj_id = '" . $objeto_id . "'",'tipoliq');

		if ($error == '' && $tipo_nom != 'LO')
			$error = "Sólo se permite generar DJ de contribuyentes locales. Quedan exceptuados Convenio Multilateral y Acuerdo Interjurisdiccional.";

		// Obtener los rubros y tributos para el comercio seleccionado y cargarlos en la grilla
		Yii::$app->session->set( 'DDJJRubrosAndTributos', $model->getRubrosAndTributos( $trib, $objeto_id, $subcta, $fiscaliza, $anio, $cuota, $fchpresenta, $fchvencimiento, $codigo, $base, $cant ) );

		if ( $calculaDDJJ )
		{
			//Obtengo un nuevo ID de DDJJ en caso de no existir
			if ( Yii::$app->session->get( 'DDJJNextDJRubro_id', '' ) == '' )
			{
				//Cargo un nuevo ID para la DDJJ
				Yii::$app->session->set( 'DDJJNextDJRubro_id', $model->getNextDjRub_id() );
			}

			//Borro todo lo que tengo en la tabla TEMP de la BD
			$model->borrarRubrosTemp( Yii::$app->session->get( 'DDJJNextDJRubro_id' ) ); //Le paso el ID de la tabla TEMP que se está usando en esta sesión

			//Grabo todo lo que tengo en el arreglo temporal en la tabla TEMP
			$model->grabarRubrosTemp( Yii::$app->session->get( 'DDJJNextDJRubro_id' ), Yii::$app->session->get( 'DDJJRubrosAndTributos' ) );

			//Calculo la declaración jurada y cargo el arreglo de liquidaciones en la variable DDJJArregloItems en sesión
			Yii::$app->session->set( 'DDJJArregloItems', $model->calcularDJ( $trib,$objeto_id,$fiscaliza,$anio,$cuota,$fchpresenta,Yii::$app->session->get( 'DDJJNextDJRubro_id' ), $aplicaBonificacion ) );

		}

		if ( $error == '' )
		{
			//Si se presiona el botón ">"
			if ( $verificaPeriodo )
			{

				$adeuda = 0;

				if( !$config_ddjj['perm_djfalta'] ){

					//Corroborar si existe una DJ para el período anterior
					$adeuda = $model->adeudaDJ( $trib,$anio,$cuota,$objeto_id );
				}

				//Corroboro si ya existe una DDJJ para ese período
				$periodo = $model->existePeriodo( $trib,$anio,$cuota,$objeto_id,$fiscaliza );

				if( $adeuda ){
					echo '<script>mostrarErrores( ["No se puede generar una DJ para el período actual. Se adeudan DDJJ."], "#ddjj_errorSummary" );</script>';
				} else if (!$periodo )
				{//No existe período

					//Pongo ORDEN = "Original"
					echo '<script>$("#ddjj_txOrden").val("Original");</script>';

					//Seteo la Fecha de Vencimiento
					echo '<script>$("#ddjj_fchvencimiento").val("'.$venc.'");</script>';

					//Pongo en visible el div grilla
					echo '<script>$(".grillas").css("display","block");</script>';

					//Pongo en oculto el div cancelar
					echo '<script>$(".cancelar").css("display","none");</script>';

					//Pongo los elementos en modo "Solo lectura".
					echo '<script>soloLecturaElementosSuperior();</script>';

					//Habilito el botón "Calcular"
					echo '<script>$("#btDDJJCalcular").attr( "readOnly", false);</script>';

					//Actualizo la grilla
					echo '<script>$("#DDJJ_cargarDatos").on("pjax:end", function(){ $.pjax.reload("#manejadorGrillas");$("#DDJJ_cargarDatos").off("pjax:end");});</script>';


				} else
				{//Existe período. Se debe pedir confirmación. La nueva DDJJ será rectificada

					//INICIO Modal Confirmación Rectificar DDJJ
			  		Modal::begin([
						'id' => 'ModalRectificarDDJJ',
						'size' => 'modal-sm',
						'header' => '<h4><b>Rectificativa</b></h4>',
						'closeButton' => [
		    				'label' => '<b>X</b>',
		        			'class' => 'btn btn-danger btn-sm pull-right',
		        			'id' => 'btCancelarModalElim'
		    				],
					]);

					echo "<center>";
					echo "<p><label>Ya existe una DDJJ para el Período.<br /> ¿Desea generar una DDJJ Rectificativa?</label></p><br />";

					echo Html::button('Aceptar',['class' => 'btn btn-success','onclick'=>'ratifica("'.$venc.'")']);

					echo "&nbsp;&nbsp;";
			 		echo Html::button('Cancelar',['class' => 'btn btn-primary','onclick'=>'ratifica(0)']);
			 		echo "</center>";

			 		Modal::end();
					//FIN Modal Confirmación Rectificar DDJJ

					echo '<script>$("#ModalRectificarDDJJ").modal("show");</script>';

				}
			} else {
				//Actualizo la grilla
				echo '<script>$("#DDJJ_cargarDatos").on("pjax:end", function(){ $.pjax.reload("#manejadorGrillas");$("#DDJJ_cargarDatos").off("pjax:end");});</script>';
			}
		} else
		{
			echo '<script>$(".grillas").css("display","none");</script>';
			echo '<script>$(".cancelar").css("display","block");</script>';
			echo '<script>mostrarErrores( ["'.$error.'"], "#ddjj_errorSummary" );</script>';
		}
//			echo '<script>$.pjax.reload("#manejadorGrillas");</script>';

	}
Pjax::end();
//FIN Bloque cargar datos

//INICIO Bloque que genera una DDJJ Rectificada
Pjax::begin(['id'=>'opcionModal']);

	//En caso de que la nueva DDJJ sea rectificada
	if(isset($_POST['rectifica']))
	{
		if ($_POST['rectifica'] == 1)
		{
			$venc = $_POST['venc'];
			//Pongo ORDEN = "Rectificada"
			echo '<script>$("#ddjj_txOrden").val("Rectificada");</script>';

			//Seteo la Fecha de Vencimiento
			echo '<script>$("#ddjj_fchvencimiento").val("'.$venc.'");</script>';

			//Pongo en visible el div grilla
			echo '<script>$(".grillas").css("display","block");</script>';

			//Pongo en oculto el div cancelar
			echo '<script>$(".cancelar").css("display","none");</script>';

			//Pongo los elementos en modo "Solo lectura".
			echo '<script>soloLecturaElementosSuperior();</script>';

			//Actualizo la grilla
			echo '<script>$.pjax.reload({container:"#manejadorGrillas"});</script>';

		} else if($_POST['rectifica'] == 0)
		{
			echo '<script>limpiarElementos()</script>';

			//Pongo en hidden el div grilla
			echo '<script>$(".grillas").css("display","none");</script>';

			//Pongo los elementos en modo "Edición".
			echo '<script>edicionElementosSuperior();</script>';
		}
	}

Pjax::end();
//FIN Bloque que genera una DDJJ Rectificada

?>

<div class="grillas" style="display:none">

	<?php if( $config_ddjj['perm_bonif']) { ?>

	<?=
		Html::checkbox( 'ckBonificacion', false, [
			'id'	=> 'ddjj_ckBonificacion',
			'label' => 'Aplicar Bonificación',
			'onclick'	=> 'f_aplicarBonificacion()',
		]);
	?>

	<?php } ?>

<?php

//INICIO Bloque que se encarga de actualizar los datos de la grilla
Pjax::begin(['id'=>'manejadorGrillas', 'enablePushState' => false, 'enableReplaceState' => false]);

	//INICIO Calculo y Seteo los valores para base y monto
	$array = Yii::$app->session->get( 'DDJJRubrosAndTributos' );

	$baseTotal = 0;
	$montoTotal = 0;

	if ( count( $array ) > 0 )
	{
		//Calculo Base
		foreach ($array as $rubros)
		{
			$baseTotal += $rubros['base'];
		}
	}

	//Calculo Monto
	$array = Yii::$app->session->get( 'DDJJArregloItems' );

	if ( count( $array ) > 0 )
	{
		foreach ($array as $rubros)
		{
			$montoTotal += $rubros['item_monto'];
		}
	}

	Yii::$app->session->set( 'DDJJBaseTotal', $baseTotal );
	Yii::$app->session->set( 'DDJJMontoTotal', $montoTotal );
	//FIN Calculo y Seteo los valores para base y monto

?>


<div class="form-panel" style="padding-right:8px;padding-bottom: 8px;padding-top: 15px">


<!-- INICIO Grilla Rubros -->
<div style="margin-top: 8px;">

<?php

	Pjax::begin();

	$dataProvRubros = new ArrayDataProvider(['models' => Yii::$app->session->get( 'DDJJRubrosAndTributos', [])]);

		echo GridView::widget([
				'id' => 'GrillaRubrosddjj',
				'headerRowOptions' => ['class' => 'grilla'],
				'rowOptions' => ['class' => 'grilla'],
				'dataProvider' => $dataProvRubros,
				'summaryOptions' => ['class' => 'hidden'],
				'columns' => [
						['attribute'=>'trib_nom','header' => 'Trib', 'contentOptions'=>['style'=>'text-align:left','width'=>'30px']],
						['attribute'=>'rubro_nom','header' => 'Rubro', 'contentOptions'=>['style'=>'text-align:left','width'=>'200px']],
						['attribute'=>'formCalculo','header' => 'Fórmula', 'contentOptions'=>['style'=>'text-align:left','width'=>'100px']],
						// Base
						['content'=> function($model, $key, $index, $column) {
							return Html::input('text','DDJJ_base',$model["base"],
									[
										'id' => $model["trib_id"]."-".$model["rubro_id"],
										'style' => 'width:80px;margin:0px;text-align:right',
										'class' => 'form-control',
										'maxlength' => 12,
										'onkeypress' => 'return justDecimal( $(this).val(), event)',
										'onchange' => 'inhabilitaGuardarDDJJ()',

									]);
							},
						'label' => 'Base',
						'contentOptions'=>['style'=>'width:60px;text-align:center'],
						],
						//Cantidad
						['content'=> function($model, $key, $index, $column) {

							$tminimo = $model['tminimo'];

							//Dependiendo de tminimo se puede modificar la cantidad
							if ( $tminimo == 3 || $tminimo == 4 || $tminimo == 8 )
							{
								return Html::input('text','DDJJ_cant',$model["cant"],
										[
											'id' => $model["trib_id"]."-".$model["rubro_id"],
											'style' => 'width:40px;margin:0px;text-align:center',
											'class' => 'form-control',

										]);
							} else
							{
								return Html::input('text','DDJJ_cant',$model["cant"],
										[
											'id' => $model["trib_id"]."-".$model["rubro_id"],
											'style' => 'width:40px;margin:0px;text-align:center',
											'class' => 'form-control solo-lectura',
											'tabIndex' => '-1',

										]);
							}

						},
						'label' => 'Cantidad',
						'contentOptions'=>['style'=>'width:40px;text-align:center'],
						],
						['attribute'=>'alicuota','header' => 'Ali', 'contentOptions'=>['style'=>'text-align:center','width'=>'40px']],
						['attribute'=>'minimo','header' => 'Mín', 'contentOptions'=>['style'=>'text-align:right','width'=>'40px']],
						['attribute'=>'monto','header' => 'Monto', 'contentOptions'=>['style'=>'text-align:right','width'=>'40px']],

		        	],
			]);

		Pjax::end();

?>

</div>
<!-- FIN Grilla Rubros -->

</div>

<table width="100%">
	<tr>
		<td width="550px" valign="top">

			<div name="ddjj_left">

			<!-- INICIO Grilla Liquidación -->
			<div class="form-panel" style="padding-right:8px;padding-bottom: 8px;margin-right: 5px">


				<?php
						Pjax::begin();

						$dataProviderLiq = new ArrayDataProvider(['models' => Yii::$app->session->get( 'DDJJArregloItems' ) ]);

							echo GridView::widget([
									'id' => 'GrillaLiqddjj',
									'headerRowOptions' => ['class' => 'grilla'],
									'rowOptions' => ['class' => 'grilla'],
									'dataProvider' => $dataProviderLiq,
									'summaryOptions' => ['class' => 'hidden'],
									'columns' => [
											['attribute'=>'item_id','header' => 'Ítem', 'contentOptions'=>['style'=>'text-align:center','width'=>'15px']],
											['attribute'=>'item_nom','header' => 'Descrip.', 'contentOptions'=>['style'=>'text-align:left','width'=>'250px']],
											['attribute'=>'item_monto','header' => 'Monto', 'contentOptions'=>['style'=>'text-align:right','width'=>'15px']],
							        	],
								]);

						Pjax::end();

				?>
			</div>
			<!-- FIN Grilla Liquidación -->

			<!-- INICIO Grilla Anticipos -->
			<div class="form-panel grillaInfoDDJJ" style="padding-right:8px;display:none">


				<?php
						Pjax::begin();
							echo GridView::widget([
									'id' => 'GrillaInfoddjj',
									'headerRowOptions' => ['class' => 'grilla'],
									'rowOptions' => ['class' => 'grilla'],
									'dataProvider' => $dataProviderAnt,
									'summaryOptions' => ['class' => 'hidden'],
									'columns' => [
											['attribute'=>'cuota','header' => 'Cuota', 'contentOptions'=>['style'=>'text-align:center','width'=>'15px']],
											['attribute'=>'base','header' => 'Base', 'contentOptions'=>['style'=>'text-align:right','width'=>'15px']],
											['attribute'=>'monto','header' => 'Monto', 'contentOptions'=>['style'=>'text-align:right','width'=>'15px']],

							        	],
								]);
						Pjax::end();

				?>
			</div>
		</td>

		<td height="100%" style="border-height:1px" valign="top">

			<div name="ddjj_rigth" style="padding-right:8px; padding-bottom: 8px" class="form-panel">

			<table height="100%">
				<tr>
					<td width="40px"><label>Base:</label></td>
					<td>
						<?= Html::input('text','txBase',number_format( Yii::$app->session->get( 'DDJJBaseTotal', 0 ), 2, '.', ''),[
								'id'=>'ddjj_txBase',
								'class'=>'form-control solo-lectura',
								'style'=>'width:80px;text-align:right;background:#E6E6FA',
								'tabIndex' => '-1',
							]);
						?>
					</td>
				</tr>
				<tr>
					<td width="30px"><label>Monto:</label></td>
					<td>
						<?= Html::input('text','txMonto',number_format( Yii::$app->session->get( 'DDJJMontoTotal', 0 ), 2, '.', ''),[
								'id'=>'ddjj_txMonto',
								'class'=>'form-control solo-lectura',
								'style'=>'width:80px;text-align:right;background:#E6E6FA',
								'tabIndex' => '-1',
							]);
						?>
					</td>
				</tr>

			</table>
			</div>

		</td>
	</tr>
</table>

<?php

Pjax::end();
//Cierro manejador de Grilla

Pjax::begin(['id'=>'pjaxCancelar']);

	$session->open();

	if (isset($_POST['reiniciar']) && $_POST['reiniciar'] == 1)
	{
		$session['DDJJArregloRubros'] = [];
		echo '<script>edicionElementosSuperior()</script>'; //Habilito la edición de los elementos superiores
		echo '<script>$(".grillas").css("display","none");</script>'; //Pongo el div grillas oculta
		echo '<script>$(".cancelar").css("display","block");</script>'; //Pongo el div cancelar visible

	}

	$session->close();

Pjax::end();

?>
<!-- botones para guardar, limpiar y cancelar -->
<?= Html::button('Calcular',['id' => 'btDDJJCalcular','class' => 'btn btn-success','onclick'=>'calcularDJ()']); ?>
&nbsp;&nbsp;
<?= Html::button('Guardar DDJJ',['id' => 'btDDJJGuardar','class' => 'btn btn-success disabled','onclick'=>'$("#frmAltaDDJJ").submit()']); ?>
&nbsp;&nbsp;
<?= Html::a('Limpiar',['view','consulta'=>0,'action'=>0],['class' => 'btn btn-primary']); ?>
&nbsp;&nbsp;
<?= Html::Button('Cancelar',['class' => 'btn btn-primary','onclick'=>'$.pjax.reload({container:"#pjaxCancelar",method:"POST",data:{reiniciar:1}})']); ?>

</div>

<div class="cancelar" style="margin-bottom: 8px">
<?= Html::a('Cancelar',['view'],['class' => 'btn btn-primary']); ?>
</div>

<!-- INICIO Mensaje de errores -->
<div id="ddjj_errorSummary" class="error-summary" style="display:none;margin-top:8px">

</div>
<!-- FIN Mensaje de errores -->

<!-- INICIO Mensajes de error -->
<table width="100%">
	<tr>
		<td width="100%">

			<?php

			Pjax::begin(['id'=>'errorDDJJ']);

			$mensaje = '';

			if (isset($_POST['mensaje'])) $mensaje = $_POST['mensaje'];

			if($mensaje != "")
			{

		    	Alert::begin([
		    		'id' => 'AlertaMensajeDDJJ',
					'options' => [
		        	'class' => 'alert-danger',
		        	'style' => $mensaje !== '' ? 'display:block' : 'display:none'
		    		],
				]);

				echo $mensaje;

				Alert::end();

				echo "<script>window.setTimeout(function() { $('#AlertaMensajeDDJJ').alert('close'); }, 5000)</script>";
			 }

			 Pjax::end();

			?>
		</td>
	</tr>
</table>
<!-- FIN Mensajes de error -->

</div>

<script>
function inhabilitaGuardarDDJJ()
{
	$( "#btDDJJGuardar" ).addClass( "disabled" );
}

function grabarDJ()
{
	$.pjax.reload({
		container:"#PjaxGrabarDJ",
		method:"POST",
		data:{
			recargar:1,
			trib:$("#ddjj_dlTrib").val(),
			objeto_id:$("#ddjj_txObjetoID").val(),
			anio:$("#ddjj_txAnio").val(),
			cuota:$("#ddjj_txCuota").val(),
			fchpresenta:$("#ddjj_fchpresentacion").val(),
			tipo:$("#ddjj_dlTipo").val(),
		}
	});
}

function ratifica(cod)
{
	$("#ModalRectificarDDJJ").modal("hide");

	if (cod != 0)
	{
		$.pjax.reload({container:"#opcionModal",method:"POST",data:{rectifica:1,venc:cod}})
	} else
	{
		edicionElementosSuperior();
	}

}

function calcularDJ()
{
	var base = new Array(),
		cant = new Array(),
		codigo = new Array(),
		error = new Array();

	//Ocultar div de errores
	$( "#ddjj_errorSummary" ).css( "display", "none" );

	$("input[name=DDJJ_base").each(function(){

		if ( $(this).val() != '' && $(this).val() != null )
			base.push( $(this).val() );
		else
			error.push( "Base no puede ser vacío." );

		codigo.push( $(this).attr("id") );

    });

	$("input[name=DDJJ_cant").each(function(){

		if ( $(this).val() != '' && $(this).val() != null )
			cant.push( $(this).val() );
		else
			error.push( "Cantidad no puede ser vacío." );

    });

	if ( error.length == 0 )
	{
		//Habilito el botón "Grabar DDJJ"
		$( "#btDDJJGuardar" ).removeClass( "disabled" );

		ocultaDivInfoDDJJ();

		//Paso los arreglos de código, base y cantidad a la función cargar
		btCargar( 0, codigo, base, cant, 1);

	} else
	{
		mostrarErrores( error, "#ddjj_errorSummary" );
	}

}

function btCargar( verificaPeriodo, codigo, base, cant, calculaDDJJ )
{
	var trib = $("#ddjj_dlTrib").val(),
		objeto_id = $("#ddjj_txObjetoID").val(),
		objeto_nom = $("#ddjj_txObjetoNom").val(),
		anio = $("#ddjj_txAnio").val(),
		cuota = $("#ddjj_txCuota").val(),
		tipo = $("#ddjj_dlTipo").val(),
		fchpresentacion = String($("#ddjj_fchpresentacion").val()),
		fchvencimiento = String($("#ddjj_fchvencimiento").val()),
		aplicaBonificacion = $( "#ddjj_ckBonificacion" ).is( ":checked" ),
		error = new Array(),
		datos = {};

	//Ocultar errores
	$( "#ddjj_errorSummary" ).css( "display", "none" );

	if (objeto_nom == '')
	{
		error.push( "Ingrese un código de objeto válido." );
	}

	if (anio == '')
		error.push( "Ingrese un año." );

	if (cuota == '')
		error.push( "Ingrese una cuota." );

	if (error != '')
		mostrarErrores( error, "#ddjj_errorSummary" );
	else
	{
		$.pjax.reload({
			container : "#DDJJ_cargarDatos",
			data : {
				"trib": trib,
				"objeto_id": objeto_id,
				"objeto_nom": objeto_nom,
				"anio": anio,
				"cuota": cuota,
				"tipo": tipo,
				"fchpresenta": fchpresentacion,
				"fchvencimiento": fchvencimiento,
				"verificaPeriodo": verificaPeriodo,
				"codigo": codigo,
				"base": base,
				"cant": cant,
				"calculaDDJJ": calculaDDJJ,
				"aplicaBonificacion" : aplicaBonificacion,
			},
			method:"POST",
		});

	}
}

function limpiarElementos()
{
	$("#ddjj_txObjetoID").val("");
	$("#ddjj_txObjetoNom").val("");
	$("#ddjj_txAnio").val("");
	$("#ddjj_txCuota").val("");
	$("#ddjj_txOrden").val("");
	$("#ddjj_fchvencimiento").val("");
	$("#ddjj_txSucursal").val("");
}

function soloLecturaElementosSuperior()
{
	$("#ddjj_txObjetoID").attr("readOnly",true);
	$("#ddjj_txObjetoNom").attr("readOnly",true);
	$("#ddjj_txAnio").attr("readOnly",true);
	$("#ddjj_txCuota").attr("readOnly",true);
	$("#ddjj_txOrden").attr("readOnly",true);
	$("#ddjj_fchvencimiento").attr("readOnly",true);
	$("#ddjj_txSucursal").attr("readOnly",true);

	$("#ddjj_htrib").attr("disabled", false);
	$("#ddjj_dlTrib").attr("disabled",true);

	$("#ddjj_tipo").attr("disabled", false);
	$("#ddjj_dlTipo").attr("disabled",true);

	$("#btCargar1").attr('disabled',true);
	$("#ddjj_fchpresentacion").attr('readOnly',true);
}

function edicionElementosSuperior()
{
	var fecha = $("#ddjj_fchpresentacion").val();

	$("#ddjj_txObjetoID").removeAttr("readOnly");

	$("#ddjj_trib").attr("disabled", true);
	$("#ddjj_dlTrib").removeAttr("readOnly");

	$("#ddjj_txAnio").removeAttr("readOnly");
	$("#ddjj_txCuota").removeAttr("readOnly");

	$("#ddjj_tipo").attr("disabled", true);
	$("#ddjj_dlTipo").removeAttr("readOnly");

	$("#ddjj_fchvencimiento").removeAttr("readOnly");
	$("#btCargar1").removeAttr('disabled');

	$.pjax.reload({
		container:"#PjaxFchPresentacion",
		method:"POST",
		data:{
			fecha:fecha,
		}
	});
}

function ocultaDivInfoDDJJ()
{
	var cuota = $("#ddjj_txCuota").val();

	if ( cuota == 12 )
	{
		//Pongo en visible el div grillaInfoDDJJ
		$(".grillaInfoDDJJ").css("display","block");

	} else
	{
		//Pongo en no visible el div grillaInfoDDJJ
		$(".grillaInfoDDJJ").css("display","none");
	}
}

function f_aplicarBonificacion(){
	inhabilitaGuardarDDJJ();
}

$(document).ready(function() {

	$("#ddjj_dlTrib").trigger("change");

	ocultaDivInfoDDJJ();
});

</script>
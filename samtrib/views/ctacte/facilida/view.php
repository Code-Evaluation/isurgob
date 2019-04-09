<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Alert;

/* @var $this yii\web\View */
/* @var $model app\models\ctacte\Intima */

$title = 'Facilidades de Pago';
$this->params['breadcrumbs'][] = $title;

?>
<div class="intima-view">
	<h1><?= Html::encode($title) ?></h1>
	<table width="100%">
		<tr>
	    	<td>
	    		<?php
	    			if (isset($alert) == null) $alert = '';
	    			Alert::begin([
	    				'id' => 'AlertaFacilida',
						'options' => [
	        			'class' => ($m == 1) ? 'alert-success' : 'alert-info',
	        			'style' => $alert !== '' ? 'display:block' : 'display:none' 
	    				],
					]);
	
					if ($alert !== '') echo $alert;
					
					Alert::end();
					
					if ($alert !== '') echo "<script>window.setTimeout(function() { $('#AlertaFacilida').alert('close'); }, 5000)</script>";
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
					if ($consulta != 0)
					{
						echo $this->render('info',[
								'id'=>$id,
			        			'model'=>$model,
			        			'dataProvider'=>$dataProvider,
			        			'consulta'=>$consulta,
			        			'dia'=>$dia,
							
							]); 
					} else 
					{
							echo $this->render('_form',[
								'id'=>$id,
			        			'model'=>$model,
			        			'dataProvider'=>$dataProvider,
			        			'consulta'=>$consulta,
			        			'dia'=>$dia,
							
							]); 
					}
							
						
						?>
			</td>
			<td width="110px" valign="top" align="right">
				<?= $this->render('menu_derecho', [
						'model'=>$model,
						'id'=>$id,
						'est'=>$est,
						'consulta'=>$consulta,
						]); ?>
			</td>
		</tr>
	</table>
</div>

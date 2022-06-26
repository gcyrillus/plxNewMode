<?php if(!defined('PLX_ROOT')) exit; 
	# Control du token du formulaire
	plxToken::validateFormToken($_POST);	
    if(!empty($_POST)) {
        $plxPlugin->setParam('cloneMode', $_POST['cloneMode'], 'string');
        $plxPlugin->saveParams();
		header('Location: parametres_plugin.php?p='.$plugin);
	exit;
    }
# Controle de l'accès à la page en fonction du profil de l'utilisateur connecté
	$plxAdmin->checkProfil(PROFIL_ADMIN);	

?>
<form action="parametres_plugin.php?p=<?php echo $plugin ?>" method="post" class="extraMode">
	<fieldset><legend><?php echo $plxPlugin->lang('L_ADD_EXTRA_MODE_FILTER'); ?></legend>
	<label><?php echo $plxPlugin->lang('L_ADD_EXTRA_MODE'); ?></label> 
	<?php
			plxUtils::printInput('cloneMode',$plxPlugin->getParam('cloneMode'),'text','20-255');
			echo plxToken::getTokenPostMethod();
	?>
	<input name="load" type="submit" value="<?php echo $plxPlugin->lang('L_SAVE_EXTRA_MODE_FILTER'); ?>" />
	</div>
</form>
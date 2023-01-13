<?php
    if(!defined('PLX_ROOT')) {
        die('Oups!');
    }

    class plxNewMode extends plxPlugin {
		
		const HOOKS = array(
			'plxMotorPreChauffageBegin',
        );
        const BEGIN_CODE = '<?php' . PHP_EOL;
        const END_CODE = PHP_EOL . '?>';
		
        public function __construct($default_lang) {
            # appel du constructeur de la classe plxPlugin (obligatoire)
            parent::__construct($default_lang);

            # Ajoute des hooks
            foreach(self::HOOKS as $hook) {
                $this->addHook($hook, $hook);
            }
			
			
			# droits pour accèder à la page config.php du plugin
			$this->setConfigProfil(PROFIL_ADMIN);
			
        }
		
 public function plxMotorPreChauffageBegin() {
			
            echo self::BEGIN_CODE;
?>		
		include(PLX_ROOT.'plugins/plxNewMode/preHeat.php');
		return true;
<?php
            echo self::END_CODE;						
        }		
		
		
		
	}
	?>

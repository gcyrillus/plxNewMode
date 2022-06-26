<?php	
		$homeCloneModes=preg_replace('/\s+/', '', $homeCloneModes = $this->plxPlugins->aPlugins['plxNewMode']->getParam('cloneMode')=='' ? '0' : $this->plxPlugins->aPlugins['plxNewMode']->getParam('cloneMode'));
		if(!empty($this->get) and $homeCloneModes !='0' and preg_match('#^(?:'.$homeCloneModes.'(?:\=))#', $this->get)) { $this->get =  ''; }
		else {if(!empty($this->get) and !preg_match('#^(?:blog|article\d{1,4}/|static\d{1,3}/|categorie\d{1,3}/|archives/\d{4}(?:/\d{2})?|tag/\w|page\d|preview|telechargement|download)#', $this->get)) { $this->get =  'error'; }}

		if(!$this->get AND $this->aConf['homestatic']!='' AND isset($this->aStats[$this->aConf['homestatic']]) AND $this->aStats[$this->aConf['homestatic']]['active']) {
			$this->mode = 'static'; # Mode static
			$this->cible = $this->aConf['homestatic'];
			$this->template = $this->aStats[ $this->cible ]['template'];
		}
		elseif(empty($this->get)
				OR preg_match('#^(blog|blog\/page\d*|\/?page\d*)$#', $this->get)
				AND !preg_match('#^(?:article|static|categorie|archives|tag|preview|telechargement|download)[\b\d/]+#', $this->get)) {
			$this->mode = 'home';
			$this->template = $this->aConf['hometemplate'];
			$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			if($this->plxGlob_arts->query('#^\d{4}\.(home[0-9,]*)\.\d{3}\.\d{12}\.[\w-]+\.xml$#')) {
				$this->motif = '#^\d{4}.(home[0-9,]*).\d{3}.\d{12}.[\w-]+.xml$#';
			} else { # Sinon on recupere tous les articles
				$this->motif = '#^\d{4}.(?:\d|,)*(?:'.$this->homepageCats.')(?:\d|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
			}
		}
		elseif($this->get AND preg_match('#^article(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->mode = 'article'; # Mode article
			$this->template = 'article.php';
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			$this->motif = '#^'.$this->cible.'.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#'; # Motif de recherche
			if($this->getArticles()) {
				# Redirection 301
				if(!isset($capture[2]) OR $this->plxRecord_arts->f('url')!=$capture[2]) {
					$this->redir301($this->urlRewrite('?article'.intval($this->cible).'/'.$this->plxRecord_arts->f('url')));
				}
			} else {
				$this->error404(L_UNKNOWN_ARTICLE);
			}
		}
		elseif($this->get AND preg_match('#^static(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complète sur 3 caractères
			if(!isset($this->aStats[$this->cible]) OR !$this->aStats[$this->cible]['active']) {
				$this->error404(L_UNKNOWN_STATIC);
			} else {
				if(!empty($this->aConf['homestatic']) AND $capture[1]){
					if($this->aConf['homestatic']==$this->cible){
						$this->redir301($this->urlRewrite());
					}
				}
				if($this->aStats[$this->cible]['url']==$capture[2]) {
					$this->mode = 'static'; # Mode static
					$this->template = $this->aStats[$this->cible]['template'];
				} else {
					$this->redir301($this->urlRewrite('?static'.intval($this->cible).'/'.$this->aStats[$this->cible]['url']));
				}
			}
		}
		elseif($this->get AND preg_match('#^categorie(\d+)\/?([\w-]+)?#',$this->get,$capture)) {
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			if(!empty($this->aCats[$this->cible]) AND $this->aCats[$this->cible]['active'] AND $this->aCats[$this->cible]['url']==$capture[2]) {
				$this->mode = 'categorie'; # Mode categorie
				$this->motif = '#^\d{4}.((?:\d|home|,)*(?:'.$this->cible.')(?:\d|home|,)*).\d{3}.\d{12}.[\w-]+.xml$#'; # Motif de recherche
				$this->template = $this->aCats[$this->cible]['template'];
				$this->tri = $this->aCats[$this->cible]['tri']; # Recuperation du tri des articles
				$this->bypage = !empty($this->aCats[$this->cible]['bypage']) ? $this->aConf['bypage'] : $this->bypage;
			}
			elseif(isset($this->aCats[$this->cible])) { # Redirection 301
				if($this->aCats[$this->cible]['url']!=$capture[2]) {
					$this->redir301($this->urlRewrite('?categorie'.intval($this->cible).'/'.$this->aCats[$this->cible]['url']));
				}
			} else {
				$this->error404(L_UNKNOWN_CATEGORY);
			}
		}
		elseif($this->get AND preg_match('#^archives\/(\d{4})[\/]?(\d{2})?[\/]?(\d{2})?#',$this->get,$capture)) {
			$this->mode = 'archives';
			$this->template = 'archives.php';
			$this->bypage = $this->aConf['bypage_archives'];
			$this->cible = $search = $capture[1];
			if(!empty($capture[2])) $this->cible = ($search .= $capture[2]);
			else $search .= '\d{2}';
			if(!empty($capture[3])) $search .= $capture[3];
			else $search .= '\d{2}';
			$this->motif = '#^\d{4}.(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.'.$search.'\d{4}.[\w-]+.xml$#';
		}
		elseif($this->get AND preg_match('#^tag\/([\w-]+)#',$this->get,$capture)) {
			$this->cible = $capture[1];
			$ids = array();
			$datetime = date('YmdHi');
			foreach($this->aTags as $idart => $tag) {
				if($tag['date']<=$datetime) {
					$tags = array_map("trim", explode(',', $tag['tags']));
					$tagUrls = array_map(array('plxUtils', 'urlify'), $tags);
					if(in_array($this->cible, $tagUrls)) {
						if(!isset($ids[$idart])) $ids[$idart] = $idart;
						if(!isset($this->cibleName)) {
							$key = array_search($this->cible, $tagUrls);
							$this->cibleName=$tags[$key];
						}
					}
				}
			}
			if(sizeof($ids)>0) {
				$this->mode = 'tags'; # Affichage en mode home
				$this->template = 'tags.php';
				$this->motif = '#('.implode('|', $ids).').(?:\d|home|,)*(?:'.$this->activeCats.'|home)(?:\d|home|,)*.\d{3}.\d{12}.[\w-]+.xml$#';
				$this->bypage = $this->aConf['bypage_tags']; # Nombre d'article par page
			} else {
				$this->error404(L_ARTICLE_NO_TAG);
			}
		}
		elseif($this->get AND preg_match('#^preview\/?#',$this->get) AND isset($_SESSION['preview'])) {
			$this->mode = 'preview';
		}
		elseif($this->get AND preg_match('#^(telechargement|download)\/(.+)$#',$this->get,$capture)) {
			if($this->sendTelechargement($capture[2])) {
				$this->mode = 'telechargement'; # Mode telechargement
				$this->cible = $capture[2];
			} else {
				$this->error404(L_DOCUMENT_NOT_FOUND);
			}
		}
		else {
			$this->error404(L_ERR_PAGE_NOT_FOUND);
		}

		# On vérifie l'existence du template
		$filename = $this->style . '/' . $this->template;
		if(!file_exists(PLX_ROOT . $this->aConf['racine_themes'] . $filename)) {
			$this->error404(L_ERR_FILE_NOTFOUND . ' ( <i>' . $filename . '</i> )');
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorPreChauffageEnd'));
		 ?>
<?php
if (!defined("WIKINI_VERSION")) {
    die("acc&egrave;s direct interdit");
}

$yeswiki_javascripts = "\n" . '  <!-- javascripts -->' . "\n";

if (isset($this->config['use_jquery_cdn']) && $this->config['use_jquery_cdn'] == "1") {
    $this->addJavascriptFile('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js', true);
} else {
    $this->addJavascriptFile('javascripts/vendor/jquery/jquery.min.js', true);
}

// on récupère le bon chemin pour le theme
if (!empty($this->config['use_fallback_theme'])) {
    $repertoire = 'themes/'.$this->config['favorite_theme'].'/javascripts';
} else {
    $jsDir = 'themes/'.$this->config['favorite_theme'].'/javascripts';
    if (is_dir('custom/'.$jsDir)) {
        $repertoire = 'custom/'.$jsDir;
    } else {
        $repertoire = $jsDir;
    }
}

// on scanne les javascripts du theme
$bootstrapjs = false;
$yeswikijs = false;
$dir = (is_dir($repertoire) ? opendir($repertoire) : false);
while ($dir && ($file = readdir($dir)) !== false) {
    if (substr($file, -3, 3) == '.js') {
        $scripts[] =  $repertoire . '/' . $file;
        if (strstr($file, 'bootstrap.min.') || strstr($file, 'bs.')) {
            // le theme contient deja le js de bootstrap
            $bootstrapjs = true;
        }
        if (strstr($file, 'yeswiki.') || strstr($file, 'yw.')) {
            // le theme contient deja le js de yeswiki
            $yeswikijs = true;
        }
    }
}
if (is_dir($repertoire)) {
    closedir($dir);
}

// s'il n'y a pas le javascript de bootstrap dans le theme, on le rajoute
if (!$bootstrapjs) {
    $this->addJavascriptFile('javascripts/vendor/bootstrap/bootstrap.min.js');
    $this->addJavascriptFile('tools/templates/libs/vendor/bootstrap3-typeahead.min.js');
}

// on trie les javascripts du theme par ordre alphabéthique et on les insere
if (isset($scripts) && is_array($scripts)) {
    asort($scripts);
    foreach ($scripts as $val) {
        $this->addJavascriptFile($val);
    }
}

// s'il n'y a pas le javascript de yeswiki dans le theme, on le rajoute
if (!$yeswikijs) {
    $this->addJavascriptFile('javascripts/yeswiki-base.js');
}

// ajoute la méthode pour les traductions js
$this->addJavascriptFile('javascripts/yeswiki-base-no-defer.js', true);

// add javascript files which are included in the custom javascript directory
$customJsPath = 'custom/javascripts';
$customJsDir = is_dir($customJsPath) ? opendir($customJsPath) : false;
while ($customJsDir && ($file = readdir($customJsDir)) !== false) {
    if (substr($file, -3, 3) == '.js') {
        $this->addJavascriptFile($customJsPath . '/' . $file);
    }
}

// si quelque chose est passée dans la variable globale pour le javascript, on l'intègre
$yeswiki_javascripts .= isset($GLOBALS['js']) ? $GLOBALS['js'] : '';

// on vide la variable globale pour le javascript
$GLOBALS['js'] = '';

// do not use $GLOBALS['prefered_language'] because can be update for current page language

// Globale wiki variable
echo "<script>
    var wiki = {
        ...((typeof wiki !== 'undefined') ? wiki : null),
        ...{
    locale: '{$GLOBALS['prefered_language']}',
    baseUrl: '{$this->config['base_url']}',
    lang: {
        ...((typeof wiki !== 'undefined') ? (wiki.lang ?? null) : null),
        ...".json_encode($GLOBALS['translations_js'] ?? null)."
    },
    pageTag: '{$this->getPageTag()}',
    isDebugEnabled: ".($this->GetConfigValue('debug') =='yes' ? 'true' : 'false')."
}};
</script>";

// TODO: CSS a ajouter ailleurs?
if (isset($GLOBALS['css']) && !empty($GLOBALS['css'])) {
    $yeswiki_javascripts .=  $GLOBALS['css'];
    $GLOBALS['css'] = '';
}

// on affiche
echo $yeswiki_javascripts;

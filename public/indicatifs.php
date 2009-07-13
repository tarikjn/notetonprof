<?
define("ROOT_DIR", "");
require(ROOT_DIR."_ini.php");


// début traitement des variables URL
$expl = @explode("/", $_SERVER["PATH_INFO"]);
if (@strlen($expl[1]) > 0)
{
	$cursus = urldecode($expl[1]);
	
	$erreur_url = FALSE;
	
	// début vérification des variables
	if (isset(Geo::$COURSE[$cursus]))
		$erreur_var = FALSE;
	else
		$erreur_var = TRUE;
	// fin vérification des variables
}
else	$erreur_url = TRUE;
// fin traitement des variables URL

$title = "Sélection d'un indicatif";
?>
<? require("tpl/haut.php"); ?>
<? if ($erreur_url) require("tpl/erreur_url.php"); else if ($erreur_var) require("tpl/erreur_var.php"); else { ?>
		<div class="navi"><a href=".">Accueil</a> &gt; Enseignement <?=Geo::$COURSE[$cursus]?></div>
		<hr />
		<h2>Séléctionne ta zone géographique</h2>
		<p>Les zones sont définies selon les indicatifs téléphoniques.</p>
		<div class="carte">
			<img src="img/france-indicatifs.png" width="358" height="415" alt="Carte des indicatifs régionaux" usemap="#map" />
			<map name="map">
				<area href="depts/<?=urlencode($cursus)?>/01/" title="01 - <?=Geo::$IND["01"]?>" shape="poly" coords="227,88, 232,101, 224,115, 205,117, 198,112, 192,110, 183,83, 190,78" />
				<area href="depts/<?=urlencode($cursus)?>/02/" title="02 - <?=Geo::$IND["02"]?>" shape="poly" coords="183,47, 187,61, 182,83, 185,95, 191,110, 195,113, 205,116, 221,124, 216,141, 214,176, 197,186, 167,185, 153,165, 144,162, 139,158, 114,166, 119,187, 93,184, 87,177, 83,174, 73,193, 56,203, 26,200, 9,182, 7,154, 21,134, 36,122, 30,123, 25,122, 21,113, 28,110, 22,105, 25,102, 16,98, 19,94, 44,90, 52,85, 63,88, 73,97, 81,94, 100,90, 99,53, 110,57, 116,69, 143,69, 150,66, 145,62, 156,52" />
				<area href="depts/<?=urlencode($cursus)?>/03/" title="03 - <?=Geo::$IND["03"]?>" shape="poly" coords="243,32, 249,37, 252,48, 261,44, 266,40, 269,54, 286,63, 312,70, 316,74, 351,81, 347,101, 340,126, 336,141, 325,144, 323,152, 311,170, 291,193, 281,187, 269,188, 265,193, 244,193, 241,183, 230,173, 218,170, 219,129, 223,115, 230,107, 232,95, 216,82, 186,73, 183,47, 179,41, 184,27, 186,10, 212,7, 239,31" />
				<area href="depts/<?=urlencode($cursus)?>/04/" title="04 - <?=Geo::$IND["04"]?>" shape="poly" coords="238,177, 244,192, 248,195, 269,189, 273,185, 283,188, 290,193, 300,189, 302,197, 313,187, 323,189, 325,208, 327,216, 332,225, 321,239, 331,255, 331,267, 344,274, 352,275, 343,292, 325,307, 321,314, 296,317, 273,306, 257,305, 250,302, 239,304, 226,313, 219,323, 217,344, 201,345, 181,342, 184,338, 192,334, 181,311, 194,306, 207,304, 222,290, 229,282, 226,275, 220,269, 213,254, 212,246, 202,256, 192,252, 198,231, 205,222, 200,190, 201,185, 222,173" />
				<area href="depts/<?=urlencode($cursus)?>/04/" title="04 - <?=Geo::$IND["04"]?>" shape="poly" coords="346,307, 344,352, 341,364, 336,367, 322,345, 325,321, 339,316, 343,315, 346,305" />
				<area href="depts/<?=urlencode($cursus)?>/05/" title="05 - <?=Geo::$IND["05"]?>" shape="poly" coords="154,166, 166,181, 172,190, 200,190, 206,201, 205,225, 192,244, 195,255, 207,253, 214,247, 216,257, 225,275, 228,279, 225,289, 208,303, 203,306, 183,310, 185,322, 191,337, 170,334, 153,331, 139,335, 132,372, 111,396, 80,407, 45,402, 20,382, 8,359, 9,321, 23,297, 45,281, 79,276, 95,275, 102,256, 102,252, 106,221, 116,232, 103,201, 108,186, 121,186, 116,162, 146,164" />
			</map>
		</div>
		<p>Sélection d'un indicatif régional :
			<a href="depts/<?=urlencode($cursus)?>/01/" title="<?=Geo::$IND["01"]?>">01</a> |
			<a href="depts/<?=urlencode($cursus)?>/02/" title="<?=Geo::$IND["02"]?>">02</a> |
			<a href="depts/<?=urlencode($cursus)?>/03/" title="<?=Geo::$IND["03"]?>">03</a> |
			<a href="depts/<?=urlencode($cursus)?>/04/" title="<?=Geo::$IND["04"]?>">04</a> |
			<a href="depts/<?=urlencode($cursus)?>/05/" title="<?=Geo::$IND["05"]?>">05</a>
		</p>
<? } ?>
<? require("tpl/bas.php"); ?>

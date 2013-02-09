<?php
if ($auth!='auth') {die();};

class wikiTemplate extends Template{
	
	
	
	
	
	function xinhaInit($l){
		$dir = htmlspecialchars($l);
		$IMConfig = array();
		$IMConfig['allow_upload'] = true;
		$IMConfig['images_dir'] =  $_SERVER['DOCUMENT_ROOT'] . "/content/uploads/$dir";
		$IMConfig['images_url'] = "content/uploads/$dir";
		$IMConfig['files_dir'] =  $_SERVER['DOCUMENT_ROOT'] . "/content/uploads/$dir";
		$IMConfig['files_url'] = "content/uploads/$dir";
		$IMConfig['thumbnail_prefix'] = 't_';
		$IMConfig['thumbnail_dir'] = 't';
		$IMConfig['resized_prefix'] = 'resized_';
		$IMConfig['resized_dir'] = '';
		$IMConfig['tmp_prefix'] = '_tmp';
		$IMConfig['max_filesize_kb_image'] = 10000;
		$IMConfig['max_filesize_kb_link'] = 10000;
		$IMConfig['max_foldersize_mb'] = 0;
		$IMConfig['allowed_image_extensions'] = array("jpg", "jpeg", "gif","png", "tiff", "bmp");
		$IMConfig['allowed_link_extensions'] = array("jpg","gif","pdf","ip","txt","psd","png","html","swf","xml","xls");
		require_once 'xinha/contrib/php-xinha.php';
		$filemanagerconfig = xinha_pass_to_php_backend($IMConfig);
		$output = '
			<script type="text/javascript">
			_editor_url  = "xinha/"   // (preferably absolute) URL (including trailing slash) where Xinha is installed
			_editor_lang = "en";       // And the language we need to use in the editor.
			_editor_skin = "silva";    // If you want use a skin, add the name (of the folder) here
			_editor_icons = "classic"; // If you want to use a different iconset, add the name (of the folder, under the `iconsets` folder) here
			</script>
			<script type="text/javascript" src="xinha/XinhaCore.js"></script>
			<script type="text/javascript">';
		$output .= "
			xinha_editors = null;
			xinha_init    = null;
			xinha_config  = null;
			xinha_plugins = null;
			xinha_init = xinha_init ? xinha_init : function()
			{
				xinha_editors = xinha_editors ? xinha_editors :['editor'];
				xinha_plugins = xinha_plugins ? xinha_plugins :
					[
					'CharacterMap',
					'ContextMenu',
					'ListType',
					'Stylist',
					'Linker',
					'SuperClean',
					'TableOperations',
					'CSSPicker',
					'ExtendedFileManager'
					];
				if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;
				xinha_config = xinha_config ? xinha_config() : new Xinha.Config();
				xinha_config.ExtendedFileManager.use_linker = false;
				if (xinha_config.ExtendedFileManager) {
				with (xinha_config.ExtendedFileManager)
					{
						$filemanagerconfig
					}
				}";
		$output .='
			xinha_config.toolbar =
				[
				["popupeditor"],
				["separator","formatblock","fontname","fontsize","bold","italic","underline","strikethrough"],
				["separator","forecolor","hilitecolor","textindicator"],
				["separator","subscript","superscript"],
				["linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"],
				["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
				["separator","inserthorizontalrule","createlink","insertimage","inserttable"],
				["linebreak","separator","undo","redo","selectall","print"], (Xinha.is_gecko ? [] : ["cut","copy","paste","overwrite","saveas"]),
				["separator","killword","clearfonts","removeformat","toggleborders","splitblock","lefttoright", "righttoleft"],
				["separator","htmlmode","showhelp","about"]
				];
			xinha_config.pageStyleSheets = [ "css/editstyle.css" ];
			xinha_editors   = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);
			Xinha.startEditors(xinha_editors);
		}
		Xinha._addEvent(window,\'load\', xinha_init); 
		</script>';
		$this->set("xinhaScript", $output);
	}
	function makeTabs ($tabs){
		$out='';
		if ($tabs){
			foreach($tabs as $tab){
				$class = $tab['selected']?'seltab':'tab';
				$out .= '<a class="' . $class . '"';
				if (isset($tab['url'])){
					$out .= ' href="' . $tab['url'] . '"';
				}
				else if (isset($tab['onclick'])){
					$out .= ' href="#" onclick="' . $tab['onclick'] . '"';
				}
				$out .= '>' . $tab['text'] . '</a>';
			}
		}
		$out .='<span class="tab, lastTab"></span>';
		return $out;
		
	}
	function makeHistoryButtons($result){
		$output='';
		$first=true;
		while ($row=@mysql_fetch_array($result, MYSQL_ASSOC)){
			if ($first){
				$selected = ' checked="checked"';
				$first = false;
			}
			else {
				$selected = '';
			}
			$output .="<label>{$row['date_ent']} by {$row['edited_by']}</label>" . '<input type="radio" onclick="changeVersion()" name="versionId" id="' . $row['id'] . '" value="' . $row['id'] . '"' . $selected . '><br>';
		}
		return $output;
	}
	
}
class codesTemplate extends Template{
	public function makeGuestCodesList($result){
		$columnWidths=array(40, 120, 80, 120, 170);
		global $user;
		$codes=array();
		$output = $this->makeListRow(array('', 'Username', 'Code', 'Date', 'Notes'), $columnWidths, 1);
		while ($row=@mysql_fetch_array($result)){
			if ($row['UID']==$user->uid){
				$username='You';
			}
			else {
				$username=$row['username'];
			}
			$formhtml='<form name="deleteCode" action="proccode.php" method="post"><input type="hidden" name="doWhat" value="delete"><input type="hidden" name="deleteWhat" value="' . $row['ID'] . '"><input type="submit" class="submitbtn" value="X"></form>';
			$output.= $this->makeListRow(array($formhtml, $username, $row['code'], date('M j, Y', $row['startDate']), $row['notes']), $columnWidths, 0);
		}
		$this->values['codes'] = $output;									   
	}
	public function makePermCodesList($codeArray){
		$this->set('permCode', $codeArray['code']);
		$output=implode(', ', $codeArray['accessZones']);
		$this->set('accessZones', $output);
	}

}
?>
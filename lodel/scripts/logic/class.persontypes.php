<?php
/**	
 * Logique des types de personnes
 *
 * PHP version 4
 *
 * LODEL - Logiciel d'Edition ELectronique.
 *
 * Home page: http://www.lodel.org
 * E-Mail: lodel@lodel.org
 *
 * All Rights Reserved
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @package lodel/logic
 * @author Ghislain Picard
 * @author Jean Lamy
 * @copyright 2001-2002, Ghislain Picard, Marin Dacos
 * @copyright 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * @copyright 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Jean Lamy
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @since Fichier ajout� depuis la version 0.8
 * @version CVS:$Id$
 */



/**
 * Classe de logique des types de personnes
 * 
 * @package lodel/logic
 * @author Ghislain Picard
 * @author Jean Lamy
 * @copyright 2001-2002, Ghislain Picard, Marin Dacos
 * @copyright 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * @copyright 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Jean Lamy
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @since Classe ajout� depuis la version 0.8
 * @see logic.php
 */
class PersonTypesLogic extends Logic
{

	/**
	 * Constructeur
	 */
	function PersonTypesLogic() 
	{
		$this->Logic("persontypes");
	}

	/**
	*  Indique si un objet est prot�g� en suppression
	*
	* Cette m�thode indique si un objet, identifi� par son identifiant num�rique et
	* �ventuellement son status, ne peut pas �tre supprim�. Dans le cas o� un objet ne serait
	* pas supprimable un message est retourn� indiquant la cause. Sinon la m�thode renvoit le
	* booleen false.
	*
	* @param integer $id identifiant de l'objet
	* @param integer $status status de l'objet
	* @return false si l'objet n'est pas prot�g� en suppression, un message sinon
	*/
	function isdeletelocked($id,$status=0) 

	{
		global $db;
		$count=$db->getOne(lq("SELECT count(*) FROM #_TP_persons WHERE idtype='$id' AND status>-64"));
		if ($db->errorno())  dberror();
		if ($count==0) {
			return false;
		} else {
			return sprintf(getlodeltextcontents("cannot_delete_hasperson","admin"),$count);
		}
	}

	/**
	 * Construction des balises select HTML pour cet objet
	 *
	 * @param array &$context le contexte, tableau pass� par r�f�rence
	 * @param string $var le nom de la variable du select
	 */
	function makeSelect(&$context, $var)

	{
		switch($var) {
		case 'gui_user_complexity' :
			require_once 'commonselect.php';
			makeSelectGuiUserComplexity($context['gui_user_complexity']);
			break;
		case 'g_type' :
			require_once 'fieldfunc.php';
			$g_typefields = $GLOBALS['g_persontypes_fields'];
			$dao=$this->_getMainTableDAO();
			$types = $dao->findMany('status > 0', '', 'g_type, title');
			foreach($types as $type){
				$arr[$type->g_type] = $type->title;
			}

			$arr2 = array('' => '--');
			foreach($g_typefields as $g_type) {
				$lg_type=strtolower($g_type);
				if ($arr[$lg_type]) {
					$arr2[$lg_type]=$g_type." &rarr; ".$arr[$lg_type];
				} else {
					$arr2[$lg_type]=$g_type;
				}
			}
			renderOptions($arr2,$context['g_type']);
			break;
		}
	}

	/**
	* Pr�paration de l'action Edit
	*
	* @access private
	* @param object $dao la DAO utilis�e
	* @param array &$context le context pass� par r�f�rence
	*/
	function _prepareEdit($dao,&$context)

	{
		// gather information for the following
		if ($context['id']) {
			$this->oldvo=$dao->getById($context['id']);
			if (!$this->oldvo) die("ERROR: internal error in PersonTypesLogic::_prepareEdit");
		}
	}


	/**
	* Sauve des donn�es dans des tables li�es �ventuellement
	*
	* Appel� par editAction pour effectuer des op�rations suppl�mentaires de sauvegarde.
	*
	* @param object $vo l'objet qui a �t� cr��
	* @param array $context le contexte
	*/
		function _saveRelatedTables($vo,$context) 

	{
		#print_r($vo);
		#print_r($this->oldvo);

		if ($vo->type!=$this->oldvo->type) {
			// name has changed
			$GLOBALS['db']->execute(lq("UPDATE #_TP_tablefields SET name='".$vo->type."' WHERE name='".$this->oldvo->type."' AND type='persons'")) or dberror();
		}
	}
	/**
	* Appel� avant l'action delete
	*
	* Cette m�thode est appel�e avant l'action delete pour effectuer des v�rifications
	* pr�liminaires � une suppression.
	*
	* @param object $dao la DAO utilis�e
	* @param array &$context le contexte pass� par r�f�r�nce
	*/
	function _prepareDelete($dao,&$context)

	{     
		// gather information for the following
		$this->vo=$dao->getById($context['id']);
		if (!$this->vo) die("ERROR: internal error in PersonTypesLogic::_prepareDelete");
	}

	function _deleteRelatedTables($id) {
		global $home;

		//require_once("typetypefunc.php"); 
		//typetype_delete("persontype","idpersontype='".$id."'");
			
		$dao=&getDAO("tablefields");
		$dao->delete("type='persons' AND name='".$this->vo->type."'");
	}


	// begin{publicfields} automatic generation  //

	/**
	 * Retourne la liste des champs publics
	 * @access private
	 */
	function _publicfields() 
	{
		return array('type' => array('type', '+'),
									'class' => array('class', '+'),
									'title' => array('text', '+'),
									'altertitle' => array('mltext', ''),
									'icon' => array('image', ''),
									'g_type' => array('select', ''),
									'style' => array('style', ''),
									'gui_user_complexity' => array('select', '+'),
									'tpl' => array('tplfile', ''),
									'tplindex' => array('tplfile', ''));
	}
	// end{publicfields} automatic generation  //

	// begin{uniquefields} automatic generation  //

	/**
	 * Retourne la liste des champs uniques
	 * @access private
	 */
	function _uniqueFields() 
	{ 
		return array(array('type'), );
	}
	// end{uniquefields} automatic generation  //


} // class 


/*-----------------------------------*/
/* loops                             */

function loop_entitytypes($context,$funcname)
{ require_once("typetypefunc.php"); 
	loop_typetable ("entitytype","persontype",$context,$funcname,$_POST['edit'] ? $context['entitytype'] : -1);}




?>
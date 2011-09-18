<?php

/**
 * @package zpanelx
 * @subpackage dryden -> ui -> tpl
 * @author Bobby Allen (ballen@zpanelcp.com)
 * @copyright ZPanel Project (http://www.zpanelcp.com/)
 * @link http://www.zpanelcp.com/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
class ui_tpl_subdomains {

    public function Template() {
		global $controller;
		$currentuser = ctrl_users::GetUserDetail();
		$domain = ctrl_users::GetUserDomains($currentuser['userid'], 2);
		if ($domain <> 0){
        	return (string) $domain;
		} 
		return (string) 0;
    }

}

?>
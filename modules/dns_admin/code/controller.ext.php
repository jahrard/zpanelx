<?php

/**
 *
 * ZPanel - A Cross-Platform Open-Source Web Hosting Control panel.
 * 
 * @package ZPanel
 * @version $Id$
 * @author Bobby Allen - ballen@zpanelcp.com
 * @copyright (c) 2008-2011 ZPanel Group - http://www.zpanelcp.com/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 *
 * This program (ZPanel) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class module_controller {

    static $ok;
    static $service;
    static $purged;
    static $deleted;
    static $deletedtype;
    static $type;
    static $reset;
    static $addmissing;
    static $logerror;
    static $logwarning;
    static $getlog;
    static $showlog;
    static $notwritable;
    static $forceupdate;

    static function getDNSConfig() {
        $display = self::DisplayDNSConfig();
        return $display;
    }

    static function DisplayDNSConfig() {
        global $zdbh;
        global $controller;
      $line = "<fieldset>";
        $line = "<legend class=\"module-legend\">" . ui_language::translate("Configure Your DNS Settings") . "</legend>";
        $line .= "<ul class=\"nav nav-pills\" id=\"dns-pills\">";
        $line .= "<li class=\"active\"><a href=\"#general\" data-toggle=\"tab\">" . ui_language::translate("General") . "</a></li>";
        $line .= "<li><a href=\"#tools\" data-toggle=\"tab\">" . ui_language::translate("Tools") . "</a></li>";
        $line .= "<li><a href=\"#services\" data-toggle=\"tab\">" . ui_language::translate("Services") . "</a></li>";
        $line .= "<li><a href=\"#logs\" data-toggle=\"tab\">" . ui_language::translate("Logs") . "</a></li>";
        $line .= "</ul>";
        $line .= "<div class=\"tab-content\">";
        //general
        $line .= "<div class=\"tab-pane active\" id=\"general\">";
        $line .= "<form class=\"form-horizontal\" action=\"./?module=dns_admin&action=UpdateDNSConfig\" method=\"post\">";
        $count = 0;
        $sql = "SELECT COUNT(*) FROM x_settings WHERE so_module_vc=:moduleName AND so_usereditable_en = 'true'";
        $numrows = $zdbh->prepare($sql);
        $GetModuleName = ui_module::GetModuleName();
        $numrows->bindParam(':moduleName', $GetModuleName);
        if ($numrows->execute()) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_settings WHERE so_module_vc=:moduleName AND so_usereditable_en = 'true' ORDER BY so_cleanname_vc");
                $GetModuleName = ui_module::GetModuleName();
                $sql->bindParam(':moduleName', $GetModuleName);
                $sql->execute();

                while ($row = $sql->fetch()) {
                    $count++;
                    if (ctrl_options::CheckForPredefinedOptions($row['so_defvalues_tx'])) {
                        $fieldhtml = ctrl_options::OuputSettingMenuField($row['so_name_vc'], $row['so_defvalues_tx'], $row['so_value_tx']);
                    } else {
                        $fieldhtml = ctrl_options::OutputSettingTextArea($row['so_name_vc'], $row['so_value_tx']);
                    }
                    $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate($row['so_cleanname_vc']) . "</label><div class=\"controls\">" . $fieldhtml . "<span class=\"help-inline\">" . ui_language::translate($row['so_desc_tx']) . "</span></div></div>";
                }
                $line .= "<div class=\"control-group\"><div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inSaveSystem\">" . ui_language::translate("Save Changes") . "</button>&nbsp;&nbsp;&nbsp;<button class=\"btn\" onclick=\"window.location.href='./?module=moduleadmin';return false;\">" . ui_language::translate("Cancel") . "</button></div></div>";
            }
        }
        $line .= "</form>";
        $line .= "</div>";
        //tools
        $line .= "<div class=\"tab-pane\" id=\"tools\">";
        $line .= "<form class=\"form-horizontal\" action=\"./?module=dns_admin&action=UpdateTools\" method=\"post\">";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Reset all Records to Default") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inResetAll\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\" for=\"select\">" . ui_language::translate("Reset Records to Default on Single Domain") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><select name=\"inResetDomainID\">";
        $line .= "<option value=\"\">--- " . ui_language::translate("Select Domain") . " ---</option>";
        $sql = "SELECT COUNT(*) FROM x_vhosts WHERE vh_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_deleted_ts IS NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $line .= " <option value=\"" . $row['vh_id_pk'] . "\">" . $row['vh_name_vc'] . "</option>";
                }
            }
        }
        $line .= "</select>";
        $line .= "&nbsp;&nbsp;&nbsp;";
        $line .= "<button class=\"btn load-button\" type=\"submit\" name=\"inResetDomain\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\" for=\"button\">" . ui_language::translate("Add Default Records to Missing Domains") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inAddMissing\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\" for=\"inType\">" . ui_language::translate("Delete Record Type from ALL Records") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><select name=\"inType\" id=\"inType\">";
        $line .= "<option value=\"A\">A</option>";
        $line .= "<option value=\"AAAA\">AAAA</option>";
        $line .= "<option value=\"CNAME\">CNAME</option>";
        $line .= "<option value=\"MX\">MX</option>";
        $line .= "<option value=\"TXT\">TXT</option>";
        $line .= "<option value=\"SRV\">SRV</option>";
        $line .= "<option value=\"SPF\">SPF</option>";
        $line .= "<option value=\"NS\">NS</option>";
        $line .= "</select>";
        $line .= "&nbsp;&nbsp;&nbsp;";
        $line .= "<button class=\"btn load-button\" type=\"submit\" name=\"inDeleteType\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Purge Deleted Zone Records From Database") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inPurge\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Delete ALL Zone Records") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inDeleteAll\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Force Records Update on Next Daemon Run") . "</label>";
        $line .= "<div class=\"controls fix-controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inForceUpdate\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "</form>";
        $line .= "</div>";
        //Services
        $line .= "<div class=\"tab-pane\" id=\"services\">";
        $line .= "<form class=\"form-horizontal\" action=\"./?module=dns_admin&action=UpdateService\" method=\"post\">";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Start Service") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inStartService\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Stop Service") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inStopService\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Reload BIND") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inReloadService\" value=\"1\">" . ui_language::translate("GO") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Service Port Status") . ":</label>";
        if (fs_director::CheckForEmptyValue(sys_monitoring::PortStatus(53))) {
            $line .= "<div class=\"controls fix-controls-text\"><font color=\"red\">" . ui_language::translate("STOPPED") . "</font>";
        } else {
            $line .= "<div class=\"controls fix-controls-text\"><font color=\"green\">" . ui_language::translate("RUNNING") . "</font>";
        }
        if (fs_director::CheckForEmptyValue(sys_monitoring::PortStatus(53))) {
            $line .= "<img src=\"/modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/down.png\" height=\"25px\" width=\"25px\" border=\"0\"/></div></div>";
        } else {
            $line .= "<img src=\"/modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/up.png\" height=\"25px\" width=\"25px\" border=\"0\"/></div></div>";
        }
        $line .= "</form>";
        $line .= "</div>";
        //logs
        self::ViewErrors();
        
        $line .= "<div class=\"tab-pane\" id=\"logs\">";
        $line .= "<form class=\"form-horizontal\" action=\"./?module=dns_admin&action=Updatelogs\" method=\"post\">";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . self::CheckLogReadable(ctrl_options::GetSystemOption('bind_log')) . " " . self::CheckLogWritable(ctrl_options::GetSystemOption('bind_log')) . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inSetPerms\" value=\"1\">" . ui_language::translate("Set Permissions") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Clear Errors") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inClearErrors\" value=\"1\">" . ui_language::translate("Clear") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Clear Warnings") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inClearWarnings\" value=\"1\">" . ui_language::translate("Clear") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("Clear Logs") . "</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" name=\"inClearLogs\" value=\"1\">" . ui_language::translate("Clear") . "</button></div></div>";
        $line .= "</form>";
        $line .= "<form class=\"form-horizontal\" name=\"launchbindlog\" action=\"modules/dns_admin/code/getbindlog.php\" target=\"bindlogwindow\" method=\"post\" onsubmit=\"window.open('', 'bindlogwindow', 'scrollbars=yes,menubar=no,height=525,width=825,resizable=no,toolbar=no,location=no,status=no')\">";
        if (count(self::$logerror) > 0) {
            $logerrorcolor = "red";
        } else {
            $logerrorcolor = NULL;
        }
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("View Errors") . " (<font color=\"" . $logerrorcolor . "\">" . count(self::$logerror) . "</font>)</label>";
        $line .= "<div class=\"controls\"><button class=\"btn load-button\" type=\"submit\" id=\"logerror_a\" name=\"inViewErrors\" value=\"1\">" . ui_language::translate("View") . "</button></div></div>";
        if (count(self::$logwarning) > 0) {
            $logwarningcolor = "red";
        } else {
            $logwarningcolor = NULL;
        }
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("View warnings") . " (<font color=\"" . $logwarningcolor . "\">" . count(self::$logwarning) . "</font>)</label>";
        $line .= "<div class=\"controls\"><button class=\"btn\" type=\"submit\" id=\"logwarning_a\" name=\"inViewWarnings\" value=\"1\">" . ui_language::translate("View") . "</button></div></div>";
        $line .= "<div class=\"control-group\"><label class=\"control-label\" id=\"bold-label\">" . ui_language::translate("View logs") . " (" . count(self::$getlog) . ")</label>";
        $line .= "<div class=\"controls\"><input type=\"hidden\" name=\"inBindLog\" value=\"" . ctrl_options::GetSystemOption('bind_log') . "\" /><button class=\"btn\" type=\"submit\" id=\"button\" name=\"inViewLogs\" value=\"1\">" . ui_language::translate("View") . "</button></div></div>";
        $line .= "</form>";
        $line .= "</div>";




        $line .= "</div>";

        //CHARTS
        $line .= self::DisplayDNSUsagepChart();
        $line .= self::DisplayRecordsUsagepChart();

        return $line;
    }

    static function doUpdateDNSConfig() {
        global $zdbh;
        global $controller;
        $sql = "SELECT COUNT(*) FROM x_settings WHERE so_module_vc=:moduleName AND so_usereditable_en = 'true'";
        $numrows = $zdbh->prepare($sql);
        $GetModuleName = ui_module::GetModuleName();
        $numrows->bindParam(':moduleName', $GetModuleName);
        
        if ($numrows->execute()) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_settings WHERE so_module_vc=:moduleName AND so_usereditable_en = 'true'");
                $GetModuleName = ui_module::GetModuleName();
                $sql->bindParam(':moduleName', $GetModuleName);
                $sql->execute();
                while ($row = $sql->fetch()) {
                    if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', $row['so_name_vc']))) {
                        $name = $controller->GetControllerRequest('FORM', $row['so_name_vc']);
                        $name2 = $row['so_name_vc'];
                        $updatesql = $zdbh->prepare("UPDATE x_settings SET so_value_tx = :name WHERE so_name_vc = :name2");
                        $updatesql->bindParam(':name', $name);
                        $updatesql->bindParam(':name2', $name2);
                        $updatesql->execute();
                        self::TriggerDNSUpdate("0");
                    }
                }
            }
        }
    }

    static function doUpdateService() {
        global $zdbh;
        global $controller;
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inStartService'))) {
            self::StartBind();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inStopService'))) {
            self::StopBind();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inReloadService'))) {
            self::ReloadBind();
        }
    }

    static function doUpdateTools() {
        global $zdbh;
        global $controller;
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inResetAll'))) {
            self::ResetAll();
            self::TriggerDNSUpdate("0");
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inResetDomain')) && !fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inResetDomainID'))) {
            self::ResetDomain($controller->GetControllerRequest('FORM', 'inResetDomainID'));
            self::TriggerDNSUpdate("0");
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inAddMissing'))) {
            self::AddMissing();
            self::TriggerDNSUpdate("0");
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inDeleteType'))) {
            self::DeleteType();
            self::TriggerDNSUpdate("0");
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inPurge'))) {
            self::Purge();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inDeleteAll'))) {
            self::DeleteAll();
            self::TriggerDNSUpdate("0");
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inForceUpdate'))) {
            self::$forceupdate = true;
            self::TriggerDNSUpdate("0");
        }
    }

    static function doUpdateLogs() {
        global $zdbh;
        global $controller;
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inSetPerms'))) {
            self::SetPerms();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inClearErrors'))) {
            self::ClearErrors();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inClearWarnings'))) {
            self::ClearWarnings();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inClearLogs'))) {
            self::ClearLog();
        }
        if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'inViewLogs'))) {
            //self::ViewLogs();
            self::$showlog = TRUE;
        }
    }

    static function StartBind() {
        if (sys_versions::ShowOSPlatformVersion() == "Windows") {
            exec('net start ' . ctrl_options::GetSystemOption('bind_service') . '', $out);
        } else {
            system(ctrl_options::GetSystemOption('zsudo') . ' service ' . ctrl_options::GetSystemOption('bind_service') . ' start', $out);
            sleep(2);
        }
    }

    static function StopBind() {
        if (sys_versions::ShowOSPlatformVersion() == "Windows") {
            exec('net stop ' . ctrl_options::GetSystemOption('bind_service') . '', $out);
        } else {
            system(ctrl_options::GetSystemOption('zsudo') . ' service ' . ctrl_options::GetSystemOption('bind_service') . ' stop', $out);
            sleep(2);
        }
    }

    static function ReloadBind() {
        if (sys_versions::ShowOSPlatformVersion() == "Windows") {
            $reload_bind = ctrl_options::GetSystemOption('bind_dir') . 'rndc.exe reload';
        } else {
            $reload_bind = ctrl_options::GetSystemOption('zsudo') . " service " . ctrl_options::GetSystemOption('bind_service') . " reload";
        }
        pclose(popen($reload_bind, 'r'));
    }

    static function ResetAll() {
        global $zdbh;
        global $controller;
        $vhosts = array();
        $numrecords = 0;
        //Get a list of current domains with records
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_deleted_ts IS NULL GROUP BY dn_vhost_fk");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $vhosts[] = $row['dn_vhost_fk'];
                    $numrecords++;
                }
            }
        }
        self::$reset = $numrecords;
        //Delete current records
        self::DeleteAll();
        //Create Default Records
        foreach ($vhosts as $vhost) {
            self::CreateDefaultRecords($vhost);
        }
    }

    static function ResetDomain($dn_vhost_fk) {
        global $zdbh;
        //Delete current records
        self::DeleteDomainRecords($dn_vhost_fk);
        //Create Default Records
        self::CreateDefaultRecords($dn_vhost_fk);
        self::$ok = true;
    }

    static function AddMissing() {
        global $zdbh;
        global $controller;
        $vhosts = array();
        $numrecords = 0;
        $sql = "SELECT COUNT(*) FROM x_vhosts WHERE vh_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_deleted_ts IS NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $vhosts[] = $row['vh_id_pk'];
                }
            }
        }
        if (!fs_director::CheckForEmptyValue($vhosts)) {
            foreach ($vhosts as $vhost) {
                $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_vhost_fk = :vhost AND dn_deleted_ts IS NULL";
                $numrows = $zdbh->prepare($sql);
                $numrows->bindParam(':vhost', $vhost);
                if ($numrows->execute()) {
                    if ($numrows->fetchColumn() == 0) {
                        self::CreateDefaultRecords($vhost);
                        $numrecords++;
                    }
                }
            }
            self::$addmissing = $numrecords;
        }
    }

    static function DeleteType() {
        global $zdbh;
        global $controller;
        $numrecords = 0;
        $type = $controller->GetControllerRequest('FORM', 'inType');
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_type_vc = :type AND dn_deleted_ts IS NULL";
        $numrows = $zdbh->prepare($sql);
        $numrows->bindParam(':type', $type);
        
        
        if ($numrows->execute()) {
            if ($numrows->fetchColumn() <> 0) {
                $type = $controller->GetControllerRequest('FORM', 'inType');
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_type_vc = :type AND dn_deleted_ts IS NULL");
                $sql->bindParam(':type', $type);
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $time = time();
                    $type = $controller->GetControllerRequest('FORM', 'inType');
                    $delete_record = $zdbh->prepare("UPDATE x_dns SET dn_deleted_ts=:time WHERE dn_id_pk = :dn_id_pk AND dn_type_vc = :type");
                    $delete_record->bindParam(':time', $time);
                    $delete_record->bindParam(':dn_id_pk', $row['dn_id_pk']);
                    $delete_record->bindParam(':type', $type);
                    $delete_record->execute();
                    $numrecords++;
                }
                self::$deletedtype = $numrecords;
                self::$type = $controller->GetControllerRequest('FORM', 'inType');
            }
        }
    }

    static function Purge() {
        global $zdbh;
        global $controller;
        $numrecords = 0;
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_deleted_ts IS NOT NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_deleted_ts IS NOT NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $delete_record = $zdbh->prepare("DELETE FROM x_dns WHERE dn_id_pk = :dn_id_pk");
                    $delete_record->bindParam(':dn_id_pk', $row['dn_id_pk']);
                    $delete_record->execute();

                    $numrecords++;
                }
                self::$purged = $numrecords;
            }
        }
    }

    static function DeleteAll() {
        global $zdbh;
        global $controller;
        $numrecords = 0;
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_deleted_ts IS NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $time = time();
                    $delete_record = $zdbh->prepare("UPDATE x_dns SET dn_deleted_ts=:time WHERE dn_id_pk = :dn_id_pk");
                    $delete_record->bindParam(':time', $time);
                    $delete_record->bindParam(':dn_id_pk', $row['dn_id_pk']);
                    $delete_record->execute();
                    $numrecords++;
                }
                self::$deleted = $numrecords;
            }
        }
    }

    static function DeleteDomainRecords($domainid) {
        global $zdbh;
        global $controller;
        $numrecords = 0;
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_vhost_fk=:domainid AND dn_deleted_ts IS NULL";
        $numrows = $zdbh->prepare($sql);
        $numrows->bindParam(':domainid', $domainid);
        if ($numrows->execute()) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_vhost_fk=:domainid AND dn_deleted_ts IS NULL");
                $sql->bindParam(':domainid', $domainid);
                $sql->execute();
                while ($row = $sql->fetch()){ 
                    $time = time();
                    $delete_record = $zdbh->prepare("UPDATE x_dns SET dn_deleted_ts=:time WHERE dn_id_pk = :dn_id_pk");
                    $delete_record->bindParam(':time', $time);
                    $delete_record->bindParam(':dn_id_pk', $row['dn_id_pk']);
                    $delete_record->execute();
                    $numrecords++;                 
                }
                self::$deleted = $numrecords;
            }
        }
    }

    static function SetPerms() {
        $bindlog = ctrl_options::GetSystemOption('bind_log');
        if (sys_versions::ShowOSPlatformVersion() <> "Windows") {
            //exec(ctrl_options::GetOption('zsudo') . " chgrp " . ctrl_options::GetOption('zsudo') . " " . $bindlog);
            exec(ctrl_options::GetSystemOption('zsudo') . " chmod 0777 " . $bindlog);
        }
    }

    static function ClearErrors() {
        $bindlog = ctrl_options::GetSystemOption('bind_log');
        if (is_writable($bindlog)) {
            $log = $bindlog;
            if (file_exists($bindlog)) {
                $handle = @fopen($log, "r");
                $getlog = array();
                if ($handle) {
                    while (!feof($handle)) {
                        $buffer = fgets($handle, 4096);
                        if (strstr($buffer, 'error:') || strstr($buffer, 'error ')) {
                            $line = "";
                        } else {
                            $line = $buffer;
                        }
                        $getlog[] = $line;
                    }fclose($handle);
                }

                $fp = fopen($log, 'w');
                foreach ($getlog as $key => $value) {
                    fwrite($fp, $value);
                }
                fclose($fp);
            }
        } else {
            self::$notwritable = true;
        }
    }

    static function ClearWarnings() {
        $bindlog = ctrl_options::GetSystemOption('bind_log');
        if (is_writable($bindlog)) {
            $log = $bindlog;
            if (file_exists($bindlog)) {
                $handle = @fopen($log, "r");
                $getlog = array();
                if ($handle) {
                    while (!feof($handle)) {
                        $buffer = fgets($handle, 4096);
                        if (strstr($buffer, 'warning:') || strstr($buffer, 'warning ')) {
                            $line = "";
                        } else {
                            $line = $buffer;
                        }
                        $getlog[] = $line;
                    }fclose($handle);
                }

                $fp = fopen($log, 'w');
                foreach ($getlog as $key => $value) {
                    fwrite($fp, $value);
                }
                fclose($fp);
            }
        } else {
            self::$notwritable = true;
        }
    }

    static function ClearLog() {
        $bindlog = ctrl_options::GetSystemOption('bind_log');
        if (is_writable($bindlog)) {
            $log = $bindlog;
            if (file_exists($bindlog)) {
                $fp = fopen($log, 'w');
                fwrite($fp, '');
                fclose($fp);
            }
        } else {
            self::$notwritable = true;
        }
    }

    static function DisplayDNSUsagepChart() {
        global $zdbh;
        global $controller;
        $numtotalrecords = 0;
        $numactiverecords = 0;
        $sql = "SELECT COUNT(*) FROM x_dns";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $numtotalrecords++;
                }
            }
        }
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_deleted_ts IS NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $numactiverecords++;
                }
            }
        }
        $total = $numtotalrecords;
        $active = $numactiverecords;
        $deleted = $total - $active;
        $line .= "<div class=\"row-fluid\">";
        $line .= "<div class=\"span6\">";
        $line .= "<fieldset>";
        $line .= "<legend class=\"module-legend\">DNS Database Usage</legend>";
        $line .= "<div class=\"text-center\"><img src=\"etc/lib/pChart2/zpanel/z3DPie.php?score=" . $active . "::" . $deleted . "&labels=Active Domain Records:   " . $active . "::Deleted Domain Records: " . $deleted . "&legendfont=verdana&legendfontsize=8&imagesize=240::190&chartsize=120::90&radius=100&legendsize=10::160\"/></div>";
        $line .= "</fieldset></div>";
        return $line;
    }

    static function DisplayRecordsUsagepChart() {
        global $zdbh;
        global $controller;
        $numtotalrecords = 0;
        $numArecords = 0;
        $numAAAArecords = 0;
        $numMXrecords = 0;
        $numCNAMErecords = 0;
        $numTXTrecords = 0;
        $numSRVrecords = 0;
        $numSPFrecords = 0;
        $numNSrecords = 0;
        $sql = "SELECT COUNT(*) FROM x_dns";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    $numtotalrecords++;
                }
            }
        }
        $sql = "SELECT COUNT(*) FROM x_dns WHERE dn_deleted_ts IS NULL";
        if ($numrows = $zdbh->query($sql)) {
            if ($numrows->fetchColumn() <> 0) {
                $sql = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_deleted_ts IS NULL");
                $sql->execute();
                while ($row = $sql->fetch()) {
                    if ($row['dn_type_vc'] == "A") {
                        $numArecords++;
                    }
                    if ($row['dn_type_vc'] == "AAAA") {
                        $numAAAArecords++;
                    }
                    if ($row['dn_type_vc'] == "MX") {
                        $numMXrecords++;
                    }
                    if ($row['dn_type_vc'] == "CNAME") {
                        $numCNAMErecords++;
                    }
                    if ($row['dn_type_vc'] == "TXT") {
                        $numTXTrecords++;
                    }
                    if ($row['dn_type_vc'] == "SRV") {
                        $numSRVrecords++;
                    }
                    if ($row['dn_type_vc'] == "SPF") {
                        $numSPFrecords++;
                    }
                    if ($row['dn_type_vc'] == "NS") {
                        $numNSrecords++;
                    }
                }
            }
        }
        $total = $numtotalrecords;
        $Arecords = $numArecords;
        $AAAArecords = $numAAAArecords;
        $MXrecords = $numMXrecords;
        $CNAMErecords = $numCNAMErecords;
        $TXTrecords = $numTXTrecords;
        $SRVrecords = $numSRVrecords;
        $SPFrecords = $numSPFrecords;
        $NSrecords = $numNSrecords;
        $line .= "<div class=\"span6\">";
        $line .= "<fieldset>";
        $line .= "<legend class=\"module-legend\">Record Types Usage</legend>";
        $line .= "<div class=\"text-center\"><img src=\"etc/lib/pChart2/zpanel/z3DPie.php?score=" . $Arecords . "::" . $NSrecords . "::" . $MXrecords . "::" . $SPFrecords . "::" . $TXTrecords . "::" . $SRVrecords . "::" . $CNAMErecords . "::" . $AAAArecords . "&labels=A: " . $Arecords . "::NS: " . $NSrecords . "::MX: " . $MXrecords . "::SPF: " . $SPFrecords . "::TXT: " . $TXTrecords . "::SRV: " . $SRVrecords . "::CNAME: " . $CNAMErecords . "::AAAA: " . $AAAArecords . "&legendfont=verdana&legendfontsize=8&imagesize=340::190&chartsize=120::90&radius=100&legendsize=240::80\"/></div>";
        $line .= "</fieldset></div></div>";
        return $line;
    }

    static function CreateDefaultRecords($vh_acc_fk) {
        global $zdbh;
        global $controller;
        $domainID = $vh_acc_fk;
        $domainName = $zdbh->query("SELECT * FROM x_vhosts WHERE vh_id_pk=" . $domainID . " AND vh_deleted_ts IS NULL")->Fetch();
        $userID = $domainName['vh_acc_fk'];
        if (!fs_director::CheckForEmptyValue(ctrl_options::GetSystemOption('server_ip'))) {
            $target = ctrl_options::GetSystemOption('server_ip');
        } else {
            $target = $_SERVER["SERVER_ADDR"];
        }
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'A',
															'@',
															3600,
															:target,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $sql->bindParam(':target', $target);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'CNAME',
															'www',
															3600,
															'@',
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'CNAME',
															'ftp',
															3600,
															'@',
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'A',
															'mail',
															86400,
															:target,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $sql->bindParam(':target', $target);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'MX',
															'@',
															86400,
															:vh_name_vc,
															10,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $Domain = 'mail.'.$domainName['vh_name_vc'];
        $sql->bindParam(':vh_name_vc', $Domain);
        $sql->bindParam(':domainID', $domainID);
        $sql->bindParam(':target', $target);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'A',
															'ns1',
															172800,
															:target,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $sql->bindParam(':target', $target);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'A',
															'ns2',
															172800,
															:target,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);
        $sql->bindParam(':domainID', $domainID);
        $sql->bindParam(':target', $target);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'NS',
															'@',
															172800,
															:vh_name_vc2,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $Domain = 'ns1.'.$domainName['vh_name_vc'];
        $sql->bindParam(':vh_name_vc', $Domain);
        $sql->bindParam(':domainID', $domainID);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->bindParam(':vh_name_vc2', $domainName['vh_name_vc']);
        $sql->execute();
        $sql = $zdbh->prepare("INSERT INTO x_dns (dn_acc_fk,
															dn_name_vc,
															dn_vhost_fk,
															dn_type_vc,
															dn_host_vc,
															dn_ttl_in,
															dn_target_vc,
															dn_priority_in,
															dn_weight_in,
															dn_port_in,
															dn_created_ts) VALUES (
															:userID,
															:vh_name_vc,
															:domainID,
															'NS',
															'@',
															172800,
															:ns2,
															NULL,
															NULL,
															NULL,
															:time)");
        $sql->bindParam(':userID', $userID);
        $Domain = 'ns2.'.$domainName['vh_name_vc'];
        $sql->bindParam(':ns2', $Domain);
        $sql->bindParam(':domainID', $domainID);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->bindParam(':vh_name_vc', $domainName['vh_name_vc']);        
        $sql->execute();
        return;
    }

    static function ViewErrors() {
        $bindlog = ctrl_options::GetSystemOption('bind_log');
        $logerror = array();
        $logwarning = array();
        $getlog = array();
        if (file_exists($bindlog)) {
            $handle = @fopen($bindlog, "r");
            $getlog = array();
            if ($handle) {
                while (!feof($handle)) {
                    $buffer = fgets($handle, 4096);
                    $getlog[] = $buffer;
                    if (strstr($buffer, 'error:') || strstr($buffer, 'error ')) {
                        $logerror[] = $buffer;
                    }
                    if (strstr($buffer, 'warning:') || strstr($buffer, 'warning ')) {
                        $logwarning[] = $buffer;
                    }
                }fclose($handle);
                if (!fs_director::CheckForEmptyValue($logerror)) {
                    self::$logerror = $logerror;
                }
                if (!fs_director::CheckForEmptyValue($logwarning)) {
                    self::$logwarning = $logwarning;
                }
                if (!fs_director::CheckForEmptyValue($getlog)) {
                    self::$getlog = $getlog;
                }
            }
        }
    }

    static function getResult() {
        if (!fs_director::CheckForEmptyValue(self::$ok)) {
            return ui_sysmessage::shout(ui_language::translate("Changes to your settings have been saved successfully!"), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$notwritable)) {
            return ui_sysmessage::shout(ui_language::translate("No permission to write to log file."), "alert-error");
        }
        if (!fs_director::CheckForEmptyValue(self::$forceupdate)) {
            return ui_sysmessage::shout(ui_language::translate("All zone records will be updated on next daemon run."), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$reset)) {
            return ui_sysmessage::shout(number_format(self::$reset) . " " . ui_language::translate("Domains records where reset to default"), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$addmissing)) {
            return ui_sysmessage::shout(number_format(self::$addmissing) . " " . ui_language::translate("Domains records were created"), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$deletedtype)) {
            return ui_sysmessage::shout(number_format(self::$deletedtype) . " '" . self::$type . "' " . ui_language::translate("Records where marked as deleted from the database"), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$deleted)) {
            return ui_sysmessage::shout(number_format(self::$deleted) . " " . ui_language::translate("Records where marked as deleted from the database"), "alert-success");
        }
        if (!fs_director::CheckForEmptyValue(self::$purged)) {
            return ui_sysmessage::shout(number_format(self::$purged) . " " . ui_language::translate("Records where purged from the database"), "alert-success");
        }
        return;
    }

    static function getModuleName() {
        $module_name = ui_module::GetModuleName();
        return $module_name;
    }

    static function getModuleIcon() {
        global $controller;
        $module_icon = "./modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/icon.png";
        return $module_icon;
    }

    static function getModuleDesc() {
        $message = ui_language::translate(ui_module::GetModuleDescription());
        return $message;
    }

    static function TriggerDNSUpdate($id) {
        global $zdbh;
        global $controller;
        $GetRecords = ctrl_options::GetSystemOption('dns_hasupdates');
        $records = explode(",", $GetRecords);
        foreach ($records as $record) {
            $RecordArray[] = $record;
        }
        if (!in_array($id, $RecordArray)) {
            $newlist = $GetRecords . "," . $id;
            $newlist = str_replace(",,", ",", $newlist);
            $sql = "UPDATE x_settings SET so_value_tx=:newlist WHERE so_name_vc='dns_hasupdates'";
            $sql = $zdbh->prepare($sql);
            $sql->bindParam(':newlist', $newlist);
            $sql->execute();
            return true;
        }
    }

    static function CheckLogReadable($filename) {
        if (is_readable($filename)) {
            $retval = "<font color=\"green\">" . ui_language::translate("Connected to log file") . "</font>";
        } else {
            $retval = "<font color=\"red\">" . ui_language::translate("Log file is not Readable") . "</font>";
        }
        return $retval;
    }

    static function CheckLogWritable($filename) {
        if (is_readable($filename)) {
            if (is_writable($filename)) {
                $retval = "<font color=\"green\">" . ui_language::translate("(writable)") . "</font>";
            } else {
                $retval = "<font color=\"red\">" . ui_language::translate("(readonly)") . "</font>";
            }
        } else {
            $retval = NULL;
        }
        return $retval;
    }

}

?>

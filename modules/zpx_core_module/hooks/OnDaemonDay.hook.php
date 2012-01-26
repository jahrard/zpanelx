<?php

global $zdbh;
$modsql = $zdbh->prepare("SELECT * FROM x_modules ORDER BY mo_name_vc ASC");
$modsql->execute();
while ($modules = $modsql->fetch()) {

    $mod_config = new xml_reader(fs_filehandler::ReadFileContents(ctrl_options::GetOption('zpanel_root') . 'modules/' . $modules['mo_folder_vc'] . '/module.xml'));
    $mod_config->Parse();
    if (true) {
        $current_version = $mod_config->document->version[0]->tagData;
        $updateurl = $mod_config->document->updateurl[0]->tagData;

        $updateinfo = new xml_reader(readfile($updateurl));
        $updateinfo->Parse();
        if (true) {
            $latest_version = $updateinfo->document->latestversion[0]->tagData;
            $downloadurl = $updateinfo->document->downloadurl[0]->tagData;
            if ($current_version < $latest_version) {
                $versionsql = $zdbh->prepare("UPDATE x_modules SET mo_updatever_vc = '$latest_version', mo_updateurl_tx = '$downloadurl' WHERE mo_id_pk = " . $modules['mo_id_pk'] . "");
            } else {
                $versionsql = $zdbh->prepare("UPDATE x_modules SET mo_updatever_vc = '', mo_updateurl_tx = '' WHERE mo_id_pk = " . $modules['mo_id_pk'] . "");
            }
            $versionsql->execute();
        } else {
            echo "\r\nThe remote file was not parsed correctly.";
        }
    } else {
        // Couldn't parse the module XML file that the server has for the module.
        echo "\r\nCouldn't open the module XML file for (" . $modules['mo_name_vc'] . ") in [" . ctrl_options::GetOption('zpanel_root') . 'modules/' . $modules['mo_folder_vc'] . '/module.xml' . "].";
    }
}
return true;
?>
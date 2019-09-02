<?php

    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir.'/adminlib.php');

    use \local_enrolmultiselect\type\settings\designation;
    use \local_enrolmultiselect\type\settings\potential_designation;

    $confirmadd = optional_param('confirmadd', 0, PARAM_INT);
    $confirmdel = optional_param('confirmdel', 0, PARAM_INT);

    $PAGE->set_url('/local/enrolmultiselect/designation.php');
    $PAGE->requires->jquery();
    
    $pluginName = $_GET['plugin_name']; //validation required, this is an important parameter and must be set when url to this location is defined

    admin_externalpage_setup("{$pluginName}_designations");

    if (!is_siteadmin()) {
        die;
    }

    // Print header.
    echo $OUTPUT->header();

    $warwickAutoUrl = new moodle_url($PAGE->url);
        
    $designationCurrentSelector = new designation("s_{$pluginName}_designation_add", [ 'plugin'=> $pluginName ] );
    $designationPotentialSelector = new potential_designation("s_{$pluginName}_designation_remove", [ 'plugin' => $pluginName ] );

    if ( !empty( $_POST ) && confirm_sesskey()) {
        $designationsToAssign = $designationCurrentSelector->get_selected_users();

        if ( !empty( $designationsToAssign ) ) {
            $designationCurrentSelector->addToConfig( $designationsToAssign );

            $designationPotentialSelector->invalidate_selected_users();
            $designationCurrentSelector->invalidate_selected_users();
        }else{
            $designationCurrentSelector->removeConfig();
        }
    }
?>
<div class="alert alert-info">
    <?php print_string('selectionmoveinfo', 'local_enrolmultiselect');?>
</div>
<form id="<?php echo "s_{$pluginName}_designation_add_form" ?>" action="<?php echo $warwickAutoUrl ?>" method="post">
    <div>
        <input type="hidden" name="section" value="settings_<?php echo $pluginName; ?>">
        <input type="hidden" name="action" value="save-settings">
        <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>">
        <input type="hidden" name="return" value="">
      
        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
            <tr>
                <td id='existingcell'>
                    <p>
                        <label for="removeselect"><?php print_string('existingdesignations', 'local_enrolmultiselect'); ?></label>
                    </p>
                    <?php $designationCurrentSelector->display(); ?>
                </td>
              
                <td id="buttonscell">
                    <?php $designationCurrentSelector->renderToggleButtons("designation_toggle"); ?>
                </td>
              
                <td id="potentialcell">
                    <p>
                        <label for="addselect"><?php print_string('disignations', 'local_enrolmultiselect'); ?></label>
                    </p>
                    <?php $designationPotentialSelector->display(); ?>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <button type="submit" name="submitbutton" id="<?php echo "s_{$pluginName}_designation_add_submit" ?>" class="btn btn-primary">Save changes</button>
    </div>
</form>

<?php
echo $OUTPUT->footer();

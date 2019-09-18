<?php
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir.'/adminlib.php');

    use \local_enrolmultiselect\type\settings\department;
    use \local_enrolmultiselect\type\settings\potential_department;

    $confirmadd = optional_param('confirmadd', 0, PARAM_INT);
    $confirmdel = optional_param('confirmdel', 0, PARAM_INT);

    $PAGE->set_url('/local/enrolmultiselect/department.php');
    $PAGE->requires->jquery();

    $pluginName = $_GET['plugin_name']; //validation required, this is an important parameter and must be set when url to this location is defined

    admin_externalpage_setup("{$pluginName}_departments");

    /*if (!is_siteadmin()) {
        die;
    }*/

    // Print header.
    echo $OUTPUT->header();

    $warwickAutoUrl = new moodle_url($PAGE->url);
    
    $departmentCurrentSelector = new department("s_{$pluginName}_department_add", ['plugin'=> $pluginName]);
    $departmentPotentialSelector = new potential_department("s_{$pluginName}_department_remove", ['plugin' => $pluginName]);

    if ( !empty( $_POST ) && confirm_sesskey()) {
        $departmentsToAssign = $departmentCurrentSelector->get_selected_users();

        if ( !empty( $departmentsToAssign ) ) {
            $departmentCurrentSelector->addToConfig( $departmentsToAssign );

            $departmentPotentialSelector->invalidate_selected_users();
            $departmentCurrentSelector->invalidate_selected_users();
        }else{
            $departmentCurrentSelector->removeConfig();
        }
    }
?>
<div class="alert alert-info">
    <?php print_string('selectionmoveinfo', 'local_enrolmultiselect');?>
</div>
<form id="<?php echo "s_{$pluginName}_department_add_form" ?>" action="<?php echo $warwickAutoUrl ?>" method="post">
    <div>
        <input type="hidden" name="section" value="settings_<?php echo $pluginName; ?>">
        <input type="hidden" name="action" value="save-settings">
        <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>">
        <input type="hidden" name="return" value="">
      
        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
            <tr>
                <td id='existingcell'>
                    <p>
                        <label class="multiselect-label" for="removeselect"><?php print_string('existingdepartments', 'local_enrolmultiselect'); ?></label>
                    </p>
                    <?php $departmentCurrentSelector->display(); ?>
                </td>
                <td id="buttonscell">
                    <?php $departmentCurrentSelector->renderToggleButtons("department_toggle"); ?>
                </td>
                <td id="potentialcell">
                    <p>
                        <label class="multiselect-label" for="addselect"><?php print_string('departments', 'local_enrolmultiselect'); ?></label>
                    </p>
                    <?php $departmentPotentialSelector->display(); ?>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <button type="submit" name="submitbutton" id="<?php echo "s_{$pluginName}_department_add_submit" ?>" class="btn btn-primary">Save changes</button>
    </div>
</form>

<?php
echo $OUTPUT->footer();

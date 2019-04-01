<?php
    require_once(__DIR__ . '/../../config.php');
    require_once($CFG->libdir.'/adminlib.php');

    use \local_enrolmultiselect\type\settings\department;
    use \local_enrolmultiselect\type\settings\potential_department;

    $confirmadd = optional_param('confirmadd', 0, PARAM_INT);
    $confirmdel = optional_param('confirmdel', 0, PARAM_INT);

    $PAGE->set_url('/local/enrolmultiselect/department.php');

    $pluginName = $_GET['plugin_name']; //validation required, this is an important parameter and must be set when url to this location is defined

    admin_externalpage_setup("{$pluginName}_departments");

    if (!is_siteadmin()) {
        die;
    }

    // Print header.
    echo $OUTPUT->header();

    $warwickAutoUrl = new moodle_url($PAGE->url);
    
    $departmentCurrentSelector = new department("s_{$pluginName}_department_add", ['plugin'=> $pluginName]);
    $departmentPotentialSelector = new potential_department("s_{$pluginName}_department_remove", ['plugin' => $pluginName]);

    if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
        $departmentsToAssign = $departmentPotentialSelector->get_selected_users();

        if ( !empty( $departmentsToAssign ) ) {
            $departmentCurrentSelector->addToConfig( $departmentsToAssign );

            $departmentPotentialSelector->invalidate_selected_users();
            $departmentCurrentSelector->invalidate_selected_users();
        }
    }
    
    if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
        $departmentsToRemove = $departmentCurrentSelector->get_selected_users();

        if ( !empty( $departmentsToRemove ) ) {
            $departmentCurrentSelector->removeFromConfig( $departmentsToRemove );

            $departmentPotentialSelector->invalidate_selected_users();
            $departmentCurrentSelector->invalidate_selected_users();
        }
    }

?>

<form action="<?php echo $warwickAutoUrl ?>" method="post">
    <div>
        <input type="hidden" name="section" value="settings_<?php echo $pluginName; ?>">
        <input type="hidden" name="action" value="save-settings">
        <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>">
        <input type="hidden" name="return" value="">
      
        <table class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">
            <tr>
              <td id='existingcell'>
                  <p>
                    <label for="removeselect"><?php print_string('existingdepartments', 'local_enrolmultiselect'); ?></label>
                  </p>
                  <?php $departmentCurrentSelector->display(); ?>
                  </td>
              <td id="buttonscell">
                <p class="arrow_button">
                    <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>"
                           title="<?php print_string('add'); ?>" class="btn btn-secondary"/><br />
                    <input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>"
                           title="<?php print_string('remove'); ?>" class="btn btn-secondary"/><br />
                </p>
              </td>
              <td id="potentialcell">
                  <p>
                    <label for="addselect"><?php print_string('departments', 'local_enrolmultiselect'); ?></label>
                  </p>
                  <?php $departmentPotentialSelector->display(); ?>
              </td>
            </tr>
        </table>

    </div>
</form>

<?php
echo $OUTPUT->footer();
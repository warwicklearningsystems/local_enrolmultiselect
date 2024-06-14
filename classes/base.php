<?php

namespace local_enrolmultiselect;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();
/**
 * The default size of a user selector.
 */
define('USER_SELECTOR_DEFAULT_ROWS', 20);

use \enrol_selector_group\members_selector;
use \enrol_selector_group\non_members_selector;
use \enrol_warwickguest\selector\config;

abstract class base{
    
    /** @var string The control name (and id) in the HTML. */
    public $name;
    
    /**
     *
     * @var string The name of the plugin that this selector is bound to
     */
    public $plugin;
    
    /**
     *
     * @var config 
     */
    protected $config;

    /**
     *
     * @var string 
     */
    protected $propertyFromConfigToDisplay;
    /** @var array Extra fields to search on and return in addition to firstname and lastname. */
    protected $extrafields;
    /** @var object Context used for capability checks regarding this selector (does
     * not necessarily restrict user list) */
    protected $accesscontext;
    /** @var boolean Whether the conrol should allow selection of many users, or just one. */
    protected $multiselect = true;
    /** @var int The height this control should have, in rows. */
    protected $rows = USER_SELECTOR_DEFAULT_ROWS;
    /** @var array A list of userids that should not be returned by this control. */
    protected $exclude = array();
    /** @var array|null A list of the users who are selected. */
    protected $selected = null;
    /** @var boolean When the search changes, do we keep previously selected options that do
     * not match the new search term? */
    protected $preserveselected = false;
    /** @var boolean If only one user matches the search, should we select them automatically. */
    protected $autoselectunique = false;
    /** @var boolean When searching, do wem name that this control is being display only match the starts of fields (better performance)
     * or do we match occurrences anywhere? */
    protected $searchanywhere = false;
    /** @var mixed This is used by get selected users */
    protected $validatinguserids = null;

    /**  @var boolean Used to ensure we only output the search options for one user selector on
     * each page. */
    private static $searchoptionsoutput = false;

    /** @var array JavaScript YUI3 Module definition */
    protected static $jsmodule = array(
                'name' => 'user_selector',
                'fullpath' => '/local/enrolmultiselect/module.js',
                'requires'  => array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification'),
                'strings' => array(
                    array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%'),
                    array('nomatchingusers', 'local_enrolmultiselect', ['type' => '%%TYPE%%','search' => '%%SEARCHTERM%%']),
                    array('none', 'moodle')
                ));
    
    protected static $jsToggleModule = array(
        'name' => 'selector_toggle',
        'fullpath' => '/local/enrolmultiselect/toggle.js',
        'requires'  => array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification'),
    );

    /** @var int this is used to define maximum number of users visible in list */
    public $maxusersperpage = 100;

    /** @var boolean Whether to override fullname() */
    public $viewfullnames = false;
    public $hash;

    /**
     * 
     * @global type $CFG
     * @global type $PAGE
     * @param type $name
     * @param type $options
     * @param type $plugin
     */
    public function __construct($name, $options) {
        global $CFG, $PAGE;

        // Initialise member variables from constructor arguments.
        $this->name = $name;
        
        $this->plugin = $options['plugin'];


        // Check if some legacy code tries to override $CFG->showuseridentity.
        if (isset($options['extrafields'])) {
            debugging('The user_selector classes do not support custom list of extra identity fields any more. '.
                'Instead, the user identity fields defined by the site administrator will be used to respect '.
                'the configured privacy setting.', DEBUG_DEVELOPER);
            unset($options['extrafields']);
        }

        

        if (isset($options['exclude']) && is_array($options['exclude'])) {
            $this->exclude = $options['exclude'];
        }
        if (isset($options['multiselect'])) {
            $this->multiselect = $options['multiselect'];
        }

        // Read the user prefs / optional_params that we use.
        $this->preserveselected = $this->initialise_option('userselector_preserveselected', $this->preserveselected);
        $this->autoselectunique = $this->initialise_option('userselector_autoselectunique', $this->autoselectunique);
        $this->searchanywhere = $this->initialise_option('userselector_searchanywhere', $this->searchanywhere);

        if (!empty($CFG->maxusersperpage)) {
            $this->maxusersperpage = $CFG->maxusersperpage;
        }
    }
    
    /**
     * All to the list of user ids that this control will not select.
     *
     * For example, on the role assign page, we do not list the users who already have the role in question.
     *
     * @param array $arrayofuserids the user ids to exclude.
     */
    public function exclude($arrayofuserids) {
        $this->exclude = array_unique(array_merge($this->exclude, $arrayofuserids));
    }

    /**
     * Clear the list of excluded user ids.
     */
    public function clear_exclusions() {
        $this->exclude = array();
    }

    /**
     * Returns the list of user ids that this control will not select.
     *
     * @return array the list of user ids that this control will not select.
     */
    public function get_exclusions() {
        return clone($this->exclude);
    }

    /**
     * The users that were selected.
     *
     * This is a more sophisticated version of optional_param($this->name, array(), PARAM_INT) that validates the
     * returned list of ids against the rules for this user selector.
     *
     * @return array of user objects.
     */
    public function get_selected_users() {
        // Do a lazy load.
        if (is_null($this->selected)) {
            $this->selected = $this->load_selected_users();
        }
        return $this->selected;
    }

    /**
     * Convenience method for when multiselect is false (throws an exception if not).
     *
     * @throws moodle_exception
     * @return object the selected user object, or null if none.
     */
    public function get_selected_user() {
        if ($this->multiselect) {
            throw new moodle_exception('cannotcallusgetselecteduser');
        }
        $users = $this->get_selected_users();
        if (count($users) == 1) {
            return reset($users);
        } else if (count($users) == 0) {
            return null;
        } else {
            throw new moodle_exception('userselectortoomany');
        }
    }

    /**
     * Invalidates the list of selected users.
     *
     * If you update the database in such a way that it is likely to change the
     * list of users that this component is allowed to select from, then you
     * must call this method. For example, on the role assign page, after you have
     * assigned some roles to some users, you should call this.
     */
    public function invalidate_selected_users() {
        $this->selected = null;
    }

    /**
     * Output this user_selector as HTML.
     *
     * @param boolean $return if true, return the HTML as a string instead of outputting it.
     * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
     */
    public function display($return = false) {
        global $PAGE;

        // Get the list of requested users.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedusers = $this->find_users($search);

        // Output the select.
        $name = $this->name;
        $multiselect = '';
        if ($this->multiselect) {
            $name .= '[]';
            $multiselect = 'multiple="multiple" ';
        }
        $output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '" class="form-control no-overflow">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedusers, $search);

        // Output the search controls.
        $output .= "</select>\n<div class=\"form-inline\">\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="' .
                $this->name . '_searchtext" size="15" value="' . s($search) . '" class="form-control"/>';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="' .
                $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" class="btn btn-secondary"/>';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="' .
                $this->name . '_clearbutton" value="' . get_string('clear') . '" class="btn btn-secondary"/>';

        // And the search options.
        $optionsoutput = false;
        if (!base::$searchoptionsoutput) {
            $output .= print_collapsible_region_start('', 'userselector_options',
                get_string('searchoptions'), 'userselector_optionscollapsed', true, true);
            $output .= $this->option_checkbox('preserveselected', $this->preserveselected,
                get_string('userselectorpreserveselected'));
            $output .= $this->option_checkbox('autoselectunique', $this->autoselectunique,
                get_string('userselectorautoselectunique'));
            $output .= $this->option_checkbox('searchanywhere', $this->searchanywhere,
                get_string('userselectorsearchanywhere'));
            $output .= print_collapsible_region_end(true);

            $PAGE->requires->js_init_call('M.local_enrolmultiselect.init_user_selector_options_tracker', array(), false, self::$jsmodule);
            base::$searchoptionsoutput = true;
        }
        $output .= "</div>\n</div>\n\n";

        // Initialise the ajax functionality.
        $output .= $this->initialise_javascript($search);

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * 
     * @global type $OUTPUT
     * @global \local_enrolmultiselect\type $PAGE
     * @param type $toggleName
     * @param type $return
     * @return type
     */
    public function renderToggleButtons($toggleName, $return = false){
        global $OUTPUT, $PAGE;
        
        $addTitle    = get_string('add');
        $leftArrow   = $OUTPUT->larrow().'&nbsp;'.$addTitle;
        $removeTitle = get_string('remove');
        $rightArrow  = $removeTitle.'&nbsp;'.$OUTPUT->rarrow();
        $toggleId = $toggleName.'_'.uniqid();
        $selectorNameId = $toggleId.'_add';
        $potentialSelectorNameId = $toggleId.'_remove';

$html = <<<___HTML___
<div id="$toggleId">
    <p class="arrow_button">
        <input name="add" id="$selectorNameId" type="submit" value="$leftArrow" title="$addTitle" class="btn btn-secondary"/><br/>
        <input name="remove" id="$potentialSelectorNameId" type="submit" value="$rightArrow" title="$removeTitle" class="btn btn-secondary"/><br/>
    </p>
</div>
___HTML___;

        $this->initialiseToggleJs($toggleId);

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }
    
    
        
    /**
     * 
     * @global \local_enrolmultiselect\type $PAGE
     * @param type $selectorName
     * @param type $potentialSelectorName
     * @return type
     */
    protected function initialiseToggleJs($toggleId){
        global $PAGE;

        // Put the options into the session, to allow search.php to respond to the ajax requests.
        $options = $this->get_options();
        $hash = md5(serialize($options));
        $this->setHash($hash);
        //$USER->userselectors[$hash] = $options;
        
        $PAGE->requires->js_init_call(
            'M.local_enrolmultiselect.init_selector_toggle',
            array($toggleId, $hash),
            true,
            self::$jsToggleModule
        );
        
    }

    /**
     * The height this control will be displayed, in rows.
     *
     * @param integer $numrows the desired height.
     */
    public function set_rows($numrows) {
        $this->rows = $numrows;
    }

    /**
     * Returns the number of rows to display in this control.
     *
     * @return integer the height this control will be displayed, in rows.
     */
    public function get_rows() {
        return $this->rows;
    }

    /**
     * Whether this control will allow selection of many, or just one user.
     *
     * @param boolean $multiselect true = allow multiple selection.
     */
    public function set_multiselect($multiselect) {
        $this->multiselect = $multiselect;
    }

    /**
     * Returns true is multiselect should be allowed.
     *
     * @return boolean whether this control will allow selection of more than one user.
     */
    public function is_multiselect() {
        return $this->multiselect;
    }

    /**
     * Returns the id/name of this control.
     *
     * @return string the id/name that this control will have in the HTML.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set the user fields that are displayed in the selector in addition to the user's name.
     *
     * @param array $fields a list of field names that exist in the user table.
     */
    public function set_extra_fields($fields) {
        $this->extrafields = $fields;
    }

    /**
     * Search the database for users matching the $search string, and any other
     * conditions that apply. The SQL for testing whether a user matches the
     * search string should be obtained by calling the search_sql method.
     *
     * This method is used both when getting the list of choices to display to
     * the user, and also when validating a list of users that was selected.
     *
     * When preparing a list of users to choose from ($this->is_validating()
     * return false) you should probably have an maximum number of users you will
     * return, and if more users than this match your search, you should instead
     * return a message generated by the too_many_results() method. However, you
     * should not do this when validating.
     *
     * If you are writing a new user_selector subclass, I strongly recommend you
     * look at some of the subclasses later in this file and in admin/roles/lib.php.
     * They should help you see exactly what you have to do.
     *
     * @param string $search the search string.
     * @return array An array of arrays of users. The array keys of the outer
     *      array should be the string names of optgroups. The keys of the inner
     *      arrays should be userids, and the values should be user objects
     *      containing at least the list of fields returned by the method
     *      required_fields_sql(). If a user object has a ->disabled property
     *      that is true, then that option will be displayed greyed out, and
     *      will not be returned by get_selected_users.
     */
    public abstract function find_users($search);
    public abstract function getGroups();
    public abstract function getDefaultGroup();

    /**
     *
     * Note: this function must be implemented if you use the search ajax field
     *       (e.g. set $options['file'] = '/admin/filecontainingyourclass.php';)
     * @return array the options needed to recreate this user_selector.
     */
    protected function get_options() {
        return array(
            'class' => get_class($this),
            'name' => $this->name,
            'exclude' => $this->exclude,
            'extrafields' => $this->extrafields,
            'multiselect' => $this->multiselect
        );
    }

    /**
     * Returns true if this control is validating a list of users.
     *
     * @return boolean if true, we are validating a list of selected users,
     *      rather than preparing a list of uesrs to choose from.
     */
    protected function is_validating() {
        return !is_null($this->validatinguserids);
    }

    /**
     * Get the list of users that were selected by doing optional_param then validating the result.
     *
     * @return array of user objects.
     */
    protected function load_selected_users() {
        // See if we got anything.
        if ($this->multiselect) {
            $userids = optional_param_array($this->name, array(), PARAM_RAW);
        } else if ($userid = optional_param($this->name, 0, PARAM_RAW)) {
            $userids = array($userid);
        }
        return $userids;
        //exit(print_r($userids));
        //return $userids;
        // If there are no users there is nobody to load.
        if (empty($userids)) {
            return array();
        }

        // If we did, use the find_users method to validate the ids.
        $this->validatinguserids = $userids;
        $groupedusers = $this->find_users('');
        $this->validatinguserids = null;

        // Aggregate the resulting list back into a single one.
        $users = array();
        foreach ($groupedusers as $group) {
            foreach ($group as $user) {
                if (!isset($users[$user->id]) && empty($user->disabled) && in_array($user->id, $userids)) {
                    $users[$user->id] = $user;
                }
            }
        }

        // If we are only supposed to be selecting a single user, make sure we do.
        if (!$this->multiselect && count($users) > 1) {
            $users = array_slice($users, 0, 1);
        }

        return $users;
    }

    /**
     * Returns SQL to select required fields.
     *
     * @param string $u the table alias for the user table in the query being
     *      built. May be ''.
     * @return string fragment of SQL to go in the select list of the query.
     */
    protected function required_fields_sql($u) {
        // Raw list of fields.
        $fields = array('id');
        // Add additional name fields.
        $fields = array_merge($fields, get_all_user_name_fields(), $this->extrafields);

        // Prepend the table alias.
        if ($u) {
            foreach ($fields as &$field) {
                $field = $u . '.' . $field;
            }
        }
        return implode(',', $fields);
    }

    /**
     * Returns an array with SQL to perform a search and the params that go into it.
     *
     * @param string $search the text to search for.
     * @param string $u the table alias for the user table in the query being
     *      built. May be ''.
     * @return array an array with two elements, a fragment of SQL to go in the
     *      where clause the query, and an array containing any required parameters.
     *      this uses ? style placeholders.
     */
    protected function search_sql($search, $u) {
        
        return $this->users_search_sql($search, $u, $this->searchanywhere,
                $this->exclude, $this->validatinguserids);
    }
    
    /**
    * Returns SQL used to search through user table to find users (in a query
    * which may also join and apply other conditions).
    *
    * You can combine this SQL with an existing query by adding 'AND $sql' to the
    * WHERE clause of your query (where $sql is the first element in the array
    * returned by this function), and merging in the $params array to the parameters
    * of your query (where $params is the second element). Your query should use
    * named parameters such as :param, rather than the question mark style.
    *
    * There are examples of basic usage in the unit test for this function.
    *
    * @param string $search the text to search for (empty string = find all)
    * @param string $u the table alias for the user table in the query being
    *     built. May be ''.
    * @param bool $searchanywhere If true (default), searches in the middle of
    *     names, otherwise only searches at start
    * @param array $extrafields Array of extra user fields to include in search
    * @param array $exclude Array of user ids to exclude (empty = don't exclude)
    * @param array $includeonly If specified, only returns users that have ids
    *     incldued in this array (empty = don't restrict)
    * @return array an array with two elements, a fragment of SQL to go in the
    *     where clause the query, and an associative array containing any required
    *     parameters (using named placeholders).
    */
   protected function users_search_sql($search, $u = 'u', $searchanywhere = true,
           array $exclude = null, array $includeonly = null) {
       global $DB, $CFG;
       $params = array();
       $tests = array();

       if ($u) {
           $u .= '.';
       }

       // If we have a $search string, put a field LIKE '$search%' condition on each field.
       if ($search) {
           /*$conditions = array(
               $DB->sql_fullname($u . 'firstname', $u . 'lastname'),
               $conditions[] = $u . 'lastname'
           );*/
           
           $conditions = [$this->propertyFromConfigToDisplay];
           /*foreach ($extrafields as $field) {
               $conditions[] = $u . $field;
           }*/
           if ($searchanywhere) {
               $searchparam = '%' . $search . '%';
           } else {
               $searchparam = $search . '%';
           }
           $i = 0;
           
           foreach ($conditions as $key => $condition) {
               $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false, false);
               $params["con{$i}00"] = $searchparam;
               $i++;
           }
           $tests[] = '(' . implode(' OR ', $conditions) . ')';
       }

       // Add some additional sensible conditions.
       $tests[] = $u . "id <> :guestid";
       $params['guestid'] = $CFG->siteguest;
       $tests[] = $u . 'deleted = 0';
       $tests[] = $u . 'confirmed = 1';

       // If we are being asked to exclude any users, do that.
       if (!empty($exclude)) {
           list($usertest, $userparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED, 'ex', false);
           $tests[] = $u . 'id ' . $usertest;
           $params = array_merge($params, $userparams);
       }

       // If we are validating a set list of userids, add an id IN (...) test.
       if (!empty($includeonly)) {
           list($usertest, $userparams) = $DB->get_in_or_equal($includeonly, SQL_PARAMS_NAMED, 'val');
           $tests[] = $u . 'id ' . $usertest;
           $params = array_merge($params, $userparams);
       }

       // In case there are no tests, add one result (this makes it easier to combine
       // this with an existing query as you can always add AND $sql).
       if (empty($tests)) {
           $tests[] = '1 = 1';
       }

       // Combing the conditions and return.
       return array(implode(' AND ', $tests), $params);
   }


    /**
     * Used to generate a nice message when there are too many users to show.
     *
     * The message includes the number of users that currently match, and the
     * text of the message depends on whether the search term is non-blank.
     *
     * @param string $search the search term, as passed in to the find users method.
     * @param int $count the number of users that currently match.
     * @return array in the right format to return from the find_users method.
     */
    protected function too_many_results($search, $count) {
        if ($search) {
            $a = new stdClass;
            $a->count = $count;
            $a->search = $search;
            return array(get_string('toomanyusersmatchsearch', '', $a) => array(),
                    get_string('pleasesearchmore') => array());
        } else {
            return array(get_string('toomanyuserstoshow', '', $count) => array(),
                    get_string('pleaseusesearch') => array());
        }
    }

    /**
     * Output the list of <optgroup>s and <options>s that go inside the select.
     *
     * This method should do the same as the JavaScript method
     * user_selector.prototype.handle_response.
     *
     * @param array $groupedusers an array, as returned by find_users.
     * @param string $search
     * @return string HTML code.
     */
    protected function output_options($groupedusers, $search) {
        $output = '';

        // Ensure that the list of previously selected users is up to date.
        $this->get_selected_users();

        // If $groupedusers is empty, make a 'no matching users' group. If there is
        // only one selected user, set a flag to select them if that option is turned on.
        $select = false;
        if (empty($groupedusers)) {
            if (!empty($search)) {
                $groupedusers = array(get_string('nomatchingusers', 'local_enrolmultiselect', ['type' => 'fff', 'search' => $search]) => array());
            } else {
                $groupedusers = array(get_string('none') => array());
            }
        } else if ($this->autoselectunique && count($groupedusers) == 1 &&
                count(reset($groupedusers)) == 1) {
            $select = true;
            if (!$this->multiselect) {
                $this->selected = array();
            }
        }

        // Output each optgroup.
        foreach ($groupedusers as $groupname => $users) {
            $output .= $this->output_optgroup($groupname, $users, $select);
        }

        // If there were previously selected users who do not match the search, show them too.
        if ($this->preserveselected && !empty($this->selected)) {
            $output .= $this->output_optgroup(get_string('previouslyselectedusers', '', $search), $this->selected, true);
        }

        // This method trashes $this->selected, so clear the cache so it is rebuilt before anyone tried to use it again.
        $this->selected = null;

        return $output;
    }

    /**
     * Output one particular optgroup. Used by the preceding function output_options.
     *
     * @param string $groupname the label for this optgroup.
     * @param array $users the users to put in this optgroup.
     * @param boolean $select if true, select the users in this group.
     * @return string HTML code.
     */
    protected function output_optgroup($groupname, $users, $select) {
        if (!empty($users)) {
            $output = '  <optgroup data-groupname="'. htmlspecialchars($groupname) .'" label="' . htmlspecialchars($groupname) . ' (' . count($users) . ')">' . "\n";
            foreach ($users as $user) {
                $attributes = '';
                if (!empty($user->disabled)) {
                    $attributes .= ' disabled="disabled"';
                } else if ($select || isset($this->selected[$user->{$this->propertyFromConfigToDisplay}])) {
                    $attributes .= ' selected="selected"';
                }
                unset($this->selected[$user->id]);
                $output .= '    <option' . $attributes . ' value="' . $user->{$this->propertyFromConfigToDisplay} . '">' .
                        $this->output_user($user) . "</option>\n";
                if (!empty($user->infobelow)) {
                    // Poor man's indent  here is because CSS styles do not work in select options, except in Firefox.
                    $output .= '    <option disabled="disabled" class="userselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($user->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup data-groupname="'. htmlspecialchars($groupname) .'" label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    /**
     * Convert a user object to a string suitable for displaying as an option in the list box.
     *
     * @param object $user the user to display.
     * @return string a string representation of the user.
     */
    public function output_user($user) {
        $out = fullname($user, $this->viewfullnames);
        $out = $user->{$this->propertyFromConfigToDisplay};
        /*if ($this->extrafields) {
            $displayfields = array();
            foreach ($this->extrafields as $field) {
                $displayfields[] = $user->{$field};
            }
            $out .= ' (' . implode(', ', $displayfields) . ')';
        }*/
        return $out;
    }

    /**
     * Returns the string to use for the search button caption.
     *
     * @return string the caption for the search button.
     */
    protected function search_button_caption() {
        return get_string('search');
    }

    /**
     * Initialise one of the option checkboxes, either from  the request, or failing that from the
     * user_preferences table, or finally from the given default.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|null|string
     */
    private function initialise_option($name, $default) {
        $param = optional_param($name, null, PARAM_BOOL);
        if (is_null($param)) {
            return get_user_preferences($name, $default);
        } else {
            set_user_preference($name, $param);
            return $param;
        }
    }

    /**
     * Output one of the options checkboxes.
     *
     * @param string $name
     * @param string $on
     * @param string $label
     * @return string
     */
    private function option_checkbox($name, $on, $label) {
        if ($on) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }
        $name = 'userselector_' . $name;
        // For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
        // It seems that document.getElementById('frog') in IE will return and element with name="frog".
        $output = '<div class="form-check"><input type="hidden" name="' . $name . '" value="0" />' .
                    '<label class="form-check-label" for="' . $name . 'id">' .
                        '<input class="form-check-input" type="checkbox" id="' . $name . 'id" name="' . $name .
                            '" value="1"' . $checked . ' /> ' . $label .
                    "</label>
                   </div>\n";
        // user_preference_allow_ajax_update($name, PARAM_BOOL);
        $output .= "<script>
                    require(['core_user/repository'], function(repository) {
                        repository.set_preference('$name', $checked ? 1 : 0);
                    });
                </script>";
        return $output;
    }

    /**
     * Initialises JS for this control.
     *
     * @param string $search
     * @return string any HTML needed here.
     */
    protected function initialise_javascript($search) {
        global $USER, $PAGE, $OUTPUT;
        $output = '';

        // Put the options into the session, to allow search.php to respond to the ajax requests.
        $options = $this->get_options();
        $hash = md5(serialize($options));
        $USER->userselectors[$hash] = $options;

        // Initialise the selector.
        /*$PAGE->requires->js_init_call(
            'M.core_user.init_user_selector',
            array($this->name, $hash, $this->extrafields, $search),
            false,
            self::$jsmodule
        );*/
        
        $PAGE->requires->js_init_call(
            'M.local_enrolmultiselect.init_user_selector',
            array($this->name, $hash, $this->extrafields, $search),
            false,
            self::$jsmodule
        );
        
        return $output;
    }
    
    public function setHash($hash){
        $this->hash = $hash;
    }
    
    public function getHash(){
        return $this->hash;
    }
    
    /**
     * 
     * @param type $designationObject
     * @return type
     */
    public function getGroupName($designationObject){

        foreach($this->getGroups() as $group){
            $group = $this->identifyGroup($group, $designationObject->{$this->propertyFromConfigToDisplay});
            
            if( $group ){
                return $group;
            }
        }
        
        return $this->getDefaultGroup();
    }
    
    /**
     * 
     * @param string $stringToFind
     * @param string $subjectString
     * @return boolean
     */
    private function identifyGroup($stringToFind, $subjectString){
        if( utils::strStartsWith($stringToFind, $subjectString)){
            return $stringToFind;
        }
        
        if( utils::strEndsWith($stringToFind, $subjectString)){
            return $stringToFind;
        }

        return false;
    }
    
    /**
     * 
     * @param array $options
     */
    public function addToConfig( $values ){
        
        if( empty( $values ) )
            return false;

        /*$currentValues = $this->config->getFlatConfigByProperty() ? $this->config->getFlatConfigByProperty() : [];
        $newValues = array_diff( $values, $currentValues );
        $valuesToAdd = array_merge( $currentValues, $newValues );*/

        return $this->config->setConfig( $values );
    }
    
    /**
     * 
     * @param array $values
     */
    public function removeFromConfig( $values ){
        
        if( empty( $values ) )
            return false;

        $currentValues = $this->config->getFlatConfigByProperty() ? $this->config->getFlatConfigByProperty() : [];
        $newValues = array_diff( $currentValues, $values );
        
        return $this->config->setConfig( $newValues );
    }

    public function removeConfig(){
        return $this->config->removeConfig();
    }
    
    public static function switchOffSearchOptionsOutput(){
        self::$searchoptionsoutput = true;
    }
}

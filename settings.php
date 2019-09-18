<?php

//this is specifically for non-site-admins, site-admin will already have these settings soc check avoids duplicate category/name errors
if( !$hassiteconfig ){

    $systemcontext = context_system::instance();
    $hasCapabilityWariwckAuto = has_capability( 'enrol/warwickauto:nonsiteadminconfig', $systemcontext );
    $hasCapabilityWariwckGuest = has_capability( 'enrol/warwickguest:nonsiteadminconfig', $systemcontext );

    if( $hasCapabilityWariwckAuto || $hasCapabilityWariwckGuest ){
        $ADMIN->add('modules', new admin_category('enrolments', new lang_string('enrolments', 'enrol'), false));
    }

    if( $hasCapabilityWariwckAuto ){
        $settings = new admin_settingpage('enrolsettingswarwickauto',
                get_string('pluginname', 'enrol_warwickauto'),
                'enrol/warwickauto:nonsiteadminconfig');

        include(__DIR__.'/../../enrol/warwickauto/settings.php');

        if ($settings) {
            $ADMIN->add('enrolments', $settings);
        }
    }

    if( $hasCapabilityWariwckGuest ){
        $settings = new admin_settingpage('enrolsettingswarwickguest',
                get_string('pluginname', 'enrol_warwickguest'),
                'enrol/warwickguest:nonsiteadminconfig');

        include(__DIR__.'/../../enrol/warwickguest/settings.php');

        if ($settings) {
            $ADMIN->add('enrolments', $settings);
        }
    }
}
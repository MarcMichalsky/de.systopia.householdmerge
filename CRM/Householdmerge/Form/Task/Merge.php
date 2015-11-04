<?php
/*-------------------------------------------------------+
| Household Merger Extension                             |
| Copyright (C) 2015 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Householdmerge_Form_Task_Merge extends CRM_Contact_Form_Task {
  
  function buildQuickForm() {
    // first: load all the contacts
    $contacts = array();
    foreach ($this->_contactIds as $contact_id) {
      $contacts[$contact_id] = civicrm_api3('Contact', 'getsingle', array('id' => $contact_id)); 
    }
    $this->assign('contacts', $contacts);
    $patterns = $this->calculatePatterns($contacts);
    $patterns['custom'] = ts("Custom Name", array('domain' => 'de.systopia.householdmerge'));

    // adjust title
    CRM_Utils_System::setTitle(ts("Merge %1 contacts into a Household", array(1=>count($contacts), 'domain' => 'de.systopia.householdmerge')));

    // Add switch
    $this->add('hidden', 'hh_option');

    // Add "CREATE NEW" elements
    $this->add(
      'text',
      'household_name',
      ts('Household Name'),
      array('size' => 32, 'placeholder' => ts("Enter houshold name", array('domain' => 'de.systopia.householdmerge'))),
      FALSE
    );

    $pattern_select = $this->add(
      'select',
      'household_name_pattern',
      ts('Household Name', array('domain' => 'de.systopia.householdmerge')),
      $patterns,
      TRUE
    );
    $pattern_select->setSelected('custom');

    // Add "MERGE INTO" elements
    $this->add(
      'text',
      'existing_household',
      ts('Enter Houshold ID', array('domain' => 'de.systopia.householdmerge')),
      array('size' => 5),
      false
    );


    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Merge', array('domain' => 'de.systopia.householdmerge')),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Abort', array('domain' => 'de.systopia.householdmerge')),
        'isDefault' => FALSE,
      ),
    ));

    parent::buildQuickForm();
  }


  function postProcess() {
    $values = $this->exportValues();
    // find/determine household name
    if ($values['hh_option'] == 'new') {
      // extract the name:
      if ($values['household_name_pattern'] == 'custom') {
        $household_name = $values['household_name'];
      } else {
        $household_name = $values['household_name_pattern'];
      }
      $household = civicrm_api3('Contact', 'create', array(
         'contact_type'   => 'Household',
         'household_name' => $household_name,
        ));
      $household_id = $household['id'];
    } elseif ($values['hh_option'] == 'existing') {
      $household_id = (int) $values['existing_household'];
    }

    // find contact_ids
    $contact_ids = implode(',', $this->_contactIds);

    // ...and pass the ball to the merge view
    $mergeview_url = CRM_Utils_System::url('civicrm/household/mergeview', "hid=$household_id&oids=$contact_ids");
    CRM_Utils_System::redirect($mergeview_url);

    parent::postProcess();
  }


  /*************************************************
   **             Helper Functions                **
   ************************************************/

  function calculatePatterns(&$contacts) {
    $patterns = array();

    // first do some analysis
    $first_names = array();
    $last_names = array();
    $common_last = NULL;
    foreach ($contacts as $contact_id => $contact) {
      if (!empty($contact['first_name'])) {
        $first_names[] = $contact['first_name'];
      }
      if (!empty($contact['last_name'])) {
        if (!in_array($contact['last_name'], $last_names)) {
          $last_names[] = $contact['last_name'];
        }

        if ($common_last===NULL) {
          $common_last = $contact['last_name'];
        } elseif (strlen($common_last) > 0 && $common_last != $contact['last_name']) {
          $common_last = ''; // empty string means 'no common last name'
        }
      }
    }

    // OPTION 1: "Jim, John and Jane Example"
    if (count($first_names) > 1) {
      if ($common_last) {
        $last_name = $common_last;
      } else {
        $last_name = implode('-', $last_names);
      }
      $first_names_count = count($first_names);
      $and = ts('and', array('domain' => 'de.systopia.householdmerge'));
      if ($first_names_count > 2) {
        $first_part = implode(', ', array_slice($first_names, 0, $first_names_count-1));
        $patterns[1] = $first_part.' '.$and.' '.$first_names[$first_names_count-1].' '.$last_name;
      } else {
        $patterns[1] = implode(' '.$and.' ', $first_names).' '.$last_name;        
      }
    }

    // OPTION 2: "The Examples"
    if ($common_last) {
      $patterns[2] = ts("The %1s", array(1=>$common_last, 'domain' => 'de.systopia.householdmerge'));
    }

    // OPTION 3: "Example Family"
    if ($common_last) {
      $patterns[3] = ts("%1 Family", array(1=>$common_last, 'domain' => 'de.systopia.householdmerge'));
    }


    // transform patterns into selection list
    $pattern_selection = array();
    foreach ($patterns as $pattern) {
      $pattern_selection[$pattern] = $pattern;
    }

    return $pattern_selection;
  }
}

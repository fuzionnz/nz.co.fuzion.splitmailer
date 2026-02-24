<?php

require_once 'splitmailer.civix.php';
use CRM_Splitmailer_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function splitmailer_civicrm_config(&$config) {
  _splitmailer_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_alterMailer().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterMailer/
 */
function splitmailer_civicrm_alterMailer(&$mailer, $driver, $params) {
  $limit = 5000;
  $primaryMailer = 'mail';
  $secondaryMailer = 'CRM_Mailing_BAO_Spool';
  if ($driver == $primaryMailer) {
    $deliveredToday = CRM_Core_DAO::singleValueQuery(
      "select count(id)
      from civicrm_mailing_event_delivered
      where DATE(time_stamp) = CURDATE()"
    );
    //If no. of emails sent today is more than the limit, switch the mailer.
    if ($deliveredToday >= $limit) {
      $mailer = _getMailer($secondaryMailer, $params);
    }
  }
}

/**
 * Get Mailer
 */
function _getMailer($driver, $params) {
  if ($driver == 'CRM_Mailing_BAO_Spool') {
    $mailer = new CRM_Mailing_BAO_Spool($params);
  }
  else {
    $mailer = Mail::factory($driver, $params);
  }

  $mailer = new CRM_Utils_Mail_FilteredPearMailer($driver, $params, $mailer);
  if (in_array($driver, ['smtp', 'mail', 'sendmail'])) {
    $mailer->addFilter('2000_log', ['CRM_Utils_Mail_Logger', 'filter']);
    $mailer->addFilter('2100_validate', function ($mailer, &$recipients, &$headers, &$body) {
      if (!is_array($headers)) {
        return PEAR::raiseError('$headers must be an array');
      }
    });
  }
  return $mailer;
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function splitmailer_civicrm_install() {
  _splitmailer_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function splitmailer_civicrm_enable() {
  _splitmailer_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function splitmailer_civicrm_navigationMenu(&$menu) {
  _splitmailer_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _splitmailer_civix_navigationMenu($menu);
} // */

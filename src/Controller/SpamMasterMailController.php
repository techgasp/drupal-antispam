<?php

namespace Drupal\spammaster\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class controller.
 */
class SpamMasterMailController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function spammasterlictrialcreation() {

    // Email key.
    $key = 'license_trial_create';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $spammaster_license = $spammaster_settings->get('spammaster.license_key');
    $spammaster_status = $spammaster_settings->get('spammaster.license_status');
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');
    $to = \Drupal::currentUser()->getEmail();
    if ($spammaster_status == 'VALID') {
      // Email Content.
      $spam_master_table_content = 'Congratulations, ' . $spammaster_site_name . ' is now protected by Spam Master against millions of threats.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Your License is: ' . $spammaster_license . '.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Protected Against: ' . number_format($spammaster_license_protection) . ' million threats.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Your free trial license expires in 7 days.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Enjoy,';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'TechGasp Team';
      $spam_master_table_content .= "\r\n";
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'spammaster';
      $params['message'] = $spam_master_table_content;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = TRUE;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      \Drupal::logger('spammaster-mail')->notice('Spam Master: mail trial license created sent To: ' . $to);
      drupal_set_message(t('Remember to visit Spam Master configuration page.'));
    }
    else {
      drupal_set_message(t('Spam Master Trial license could not be created. License status is:') . ' ' . $spammaster_status . '. ' . t('Check Spam Master configuration page and read more about statuses.'), 'error');
      \Drupal::logger('spammaster-mail')->notice('Spam Master: mail not sent, license contains malfunction.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterlicexpired() {

    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = $site_settings->get('mail');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $spammaster_license = $spammaster_settings->get('spammaster.license_key');
    $spammaster_status = $spammaster_settings->get('spammaster.license_status');
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');
    $spammaster_type = $spammaster_settings->get('spammaster.type');
    if ($spammaster_type == 'TRAIL') {
      // Email key.
      $key = 'license_trial_end';
      // Email Content.
      $spam_master_table_content = $blogname . ' is no longer protected by Spam Master against millions of threats.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'If you enjoyed the protection you can quickly get a full license, it costs peanuts per year.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Go to Spam Master settings page and click get full license.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Thanks.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'TechGasp Team';
      $spam_master_table_content .= "\r\n";
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'spammaster';
      $params['message'] = $spam_master_table_content;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = TRUE;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      \Drupal::logger('spammaster-mail')->notice('Spam Master: mail trial license expired sent To: ' . $to);
    }
    if ($spammaster_type == 'FULL') {
      // Email key.
      $key = 'license_full_end';
      // Email Content.
      $spam_master_table_content = $blogname . ' is no longer protected by Spam Master against millions of threats.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Hope you have enjoyed 1 year of bombastic protection. You can quickly get another license and get protected again, it costs peanuts per year.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Go to Spam Master settings page and click get full license.';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'Thanks,';
      $spam_master_table_content .= "\r\n";
      $spam_master_table_content .= 'TechGasp Team';
      $spam_master_table_content .= "\r\n";
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'spammaster';
      $to = \Drupal::currentUser()->getEmail();
      $params['message'] = $spam_master_table_content;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = TRUE;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      \Drupal::logger('spammaster-mail')->notice('Spam Master: mail full license expired sent To: ' . $to);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterlicmalfunctions() {

    // Email key.
    $key = 'license_malfunction';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = $site_settings->get('mail');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $spammaster_license = $spammaster_settings->get('spammaster.license_key');
    $spammaster_status = $spammaster_settings->get('spammaster.license_status');

    // Email Content.
    $spam_master_table_content = 'Warning, your ' . $spammaster_site_name . ' might not be 100% protected.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Your License: ' . $spammaster_license . ' status is: ' . $spammaster_status . '.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Some license status are easy to fix, example Malfunction 1 just means you need to update the module to the latest version and the status will automatically fix itself.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'All statuses are explained in our website documentation section and, in case of trouble get in touch with our support.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'TechGasp Team';
    $spam_master_table_content .= "\r\n";
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'spammaster';
    $params['message'] = $spam_master_table_content;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log message.
    \Drupal::logger('spammaster-mail')->notice('Spam Master: mail license malfunction sent To: ' . $to);
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterlicalertlevel3() {

    // Email key.
    $key = 'lic_alert_level_3';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = $site_settings->get('mail');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $spammaster_license = $spammaster_settings->get('spammaster.license_key');
    $spammaster_status = $spammaster_settings->get('spammaster.license_status');
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');

    // Email Content.
    $spam_master_table_content = 'Warning!!! Spam Master Alert 3 detected for ' . $spammaster_site_name . '.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Your License: ' . $spammaster_license . ' status is: ' . $spammaster_status . ' and you are protected against: ' . number_format($spammaster_license_protection) . ' threats.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'The daily Alert 3 email will automatically stop when your website alert level drops to safer levels.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'All alert levels are explained in our website documentation section and, in case of trouble get in touch with our support.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'TechGasp Team';
    $spam_master_table_content .= "\r\n";
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'spammaster';
    $params['message'] = $spam_master_table_content;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log message.
    \Drupal::logger('spammaster-mail')->notice('Spam Master: mail alert level 3 sent To: ' . $to);
  }

  /**
   * The Mail function for Daily Report.
   */
  public function spammastermaildailyreport() {

    // Email key.
    $key = 'mail_daily_report';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = $site_settings->get('mail');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $response_key = $spammaster_settings->get('spammaster.license_status');
    if ($response_key == 'VALID') {
      $spam_master_warning = 'Your license status is Valid & Online.';
      $spam_master_warning_signature = 'All is good.';
    }
    if ($response_key == 'MALFUNCTION_1') {
      $spam_master_warning = 'Warnings: Malfunction 1, please update Spam Master to the latest version.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    if ($response_key == 'MALFUNCTION_2') {
      $spam_master_warning = 'Warnings: Malfunction 2, urgently update Spam Master, your installed version is extremely old.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');
    $spammaster_license_probability = $spammaster_settings->get('spammaster.license_probability');
    $spammaster_license_alert_level = $spammaster_settings->get('spammaster.license_alert_level');
    if ($spammaster_license_alert_level == 'ALERT_0') {
      $spam_master_alert_level_deconstructed = '0';
    }
    if ($spammaster_license_alert_level == 'ALERT_1') {
      $spam_master_alert_level_deconstructed = '1';
    }
    if ($spammaster_license_alert_level == 'ALERT_2') {
      $spam_master_alert_level_deconstructed = '2';
    }
    $spammaster_total_block_count = $spammaster_settings->get('spammaster.total_block_count');
    if ($spammaster_total_block_count <= '10') {
      $spam_master_block_count_result = 'Total Triggers: good, less than 10';
    }
    if ($spammaster_total_block_count >= '11') {
      $spam_master_block_count_result = 'Total Triggers: ' . number_format($spammaster_total_block_count) . ' firewall triggers & registrations blocked';
    }
    // Get count last 7 days of blocks from whatchdog.
    $time = date('Y-m-d H:i:s');
    $time_expires = date('Y-m-d H:i:s', strtotime($time . '-1 days'));
    $spammaster_spam_watch_query = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_spam_watch_query->fields('u', ['spamkey']);
    $spammaster_spam_watch_query->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires, ':time' => $time]);
    $spammaster_spam_watch_query->where('(spamkey = :registration OR spamkey = :comment OR spamkey = :contact OR spamkey = :firewall)', [
      ':registration' => 'spammaster-registration',
      ':comment' => 'spammaster-comment',
      'contact' => 'spammaster-contact',
      ':firewall' => 'spammaster-firewall',
    ]);
    $spammaster_spam_watch_result = $spammaster_spam_watch_query->countQuery()->execute()->fetchField();
    if (empty($spammaster_spam_watch_result)) {
      $spam_master_daily_block_count_result = 'Weekly Triggers: good, nothing to report';
    }
    else {
      $spam_master_daily_block_count_result = 'Weekly Triggers: ' . number_format($spammaster_spam_watch_result) . ' firewall triggers';
    }
    // Email Content.
    $spam_master_table_content = 'Spam Master daily Report for ' . $spammaster_site_name . '.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_warning;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Alert Level: ' . $spam_master_alert_level_deconstructed;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Probability: ' . $spammaster_license_probability . '%';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Protected Against: ' . number_format($spammaster_license_protection) . ' million threats';
    $spam_master_table_content .= $spam_master_daily_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_warning_signature;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'The daily report email can be turned off in Spam Master module settings page, Emails & Reporting section.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'TechGasp Team';
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'spammaster';
    $params['message'] = $spam_master_table_content;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log message.
    \Drupal::logger('spammaster-mail')->notice('Spam Master: mail daily sent To: ' . $to);
  }

  /**
   * {@inheritdoc}
   */
  public function spammastermailweeklyreport() {

    // Email key.
    $key = 'mail_weekly_report';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = $site_settings->get('mail');
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $response_key = $spammaster_settings->get('spammaster.license_status');
    if ($response_key == 'VALID') {
      $spam_master_warning = 'Your license status is Valid & Online.';
      $spam_master_warning_signature = 'All is good.';
    }
    if ($response_key == 'MALFUNCTION_1') {
      $spam_master_warning = 'Warnings: Malfunction 1, please update Spam Master to the latest version.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    if ($response_key == 'MALFUNCTION_2') {
      $spam_master_warning = 'Warnings: Malfunction 2, urgently update Spam Master, your installed version is extremely old.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');
    $spammaster_license_probability = $spammaster_settings->get('spammaster.license_probability');
    $spammaster_license_alert_level = $spammaster_settings->get('spammaster.license_alert_level');
    if ($spammaster_license_alert_level == 'ALERT_0') {
      $spam_master_alert_level_deconstructed = '0';
    }
    if ($spammaster_license_alert_level == 'ALERT_1') {
      $spam_master_alert_level_deconstructed = '1';
    }
    if ($spammaster_license_alert_level == 'ALERT_2') {
      $spam_master_alert_level_deconstructed = '2';
    }
    $spammaster_total_block_count = $spammaster_settings->get('spammaster.total_block_count');
    if ($spammaster_total_block_count <= '10') {
      $spam_master_block_count_result = 'Total Triggers: good, less than 10';
    }
    if ($spammaster_total_block_count >= '11') {
      $spam_master_block_count_result = 'Total Triggers: ' . number_format($spammaster_total_block_count) . ' firewall triggers & registrations blocked';
    }
    $spammaster_license_alert_level = $spammaster_settings->get('spammaster.license_alert_level');
    // Get count last 7 days of blocks from whatchdog.
    $time = date('Y-m-d H:i:s');
    $time_expires = date('Y-m-d H:i:s', strtotime($time . '-1 days'));
    $spammaster_spam_watch_query = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_spam_watch_query->fields('u', ['spamkey']);
    $spammaster_spam_watch_query->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires, ':time' => $time]);
    $spammaster_spam_watch_query->where('(spamkey = :registration OR spamkey = :comment OR spamkey = :contact OR spamkey = :firewall)', [
      ':registration' => 'spammaster-registration',
      ':comment' => 'spammaster-comment',
      'contact' => 'spammaster-contact',
      ':firewall' => 'spammaster-firewall',
    ]);
    $spammaster_spam_watch_result = $spammaster_spam_watch_query->countQuery()->execute()->fetchField();
    if (empty($spammaster_spam_watch_result)) {
      $spam_master_daily_block_count_result = 'Weekly Triggers: good, nothing to report';
    }
    else {
      $spam_master_daily_block_count_result = 'Weekly Triggers: ' . number_format($spammaster_spam_watch_result) . ' firewall triggers';
    }
    $spammaster_buffer_size = \Drupal::database()->select('spammaster_threats', 'u');
    $spammaster_buffer_size->fields('u', ['threat']);
    $spammaster_buffer_size_result = $spammaster_buffer_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_buffer_size_result)) {
      $spammaster_buffer_size_result_count = '1';
    }
    else {
      $spammaster_buffer_size_result_count = $spammaster_buffer_size_result;
    }
    $spammaster_registration_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration_size->fields('u', ['spamkey']);
    $spammaster_registration_size->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_size_result = $spammaster_registration_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_registration_size_result)) {
      $spam_master_registration_count_result = 'Total Registrations Blocked: 0';
    }
    else {
      $spam_master_registration_count_result = 'Total Registrations Blocked: ' . number_format($spammaster_registration_size_result);
    }
    $spammaster_comment_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment_size->fields('u', ['spamkey']);
    $spammaster_comment_size->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_size_result = $spammaster_comment_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_comment_size_result)) {
      $spam_master_comment_count_result = 'Total Comments Blocked: 0';
    }
    else {
      $spam_master_comment_count_result = 'Total Comments Blocked: ' . number_format($spammaster_comment_size_result);
    }
    $spammaster_contact_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact_size->fields('u', ['spamkey']);
    $spammaster_contact_size->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_size_result = $spammaster_contact_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_contact_size_result)) {
      $spam_master_contact_count_result = 'Total Contacts Blocked: 0';
    }
    else {
      $spam_master_contact_count_result = 'Total Contacts Blocked: ' . number_format($spammaster_contact_size_result);
    }
    // Email Content.
    $spam_master_table_content = 'Spam Master weekly report for ' . $spammaster_site_name . '.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_warning;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Alert Level: ' . $spam_master_alert_level_deconstructed;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Probability: ' . $spammaster_license_probability . '%';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Protected Against: ' . number_format($spammaster_license_protection) . ' million threats';
    $spam_master_table_content .= $spam_master_daily_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_registration_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_comment_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_contact_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Buffer Size: ' . number_format($spammaster_buffer_size_result_count);
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_warning_signature;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'The weekly report email can be turned off in Spam Master module settings page, Emails & Reporting section.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'See you next week!';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'TechGasp Team';
    $spam_master_table_content .= "\r\n";
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'spammaster';
    $params['message'] = $spam_master_table_content;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log message.
    \Drupal::logger('spammaster-mail')->notice('Spam Master: mail weekly sent To: ' . $to);
  }

  /**
   * {@inheritdoc}
   */
  public function spammastermailhelpreport() {

    // Email key.
    $key = 'mail_help_report';
    // Get variables.
    $site_settings = \Drupal::config('system.site');
    $spammaster_site_name = $site_settings->get('name');
    $to = 'c3RhdHNAdGVjaGdhc3AuY29t';
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $response_key = $spammaster_settings->get('spammaster.license_status');
    if ($response_key == 'VALID') {
      $spam_master_warning = 'Your license status is Valid & Online.';
      $spam_master_warning_signature = 'All is good.';
    }
    if ($response_key == 'MALFUNCTION_1') {
      $spam_master_warning = 'Warnings: Malfunction 1, please update Spam Master to the latest version.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    if ($response_key == 'MALFUNCTION_2') {
      $spam_master_warning = 'Warnings: Malfunction 2, urgently update Spam Master, your installed version is extremely old.';
      $spam_master_warning_signature = 'Please correct the warnings.';
    }
    $spammaster_license_protection = $spammaster_settings->get('spammaster.license_protection');
    $spammaster_license_probability = $spammaster_settings->get('spammaster.license_probability');
    $spammaster_license_alert_level = $spammaster_settings->get('spammaster.license_alert_level');
    if ($spammaster_license_alert_level == 'ALERT_0') {
      $spam_master_alert_level_deconstructed = '0';
    }
    if ($spammaster_license_alert_level == 'ALERT_1') {
      $spam_master_alert_level_deconstructed = '1';
    }
    if ($spammaster_license_alert_level == 'ALERT_2') {
      $spam_master_alert_level_deconstructed = '2';
    }
    $spammaster_total_block_count = $spammaster_settings->get('spammaster.total_block_count');
    if ($spammaster_total_block_count <= '10') {
      $spam_master_block_count_result = 'Total Triggers: good, less than 10';
    }
    if ($spammaster_total_block_count >= '11') {
      $spam_master_block_count_result = 'Total Triggers: ' . number_format($spammaster_total_block_count) . ' firewall triggers & registrations blocked';
    }
    $spammaster_license_alert_level = $spammaster_settings->get('spammaster.license_alert_level');
    // Get count last 7 days of blocks from whatchdog.
    $time = date('Y-m-d H:i:s');
    $time_expires = date('Y-m-d H:i:s', strtotime($time . '-1 days'));
    $spammaster_spam_watch_query = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_spam_watch_query->fields('u', ['spamkey']);
    $spammaster_spam_watch_query->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires, ':time' => $time]);
    $spammaster_spam_watch_query->where('(spamkey = :registration OR spamkey = :comment OR spamkey = :contact OR spamkey = :firewall)', [
      ':registration' => 'spammaster-registration',
      ':comment' => 'spammaster-comment',
      'contact' => 'spammaster-contact',
      ':firewall' => 'spammaster-firewall',
    ]);
    $spammaster_spam_watch_result = $spammaster_spam_watch_query->countQuery()->execute()->fetchField();
    if (empty($spammaster_spam_watch_result)) {
      $spam_master_daily_block_count_result = 'Weekly Triggers: good, nothing to report';
    }
    else {
      $spam_master_daily_block_count_result = 'Weekly Triggers: ' . number_format($spammaster_spam_watch_result) . ' firewall triggers';
    }
    $spammaster_buffer_size = \Drupal::database()->select('spammaster_threats', 'u');
    $spammaster_buffer_size->fields('u', ['threat']);
    $spammaster_buffer_size_result = $spammaster_buffer_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_buffer_size_result)) {
      $spammaster_buffer_size_result_count = '1';
    }
    else {
      $spammaster_buffer_size_result_count = $spammaster_buffer_size_result;
    }
    $spammaster_registration_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration_size->fields('u', ['spamkey']);
    $spammaster_registration_size->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_size_result = $spammaster_registration_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_registration_size_result)) {
      $spam_master_registration_count_result = 'Total Registrations Blocked: 0';
    }
    else {
      $spam_master_registration_count_result = 'Total Registrations Blocked: ' . $spammaster_registration_size_result;
    }
    $spammaster_comment_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment_size->fields('u', ['spamkey']);
    $spammaster_comment_size->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_size_result = $spammaster_comment_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_comment_size_result)) {
      $spam_master_comment_count_result = 'Total Comments Blocked: 0';
    }
    else {
      $spam_master_comment_count_result = 'Total Comments Blocked: ' . $spammaster_comment_size_result;
    }
    $spammaster_contact_size = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact_size->fields('u', ['spamkey']);
    $spammaster_contact_size->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_size_result = $spammaster_contact_size->countQuery()->execute()->fetchField();
    if (empty($spammaster_contact_size_result)) {
      $spam_master_contact_count_result = 'Total Contacts Blocked: 0';
    }
    else {
      $spam_master_contact_count_result = 'Total Contacts Blocked: ' . $spammaster_contact_size_result;
    }
    // Email Content.
    $spam_master_table_content = 'Spam Master weekly report for ' . $spammaster_site_name . '.';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Alert Level: ' . $spam_master_alert_level_deconstructed;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Probability: ' . $spammaster_license_probability . '%';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Protected Against: ' . number_format($spammaster_license_protection) . ' million threats';
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_daily_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_block_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_registration_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_comment_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= $spam_master_contact_count_result;
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Buffer Size: ' . number_format($spammaster_buffer_size_result_count);
    $spam_master_table_content .= "\r\n";
    $spam_master_table_content .= 'Spam Master Statistics powered by TechGasp.';
    $spam_master_table_content .= "\r\n";
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'spammaster';
    $params['message'] = $spam_master_table_content;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, base64_decode($to), $langcode, $params, NULL, $send);
    // Log message.
    \Drupal::logger('spammaster-mail')->notice('Spam Master: mail help us improve was successfully sent');
  }

}

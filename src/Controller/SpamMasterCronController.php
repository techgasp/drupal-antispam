<?php

namespace Drupal\spammaster\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class controller.
 */
class SpamMasterCronController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function spammasterdailycron() {
    $spammaster_settings = \Drupal::config('spammaster.settings');
    $spammaster_response_key = $spammaster_settings->get('spammaster.license_status');
    $spammaster_alert_3 = $spammaster_settings->get('spammaster.license_alert_level');
    $spammaster_email_alert_3 = $spammaster_settings->get('spammaster.email_alert_3');
    $spammaster_email_daily_report = $spammaster_settings->get('spammaster.email_daily_report');

    if ($spammaster_response_key == 'VALID' || $spammaster_response_key == 'MALFUNCTION_1' || $spammaster_response_key == 'MALFUNCTION_2') {
      // Implements daily cron request via controllers.
      // Call Lic Controller.
      $spammaster_lic_controller = new SpamMasterLicController();
      $spammaster_lic_daily = $spammaster_lic_controller->spammasterlicdaily();

      // Call Mail Controller.
      $spammaster_mail_controller = new SpamMasterMailController();
      if ($spammaster_email_alert_3 != 0 && $spammaster_alert_3 == 'ALERT_3') {
        $spammaster_mail_daily_alert_3 = $spammaster_mail_controller->spammasterlicalertlevel3();
      }
      if ($spammaster_email_daily_report != 0) {
        $spammaster_mail_daily_report = $spammaster_mail_controller->spammastermaildailyreport();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterweeklycron() {

    $spammaster_settings = \Drupal::config('spammaster.settings');
    $response_key = $spammaster_settings->get('spammaster.license_status');
    $spammaster_email_weekly_report = $spammaster_settings->get('spammaster.email_weekly_report');
    $spammaster_email_improve = $spammaster_settings->get('spammaster.email_improve');

    if ($response_key == 'VALID' || $response_key == 'MALFUNCTION_1' || $response_key == 'MALFUNCTION_2') {
      // Implements daily cron request via controllers.
      // Call Mail Controller.
      $spammaster_mail_controller = new SpamMasterMailController();
      if ($spammaster_email_weekly_report != 0) {
        $spammaster_mail_weekly_report = $spammaster_mail_controller->spammastermailweeklyreport();
      }
      if ($spammaster_email_improve != 0) {
        $spammaster_mail_help_report = $spammaster_mail_controller->spammastermailhelpreport();
      }
    }
  }

}

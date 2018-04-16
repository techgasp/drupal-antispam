<?php

namespace Drupal\spammaster\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\spammaster\Controller\SpamMasterLicController;
use Drupal\Core\Url;

/**
 * Class controller.
 */
class SpamMasterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spammaster_form';
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterstatisticspage($form, &$form_state) {
    $spam_get_statistics = $form_state->getValue('statistics_header')['buttons']['addrow']['statistics'];
    if (!empty($spam_get_statistics)) {
      $spammaster_build_statistics_url = 'http://' . $_SERVER['SERVER_NAME'] . '/statistics';
      $spammaster_statistics_url = Url::fromUri($spammaster_build_statistics_url);
      $form_state->setRedirectUrl($spammaster_statistics_url);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterfirewallpage($form, &$form_state) {
    $spam_get_firewall = $form_state->getValue('statistics_header')['buttons']['addrow']['firewall'];
    if (!empty($spam_get_firewall)) {
      $spammaster_build_firewall_url = 'http://' . $_SERVER['SERVER_NAME'] . '/firewall';
      $spammaster_firewall_url = Url::fromUri($spammaster_build_firewall_url);
      $form_state->setRedirectUrl($spammaster_firewall_url);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterdeletethreat($form, &$form_state) {
    $spam_form_delete = $form_state->getValue('buffer_header')['table'];
    $spammaster_buffer_date = date("Y-m-d H:i:s");
    foreach ($spam_form_delete as $spam_row_delete) {
      if (!empty($spam_row_delete)) {
        db_query('DELETE FROM {spammaster_threats} WHERE id = :row', [':row' => $spam_row_delete]);
        drupal_set_message(t('Saved Spam Buffer deletion.'));
        \Drupal::logger('spammaster-buffer')->notice('Spam Master: buffer deletion, Id: ' . $spam_row_delete);
        $spammaster_db_buffer_delete = db_insert('spammaster_keys')->fields([
          'date' => $spammaster_buffer_date,
          'spamkey' => 'spammaster-buffer',
          'spamvalue' => 'Spam Master: buffer deletion, Id: ' . $spam_row_delete,
        ])->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterdeletekey($form, &$form_state) {
    $spam_form_key_delete = $form_state->getValue('statistics_header')['table'];
    $spammaster_key_date = date("Y-m-d H:i:s");
    foreach ($spam_form_key_delete as $spam_key_delete) {
      if (!empty($spam_key_delete)) {
        db_query('DELETE FROM {spammaster_keys} WHERE id = :row', [':row' => $spam_key_delete]);
        drupal_set_message(t('Saved Spam Master Log deletion.'));
        \Drupal::logger('spammaster-log')->notice('Spam Master: log deletion, Id: ' . $spam_key_delete);
        $spammaster_db_key_delete = db_insert('spammaster_keys')->fields([
          'date' => $spammaster_key_date,
          'spamkey' => 'spammaster-log',
          'spamvalue' => 'Spam Master: log deletion, Id: ' . $spam_key_delete,
        ])->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterdeletekeysall() {
    \Drupal::configFactory()->getEditable('spammaster.settings')
      ->set('spammaster.total_block_count', '0')
      ->save();
    $spammaster_db_keys_truncate = db_truncate('spammaster_keys')->execute();
    drupal_set_message(t('Saved Spam Master Statistics & Logs full deletion.'));
    \Drupal::logger('spammaster-log')->notice('Spam Master: Statistics & Logs full deletion.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    // Default settings.
    $config = $this->config('spammaster.settings');
    // Type.
    $response_key = $config->get('spammaster.license_status');
    // Statuses  Settings.
    $response_type = $config->get('spammaster.type');
    $spammaster_protection_total_number = $config->get('spammaster.license_protection');
    // STATUS VALID.
    if ($response_key == 'VALID') {
      $license_status = "VALID LICENSE";
      $protection_total_number_text = number_format($spammaster_protection_total_number) . ' Threats & Exploits';
    }
    // STATUS EXPIRED.
    if ($response_key == 'EXPIRED') {
      $license_status = "EXPIRED LICENSE";
      $protection_total_number_text = "0 Threats & Exploits - EXPIRED OFFLINE";
    }
    // STATUS MALFUNCTION_1.
    if ($response_key == 'MALFUNCTION_1') {
      $license_status = "VALID LICENSE";
      $protection_total_number_text = number_format($spammaster_protection_total_number) . ' Threats & Exploits';
    }
    // STATUS MALFUNCTION_2.
    if ($response_key == 'MALFUNCTION_2') {
      $license_status = "VALID LICENSE";
      $protection_total_number_text = number_format($spammaster_protection_total_number) . ' Threats & Exploits';
    }
    // STATUS MALFUNCTION_3.
    if ($response_key == 'MALFUNCTION_3') {
      $license_status = "MALFUNCTION_3 OFFLINE";
      $protection_total_number_text = "0 Threats & Exploits - MALFUNCTION_3 OFFLINE";
    }
    // STATUS INACTIVE NO LICENSE SENT YET.
    if ($response_key == 'INACTIVE') {
      $license_status = "INACTIVE LICENSE";
      $protection_total_number_text = "0 Threats & Exploits - INACTIVE OFFLINE";
    }

    // Alert Level Settings.
    $spammaster_alert_level = $config->get('spammaster.license_alert_level');
    // ALERT LEVEL, EMPTY.
    if (empty($spammaster_alert_level)) {
      $spammaster_alert_level_label = 'Empty data. ';
      $spammaster_alert_level_text = "No RBL (real-time blacklist) Server Sync";
      $spammaster_alert_level_p_label = "";
    }
    // ALERT LEVEL, MALFUNCTION_3.
    if ($spammaster_alert_level == 'MALFUNCTION_3') {
      $spammaster_alert_level_label = 'MALFUNCTION_3-> ';
      $spammaster_alert_level_text = "No RBL (real-time blacklist) Server Sync";
      $spammaster_alert_level_p_label = "";
    }
    // ALERT LEVEL, ALERT_0.
    if ($spammaster_alert_level == 'ALERT_0') {
      $spammaster_alert_level_label = 'Alert 0 -> ';
      $spammaster_alert_level_text = "Low level of spam and threats. Your website is mainly being visited by occasional harvester bots.";
      $spammaster_alert_level_p_label = " % percent probability";
    }
    // ALERT LEVEL, ALERT_1.
    if ($spammaster_alert_level == 'ALERT_1') {
      $spammaster_alert_level_label = 'Alert 1 -> ';
      $spammaster_alert_level_text = "Low level of spam and threats. Your website is mainly being visited by occasional human spammers and harvester bots.";
      $spammaster_alert_level_p_label = " % percent probability";
    }
    // ALERT LEVEL, ALERT_2.
    if ($spammaster_alert_level == 'ALERT_2') {
      $spammaster_alert_level_label = 'Alert 2 -> ';
      $spammaster_alert_level_text = "Medium level of spam and threats. Spam Master is actively fighting constant attempts of spam and threats by machine bots.";
      $spammaster_alert_level_p_label = " % percent probability";
    }
    // ALERT LEVEL, ALERT_3.
    if ($spammaster_alert_level == 'ALERT_3') {
      $spammaster_alert_level_label = 'Alert 3 -> ';
      $spammaster_alert_level_text = "WARNING! High level of spam and threats, flood detected. Spam Master is fighting an array of human spammers and bot networks which include exploit attempts.";
      $spammaster_alert_level_p_label = " % percent probability";
    }

    // Start TREE-> 1 license and status.
    $form['license_header'] = [
      '#type' => 'details',
      '#title' => $this->t('<h3>License & Status</h3>'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];

    // Insert license key field.
    $form['license_header']['license_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Insert license key number:'),
      '#default_value' => $config->get('spammaster.license_key'),
      '#description' => t('Insert your license key number. <a href="@spammaster_url">Get full rbl license</a>.', ['@spammaster_url' => 'https://wordpress.techgasp.com/spam-master/']),
    ];

    $spammaster_lic_controller = new SpamMasterLicController();
    $form['license_header']['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Save & Refresh License'),
      '#submit' => [
        '::validateForm',
        '::submitForm',
        [$spammaster_lic_controller, 'spammasterlicmanualcreation'],
        '::spammasterrefesh',
      ],
    ];

    // Insert license table inside tree.
    $form['license_header']['license'] = [
      '#type' => 'table',
    ];
    // Insert addrow license status field.
    $form['license_header']['license']['addrow']['license_status'] = [
      '#disabled' => TRUE,
      '#type' => 'textarea',
      '#rows' => 2,
      '#title' => $this->t('Your licence status:'),
      '#default_value' => $config->get('spammaster.type') . ' -> ' . $license_status,
      '#description' => t('Your license status should always be <b>VALID</b>. <a href="@spammaster_url">About Statuses</a>.', ['@spammaster_url' => 'https://spammaster.techgasp.com/documentation/']),
    ];
    // Insert addrow alert level field.
    $form['license_header']['license']['addrow']['license_alert_level'] = [
      '#disabled' => TRUE,
      '#type' => 'textarea',
      '#rows' => 2,
      '#title' => $this->t('Your alert level:'),
      '#default_value' => $spammaster_alert_level_label . $spammaster_alert_level_text,
      '#description' => t('Your website alert level. <a href="@spammaster_url">About Alert Levels</a>.', ['@spammaster_url' => 'https://spammaster.techgasp.com/documentation/']),
    ];

    // Insert spam table inside tree.
    $form['license_header']['spam'] = [
      '#type' => 'table',
    ];
    // Insert addrow license status field.
    $form['license_header']['spam']['addrow']['license_protection'] = [
      '#disabled' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Your protection count:'),
      '#default_value' => $protection_total_number_text,
      '#description' => $this->t('Threats & Exploits protection number.'),
    ];
    // Insert addrow alert level field.
    $form['license_header']['spam']['addrow']['license_probability'] = [
      '#disabled' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Your spam probability:'),
      '#default_value' => $config->get('spammaster.license_probability') . $spammaster_alert_level_p_label,
      '#description' => t('Your spam probability. <a href="@spammaster_url">About Spam Probability</a>.', ['@spammaster_url' => 'https://spammaster.techgasp.com/documentation/']),
    ];

    // Start TREE-> 2 protection tools.
    $form['protection_header'] = [
      '#type' => 'details',
      '#title' => $this->t('<h3>Protection Tools</h3>'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];

    // Insert license key field.
    $form['protection_header']['block_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change block message:'),
      '#default_value' => $config->get('spammaster.block_message'),
      '#description' => $this->t('Message to display to blocked spam users who are not allowed to register, contact or comment in your Drupal. Keep it short.'),
    ];

    // Insert basic tools table inside tree.
    $form['protection_header']['basic'] = [
      '#type' => 'table',
      '#header' => [
          ['data' => 'Activate individual Basic Tools to implement Spam Master across your site.', 'colspan' => 4],
      ],
    ];
    $form['protection_header']['basic']['addrow']['basic_firewall'] = [
      '#type' => 'select',
      '#title' => t('Firewall Scan'),
      '#options' => [
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.basic_firewall'),
      '#description' => t('Set this to <em>Yes</em> if you would like the Firewall scan implemented across your site. Greatly reduces server resources like CPU and Memory.'),
    ];
    $form['protection_header']['basic']['addrow']['basic_registration'] = [
      '#type' => 'select',
      '#title' => t('Registration Scan'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.basic_registration'),
      '#description' => t('Set this to <em>Yes</em> if you would like the Registraion Scan for new registration attempts. Applies to registration form.'),
    ];
    $form['protection_header']['basic']['addrow']['basic_comment'] = [
      '#type' => 'select',
      '#title' => t('Comment Scan'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.basic_comment'),
      '#description' => t('Set this to <em>Yes</em> if you would like the Comment Scan for new comment attempts. Applies to comment form.'),
    ];
    $form['protection_header']['basic']['addrow']['basic_contact'] = [
      '#type' => 'select',
      '#title' => t('Contact Scan'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.basic_contact'),
      '#description' => t('Set this to <em>Yes</em> if you would like the Contact Scanto be display on the contact form.'),
    ];

    // Insert Extra tools table inside tree.
    $form['protection_header']['extra'] = [
      '#type' => 'table',
      '#header' => [
          ['data' => 'Activate individual Extra Tools to implement Spam Master across your site.', 'colspan' => 4],
      ],
    ];

    $form['protection_header']['extra']['addrow']['extra_honeypot'] = [
      '#type' => 'select',
      '#title' => t('Honeypot'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_honeypot'),
      '#description' => t('Set this to <em>Yes</em> if you would like 2 Honeypot fields implemented across your site forms.'),
    ];
    $form['protection_header']['extra']['addrow']['extra_recaptcha'] = [
      '#type' => 'select',
      '#title' => t('Google re-Captcha V2'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_recaptcha'),
      '#description' => t('Set this to <em>Yes</em> if you would like Google re-Captcha V2 implemented across your site forms.'),
    ];
    // Insert addrow re-captcha api key.
    $form['protection_header']['extra']['addrow']['extra_recaptcha_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google re-Captcha API Site Key:'),
      '#default_value' => $config->get('spammaster.extra_recaptcha_api_key'),
      '#description' => $this->t('Insert your Google re-Captcha api key.'),
    ];
    // Insert addrow re-captcha secrete key.
    $form['protection_header']['extra']['addrow']['extra_recaptcha_api_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google re-Captcha API Secret Key:'),
      '#default_value' => $config->get('spammaster.extra_recaptcha_api_secret_key'),
      '#description' => $this->t('Insert your Google re-Captcha api secret key.'),
    ];

    $form['protection_header']['extra']['addrow1']['extra_recaptcha_login'] = [
      '#type' => 'select',
      '#title' => t('re-Captcha on Login Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_recaptcha_login'),
      '#description' => t('Set this to <em>Yes</em> if you would like Google re-Captcha implemented on the Login Form.'),
    ];
    $form['protection_header']['extra']['addrow1']['extra_recaptcha_registration'] = [
      '#type' => 'select',
      '#title' => t('re-Captcha on Registration Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_recaptcha_registration'),
      '#description' => t('Set this to <em>Yes</em> if you would like Google re-Captcha implemented on the Registration Form.'),
    ];
    $form['protection_header']['extra']['addrow1']['extra_recaptcha_comment'] = [
      '#type' => 'select',
      '#title' => t('re-Captcha on Comment Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_recaptcha_comment'),
      '#description' => t('Set this to <em>Yes</em> if you would like Google re-Captcha implemented on the Comment Form.'),
    ];
    $form['protection_header']['extra']['addrow1']['extra_recaptcha_contact'] = [
      '#type' => 'select',
      '#title' => t('re-Captcha on Contact Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_recaptcha_contact'),
      '#description' => t('Set this to <em>Yes</em> if you would like Google re-Captcha implemented on the Contact Form.'),
    ];

    $form['protection_header']['extra']['addrow2']['extra_honeypot_login'] = [
      '#type' => 'select',
      '#title' => t('honeypot on Login Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_honeypot_login'),
      '#description' => t('Set this to <em>Yes</em> if you would like Honeypot on the Login Form.'),
    ];
    $form['protection_header']['extra']['addrow2']['extra_honeypot_registration'] = [
      '#type' => 'select',
      '#title' => t('honeypot on Registration Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_honeypot_registration'),
      '#description' => t('Set this to <em>Yes</em> if you would like Honeypot on the Registration Form.'),
    ];
    $form['protection_header']['extra']['addrow2']['extra_honeypot_comment'] = [
      '#type' => 'select',
      '#title' => t('honeypot on Comment Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_honeypot_comment'),
      '#description' => t('Set this to <em>Yes</em> if you would like Honeypot on the Comment Form.'),
    ];
    $form['protection_header']['extra']['addrow2']['extra_honeypot_contact'] = [
      '#type' => 'select',
      '#title' => t('honeypot on Contact Form'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.extra_honeypot_contact'),
      '#description' => t('Set this to <em>Yes</em> if you would like Honeypot on the Contact Form.'),
    ];

    // Insert signature tools table inside tree.
    $form['protection_header']['signature'] = [
      '#type' => 'table',
      '#header' => [
          ['data' => 'Signtures are a huge deterrent against all forms of human span.', 'colspan' => 4],
      ],
    ];
    $form['protection_header']['signature']['addrow']['signature_registration'] = [
      '#type' => 'select',
      '#title' => t('Registration Signature'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.signature_registration'),
      '#description' => t('Set this to <em>Yes</em> if you would like a Protection Signature to be displayed on the registration form.'),
    ];
    $form['protection_header']['signature']['addrow']['signature_login'] = [
      '#type' => 'select',
      '#title' => t('Login Signature'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.signature_login'),
      '#description' => t('Set this to <em>Yes</em> if you would like a Protection Signature to be display on the login form.'),
    ];
    $form['protection_header']['signature']['addrow']['signature_comment'] = [
      '#type' => 'select',
      '#title' => t('Comment Signature'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.signature_comment'),
      '#description' => t('Set this to <em>Yes</em> if you would like a Protection Signature to be display on the comment form.'),
    ];
    $form['protection_header']['signature']['addrow']['signature_contact'] = [
      '#type' => 'select',
      '#title' => t('Contact Signature'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.signature_contact'),
      '#description' => t('Set this to <em>Yes</em> if you would like a Protection Signature to be display on the contact form.'),
    ];

    // Insert email tools table inside tree.
    $form['protection_header']['email'] = [
      '#type' => 'table',
      '#header' => [
        ['data' => 'Emails & Reporting adds an extra watchful eye over your drupal website security. Emails and reports are sent to the email address found in your drupal Configuration, Basic Site Settings.', 'colspan' => 4],
      ],
    ];
    $form['protection_header']['email']['addrow']['email_alert_3'] = [
      '#type' => 'select',
      '#title' => t('Alert 3 Warning Email'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.email_alert_3'),
      '#description' => t('Set this to <em>Yes</em> to receive the alert 3 email. Only sent if your website reached or is at a dangerous level.'),
    ];
    $form['protection_header']['email']['addrow']['email_daily_report'] = [
      '#type' => 'select',
      '#title' => t('Daily Report Email'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.email_daily_report'),
      '#description' => t('Set this to <em>Yes</em> to receive the daily report for normal alert levels and spam probability percentage.'),
    ];
    $form['protection_header']['email']['addrow']['email_weekly_report'] = [
      '#type' => 'select',
      '#title' => t('Weekly Report Email'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.email_weekly_report'),
      '#description' => t('Set this to <em>Yes</em> to receive the Weekly detailed email report.'),
    ];
    $form['protection_header']['email']['addrow']['email_improve'] = [
      '#type' => 'select',
      '#title' => t('Help Us Improve Spam Master'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.email_improve'),
      '#description' => t('Set this to <em>Yes</em> to help Us improve Spam Master with weekly statistical data, same as your weekly report.'),
    ];

    // Start TREE-> 3 Buffer.
    $form['buffer_header'] = [
      '#type' => 'details',
      '#title' => $this->t('<h3>Spam Buffer</h3>'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];

    // Construct header.
    $header = [
      'id' => t('ID'),
      'date' => t('Date'),
      'threat' => t('Threat'),
      'search' => t('Search'),
    ];
    // Get table spammaster_threats data.
    $query = \Drupal::database()->select('spammaster_threats', 'u');
    $query->fields('u', ['id', 'date', 'threat']);
    // Pagination, we need to extend pagerselectextender and limit the query.
    $query->orderBy('id', 'DESC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $spammaster_spam_buffer = $pager->execute()->fetchAll();

    $output = [];
    foreach ($spammaster_spam_buffer as $results) {
      if (!empty($results)) {
        if (filter_var($results->threat, FILTER_VALIDATE_IP)) {
          $search = Url::fromUri('https://spammaster.techgasp.com/search-threat/?search_spam_threat=' . $results->threat);
          $search_display = \Drupal::l('+ Spam Master online database', $search);
        }
        else {
          $search_display = 'discard email';
          $search = '';
        }
        $output[$results->id] = [
          'id' => $results->id,
          'date' => $results->date,
          'threat' => $results->threat,
          'search' => $search_display,
        ];
      }
    }
    // Spam Buffer Description.
    $form['buffer_header']['description'] = [
      '#markup' => '<p>Spam Master Buffer greatly reduces server resources like cpu, memory and bandwidth by doing fast local machine checks. Also prevents major attacks like flooding, DoS , etc. via Spam Master Firewall.</p>',
    ];
    // Display table.
    $form['buffer_header']['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No threats found'),
    ];
    // Delete button at end of table, calls spammasterdeletethreat function.
    $form['buffer_header']['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Delete Spam Entry'),
      '#submit' => ['::spammasterdeletethreat'],
    ];

    // Form pager if ore than 25 entries.
    $form['buffer_header']['pager'] = [
      '#type' => 'pager',
    ];

    // Start TREE-> 4 statistics.
    $form['statistics_header'] = [
      '#type' => 'details',
      '#title' => $this->t('<h3>Statistics & Log</h3>'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];
    // Create buttons table.
    $form['statistics_header']['buttons'] = [
      '#type' => 'table',
      '#header' => [],
    ];
    // Insert addrow statistics button.
    $form['statistics_header']['buttons']['addrow']['statistics'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Visit your Statistics Page'),
      '#submit' => ['::spammasterstatisticspage'],
    ];
    // Insert addrow firewall button.
    $form['statistics_header']['buttons']['addrow']['firewall'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Visit your Firewall Page'),
      '#submit' => ['::spammasterfirewallpage'],
    ];

    $spammaster_total_block_count = $config->get('spammaster.total_block_count');
    if (empty($spammaster_total_block_count)) {
      $spammaster_total_block_count = '0';
    }
    // Insert statistics table inside tree.
    $form['statistics_header']['total_block_count'] = [
      '#markup' => '<h2>Total Blocks: <b>' . $spammaster_total_block_count . '</b></h2>',
    ];

    $form['statistics_header']['statistics'] = [
      '#type' => 'table',
      '#header' => [
        'firewall' => 'Firewall',
        'registration' => 'Registration',
        'comment' => 'Comment',
        'contact' => 'contact',
      ],
    ];
    // Set wide dates.
    $time = date('Y-m-d H:i:s');
    $time_expires_1_day = date('Y-m-d H:i:s', strtotime($time . '-1 days'));
    $time_expires_7_days = date('Y-m-d H:i:s', strtotime($time . '-7 days'));
    $time_expires_31_days = date('Y-m-d H:i:s', strtotime($time . '-31 days'));

    // Generate Firewall Stats 1 day.
    $spammaster_firewall_1 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_firewall_1->fields('u', ['spamkey']);
    $spammaster_firewall_1->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_1_day, ':time' => $time]);
    $spammaster_firewall_1->where('(spamkey = :firewall)', [':firewall' => 'spammaster-firewall']);
    $spammaster_firewall_1_result = $spammaster_firewall_1->countQuery()->execute()->fetchField();
    // Generate Firewall Stats 7 days.
    $spammaster_firewall_7 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_firewall_7->fields('u', ['spamkey']);
    $spammaster_firewall_7->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_7_days, ':time' => $time]);
    $spammaster_firewall_7->where('(spamkey = :firewall)', [':firewall' => 'spammaster-firewall']);
    $spammaster_firewall_7_result = $spammaster_firewall_7->countQuery()->execute()->fetchField();
    // Generate Firewall Stats 31 days.
    $spammaster_firewall_31 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_firewall_31->fields('u', ['spamkey']);
    $spammaster_firewall_31->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_31_days, ':time' => $time]);
    $spammaster_firewall_31->where('(spamkey = :firewall)', [':firewall' => 'spammaster-firewall']);
    $spammaster_firewall_31_result = $spammaster_firewall_31->countQuery()->execute()->fetchField();
    // Generate Firewall Stats total.
    $spammaster_firewall = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_firewall->fields('u', ['spamkey']);
    $spammaster_firewall->where('(spamkey = :firewall)', [':firewall' => 'spammaster-firewall']);
    $spammaster_firewall_result = $spammaster_firewall->countQuery()->execute()->fetchField();
    $form['statistics_header']['statistics']['addrow']['firewall'] = [
      '#markup' =>
      '<p>Daily Blocks: <b>' . $spammaster_firewall_1_result . '</b></p>
      <p>Weekly Blocks: <b>' . $spammaster_firewall_7_result . '</b></p>
      <p>Monthly Blocks: <b>' . $spammaster_firewall_31_result . '</b></p>
      <p>Total Blocks: <b>' . $spammaster_firewall_result . '</b></p>',
    ];

    // Generate Registration Stats 1 day.
    $spammaster_registration_1 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration_1->fields('u', ['spamkey']);
    $spammaster_registration_1->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_1_day, ':time' => $time]);
    $spammaster_registration_1->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_1_result = $spammaster_registration_1->countQuery()->execute()->fetchField();
    // Generate Registration Stats 7 days.
    $spammaster_registration_7 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration_7->fields('u', ['spamkey']);
    $spammaster_registration_7->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_7_days, ':time' => $time]);
    $spammaster_registration_7->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_7_result = $spammaster_registration_7->countQuery()->execute()->fetchField();
    // Generate Registration Stats 31 days.
    $spammaster_registration_31 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration_31->fields('u', ['spamkey']);
    $spammaster_registration_31->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_31_days, ':time' => $time]);
    $spammaster_registration_31->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_31_result = $spammaster_registration_31->countQuery()->execute()->fetchField();
    // Generate Registration Stats total.
    $spammaster_registration = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_registration->fields('u', ['spamkey']);
    $spammaster_registration->where('(spamkey = :registration)', [':registration' => 'spammaster-registration']);
    $spammaster_registration_result = $spammaster_registration->countQuery()->execute()->fetchField();
    $form['statistics_header']['statistics']['addrow']['registration'] = [
      '#markup' =>
      '<p>Daily Blocks: <b>' . $spammaster_registration_1_result . '</b></p>
      <p>Weekly Blocks: <b>' . $spammaster_registration_7_result . '</b></p>
      <p>Monthly Blocks: <b>' . $spammaster_registration_31_result . '</b></p>
      <p>Total Blocks: <b>' . $spammaster_registration_result . '</b></p>',
    ];

    // Generate Comment Stats 1 day.
    $spammaster_comment_1 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment_1->fields('u', ['spamkey']);
    $spammaster_comment_1->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_1_day, ':time' => $time]);
    $spammaster_comment_1->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_1_result = $spammaster_comment_1->countQuery()->execute()->fetchField();
    // Generate Comment Stats 7 days.
    $spammaster_comment_7 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment_7->fields('u', ['spamkey']);
    $spammaster_comment_7->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_7_days, ':time' => $time]);
    $spammaster_comment_7->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_7_result = $spammaster_comment_7->countQuery()->execute()->fetchField();
    // Generate Comment Stats 31 days.
    $spammaster_comment_31 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment_31->fields('u', ['spamkey']);
    $spammaster_comment_31->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_31_days, ':time' => $time]);
    $spammaster_comment_31->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_31_result = $spammaster_comment_31->countQuery()->execute()->fetchField();
    // Generate Comment Stats total.
    $spammaster_comment = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_comment->fields('u', ['spamkey']);
    $spammaster_comment->where('(spamkey = :comment)', [':comment' => 'spammaster-comment']);
    $spammaster_comment_result = $spammaster_comment->countQuery()->execute()->fetchField();
    $form['statistics_header']['statistics']['addrow']['comment'] = [
      '#markup' =>
      '<p>Daily Blocks: <b>' . $spammaster_comment_1_result . '</b></p>
      <p>Weekly Blocks: <b>' . $spammaster_comment_7_result . '</b></p>
      <p>Monthly Blocks: <b>' . $spammaster_comment_31_result . '</b></p>
      <p>Total Blocks: <b>' . $spammaster_comment_result . '</b></p>',
    ];

    // Generate Contact Stats 1 day.
    $spammaster_contact_1 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact_1->fields('u', ['spamkey']);
    $spammaster_contact_1->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_1_day, ':time' => $time]);
    $spammaster_contact_1->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_1_result = $spammaster_contact_1->countQuery()->execute()->fetchField();
    // Generate Contact Stats 7 days.
    $spammaster_contact_7 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact_7->fields('u', ['spamkey']);
    $spammaster_contact_7->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_7_days, ':time' => $time]);
    $spammaster_contact_7->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_7_result = $spammaster_contact_7->countQuery()->execute()->fetchField();
    // Generate Contact Stats 31 days.
    $spammaster_contact_31 = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact_31->fields('u', ['spamkey']);
    $spammaster_contact_31->where('(date BETWEEN :time_expires AND :time)', [':time_expires' => $time_expires_31_days, ':time' => $time]);
    $spammaster_contact_31->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_31_result = $spammaster_contact_31->countQuery()->execute()->fetchField();
    // Generate Contact Stats total.
    $spammaster_contact = \Drupal::database()->select('spammaster_keys', 'u');
    $spammaster_contact->fields('u', ['spamkey']);
    $spammaster_contact->where('(spamkey = :contact)', [':contact' => 'spammaster-contact']);
    $spammaster_contact_result = $spammaster_contact->countQuery()->execute()->fetchField();
    $form['statistics_header']['statistics']['addrow']['contact'] = [
      '#markup' =>
      '<p>Daily Blocks: <b>' . $spammaster_contact_1_result . '</b></p>
      <p>Weekly Blocks: <b>' . $spammaster_contact_7_result . '</b></p>
      <p>Monthly Blocks: <b>' . $spammaster_contact_31_result . '</b></p>
      <p>Total Blocks: <b>' . $spammaster_contact_result . '</b></p>',
    ];

    // Construct header.
    $header_key = [
      'id' => t('ID'),
      'date' => t('Date'),
      'spamkey' => t('Type'),
      'spamvalue' => t('Description'),
    ];
    // Get table spammaster_keys data.
    $query_db = \Drupal::database()->select('spammaster_keys', 'u');
    $query_db->fields('u', ['id', 'date', 'spamkey', 'spamvalue']);
    // Pagination, we need to extend pagerselectextender and limit the query.
    $query_db->orderBy('id', 'DESC');
    $pager_db = $query_db->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $spammaster_spam_key = $pager_db->execute()->fetchAll();

    $output_key = [];
    foreach ($spammaster_spam_key as $results_key) {
      if (!empty($results_key)) {
        $output_key[$results_key->id] = [
          'id' => $results_key->id,
          'date' => $results_key->date,
          'spamkey' => $results_key->spamkey,
          'spamvalue' => $results_key->spamvalue,
        ];
      }
    }
    // Display table.
    $form['statistics_header']['table'] = [
      '#type' => 'tableselect',
      '#header' => $header_key,
      '#options' => $output_key,
      '#empty' => t('No log found'),
    ];
    // Delete button at end of table, calls spammasterdeletekey function.
    $form['statistics_header']['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Delete Log Entry'),
      '#submit' => ['::spammasterdeletekey'],
    ];
    // Delete button at end of table, calls spammasterdeletekeysall function.
    $form['statistics_header']['submit_all'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => ['button button--primary'],
      ],
      '#value' => t('Delete all Statistics & Logs -> Caution, no way back'),
      '#submit' => ['::spammasterdeletekeysall'],
    ];
    // Form pager if ore than 25 entries.
    $form['statistics_header']['pager_db'] = [
      '#type' => 'pager',
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get module config settings.
    $config = $this->config('spammaster.settings');
    if (empty($form_state->getValue('license_header')['license_key'])) {
      $form_state->setErrorByName('license_header', $this->t('License key can not be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('spammaster.settings');
    $config->set('spammaster.license_key', $form_state->getValue('license_header')['license_key']);
    $config->set('spammaster.block_message', $form_state->getValue('protection_header')['block_message']);
    $config->set('spammaster.basic_registration', $form_state->getValue('protection_header')['basic']['addrow']['basic_registration']);
    $config->set('spammaster.basic_comment', $form_state->getValue('protection_header')['basic']['addrow']['basic_comment']);
    $config->set('spammaster.basic_contact', $form_state->getValue('protection_header')['basic']['addrow']['basic_contact']);
    $config->set('spammaster.extra_honeypot', $form_state->getValue('protection_header')['extra']['addrow']['extra_honeypot']);
    $config->set('spammaster.extra_recaptcha', $form_state->getValue('protection_header')['extra']['addrow']['extra_recaptcha']);
    $config->set('spammaster.extra_recaptcha_api_key', $form_state->getValue('protection_header')['extra']['addrow']['extra_recaptcha_api_key']);
    $config->set('spammaster.extra_recaptcha_api_secret_key', $form_state->getValue('protection_header')['extra']['addrow']['extra_recaptcha_api_secret_key']);
    $config->set('spammaster.extra_recaptcha_login', $form_state->getValue('protection_header')['extra']['addrow1']['extra_recaptcha_login']);
    $config->set('spammaster.extra_recaptcha_registration', $form_state->getValue('protection_header')['extra']['addrow1']['extra_recaptcha_registration']);
    $config->set('spammaster.extra_recaptcha_comment', $form_state->getValue('protection_header')['extra']['addrow1']['extra_recaptcha_comment']);
    $config->set('spammaster.extra_recaptcha_contact', $form_state->getValue('protection_header')['extra']['addrow1']['extra_recaptcha_contact']);
    $config->set('spammaster.extra_honeypot_login', $form_state->getValue('protection_header')['extra']['addrow2']['extra_honeypot_login']);
    $config->set('spammaster.extra_honeypot_registration', $form_state->getValue('protection_header')['extra']['addrow2']['extra_honeypot_registration']);
    $config->set('spammaster.extra_honeypot_comment', $form_state->getValue('protection_header')['extra']['addrow2']['extra_honeypot_comment']);
    $config->set('spammaster.extra_honeypot_contact', $form_state->getValue('protection_header')['extra']['addrow2']['extra_honeypot_contact']);
    $config->set('spammaster.signature_registration', $form_state->getValue('protection_header')['signature']['addrow']['signature_registration']);
    $config->set('spammaster.signature_login', $form_state->getValue('protection_header')['signature']['addrow']['signature_login']);
    $config->set('spammaster.signature_comment', $form_state->getValue('protection_header')['signature']['addrow']['signature_comment']);
    $config->set('spammaster.signature_contact', $form_state->getValue('protection_header')['signature']['addrow']['signature_contact']);
    $config->set('spammaster.email_alert_3', $form_state->getValue('protection_header')['email']['addrow']['email_alert_3']);
    $config->set('spammaster.email_daily_report', $form_state->getValue('protection_header')['email']['addrow']['email_daily_report']);
    $config->set('spammaster.email_weekly_report', $form_state->getValue('protection_header')['email']['addrow']['email_weekly_report']);
    $config->set('spammaster.email_improve', $form_state->getValue('protection_header')['email']['addrow']['email_improve']);
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'spammaster.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function spammasterrefesh() {

    return header("Refresh:0");

  }

}

<?php

namespace Drupal\spammaster\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\spammaster\Controller\SpamMasterLicController;

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
  public function spammasterdeletethreat($form, &$form_state) {
    $spam_form_delete = $form_state->getValue('buffer_header')['table'];
    foreach ($spam_form_delete as $spam_row_delete) {
      if (!empty($spam_row_delete)) {
        db_query('DELETE FROM {spammaster_threats} WHERE id = :row', [':row' => $spam_row_delete]);
        drupal_set_message(t('Saved Spam Buffer deletion.'));
        \Drupal::logger('spammaster-buffer')->notice('Spam Master: buffer deletion, Id: ' . $spam_row_delete);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

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

    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('spammaster.settings');

    // Start TREE->1 license and status.
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

    // Start TREE->2 protection tools.
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
          ['data' => 'Activate individual Basic Tools to implement Spam Master accross your site.', 'colspan' => 4],
      ],
    ];
    $form['protection_header']['basic']['addrow']['basic_firewall'] = [
      '#type' => 'select',
      '#title' => t('Firewall Scan'),
      '#options' => [
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('spammaster.basic_firewall'),
      '#description' => t('Set this to <em>Yes</em> if you would like the Firewall scan implemented across you site. Greatly reduces server resources like CPU and Memory.'),
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

    // Start TREE->3 Buffer.
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
    ];
    // Get table spammaster_threats data.
    $query = \Drupal::database()->select('spammaster_threats', 'u');
    $query->fields('u', ['id', 'date', 'threat']);
    // Pagination, we need to extend pagerselectextender and limit the query.
    $query->orderBy('id', 'DESC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);
    $spammaster_spam_buffer = $pager->execute()->fetchAll();

    $output = [];
    foreach ($spammaster_spam_buffer as $results) {
      if (!empty($results)) {
        $output[$results->id] = [
          'id' => $results->id,
          'date' => $results->date,
          'threat' => $results->threat,
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

    // Start TREE->4 statistics.
    $form['statistics_header'] = [
      '#type' => 'details',
      '#title' => $this->t('<h3>Statistics</h3>'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];

    // Insert statistics table inside tree.
    $form['statistics_header']['total_block_count'] = [
      '#markup' => '<p>Total Blocks: <b>' . $config->get('spammaster.total_block_count') . '</b></p>',
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

    // Insert statistics footer inside tree.
    $form['statistics_header']['description'] = [
      '#markup' => '<p>All activity is being logged, you can check logs via top menu -> Reports -> Recent Log Messages, you can filter data by selectin the type "spammaster-*.</p>',
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

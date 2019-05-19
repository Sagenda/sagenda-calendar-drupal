<?php

namespace Drupal\sagenda\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Sagenda settings for this site.
 *
 * @internal
 */
class SagendaConfig extends ConfigFormBase
{

  /**
   * Constructs a SagendaConfig object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory)
  {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'sagenda_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['sagenda.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $settings = $this->config('sagenda.settings');

    $form['token'] = array(
      '#type' => 'textfield',
      '#default_value' => !empty($settings->get('token')) ? $settings->get('token') : '',
      '#title' => t('Token'),
      '#required' => true,
    );

    $form['date'] = array(
      '#type' => 'textfield',
      '#default_value' => !empty($settings->get('date')) ? $settings->get('date') : 'DD-MMMM-YYYY',
      '#title' => t('Date'),
      '#required' => true,
      '#description' => t('Date format should be DD-MMMM-YYYY'),
    );

    $form['time'] = array(
      '#type' => 'textfield',
      '#default_value' => !empty($settings->get('time')) ? $settings->get('time') : 'HH:mm',
      '#title' => t('Time'),
      '#required' => true,
      '#description' => t('Time format should be HH:mm'),
    );

    $weekstartson = [
      '1' => t('Monday'),
      '2' => t('Tuesday'),
      '3' => t('Wednesday'),
      '4' => t('Thursday'),
      '5' => t('Friday'),
      '6' => t('Saturday'),
      '7' => t('Sunday'),
    ];
    $form['weekstartson'] = array(
      '#type' => 'select',
      '#title' => t('Week Starts On'),
      '#options' => $weekstartson,
      '#default_value' => !empty($settings->get('weekstartson')) ? $settings->get('weekstartson') : '',
    );

    $defaultView = [
      'month' => t('Month'),
      'week' => t('Week'),
      'day' => t('Day'),
    ];
    $form['defaultView'] = array(
      '#type' => 'select',
      '#title' => t('Default View'),
      '#options' => $defaultView,
      '#default_value' => !empty($settings->get('defaultView')) ? $settings->get('defaultView') : 'month',
    );

    $form['removeMonthViewButton'] = array(
      '#type' => 'checkbox',
      '#title' => t('Remove Month view button'),
      '#description' => t('Activate/remove Month view button'),
    );

    $form['removeWeekViewButton'] = array(
      '#type' => 'checkbox',
      '#default_value' => !empty($settings->get('removeWeekViewButton')) ? $settings->get('removeWeekViewButton') : 'false',
      '#title' => t('Remove Week view button'),
      '#description' => t('Activate/remove Week view button'),
    );

    $form['removeDayViewButton'] = array(
      '#type' => 'checkbox',
      '#default_value' => !empty($settings->get('removeDayViewButton')) ? $settings->get('removeDayViewButton') : 'false',
      '#title' => t('Remove Day view button'),
      '#description' => t('Activate/remove Day view button'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $auth_token = $form_state->getValue('token');

    $config = $this->config('sagenda.settings');

    $config
      ->set('token', $auth_token)
      ->set('defaultView', $form_state->getValue('defaultView'))
      ->set('removeMonthViewButton', $form_state->getValue('removeMonthViewButton'))
      ->set('removeWeekViewButton', $form_state->getValue('removeWeekViewButton'))
      ->set('removeDayViewButton', $form_state->getValue('removeDayViewButton'))
      ->set('date', $form_state->getValue('date'))
      ->set('time', $form_state->getValue('time'))
      ->set('weekstartson', $form_state->getValue('weekstartson'));

    if ($auth_token) {
      $ch = curl_init(API_URL . 'ValidateAccount/' . $auth_token);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      $result1 = json_decode($result);

      if ($result1->Success != false) {
        $config->set('connection', 'CONNECTED');
        drupal_set_message(t("You have successfully connected."), 'status');
      } else {
        $config->set('connection', 'NOT CONNECTED');
        if (!empty($result1->Message)) {
          drupal_set_message(t('@error', array('@error' => $result1->Message)), 'error');
        }
      }
    } else {
      $config->set('connection', 'NOT CONNECTED');
      drupal_set_message(t("Please enter the auth token."), 'error');
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }
}

<?php

namespace Drupal\sagenda\Controller;


class SagendaController {

  public function displayCalendar() {
    $data = get_sagenda_calendar_config();

    return array(
      '#theme' => 'sagenda_booking_calendar',
      '#data' => $data,
      '#attached' => array(
        'library' => array(
          'sagenda/sagenda_booking_calendar',
        ),
      ),
    );
  }
}

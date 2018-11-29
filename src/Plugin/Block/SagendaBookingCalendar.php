<?php

namespace Drupal\sagenda\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManager;

/**
 * Provides a 'Sagenda Booking Calendar' Block.
 *
 * @Block(
 *   id = "sagenda_booking_calendar",
 *   admin_label = @Translation("Sagenda Booking Calendar"),
 *   category = @Translation("Sagenda Booking Calendar"),
 * )
 */
class SagendaBookingCalendar extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
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
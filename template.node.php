<?php

/**
 * @file
 * Node related preprocessors.
 */
/**
 * Implements hook_preprocess_node().
 *
 * Override or insert variables into the node templates.
 */
function culture_preprocess_node(&$variables, $hook) {
  //
  // Add tpl suggestions for node view modes.
  if (isset($variables['view_mode'])) {
    $variables['theme_hook_suggestions'][] = 'node__view_mode__' . $variables['view_mode'];
    $variables['theme_hook_suggestions'][] = 'node__' . $variables['type'] . '__view_mode__' . $variables['view_mode'];
  }

  //
  // Call our own custom preprocess functions.
  $function = 'culture_preprocess__node__' . $variables['type'];
  if (function_exists($function)) {
    call_user_func_array($function, array(&$variables));
  }

  // Opening hours on library list. but not on the search page.
  $path = drupal_get_path_alias();
  if (!(strpos($path, 'search', 0) === 0)) {
    $hide_empty_oh = variable_get('opening_hours_hide_on_empty_ding_library', 0);
    $node_have_active_oh = opening_hours_present_on_node($variables['node']->nid);
    if ($variables['type'] == 'ding_library') {
      if (empty($node_have_active_oh) && $hide_empty_oh == 1) {
        return;
      }
      $variables['opening_hours'] = theme('ding_ddbasic_opening_hours_week', array('node' => $variables['node']));
    }
  }

  // Add updated to variables.
  $variables['culture_updated'] = format_date($variables['node']->changed, 'long');

  // Modified submitted variable.
  if ($variables['display_submitted']) {
    $variables['submitted'] = format_date($variables['node']->changed, 'long');
  }
}

/**
 * Ding Campaign.
 */
function culture_preprocess__node__ding_campaign(&$variables) {
  $type = ding_base_get_value('node', $variables['node'], 'field_camp_settings', 'value');
  $variables['type'] = drupal_html_class($type);
  if (!empty($variables['field_camp_image'])) {
    $image_uri = ding_base_get_value('node', $variables['node'], 'field_camp_image', 'uri');
    $image_style = "ding_full_width";
    $image_url = file_create_url($image_uri);
    $variables['image'] = '<img src="' . $image_url . '">';
    $variables['background'] = ($type == 'text_on_image' ? 'style="background-image: url(' . $image_url . ');"' : " ");
  }
  $variables['link'] = ding_base_get_value('node', $variables['node'], 'field_camp_link', 'value');
  $variables['target'] = ding_base_get_value('node', $variables['node'], 'field_camp_new_window') ? '_blank' : '';
  $variables['panel_style'] = !empty($variables['elements']['#style']) ? drupal_html_class($variables['elements']['#style']) : '';

  // Display campaign if it is on the mobile browser.
  if (!empty($variables['field_show_on_mobiles'])) {
    $mobile_show = $variables['field_show_on_mobiles'][LANGUAGE_NONE][0]['value'];
    if ($mobile_show) {
      $variables['classes_array'][] = 'mobile-show';
    }
  }

  if (isset($type)) {
    switch ($type) {
      case 'image_and_text':
        $variables['image'] = '<div class="ding-campaign-image" style="background-image: url(' . $image_url . '"></div>';
        break;

      case 'image':
        if (!empty($variables['elements']['#widget_type']) && $variables['elements']['#widget_type'] == 'single') {
          $variables['image'] = theme('image', [
              'path' => $image_uri,
              'attributes' => ['class' => 'ding-campaign-image'],
            ]
          );
        }
        else {
          $variables['image'] = theme('image_style', [
              'style_name' => $image_style,
              'path' => $image_uri,
              'attributes' => ['class' => 'ding-campaign-image'],
            ]
          );
        }
        break;
    }
  }
}

/**
 * Ding event.
 */
function culture_preprocess__node__ding_event(&$variables) {
  $date = field_get_items('node', $variables['node'], 'field_ding_event_date');

  $price = field_get_items('node', $variables['node'], 'field_ding_event_price');
  if ($price[0]['value'] === '-1') {
    $variables['event_price'] = t('Entré');
  }
  elseif ($price !== FALSE && $price[0]['value'] !== '0') {
    $variables['event_price'] = $price[0]['value'] . ' ' . variable_get('ding_event_currency_type', 'Kr');
  }
  elseif ($price[0]['value'] === '0') {
    $variables['event_price'] = t('Free');
  }

  $location = field_get_items('node', $variables['node'], 'field_ding_event_location');
  $variables['alt_location_is_set'] = !empty($location[0]['name_line']) || !empty($location[0]['thoroughfare']);

  switch ($variables['view_mode']) {
    case 'teaser':
      // Add class if image.
      if (!empty($variables['field_ding_event_list_image'])) {
        $variables['classes_array'][] = 'has-image';
      }
      // Create image url.
      $uri = empty($variables['field_ding_event_list_image'][0]['uri']) ?
        "" : $variables['field_ding_event_list_image'][0]['uri'];

      if (!empty($uri)) {
        $variables['event_background_image'] = image_style_url('ding_panorama_list_large', $uri);
      }

      $variables['image_title'] = empty($variables['field_ding_event_list_image'][0]['title']) ?
        "" : 'title="' . $variables['field_ding_event_list_image'][0]['title'] . '"';

      // Date.
      if (!empty($date)) {
        // When the user saves the event time (e.g. danish time 2018-01-10 00:00),
        // the value is saved in the database in UTC time
        // (e.g. UTC time 2018-01-09 23:00). To print out the date/time properly
        // We first need to create the dateObject with the UTC database time, and
        // afterwards we can convert the dateObject db-time to localtime.

        // Create a dateObject from startdate, set base timezone to UTC
        $date_start = new DateObject($date[0]['value'], new DateTimeZone($date[0]['timezone_db']));
        // Set timezone to local timezone
        $date_start->setTimezone(new DateTimeZone($date[0]['timezone']));

        // Create a dateObject from enddate, set base timezone to UTC
        $date_end = new DateObject($date[0]['value2'], new DateTimeZone($date[0]['timezone_db']));
        // Set timezone to local timezone
        $date_end->setTimezone(new DateTimeZone($date[0]['timezone']));

        $variables['event_date'] = date_format_date($date_start, 'ding_date_only_version2');
        $event_time_view_settings = array(
          'label' => 'hidden',
          'type' => 'date_default',
          'settings' => array(
            'format_type' => 'ding_time_only',
            'fromto' => 'value',
          ),
        );

        // If start and end date days are equal.
        if ($date_start->format('Ymd') !== $date_end->format('Ymd')) {
          $variables['event_date'] .= ' - ' . date_format_date($date_end, 'ding_date_only_version2');
        }
        // If start and end date days and time are not equal.
        if ($date_start->format('YmdHi') !== $date_end->format('YmdHi')) {
          $event_time_view_settings['settings']['fromto'] = 'both';
        }

        $event_time_ra = field_view_field('node', $variables['node'], 'field_ding_event_date', $event_time_view_settings);
        $variables['event_time'] = $event_time_ra[0]['#markup'];
      }

      break;

    case 'full':
      if (!empty($date)) {
        array_push($variables['classes_array'], 'node-full');

        // Add event time to variables. A render array is created based on the
        // date format "time_only".
        $event_time_ra = field_view_field('node', $variables['node'], 'field_ding_event_date', array(
          'label' => 'hidden',
          'type' => 'date_default',
          'settings' => array(
            'format_type' => 'ding_time_only',
            'fromto' => 'both',
          ),
        ));
        $variables['event_time'] = $event_time_ra[0]['#markup'];

        // Make social-share button.
        $variables['share_button'] = array(
          '#theme' => 'ding_sharer',
          '#label' => t('Share this event'),
        );

        // Make book/participate in event button.
        $price = ding_base_get_value('node', $variables['node'], 'field_ding_event_price');
        $participate = t('Participate in the event');
        $book = t('Book a ticket');

        if (empty($price)) {
          $text = $participate;
        }
        else {
          $text = $book;
        }

        $link_url = ding_base_get_value('node', $variables['node'], 'field_ding_event_ticket_link', 'url');

        if (!empty($link_url)) {
          $variables['book_button'] = l($text, $link_url, array(
            'attributes' => array(
              'class' => array('ticket', 'button'),
              'target' => '_blank',
            ),
          ));
        }

        if (!empty($location)) {
          $variables['content']['group_left']['og_group_ref']['#access'] = FALSE;
        }
      }
      break;
  }
}


/**
 * Ding news.
 */
function culture_preprocess__node__ding_news(&$variables) {

  $variables['news_submitted'] = format_date($variables['created'], 'ding_date_only_version2');

  switch ($variables['view_mode']) {
    case 'full':
      array_push($variables['classes_array'], 'node-full');

      // Make social-share button.
      $variables['content']['group_right']['share_button'] = array(
        '#theme' => 'ding_sharer',
        '#label' => t('Share this news'),
      );

      break;

    case 'alternative_layout_full':
      array_push($variables['classes_array'], 'node-full', 'alternative-layout-full');

      // Make social-share button.
      $variables['content']['group_left']['share_button'] = array(
        '#theme' => 'ding_sharer',
        '#label' => t('Share this news'),
      );

      break;

    case 'teaser':
      if (!empty($variables['field_ding_news_list_image'][0]['uri'])) {
        // Get image url to use as background image.
        $uri = $variables['field_ding_news_list_image'][0]['uri'];

        $image_title = $variables['field_ding_news_list_image'][0]['title'];

        // If in view with large first teaser and first in view.
        $current_view = $variables['view']->current_display;
        $views_with_large_first = array('ding_news_frontpage_list');
        if (in_array($current_view, $views_with_large_first) && $variables['view']->result[0]->nid == $variables['nid']) {
          $img_url = image_style_url('ding_panorama_list_large_wide', $uri);
          $variables['classes_array'][] = 'ding-news-highlighted';
        }
        else {
          $img_url = image_style_url('ding_panorama_list_large', $uri);
        }
        if (!empty($image_title)) {
          $variables['news_teaser_image'] = '<div class="ding-news-list-image background-image-styling-16-9" style="background-image:url(' . $img_url . ')" title="' . $image_title . '"></div>';
        }
        else {
          $variables['news_teaser_image'] = '<div class="ding-news-list-image background-image-styling-16-9" style="background-image:url(' . $img_url . ')"></div>';
        }

        $variables['news_image_url'] = $img_url;
      }
      else {
        $variables['news_teaser_image'] = '<div class="ding-news-list-image background-image-styling-16-9"></div>';
      }
      break;
  }
}

/**
 * Ding Library.
 */
function culture_preprocess__node__ding_library(&$variables) {
  // Google maps addition to library list.
  $address = $variables['content']['field_ding_library_addresse'][0]['#address'];

  $street = $address['thoroughfare'];
  $street = preg_replace('/\s+/', '+', $street);
  $postal = $address['postal_code'];
  $city = $address['locality'];
  $country = $address['country'];
  $url = "http://www.google.com/maps/place/" . $street . "+" . $postal . "+" . $city . "+" . $country;
  $link = l(t("Show on map"), $url, array('attributes' => array('class' => 'maps-link', 'target' => '_blank')));

  $variables['content']['maps_link']['#markup'] = $link;
  $variables['content']['maps_link']['#weight'] = 10;
}

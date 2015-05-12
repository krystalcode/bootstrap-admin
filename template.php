<?php
/**
 * @file
 * template.php
 */

/**
 * Implements hook_preprocess_page().
 */
function bootstrap_admin_preprocess_page(&$variables) {
  // Build the management menu as the primary navbar menu.
  $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'management'));
  $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__management');
}

/**
 * Overrides theme_menu_tree() for the management (admin) menu.
 */
function bootstrap_admin_menu_tree__management(&$variables) {
  return '<ul class="menu nav navbar-nav primary">' . $variables['tree'] . '</ul>';
}

/**
 * Overrides theme_menu_tree() for the user menu.
 *
 * Sole reason for adding this override it on top of
 * bootstrap_menu_tree__secondary is to pull it to the right by adding the
 * navbar-right class.
 */
function bootstrap_admin_menu_tree__secondary(&$variables) {
  return '<ul class="menu nav navbar-nav navbar-right secondary">' . $variables['tree'] . '</ul>';
}

/**
 * Overrides theme_menu_link() for the management (admin) menu.
 *
 * Differences compared to the default bootstrap_menu_link() theme override are,
 * removed unnecessary code for rendering the expanded items as dropdowns, and
 * added icons for expanding/collapsing items.
 */
function bootstrap_admin_menu_link__management($variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if ($element['#below']) {
    // Add icons for expanding/collapsing parent items.
    $element['#title'] = $element['#title'] .
      _bootstrap_icon('chevron-right', array(), array('class' => array('icon-expand', 'pull-right'))) .
      _bootstrap_icon('chevron-left', array(), array('class' => array('icon-collapse', 'pull-right')))
    ;

    unset($element['#below']['#theme_wrappers']);
    $sub_menu = '<ul class="menu nav">' . drupal_render($element['#below']) . '</ul>';
  }

  // On primary navigation menu, class 'active' is not set on active menu item.
  // @see https://drupal.org/node/1896674
  if (($element['#href'] == $_GET['q'] || ($element['#href'] == '<front>' && drupal_is_front_page())) && (empty($element['#localized_options']['language']))) {
    $element['#attributes']['class'][] = 'active';
  }

  $output = l($element['#title'], $element['#href'], $element['#localized_options'] + array('html' => TRUE));

  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

/**
 * Overrides theme_admin_block().
 *
 * Render admin blocks as panels.
 */
function bootstrap_admin_admin_block($variables) {
  $block = $variables['block'];

  // Don't display the block if it has no content to display.
  if (empty($block['show'])) {
    return '';
  }

  $output = '<div class="panel panel-default panel-list-group">';

  if (!empty($block['title'])) {
    $output .= '<div class="panel-heading">';
    $output .= '<h3 class="panel-title">' . $block['title'] . '</h3>';
    $output .= '</div>';
  }

  if (empty($block['content'])) {
    $content = $block['description'];
  }
  else {
    $content = $block['content'];
  }

  $output .= '<div class="panel-body">' . $content . '</div>';

  $output .= '</div>';

  return $output;
}

/**
 * Overrides theme_admin_block_content().
 *
 * Render admin block content as a list group in both compact and extended mode.
 */
function bootstrap_admin_admin_block_content($variables) {
  $content = $variables['content'];

  if (empty($content)) {
    return '';
  }

  $output = system_admin_compact_mode() ? '<div class="list-group compact">' : '<div class="list-group">';

  foreach ($content as $item) {
    $options = $item['localized_options'] + array('html' => TRUE);
    $options['attributes']['class'][] = 'list-group-item';

    $list_item = '<h4 class="list-group-item-heading">' . $item['title'] . '</h4>';

    if (isset($item['description']) && !system_admin_compact_mode()) {
      $list_item .= '<p class="list-group-item-text-description">' . filter_xss_admin($item['description']) . '</p>';
    }

    $output .= l($list_item, $item['href'], $options);
  }

  $output .= '</div>';

  return $output;
}

/**
 * Overrides theme_admin_page().
 *
 * Render admin pages using Bootstrap Grid rows and columns.
 */
function bootstrap_admin_admin_page($variables) {
  $blocks = $variables['blocks'];

  $stripe = 0;
  $container = array('left' => '', 'right' => '');

  // Position blocks in left and right columns.
  foreach ($blocks as $block) {
    $block_output = theme('admin_block', array('block' => $block));
    if (!$block_output) {
      continue;
    }

    if (empty($block['position'])) {
      // Perform automatic striping.
      $block['position'] = ++$stripe % 2 ? 'left' : 'right';
    }

    $container[$block['position']] .= $block_output;
  }

  // Render all output as columns within a row.
  $output = '<div class="row">';
  $output .= '<div class="col-xs-12">' . theme('system_compact_link') . '</div>';

  foreach ($container as $id => $data) {
    $output .= '<div class="col-md-6">';
    $output .= $data;
    $output .= '</div>';
  }

  $output .= '</div>';

  return $output;
}

/**
 * Overrides theme_system_admin_index().
 *
 * Render the admin index page using Bootstrap Grid rows and columns.
 */
function bootstrap_admin_system_admin_index($variables) {
  $menu_items = $variables['menu_items'];

  $stripe = 0;
  $container = array('left' => '', 'right' => '');
  $flip = array('left' => 'right', 'right' => 'left');
  $position = 'left';

  // Iterate over all modules.
  foreach ($menu_items as $module => $block) {
    list($description, $items) = $block;

    // Output links.
    if (count($items)) {
      $block = array();
      $block['title'] = $module;
      $block['content'] = theme('admin_block_content', array('content' => $items));
      $block['description'] = t($description);
      $block['show'] = TRUE;

      if ($block_output = theme('admin_block', array('block' => $block))) {
        if (!isset($block['position'])) {
          // Perform automatic striping.
          $block['position'] = $position;
          $position = $flip[$position];
        }
        $container[$block['position']] .= $block_output;
      }
    }
  }

  // Render all output as columns within a row.
  $output = '<div class="row">';
  $output .= '<div class="col-xs-12">' . theme('system_compact_link') . '</div>';

  foreach ($container as $id => $data) {
    $output .= '<div class="col-md-6">';
    $output .= $data;
    $output .= '</div>';
  }

  $output .= '</div>';

  return $output;
}

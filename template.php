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

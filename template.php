<?php

/**
 * @file
 * Preprocessors.
 */
require_once __DIR__ . '/template.node.php';


/**
 * Render callback.
 *
 * Remove panels div separator.
 */

 /**
 * Implements theme_menu_tree().
 */
function culture_menu_tree__menu_block__1($vars) {
  return '<ul class="main-menu">' . $vars['tree'] . '</ul>';
}

/**
 * Implements theme_menu_tree().
 */
function culture_menu_tree__menu_block__2($vars) {
  return '<ul class="secondary-menu">' . $vars['tree'] . '</ul>';
}

/**
 * Implements hook_preprocess_panels_pane().
 */
function culture_preprocess_panels_pane(&$vars) {
  // If using lazy pane caching method, and lazy pane is returning the rendered
  // content, set the lazy_pane_render variable, so the template can take action
  // accordingly.
  $vars['is_lazy_pane_render'] = !empty($vars['pane']->cache['method'])
    && $vars['pane']->cache['method'] === 'lazy'
    && !empty($vars['display']->skip_cache);
  if ($vars['is_lazy_pane_render']) {
    $vars['theme_hook_suggestions'][] = 'panels_pane__lazy_pane_render';
  }

  // Suggestions base on sub-type.
  $vars['theme_hook_suggestions'][] = 'panels_pane__' . str_replace('-', '__', $vars['pane']->subtype);
  $vars['theme_hook_suggestions'][] = 'panels_pane__' . $vars['pane']->panel . '__' . str_replace('-', '__', $vars['pane']->subtype);

  if (isset($vars['content'])) {
    if (isset($vars['content']['profile_ding_staff_profile']['#title']) && $vars['content']['profile_ding_staff_profile']['#title'] == 'Staff') {
      $vars['theme_hook_suggestions'][] = 'panels_pane__user_profile_staff';
    }
  }

  // Suggestions on panel pane.
  $vars['theme_hook_suggestions'][] = 'panels_pane__' . $vars['pane']->panel;

  // Suggestion for mobile user menu in the header.
  if ($vars['pane']->panel == 'header' && $vars['pane']->subtype == 'user_menu') {
    $vars['theme_hook_suggestions'] = array('panels_pane__sub_menu__mobile');
  }

  // Suggestions on menus panes.
  if ($vars['pane']->subtype == 'og_menu-og_single_menu_block' || $vars['pane']->subtype == 'menu_block-3') {
    $vars['theme_hook_suggestions'][] = 'panels_pane__sub_menu';
    $vars['classes_array'][] = 'sub-menu-wrapper';

    // Change the theme wrapper for both menu-block and OG menu.
    if (isset($vars['content']['#content']) && is_array($vars['content']['#content'])) {
      // Menu-block.
      $vars['content']['#content']['#theme_wrappers'] = array('menu_tree__sub_menu');
    }
    elseif(is_array($vars['content'])) {
      // OG menu.
      $vars['content']['#theme_wrappers'] = array('menu_tree__sub_menu');
    }
  }

  if ($vars['pane']->subtype == 'menu_block-main_menu_second_level') {
    cu_body_class('has-second-level-menu');
  }
}

<?php
/**
 * @file
 * Contains Drupal\theminator\Form\TheminatorConfigForm
 */

namespace Drupal\theminator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TheminatorConfigForm
 * @package Drupal\theminator\Form
 */
class TheminatorConfigForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'theminator.config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = _theminator_get_immutable_config();

    $entity_type = "node";
    $node_bundles = \Drupal::entityManager()->getBundleInfo($entity_type);

    $registry = theme_get_registry();

    $form_config = $config->get("theme_config");

    $field_hooks = array("" => "(" . t("Default") . ")");
    foreach ($registry AS $registry_name => $registry_info) {
      if ($registry_info["base hook"] == "field" || strstr($registry_name, "theminator")) {
        $field_hooks[$registry_name] = $registry_info["template"];
      }
    }

    foreach ($node_bundles AS $node_bundle_name => $info) {
      $wrapper_name = "entity--" . $entity_type . "--" . $node_bundle_name;
      $form[$wrapper_name] = array(
        "#type" => "details",
        "#title" => $info["label"],
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#group' => 'vertical_tab_settings',
      );

      $fields = \Drupal::entityManager()->getFieldDefinitions($entity_type, $node_bundle_name);

      foreach ($fields AS $field_id => $field_info) {
        if (!$field_info->isReadOnly()) {

          $field_key = $wrapper_name . "__" . $field_info->getName();

          $form[$wrapper_name][$field_key] = array(
            "#type" => "details",
            "#title" => $field_info->getLabel(),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#group' => 'vertical_tab_settings',
          );

          $form[$wrapper_name][$field_key]['theme__' . $field_key] = array(
            "#title" => t("Theme hook"),
            "#type" => "select",
            "#options" => $field_hooks,
            "#default_value" => (empty($form_config[$field_key]['theme'])) ? '' : $form_config[$field_key]['theme'],
          );
        }
      }
    }

    $form['submit'] = array(
      "#type" => "submit",
      "#value" => t('Save settings'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $clean_values = array();

    foreach ($values AS $id => $value) {
      $key = substr($id, 7);
      if (substr($id, 0, strlen("theme__entity")) == "theme__entity") {
        if (!empty($value)) {
          $clean_values[$key]['theme'] = $value;
        }
      }
    }

    $config = _theminator_get_editable_config();
    $config->set("theme_config", $clean_values);
    $config->save();
  }
}

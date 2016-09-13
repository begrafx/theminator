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

    //dpm($config->get("theme_config"));

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
        //var_dump($field_info);
        //exit;
        if (!$field_info->isReadOnly()) {
          $form[$wrapper_name][$wrapper_name . "__" . $field_info->getName()] = array(
            "#title" => $field_info->getLabel(),
            "#type" => "select",
            "#options" => $field_hooks,
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

    foreach ($values AS $id => $value) {
      if (substr($id, 0, strlen("entity")) != "entity") {
        unset($values[$id]);
      }
    }

    $config = _theminator_get_editable_config();
    $config->set("theme_config", $values);
    $config->save();
  }
}

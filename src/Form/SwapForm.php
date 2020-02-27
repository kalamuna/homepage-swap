<?php
/**
 * @file
 * Contains Drupal\welcome\Form\MessagesForm.
 */
namespace Drupal\homepage_swap\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SwapForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'homepage_swap_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'homepage_swap.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $config = $this->config('homepage_swap.settings');

      // Get list of content types to select which can be used for
      // Swapping of homepage.
      $contentTypes = \Drupal\node\Entity\NodeType::loadMultiple();
      
      $contentTypeList = [];
      foreach($contentTypes as $type) {
        $contentTypeList[$type->id()] = $type->label();
      }

      $form['swap_content_types'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Swappable Content Types'),
          '#description' => $this->t('Please select the content types you would like to use for Swapping.'),
          '#default_value' => $config->get('entity_type.allowed_types'),
          '#options' => $contentTypeList,
      ];

      

      return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $allowed_types = array_filter($form_state->getValue('swap_content_types'));
    sort($allowed_types);
    
    $this->config('homepage_swap.settings')
      ->set('entity_type.allowed_types', $allowed_types)
      ->save();

      parent::submitForm($form, $form_state);
  }
  
}


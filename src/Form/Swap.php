<?php
/**
 * @file
 * Contains Drupal\welcome\Form\MessagesForm.
 */
namespace Drupal\homepage_swap\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;
use Drupal\node\Entity\Node;

class Swap extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'homepage_swap';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'homepage_swap.swap',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $config = $this->config('homepage_swap.settings');
      
      // Get list of content types to select which can be used for
      // Swapping of homepage.
      $contentTypes = $config->get('entity_type.allowed_types');
      $contentList = [];

      // Setup the content table list
      $output = array();

      $header = [
        'page_title' => t('Page Title'),
        'content_type' => t('Content Type'),
        'quick_links' => t('Quick Links')
      ];

      // Run through content types list and display page titles for each node of that type
      foreach($contentTypes as $ct) {
        $nids = \Drupal::entityQuery('node')->condition('type', $ct)->execute();
        $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

        // Store the node title into the nid's index
        foreach($nodes as $node) {
            $contentList[$node->id()] = $node->title->value;
            $output[$node->id()] = [
              'page_title' => t('<a href="/node/' . $node->id() . '">' . $node->title->value . "</a>"),
              'content_type' => $ct,
              'quick_links' => t(
                '<a href="/node/' . $node->id() . '">View</a>' . "&nbsp;" .
                '<a href="/node/' . $node->id() . '/edit">Edit</a>'
              ),
            ];
        }
      }

      // Get the current front page's nid
      $config = \Drupal::config('system.site');
      $front = str_replace("/node/", "", $config->get('page.front'));

      $form['swap_content'] = [
          '#type' => 'select',
          '#title' => $this->t('Select Homepage'),
          '#description' => $this->t('Please select the page you would like as the homepage.'),
          '#default_value' => $front,
          '#options' => $contentList,
      ];

      $form['confirm_swap'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('<b>Confirm Homepage Swap</b>'),
        '#description'=> $this->t('By checking this box, I understand that I will be swapping the homepage to the selected page above.'),
        '#required' => TRUE,
      ];

      $form['table'] = [
        '#type' => 'table',
        '#caption' => $this->t('<h2>Available Homepage Options</h2>'),
        '#header' => $header,
        '#rows' => $output,
        '#empty' => t('No content found'),
      ];

      return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get configuration for front page
    $config = \Drupal::configFactory()->getEditable('system.site');
    
    // Unpublish previous front page
    $prevFrontId = str_replace("/node/", "", $config->get('page.front'));
    $prevFront = Node::load($prevFrontId);
    $prevFront->status = 0;
    $prevFront->save();

    // Publish new front page
    $newFrontId = $form_state->getValue('swap_content');
    $newFront = Node::load($newFrontId);
    $newFront->status = 1;
    $newFront->save();

    // Set new front page
    $config->set('page.front', '/node/' . $newFrontId)->save();

    parent::submitForm($form, $form_state);
  }
  
}


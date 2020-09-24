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
      
      // // Get list of content types to select which can be used for
      // // Swapping of homepage.
      $contentTypes = $config->get('entity_type.allowed_types');
      $contentList = [];

      // Get the current front page's nid
      $config = \Drupal::config('system.site');
      $front = str_replace("/node/", "", $config->get('page.front'));

      $header = [
        'page_title' => t('Page Title'),
        'content_type' => t('Content Type'),
        'quick_links' => t('Quick Links'),
        'activate' => t('Activate')
      ];

      $form['description'] = array(
        '#markup' => t('<p>This interface allows you to change the home page of the website in the event of a special promotion or emergency situation</p>'),
      );

      $form['pages'] = array(
        '#type' => 'table',
        '#caption' => $this->t('<h2>Available Homepage Options</h2>'),
        '#header' => $header
      );

      // Run through content types list and display page titles for each node of that type
      foreach($contentTypes as $ct) {
        $nids = \Drupal::entityQuery('node')->condition('type', $ct)->execute();
        $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

        // Store the node title into the nid's index
        foreach($nodes as $node) {
            $contentList[$node->id()] = $node->title->value;
           
            $form['pages'][$node->id()]['page_title'] = array(
              '#markup' => t('<a href="/node/' . $node->id() . '">' . $node->title->value . "</a>"),
            );

            $form['pages'][$node->id()]['content_type'] = array(
              '#markup' => t($ct),
            );

            $form['pages'][$node->id()]['quick_links'] = array(
              '#markup' => t(
                '<a href="/node/' . $node->id() . '">View</a>' . "&nbsp;" .
                '<a href="/node/' . $node->id() . '/edit">Edit</a>'
              ),
            );

            if($node->id() == $front) {
              $form['pages'][$node->id()]['activate'] = array(
                '#markup' => t('<em>Currently active</em>'),
              );
            } else {
              $form['pages'][$node->id()]['activate'] = [
                '#markup' => $this->t('<a href="/admin/content/swap_homepage/' . $node->id() . '/confirm_swap" class="button button--secondary">Activate</a>'),
              ];
            }
        }
      }
      
      return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
  } 
}
<?php
/**
 * @file
 * Contains Drupal\homepage_swap\Form\SwapConfirm.
 */
namespace Drupal\homepage_swap\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SwapConfirmForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get configuration for front page
    $config = \Drupal::configFactory()->getEditable('system.site');
    
    // Unpublish previous front page
    $prevFrontId = str_replace("/node/", "", $config->get('page.front'));
    $prevFront = Node::load($prevFrontId);
    $prevFront->status = 0;
    $prevFront->save();

    // Publish new front page
    $newFront = Node::load($this->id);
    $newFront->status = 1;
    $newFront->save();

    // Set new front page
    $config->set('page.front', '/node/' . $this->id)->save();

    $response = new RedirectResponse("/admin/content/swap_homepage");
    $response->send();

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_swap_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('homepage_swap.swap_page');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to swap the homepage to %id?', ['%id' => $this->id]);
  }

}
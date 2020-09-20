<?php

/**
 * @file
 * Contains \Drupal\rsvplist\Form\RSVPForm
 */
namespace Drupal\rsvplist\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides and RSVP Email form.
 */
class RSVPForm extends FormBase {
  /**
   * (@inheritdoc)
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }
  
  /**
   * (@inheritdoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
    }
    else {
      $nid = null;
    }
    
    $form['email'] = array(
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("We'll send updates to the email address you provide"),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('RSVP'),
    );
    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    return $form;
  }
  
  /**
   * (@inheritdoc)
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if ($value == !\Drupal::service('email.validator')->isValid($value)){
      $form_state->setErrorByName('email', t('The email address %mail is not valid', array('%mail'=>$value) ));
      return;
    }
    $node = \Drupal::routeMatch()->getParameter('node');
    // Check if email already is set for this node
    $select = Database::getConnection()->select('rsvplist', 'r');
    $select->fields('r', array('nid'));
    $select->condition('mail', $value);
    $select->condition('nid', $node->id());
    $results = $select->execute();
    if (!empty($results->fetchCol())) {
      // We found a row with this nid and email
      $form_state->setErrorByName('email', t('The address %mail is already subscribed to this list.', array('%mail' => $value)));
    }
    
    parent::validateForm($form, $form_state);
    
  }
  
  /**
   * (@inheritdoc)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    
//    db_insert('rsvplist')
    $query = \Drupal::database()->insert('rsvplist');
    $query->fields([
      'mail',
      'nid',
      'uid',
      'created',
    ]);
    $query->values([
      $form_state->getValue('email'),
      $form_state->getValue('nid'),
      $user->id(),
      time(),
      
    ]);
    $query->execute();
    
       
//    drupal_set_message(t('The form is working.'));
    \Drupal::messenger()->addMessage(t('Thank you for your RSVP, you are on the list for the event.'));
    
    
  }
}

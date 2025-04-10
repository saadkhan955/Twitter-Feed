<?php

namespace Drupal\twitter_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Twitter Feed settings for this site.
 */
class TwitterFeedSettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'twitter_feed_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['twitter_feed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('twitter_feed.settings');

    $form['api_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Twitter API Settings'),
      '#open' => TRUE,
    ];

    $form['api_settings']['bearer_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer Token'),
      '#description' => $this->t('Enter your Twitter API v2 Bearer Token. You can obtain this from the Twitter Developer Portal.'),
      '#default_value' => $config->get('bearer_token'),
      '#required' => TRUE,
    ];

    $form['default_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Default Twitter Feed Settings'),
      '#open' => TRUE,
    ];

    $form['default_settings']['default_handle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Twitter Handle'),
      '#description' => $this->t('Enter the default Twitter handle to use (without the @ symbol).'),
      '#default_value' => $config->get('default_handle'),
      '#required' => TRUE,
    ];

    $form['default_settings']['tweet_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Tweets'),
      '#description' => $this->t('The number of tweets to display (maximum 10).'),
      '#default_value' => $config->get('tweet_count') ? $config->get('tweet_count') : 5,
      '#min' => 1,
      '#max' => 10,
      '#required' => TRUE,
    ];

    $form['cache_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Cache Settings'),
      '#open' => TRUE,
    ];

    $form['cache_settings']['cache_lifetime'] = [
      '#type' => 'number',
      '#title' => $this->t('Cache Lifetime'),
      '#description' => $this->t('How long to cache tweets in seconds (default: 3600 = 1 hour).'),
      '#default_value' => $config->get('cache_lifetime') ? $config->get('cache_lifetime') : 3600,
      '#min' => 300,
      '#required' => TRUE,
    ];

    $form['dev_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Dev Mode (use dummy tweets)'),
      '#description' => $this->t('Skip live API calls and use fake tweet data for development.'),
      '#default_value' => $config->get('dev_mode'),
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);

    // Ensure the Twitter handle doesn't include the @ symbol.
    $handle = $form_state->getValue('default_handle');
    if (strpos($handle, '@') === 0) {
      $form_state->setErrorByName('default_handle', $this->t('Please enter the Twitter handle without the @ symbol.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config('twitter_feed.settings')
      ->set('bearer_token', $form_state->getValue('bearer_token'))
      ->set('default_handle', $form_state->getValue('default_handle'))
      ->set('tweet_count', $form_state->getValue('tweet_count'))
      ->set('cache_lifetime', $form_state->getValue('cache_lifetime'))
      ->set('dev_mode', $form_state->getValue('dev_mode'))
      ->save();

    // Invalidate the twitter_feed cache tag to refresh any cached tweets.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['twitter_feed']);

    parent::submitForm($form, $form_state);
  }

}
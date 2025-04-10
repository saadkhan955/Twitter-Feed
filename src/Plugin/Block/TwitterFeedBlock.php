<?php

namespace Drupal\twitter_feed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\twitter_feed\TwitterService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Twitter Feed' block.
 *
 * @Block(
 *   id = "twitter_feed_block",
 *   admin_label = @Translation("Twitter Feed Block"),
 *   category = @Translation("Social Media")
 * )
 */
class TwitterFeedBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  protected $twitterService;
  protected $configFactory;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TwitterService $twitter_service, ConfigFactoryInterface $config_factory)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->twitterService = $twitter_service;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('twitter_feed.twitter_service'),
      $container->get('config.factory')
    );
  }

  public function defaultConfiguration()
  {
    return [
      'twitter_handle' => '',
      'tweet_count' => 5,
      'show_profile_image' => TRUE,
      'default_theme' => 'light',
      'allow_theme_toggle' => TRUE,
    ];
  }

  public function blockForm($form, FormStateInterface $form_state)
  {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['twitter_handle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Handle'),
      '#description' => $this->t('Enter the Twitter handle to display tweets from (without the @ symbol).'),
      '#default_value' => $config['twitter_handle'],
      '#required' => TRUE,
    ];

    $form['tweet_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Tweets'),
      '#description' => $this->t('The number of tweets to display (maximum 10).'),
      '#default_value' => $config['tweet_count'],
      '#min' => 1,
      '#max' => 10,
      '#required' => TRUE,
    ];

    $form['show_profile_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Profile Image'),
      '#description' => $this->t('Display the user profile image next to tweets.'),
      '#default_value' => $config['show_profile_image'],
    ];

    $form['appearance'] = [
      '#type' => 'details',
      '#title' => $this->t('Appearance Settings'),
      '#open' => TRUE,
    ];

    $form['appearance']['default_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Theme'),
      '#description' => $this->t('Select the default color theme for this Twitter feed.'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
        'auto' => $this->t('Auto (follow system preference)'),
      ],
      '#default_value' => $config['default_theme'] ?? 'light',
    ];

    $form['appearance']['allow_theme_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow Users to Toggle Theme'),
      '#description' => $this->t('Display a theme toggle button on the Twitter feed.'),
      '#default_value' => $config['allow_theme_toggle'] ?? TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $this->configuration['twitter_handle'] = $form_state->getValue('twitter_handle');
    $this->configuration['tweet_count'] = $form_state->getValue('tweet_count');
    $this->configuration['show_profile_image'] = $form_state->getValue('show_profile_image');
    $this->configuration['default_theme'] = $form_state->getValue(['appearance', 'default_theme']);
    $this->configuration['allow_theme_toggle'] = $form_state->getValue(['appearance', 'allow_theme_toggle']);
  }

  public function build()
  {
    $config = $this->getConfiguration();
    $tweets = $this->twitterService->getLatestTweets($config['twitter_handle'], $config['tweet_count']);

    $build = [
      '#theme' => 'twitter_feed_block',
      '#tweets' => $tweets,
      '#handle' => $config['twitter_handle'],
      '#show_profile_image' => $config['show_profile_image'],
      '#default_theme' => $config['default_theme'],
      '#allow_theme_toggle' => $config['allow_theme_toggle'],
      '#attached' => [
        'library' => [
          'twitter_feed/twitter_feed',
          'twitter_feed/theme_toggle',
        ],
      ],
    ];

    return $build;
  }

  public function getCacheContexts()
  {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

  public function getCacheTags()
  {
    return Cache::mergeTags(parent::getCacheTags(), ['twitter_feed']);
  }

  public function getCacheMaxAge()
  {
    $config = $this->configFactory->get('twitter_feed.settings');
    return $config->get('cache_lifetime') ?: 3600;
  }

}

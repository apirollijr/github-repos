<?php
/*
Plugin Name: GitHub Repos Viewer
Description: Displays your public GitHub repositories using a shortcode.
Version: 1.0
Author: Anthony Pirolli Jr.
*/

if (!defined('ABSPATH')) exit;

function github_repos_shortcode($atts) {
  $atts = shortcode_atts([
    'user' => 'apirollijr',
    'count' => 6,
  ], $atts);

  $username = esc_attr($atts['user']);
  $count = intval($atts['count']);

  $response = wp_remote_get("https://api.github.com/users/{$username}/repos?per_page={$count}", [
    'headers' => [
      'User-Agent' => 'WordPress-GitHub-Repos-Plugin'
    ]
  ]);

  if (is_wp_error($response)) {
    return '<p>Error: ' . esc_html($response->get_error_message()) . '</p>';
  }

  $body = wp_remote_retrieve_body($response);
  $repos = json_decode($body);

  if (!is_array($repos)) {
    return '<p>Unexpected GitHub API response.</p>';
  }

  ob_start();
  echo '<div class="github-repos">';
  foreach ($repos as $repo) {
    echo '<div class="github-repo">';
    echo '<h3><a href="' . esc_url($repo->html_url) . '" target="_blank">' . esc_html($repo->name) . '</a></h3>';
    echo '<p>' . esc_html($repo->description ?: 'No description.') . '</p>';
    echo '</div>';
  }
  echo '</div>';

  return ob_get_clean();
}
add_shortcode('github_repos', 'github_repos_shortcode');

// Optional: Add basic styles
function github_repos_styles() {
  echo '<style>
    .github-repos {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 2rem;
    }
    .github-repo {
      background: #fff;
      padding: 1.2rem;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .github-repo h3 {
      margin: 0 0 10px;
    }
    .github-repo a {
      color: #0366d6;
      text-decoration: none;
    }
    .github-repo a:hover {
      text-decoration: underline;
    }
  </style>';
}
add_action('wp_head', 'github_repos_styles');

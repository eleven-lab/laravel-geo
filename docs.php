<?php

use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
  ->files()
  ->name('*.php')
  ->in(__DIR__.'/src');

return new Sami($iterator, [
    'theme' => 'default',
    'title' => 'karomap/laravel-geo',
    'build_dir' => __DIR__.'/docs',
    'cache_dir' => __DIR__.'/cache',
    'remote_repository' => new GitHubRemoteRepository('karomap/laravel-geo', __DIR__),
    'default_opened_level' => 2,
]);

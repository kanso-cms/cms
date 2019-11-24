<?php

use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = dirname(__FILE__, 2);

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('api')
    ->exclude('bin')
    ->exclude('vendor')
    ->exclude('app')
    ->exclude('tests')
    ->exclude('kanso/cms/admin/assets/js/vendor/JavaScriptSpellCheck/core/php')
    ->in($dir)
;

// generate documentation for all v2.0.* tags, the 2.0 branch, and the master one
$versions = GitVersionCollection::create($dir)
    ->addFromTags('2.0.*')
    ->addFromTags('3.*')
    ->addFromTags('4.*')
    ->addFromTags('5.*')
    ->add('master', 'master branch')
;

return new Sami($iterator, [
    'theme'                => 'default',
    'versions'             => $versions,
    'title'                => 'Kanso CMS API',
    'build_dir'            => __DIR__ . '/../api/build/%version%',
    'cache_dir'            => __DIR__ . '/../api/cache/%version%',
    'remote_repository'    => new GitHubRemoteRepository('kanso-cms/cms', $dir),
    'default_opened_level' => 2,
]);

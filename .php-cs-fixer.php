<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'bootstrap', 'storage'])
    ->name('*.php')
    ->notName('*.blade.php');

$config = new Config();

return $config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal',
            ],
        ],
        'blank_line_before_statement' => ['statements' => ['return']],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments']],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
            'after_heredoc' => false,
        ],
    ]);

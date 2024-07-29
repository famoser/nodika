<?php

$fileHeaderComment = <<<COMMENT
This file is part of the nodika project.

(c) Florian Moser <git@famoser.ch>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
COMMENT;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'header_comment' => ['header' => $fileHeaderComment, 'separate' => 'both'],
    ])
    ->setFinder($finder)
;

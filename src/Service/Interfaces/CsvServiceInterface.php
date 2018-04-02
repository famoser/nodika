<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface CsvServiceInterface
{

    /**
     * creates a response containing the data rendered as a csv
     *
     * @param string $filename
     * @param string[] $header
     * @param string[][] $data
     *
     * @return StreamedResponse
     */
    public function renderCsv($filename, $data, $header = null);
}

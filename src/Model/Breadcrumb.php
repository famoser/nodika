<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/19/18
 * Time: 10:45 AM
 */

namespace App\Model;


class Breadcrumb
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    /**
     * Breadcrumb constructor.
     * @param string $path
     * @param string $name
     */
    public function __construct($path, $name)
    {
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
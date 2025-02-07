<?php

namespace App\Data;

use App\Entity\Category;

class SearchData
{
    /**
     * @var integer
     */
    public int $page = 1;

    /**
     * @var string
     */
    public ?string $q = '';

    /**
     * @var Category|null
     */
    public ?Category $categories = null;
}
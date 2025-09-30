<?php
class SearchController {
    protected $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleSearch($query) {
        $query = trim($query);
        if ($query === '') return [];
        return $this->model->searchByTitleOrAuthor($query);
    }
}

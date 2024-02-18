<?php

use App\Constants\App;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if(!function_exists('paginatedRequest')){
    function paginatedRequest(): array
    {
        // get current user ID
        $currentUserId = auth()->user() ? auth()->user()->id : null;

        // get request parameters
        $requestParams = request()->all();

        // get limit
        $rowsPagePageKey = Arr::exists($requestParams, 'rowsPerPage') ? 'rowsPerPage' : 'rows_per_page';
        $limit = Arr::get($requestParams, $rowsPagePageKey, App::DEFAULT_PAGE_LIMIT);
        if ((int) $limit === 0) { // for cases of all, rows_per_page is 0 value
            $limit = Arr::get($requestParams, 'per_page', App::DEFAULT_PAGE_LIMIT);
        }

        // get keyword for general search
        $keyword = Arr::get($requestParams, 'keyword', '');

        // get sort field
        $field = Arr::get($requestParams, 'sort_by', 'created_at');

        // get sorting direction
        $descending = Arr::get($requestParams, 'descending', 'false');
        $sort = $descending === 'true' ? 'desc' : 'asc';

        // get display value
        $showAllRecords = Arr::get($requestParams, 'display_all', 0);

        // get filter
        $filters = array_filter(json_decode(Arr::get($requestParams, 'filters', '{}'), true));

        return [
            "current_user_id" => $currentUserId,
            "search_keyword" => $keyword,
            "page_limit" => $limit,
            "sort_field" => $field,
            "sort" => $sort,
            "filters" => $filters,
            "show_all_records" => (boolean)$showAllRecords
        ];
    }
}

if (!function_exists('generateSlug')) {
    function generateSlug(string $sluggableString): string
    {
        return Str::slug($sluggableString);
    }
}

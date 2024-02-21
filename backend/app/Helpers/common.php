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
//        $filters = array_filter(Arr::get($requestParams, 'filters', '{}'));

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

if (!function_exists('jeEncrypter')) {
    function jeEncrypter($txt) {
        $cipher = "aes-128-ctr";
        $key = "mj-vapors"; // static for now
        $ciphertext = "";

        // check if exist the cipher
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext = openssl_encrypt($txt, $cipher, $key, $options=0, $iv);
        }

        return $ciphertext."+*".base64_encode($iv);
    }
}

if (!function_exists('jeDecrypter')) {
    function jeDecrypter($txt) {
        $original_plaintext = "";
        $cipher = "aes-128-ctr";
        $key = "mj-vapors"; // static for now
        [$txt, $iv] = explode("+*", $txt);
        $iv = base64_decode($iv);

        if (in_array($cipher, openssl_get_cipher_methods())) {
            $original_plaintext = openssl_decrypt($txt, $cipher, $key, $options=0, $iv);
        }

        return $original_plaintext;
    }
}

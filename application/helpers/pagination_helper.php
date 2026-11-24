<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pagination Helper
 * Helper untuk setup pagination dengan mudah
 */

if (!function_exists('setup_pagination')) {
    /**
     * Setup pagination configuration
     * @param string $base_url
     * @param int $total_rows
     * @param int $per_page Default 10
     * @param int $uri_segment Default 3
     * @return array Config untuk pagination
     */
    function setup_pagination($base_url, $total_rows, $per_page = 10, $uri_segment = 3) {
        $CI =& get_instance();
        $CI->load->library('pagination');
        
        $config = array(
            'base_url' => $base_url,
            'total_rows' => $total_rows,
            'per_page' => $per_page,
            'uri_segment' => $uri_segment,
            'use_page_numbers' => TRUE,
            'first_link' => '&laquo; Awal',
            'last_link' => 'Akhir &raquo;',
            'next_link' => '&gt;',
            'prev_link' => '&lt;',
            'full_tag_open' => '<ul class="pagination pagination-sm m-0 float-right">',
            'full_tag_close' => '</ul>',
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close' => '</li>',
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close' => '</li>',
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close' => '</a></li>',
            'num_tag_open' => '<li class="page-item">',
            'num_tag_close' => '</li>',
            'attributes' => array('class' => 'page-link')
        );
        
        $CI->pagination->initialize($config);
        return $CI->pagination->create_links();
    }
}

















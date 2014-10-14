<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* CI 2.1.4
Pagination 일반형 변환 및 onclick 추가
*/
class MY_Pagination extends CI_Pagination {
    public $onclick = '';

	public $num_links        = 4;
	public $cur_page         = 1;
	public $uri_segment      = 5;
	public $use_page_numbers = TRUE;

    public $first_link = '&laquo; 처음';
    public $next_link  = '&gt;';
    public $prev_link  = '&lt;';
    public $last_link  = '끝 &raquo;';

    public $full_tag_open  = '<ul class="pagination">';
    public $full_tag_close = '</ul>';
    public $num_tag_open   = '<li>';
    public $num_tag_close  = '</li>';
    public $cur_tag_open   = '<li class="active"><a>';
    public $cur_tag_close  = '</a></li>';
    public $next_tag_open  = '<li>';
    public $next_tag_close = '</li>';
    public $prev_tag_open  = '<li>';
    public $prev_tag_close = '</li>';

    public $first_tag_open  = '<li>';
    public $first_tag_close = '</li>';
    public $last_tag_open   = '<li>';
    public $last_tag_close  = '</li>';

    function create_links() {
        # <<<Custom
        $this->suffix = preg_replace('(/page/[0-9]*)', '', $this->suffix);
        $this->first_url = $this->base_url.'1'.$this->suffix;
        
        function on_click($func, $n) {
            return " onclick=\"".str_replace('%n', $n, $func)."return false;\"";
        }
        # Custom>>>

        // If our item count or per-page total is zero there is no need to continue.
        if ($this->total_rows == 0 OR $this->per_page == 0)
        {
            return '';
        }

        // Calculate the total number of pages
        $num_pages = ceil($this->total_rows / $this->per_page);

        // Is there only one page? Hm... nothing more to do here then.
        if ($num_pages == 1)
        {
            return '';
        }

        // Set the base page index for starting page number
        if ($this->use_page_numbers)
        {
            $base_page = 1;
        }
        else
        {
            $base_page = 0;
        }

        // Determine the current page number.
        $CI =& get_instance();

        if ($this->onclick)
        {
            // HIGH PASS
        }
        elseif ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
        {
            if ($CI->input->get($this->query_string_segment) != $base_page)
            {
                $this->cur_page = $CI->input->get($this->query_string_segment);

                // Prep the current page - no funny business!
                $this->cur_page = (int) $this->cur_page;
            }
        }
        else
        {
            if ($CI->uri->segment($this->uri_segment) != $base_page)
            {
                $this->cur_page = $CI->uri->segment($this->uri_segment);

                // Prep the current page - no funny business!
                $this->cur_page = (int) $this->cur_page;
            }
        }
        
        // Set current page to 1 if using page numbers instead of offset
        if ($this->use_page_numbers AND $this->cur_page == 0)
        {
            $this->cur_page = $base_page;
        }

        $this->num_links = (int)$this->num_links;

        if ($this->num_links < 1)
        {
            show_error('Your number of links must be a positive number.');
        }

        if ( ! is_numeric($this->cur_page))
        {
            $this->cur_page = $base_page;
        }

        // Is the page number beyond the result range?
        // If so we show the last page
        if ($this->use_page_numbers)
        {
            if ($this->cur_page > $num_pages)
            {
                $this->cur_page = $num_pages;
            }
        }
        else
        {
            if ($this->cur_page > $this->total_rows)
            {
                $this->cur_page = ($num_pages - 1) * $this->per_page;
            }
        }

        $uri_page_number = $this->cur_page;
        
        if ( ! $this->use_page_numbers)
        {
            $this->cur_page = floor(($this->cur_page/$this->per_page) + 1);
        }

        // Calculate the start and end numbers. These determine
        // which number to start and end the digit links with
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        // Is pagination being used over GET or POST?  If get, add a per_page query
        // string. If post, add a trailing slash to the base URL if needed
        if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
        {
            $this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
        }
        else
        {
            $this->base_url = rtrim($this->base_url, '/') .'/';
        }

        // And here we go...
        $output = '';

        // Render the "First" link
        if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))
        {
            $first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
            $output .= $this->first_tag_open.'<a '.$this->anchor_class.'href="'.$first_url.'"'.($this->onclick?on_click($this->onclick, 1):'').'>'.$this->first_link.'</a>'.$this->first_tag_close;
        }

        // Render the "previous" link
        if  ($this->prev_link !== FALSE AND $this->cur_page != 1)
        {
            if ($this->use_page_numbers)
            {
                $i = $uri_page_number - 1;
            }
            else
            {
                $i = $uri_page_number - $this->per_page;
            }

            if ($i == 0 && $this->first_url != '')
            {
                $output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'"'.($this->onclick?on_click($this->onclick, 1):'').'>'.$this->prev_link.'</a>'.$this->prev_tag_close;
            }
            else
            {
                // $i = ($i == 0) ? '' : $this->prefix.$i.$this->suffix;
                $output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'"'.($this->onclick?on_click($this->onclick, $i):'').'>'.$this->prev_link.'</a>'.$this->prev_tag_close;
            }

        }

        // Render the pages
        if ($this->display_pages !== FALSE)
        {
            // Write the digit links
            for ($loop = $start -1; $loop <= $end; $loop++)
            {
                if ($this->use_page_numbers)
                {
                    $i = $loop;
                }
                else
                {
                    $i = ($loop * $this->per_page) - $this->per_page;
                }

                if ($i >= $base_page)
                {
                    if ($this->cur_page == $loop)
                    {
                        $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
                    }
                    else
                    {
                        $n = ($i == $base_page) ? '' : $i;

                        if ($n == '' && $this->first_url != '')
                        {
                            $output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'"'.($this->onclick?on_click($this->onclick, 1):'').'>'.$loop.'</a>'.$this->num_tag_close;
                        }
                        else
                        {
                            // $n = ($n == '') ? '' : $this->prefix.$n.$this->suffix;
                            $output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$n.$this->suffix.'"'.($this->onclick?on_click($this->onclick, $n):'').'>'.$loop.'</a>'.$this->num_tag_close;
                        }
                    }
                }
            }
        }

        // Render the "next" link
        if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)
        {
            if ($this->use_page_numbers)
            {
                $i = $this->cur_page + 1;
            }
            else
            {
                $i = ($this->cur_page * $this->per_page);
            }

            $output .= $this->next_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'" '.($this->onclick?on_click($this->onclick, $i):'').'>'.$this->next_link.'</a>'.$this->next_tag_close;
        }

        // Render the "Last" link
        if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)
        {
            if ($this->use_page_numbers)
            {
                $i = $num_pages;
            }
            else
            {
                $i = (($num_pages * $this->per_page) - $this->per_page);
            }
            $output .= $this->last_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'" '.($this->onclick?on_click($this->onclick, $i):'').'>'.$this->last_link.'</a>'.$this->last_tag_close;
        }

        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = $this->full_tag_open.$output.$this->full_tag_close;

        return $output;
    }
}
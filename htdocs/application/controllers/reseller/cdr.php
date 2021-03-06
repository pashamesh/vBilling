<?php 
/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * The Original Code is "vBilling - VoIP Billing and Routing Platform"
 * 
 * The Initial Developer of the Original Code is 
 * Digital Linx [<] info at digitallinx.com [>]
 * Portions created by Initial Developer (Digital Linx) are Copyright (C) 2011
 * Initial Developer (Digital Linx). All Rights Reserved.
 *
 * Contributor(s)
 * "Digital Linx - <vbilling at digitallinx.com>"
 *
 * vBilling - VoIP Billing and Routing Platform
 * version 0.1.3
 *
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cdr extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('reseller/cdr_model');
        $this->load->model('reseller/groups_model');
        
		//validate login
		if (!user_login())
		{
			redirect ('home/');
		}
		else
		{
			if($this->session->userdata('user_type') != 'reseller')
			{
				redirect ('customer/');
			}
		}
	}

	function index()
	{

		$filter_display_results = 'sec';

		//this is defualt start and end time  
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_phonenum    = '';
		$filter_customers   = '';
		$filter_groups      = '';
		$filter_call_type   = '';
		$filter_quick       = '';
		$duration_from      = '';
		$duration_to        = '';
        $filter_sort        = '';
        $filter_contents    = 'all';
		$search             = '';

		$msg_records_found = "Records Found";

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_phonenum        = $this->input->get('filter_phonenum');
			$filter_customers       = $this->input->get('filter_customers');
			$filter_groups          = $this->input->get('filter_groups');
			$filter_call_type       = $this->input->get('filter_call_type');
			$filter_quick           = $this->input->get('filter_quick');
			$duration_from          = $this->input->get('duration_from');
			$duration_to            = $this->input->get('duration_to');
			$filter_display_results = $this->input->get('filter_display_results');
            $filter_sort            = $this->input->get('filter_sort');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$msg_records_found      = "Records Found Based On Search Criteria";
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data['filter_date_from']           = $filter_date_from;
		$data['filter_date_to']             = $filter_date_to;
		$data['filter_phonenum']            = $filter_phonenum;
		$data['filter_customers']           = $filter_customers;
		$data['filter_groups']              = $filter_groups;
		$data['filter_call_type']           = $filter_call_type;
		$data['filter_quick']               = $filter_quick;
		$data['duration_from']              = $duration_from;
		$data['duration_to']                = $duration_to;
        $data['filter_sort']                = $filter_sort;
        $data['filter_contents']            = $filter_contents;
		$data['filter_display_results']     = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'reseller/cdr/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_phonenum='.$filter_phonenum.'&filter_customers='.$filter_customers.'&filter_groups='.$filter_groups.'&filter_call_type='.$filter_call_type.'&filter_display_results='.$filter_display_results.'&filter_quick='.$filter_quick.'&duration_from='.$duration_from.'&duration_to='.$duration_to.'&filter_sort='.$filter_sort.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 6;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_cdr_main_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_customers, $filter_groups, $filter_call_type, $duration_from, $duration_to, $filter_contents);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

		$data['cdr']            =   $this->cdr_model->get_all_cdr_data($config['per_page'],$config['uri_segment'],$filter_date_from, $filter_date_to, $filter_phonenum, $filter_customers, $filter_groups, $filter_call_type, $duration_from, $duration_to, $filter_sort, $filter_contents);
		$data['page_name']		=	'view_cdr_data';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'list_cdr';
		$data['page_title']		=	'CDR DETAILS';
		$data['main_menu']	    =	'default/main_menu/reseller_main_menu';
		$data['sub_menu']	    =	'default/sub_menu/reseller/cdr_sub_menu';
		$data['main_content']	=	'reseller/cdr/cdr_view';
		$this->load->view('default/template',$data);
	}
    
    function my_cdr()
	{
        $filter_display_results = 'sec';

		//this is default start and end time  
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_phonenum    = '';
		$filter_call_type   = '';
		$filter_quick       = '';
		$duration_from      = '';
		$duration_to        = '';
        $filter_sort        = '';
		$search             = '';

		$msg_records_found = "Records Found";

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_phonenum        = $this->input->get('filter_phonenum');
			$filter_call_type       = $this->input->get('filter_call_type');
			$filter_quick           = $this->input->get('filter_quick');
			$duration_from          = $this->input->get('duration_from');
			$duration_to            = $this->input->get('duration_to');
			$filter_display_results = $this->input->get('filter_display_results');
            $filter_sort            = $this->input->get('filter_sort');
			$search                 = $this->input->get('searchFilter');

			$msg_records_found      = "Records Found Based On Search Criteria";
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
		$data['filter_date_from']       = $filter_date_from;
		$data['filter_date_to']         = $filter_date_to;
		$data['filter_phonenum']        = $filter_phonenum;
		$data['filter_call_type']       = $filter_call_type;
		$data['filter_quick']           = $filter_quick;
		$data['duration_from']          = $duration_from;
		$data['duration_to']            = $duration_to;
		$data['filter_sort']            = $filter_sort;
		$data['filter_display_results'] = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'reseller/cdr/m/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_phonenum='.$filter_phonenum.'&filter_call_type='.$filter_call_type.'&filter_display_results='.$filter_display_results.'&filter_quick='.$filter_quick.'&duration_from='.$duration_from.'&duration_to='.$duration_to.'&filter_sort='.$filter_sort.'';
		$config['page_query_string'] = TRUE;
		$config['num_links']         = 6;

		$config['cur_tag_open']  = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link']      = 'next';
		$config['next_tag_open']  = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link']      = 'previous';
		$config['prev_tag_open']  = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link']  = 'last';

		$data['count'] = $this->cdr_model->get_my_cdr_main_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_call_type, $duration_from, $duration_to);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

		$data['cdr']            =   $this->cdr_model->get_all_my_cdr_data($config['per_page'],$config['uri_segment'],$filter_date_from, $filter_date_to, $filter_phonenum, $filter_call_type, $duration_from, $duration_to, $filter_sort);
		$data['page_name']		=	'view_my_cdr_data';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'my_cdr';
		$data['page_title']		=	'MY CDR DETAILS';
		$data['main_menu']	    =	'default/main_menu/reseller_main_menu';
		$data['sub_menu']	    =	'default/sub_menu/reseller/cdr_sub_menu';
		$data['main_content']	=	'reseller/cdr/my_cdr_view';
		$this->load->view('default/template',$data);
	}


	function customer_stats()
	{
		$filter_display_results = 'min';

		$filter_customers   = '';
        $filter_contents    = 'all';
		$search             = '';

		if($this->input->get('searchFilter'))
		{
			$filter_customers       = $this->input->get('filter_customers');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$filter_display_results = $this->input->get('filter_display_results');
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }
        
		$data['filter_customers']           = $filter_customers;
        $data['filter_contents']            = $filter_contents;
		$data['filter_display_results']     = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'reseller/cdr/customer_stats/?searchFilter='.$search.'&filter_customers='.$filter_customers.'&filter_display_results='.$filter_display_results.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_all_customers_count($filter_customers, $filter_contents);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['customers']      =   $this->cdr_model->get_all_customers($config['per_page'],$config['uri_segment'], $filter_customers, $filter_contents);
		$data['page_name']		=	'customer_stats';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'customer_stats';
		$data['page_title']		=	'CUSTOMER STATISTICS';
		$data['main_menu']	    =	'default/main_menu/reseller_main_menu';
		$data['sub_menu']	    =	'default/sub_menu/reseller/cdr_sub_menu';
		$data['main_content']	=	'reseller/cdr/customer_stats_view';
		$this->load->view('default/template',$data);
	}

	function call_destination()
	{
		$filter_display_results = 'min';

		//this is defualt start and end time  
		$startTime = time() - 86400; //last 24hrs 
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_countries   = '';
        $filter_contents    = 'all';
		$search             = '';

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_countries       = $this->input->get('filter_countries');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$filter_display_results = $this->input->get('filter_display_results');
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data['filter_date_from']           = $filter_date_from;
		$data['filter_date_to']             = $filter_date_to;
		$data['filter_countries']           = $filter_countries;
		$data['filter_display_results']     = $filter_display_results;
        $data['filter_contents']            = $filter_contents;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'reseller/cdr/call_destination/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_countries='.$filter_countries.'&filter_display_results='.$filter_display_results.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_all_countries_count($filter_countries, $filter_date_from, $filter_date_to, $filter_contents);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['countries']      =   $this->cdr_model->get_all_countries($config['per_page'],$config['uri_segment'], $filter_countries, $filter_date_from, $filter_date_to, $filter_contents);
		$data['page_name']		=	'call_destination';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'call_destination';
		$data['page_title']		=	'CALL DESTINATION DETAILS';
		$data['main_menu']	    =	'default/main_menu/reseller_main_menu';
		$data['sub_menu']	    =	'default/sub_menu/reseller/cdr_sub_menu';
		$data['main_content']	=	'reseller/cdr/call_destination_view';
		$this->load->view('default/template',$data);
	}

	function get_calculated_date_time()
	{
		$value = $this->input->post('val');

		$return_val = '';

		$current_date_time = date('Y-m-d H:i:s');
		$curr_date_starting_from_12_Am = "".date('Y-m-d')." 00:00:00";

		if($value == 'today' || $value == '')
		{
			$return_val = $curr_date_starting_from_12_Am.'|'.$current_date_time;
		}
		else if($value == 'last_hour')
		{
			$time = time();
			$last_hour = $time - 3600;
			$last_hour = date('Y-m-d H:i:s', $last_hour);
			$return_val = $last_hour.'|'.$current_date_time;
		}
		else if($value == 'last_24_hour')
		{
			$time = time();
			$last_24_hour = $time - 86400;
			$last_24_hour = date('Y-m-d H:i:s', $last_24_hour);
			$return_val = $last_24_hour.'|'.$current_date_time;
		}
		echo $return_val;
	}

	function export_pdf()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
        $filter_contents        = $this->input->get('filter_contents');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data_cdr = $this->cdr_model->export_cdr_data($filter_date_from, $filter_date_to, $filter_phonenum, $filter_customers, $filter_groups, $filter_call_type, $duration_from, $duration_to, $filter_sort, $filter_contents);

		if($data_cdr->num_rows() > 0)
		{
			$this->load->library('pdf');

			// set document information
			$this->pdf->SetSubject('CDR Export');
			$this->pdf->SetKeywords('DigitalLinx, CDR, export');

			// add a page
            //$this->SetPrintHeader(false);
           // $this->SetPrintFooter(false);
			$this->pdf->AddPage();

			$this->pdf->SetFont('helvetica', '', 6);
            
            $sql12 = "SELECT * FROM settings WHERE customer_id = '".$this->session->userdata('customer_id')."'";
            $query12 = $this->db->query($sql12);
            if($query12->num_rows() > 0)
            {
                $row12 = $query12->row();
                $data_array = explode(',',$row12->optional_cdr_fields_include);
            }
            else
            {
                $data_array = array();
            }
			$tbl = '<table cellspacing="0" cellpadding="1" border="1" width="100%">
				<tr style="background-color:grey; color:#ffffff;">
                <td height="20" align="center">Date/Time</td>
				<td align="center">Destination</td>
				<td align="center">Bill Duration</td>
                <td align="center">Total Charges</td>';
            
                if(count($data_array) > 0)
                {
                    
                    if(in_array('sell_rate',$data_array)) 
                    {
                        $tbl .= '<td align="center">Sell Rate</td>';
                    }
                    if(in_array('cost_rate',$data_array)) 
                    {
                        $tbl .= '<td align="center">Cost Rate</td>';
                    }
                    if(in_array('buy_initblock',$data_array)) 
                    {
                        $tbl .= '<td align="center">Buy Init Block</td>';
                    }
                    if(in_array('sell_initblock',$data_array)) 
                    {
                        $tbl .= '<td align="center">Sell Init Block</td>';
                    }
                    if(in_array('total_buy_cost',$data_array)) 
                    {
                        $tbl .= '<td align="center">Total Buy Cost</td>';
                    }
                }
                
				$tbl .= '</tr>';
			foreach ($data_cdr->result() as $row)
			{
				$tbl .=   '<tr>
					<td align="center" height="30">'.date("Y-m-d H:i:s", $row->created_time/1000000).'</td>
					<td align="center">'.$row->destination_number.'</td>
					<td align="center">'.$row->billsec.'</td>';
                    
                    if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                    {
                        if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                            $tbl .=  '<td align="center">'.$row->total_sell_cost.'</td>';
                        } else {
                            $tbl .= '<td align="center">0</td>';
                        }
                    }
                    else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                    {
                        if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                            $tbl .=  '<td align="center">'.$row->total_reseller_sell_cost.'</td>';
                        } else {
                            $tbl .= '<td align="center">0</td>';
                        }
                    }

					if(count($data_array) > 0)
                    {
                        
                        if(in_array('sell_rate',$data_array)) 
                        {
                            if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                            {
                                $tbl .= '<td align="center">'.$row->sell_rate.'</td>';
                                if($filter_display_results == 'sec')
                                {
                                    $sellrate       = $row->sell_rate / 60; // sell rate per sec
                                    $sellrate       = round($sellrate, 4);
                                }
                                else
                                {
                                    $sellrate       = $row->sell_rate; // sell rate by default is in min
                                }
                                $tbl .= '<td align="center">'.$sellrate.' '.$filter_display_results.'</td>';
                            }
                            else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                            {
                                $getRate = $this->groups_model->get_single_rate($row->reseller_rate_id , $row->reseller_rate_group);
                                $getRateRow = $getRate->row();
                                
                                if($filter_display_results == 'sec')
                                {
                                    $sellrate       = $getRateRow->sell_rate / 60; // sell rate per sec
                                    $sellrate       = round($sellrate, 4);
                                }
                                else
                                {
                                    $sellrate       = $getRateRow->sell_rate; // sell rate by default is in min
                                }
                                $tbl .= '<td align="center">'.$sellrate.' '.$filter_display_results.'</td>';
                            }
                        }
                        if(in_array('cost_rate',$data_array)) 
                        {
                            if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                            {
                                if($filter_display_results == 'sec')
                                {
                                    $costrate       = $row->cost_rate / 60; // sell rate per sec
                                    $costrate       = round($costrate, 4);
                                }
                                else
                                {
                                    $costrate       = $row->cost_rate; // sell rate by default is in min
                                }
                                $tbl .= '<td align="center">'.$costrate.' '.$filter_display_results.'</td>';
                            }
                            else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                            {
                                $getRate = $this->groups_model->get_single_rate($row->reseller_rate_id , $row->reseller_rate_group);
                                $getRateRow = $getRate->row();
                                
                                if($filter_display_results == 'sec')
                                {
                                    $costrate       = $getRateRow->cost_rate / 60; // sell rate per sec
                                    $costrate       = round($costrate, 4);
                                }
                                else
                                {
                                    $costrate       = $getRateRow->cost_rate; // sell rate by default is in min
                                }
                                $tbl .= '<td align="center">'.$costrate.' '.$filter_display_results.'</td>';
                            }
                        }
                        if(in_array('buy_initblock',$data_array)) 
                        {
                            if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                            {
                                $tbl .= '<td align="center">'.$row->buy_initblock.'</td>';
                            }
                            else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                            {
                                $getRate = $this->groups_model->get_single_rate($row->reseller_rate_id , $row->reseller_rate_group);
                                $getRateRow = $getRate->row();
                                
                                $tbl .= '<td align="center">'.$getRateRow->buy_initblock.'</td>';
                            }
                        }
                        if(in_array('sell_initblock',$data_array)) 
                        {
                            if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                            {
                                $tbl .= '<td align="center">'.$row->sell_initblock.'</td>';
                            }
                            else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                            {
                                $getRate = $this->groups_model->get_single_rate($row->reseller_rate_id , $row->reseller_rate_group);
                                $getRateRow = $getRate->row();
                                
                                $tbl .= '<td align="center">'.$getRateRow->sell_initblock.'</td>';
                            }
                        }
                        if(in_array('total_buy_cost',$data_array)) 
                        {
                            if($row->parent_reseller_id == $this->session->userdata('customer_id')) //directly apply rates
                            {
                                if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                                    $tbl .=  '<td align="center">'.$row->total_buy_cost.'</td>';
                                } else {
                                    $tbl .= '<td align="center">0</td>';
                                }
                            }
                            else if($row->parent_reseller_id != $this->session->userdata('customer_id') && $row->grand_parent_reseller_id == $this->session->userdata('customer_id')) //indirect
                            {
                                if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                                    $tbl .=  '<td align="center">'.$row->total_reseller_buy_cost.'</td>';
                                } else {
                                    $tbl .= '<td align="center">0</td>';
                                }
                            }
                        }
                    }
                    $tbl .= '</tr>';   

			}

			$tbl .=  '</table>';

			$this->pdf->writeHTML($tbl, true, false, false, false, '');

			//Close and output PDF document
			$this->pdf->Output('cdr.pdf', 'I');
		}
		else
		{
			redirect('reseller/cdr/');
		}
	}

	function export_excel()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');
        $filter_contents        = $this->input->get('filter_contents');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data_cdr   =   $this->cdr_model->export_cdr_data($filter_date_from, $filter_date_to, $filter_phonenum, $filter_customers, $filter_groups, $filter_call_type, $duration_from, $duration_to, $filter_sort, $filter_contents);

		if($data_cdr->num_rows() > 0)
		{
			$sql12 = "SELECT * FROM settings WHERE customer_id = '".$this->session->userdata('customer_id')."'";
            $query12 = $this->db->query($sql12);
            $row12 = $query12->row();
            $data_array = explode(',',$row12->value);
            
            $this->load->library('Spreadsheet_Excel_Writer');
			$workbook = new Spreadsheet_Excel_Writer();

			$format_bold =& $workbook->addFormat();
			$format_bold->setBold();

			$format_head =& $workbook->addFormat();
			$format_head->setBold();
			$format_head->setPattern(1);
			$format_head->setFgColor('red');
			$format_head->setBgColor('white');
			$format_head->setAlign('merge');

			$format_title =& $workbook->addFormat();
			$format_title->setBold();
			$format_title->setPattern(1);
			$format_title->setFgColor('white');
			$format_title->setBgColor('grey');
			$format_title->setAlign('merge');

			$format_cell =& $workbook->addFormat();
			$format_cell->setAlign('merge');

			$worksheet =& $workbook->addWorksheet();
			$worksheet->write(0, 0, "CDR EXPORT :: DigitalLinx.com", $format_head);
			// Couple of empty cells to make it look better
			$worksheet->write(0, 1, "", $format_head);
			$worksheet->write(0, 2, "", $format_head);
			$worksheet->write(0, 3, "", $format_head);
			$worksheet->write(0, 4, "", $format_head);
			$worksheet->write(0, 5, "", $format_head);
			$worksheet->write(0, 6, "", $format_head);
			$worksheet->write(0, 7, "", $format_head);
			$worksheet->write(0, 8, "", $format_head);
			$worksheet->write(0, 9, "", $format_head);
			$worksheet->write(0, 10, "", $format_head);
			$worksheet->write(0, 11, "", $format_head);
			$worksheet->write(0, 12, "", $format_head);
			$worksheet->write(0, 13, "", $format_head);
			$worksheet->write(0, 14, "", $format_head);
			$worksheet->write(0, 15, "", $format_head);
			$worksheet->write(0, 16, "", $format_head);
			$worksheet->write(0, 17, "", $format_head);
			$worksheet->write(0, 18, "", $format_head);
			$worksheet->write(0, 19, "", $format_head);
			$worksheet->write(0, 20, "", $format_head);
			$worksheet->write(0, 21, "", $format_head);
			$worksheet->write(0, 22, "", $format_head);

			$worksheet->write(1, 0, "Date/Time", $format_title);
			$worksheet->write(1, 1, "", $format_title);
			$worksheet->write(1, 2, "Destination", $format_title);
			$worksheet->write(1, 3, "", $format_title);
			$worksheet->write(1, 4, "Bill Duration", $format_title);
			$worksheet->write(1, 5, "", $format_title);
            $worksheet->write(1, 6, "Total Charges", $format_title);
			$worksheet->write(1, 7, "", $format_title);
            
            $increment = 7;
            if(count($data_array) > 0)
            {
                if(in_array('caller_id_number',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Caller ID Num", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('duration',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Duration", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('network_addr',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Network Address", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('username',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Username", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('sip_user_agent',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "SIP User Agent", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('ani',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "ANI", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('cidr',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "CIDR", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('sell_rate',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Sell Rate", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('cost_rate',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Cost Rate", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('buy_initblock',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Buy Init Block", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('sell_initblock',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Sell Init Block", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('total_buy_cost',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Total Buy Cost", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('gateway',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Gateway", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
                if(in_array('total_failed_gateways',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Failed Gateways", $format_title);
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "", $format_title);
                }
            }
            

			$count = 2;
			foreach($data_cdr->result() as $row)
			{
				$worksheet->write($count, 0, "".date("Y-m-d H:i:s", $row->created_time/1000000)."", $format_cell);
				$worksheet->write($count, 1, "", $format_cell);
				$worksheet->write($count, 2, "".$row->destination_number."", $format_cell);
				$worksheet->write($count, 3, "", $format_cell);
				$worksheet->write($count, 4, "".$row->billsec."", $format_cell);
				$worksheet->write($count, 5, "", $format_cell);
                if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
					$worksheet->write($count, 6, "".$row->total_sell_cost."", $format_cell);
					$worksheet->write($count, 7, "", $format_cell);
				}
				else{
					$worksheet->write($count, 6, "0", $format_cell);
					$worksheet->write($count, 7, "", $format_cell);
				}
                
                $increment = 7;
				if(count($data_array) > 0)
                {
                    if(in_array('caller_id_number',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->caller_id_number."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('duration',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->duration."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('network_addr',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->network_addr."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('username',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->username."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('sip_user_agent',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->sip_user_agent."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('ani',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->ani."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('cidr',$data_array))
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->cidr."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('sell_rate',$data_array)) 
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->sell_rate."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('cost_rate',$data_array)) 
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->cost_rate."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('buy_initblock',$data_array)) 
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->buy_initblock."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('sell_initblock',$data_array)) 
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->sell_initblock."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                    if(in_array('total_buy_cost',$data_array)) 
                    {
                        if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "".$row->total_buy_cost."", $format_cell);
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "", $format_cell);
                        } else {
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "0", $format_cell);
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "", $format_cell);
                        }
                    }
                    if(in_array('gateway',$data_array)) 
                    {
                        if($row->gateway != '')
                        {
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "".$row->gateway."", $format_cell);
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "", $format_cell);
                        }
                        else
                        {
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "-", $format_cell);
                            $increment = $increment + 1;
                            $worksheet->write($count, $increment, "", $format_cell);
                        }
                    }
                    if(in_array('total_failed_gateways',$data_array)) 
                    {
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "".$row->total_failed_gateways."", $format_cell);
                        $increment = $increment + 1;
                        $worksheet->write($count, $increment, "", $format_cell);
                    }
                }
                
				$count = $count + 1;
			}
			$workbook->send('test.xls');
			$workbook->close();
		}
		else
		{
			redirect('cdr/');
		}
	}

	function export_csv()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_caller_ip       = $this->input->get('filter_caller_ip');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_gateways        = $this->input->get('filter_gateways');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}

		$data_cdr   =   $this->cdr_model->export_cdr_data_csv($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort);

		if($data_cdr->num_rows() > 0)
		{
			$headers = array('Date/Time', 'Destination', 'Bill Duration', 'Hangup Cause', 'IP Address', 'Username', 'Sell Rate', 'Sell Init Block', 'Cost Rate','Buy Init Block', 'Total Charges', 'Total Cost', 'Margin', 'Markup');

			$fp = fopen('php://output', 'w');
			if ($fp) {
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="export.csv"');
				header('Pragma: no-cache');
				header('Expires: 0');
				fputcsv($fp, $headers);

				foreach ($data_cdr->result_array() as $row)
				{
					print '"' . stripslashes(implode('","',$row)) . "\"\n";
				}
				die;
			}
		}
		else
		{
			redirect('cdr/');
		}
	}
    
    function tooltip()
    {
        $id = $this->input->post('id');
        $data = $this->cdr_model->get_parent_cdr_data($id);
        
        if($data->num_rows() > 0)
        {
        $txt = '<table><tr><td width="100px" style="color:#000">GATEWAY</td><td width="100px" style="color:#000">HANGUP CAUSE</td></tr>';
        foreach($data->result() as $row)
        {
            $txt .= '<tr><td>'.$row->gateway.'</td><td>'.$row->hangup_cause.'</td></tr>';
        }
        $txt .= '<table>';
        echo $txt;
        }
        else
        {
            echo "No Result Found";
        }
    }
}
<?php

use Mpdf\Tag\P;

defined('BASEPATH') or exit('No direct script access allowed');
require_once(dirname(__FILE__) . "/General.php");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

class Election extends General
{
    protected $title = 'Election';
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        if ($this->session->userdata("id_admin")) {
            $date_time = $this->get_current_date_time();
            $data["date_time"] = $date_time["dateTime"];
            $role = $this->session->userdata("role");
            $data['title'] = $this->title;
            $this->load_template_view('templates/election/election', $data);
        } else {
            redirect(base_url('Login'));
        }
    }
    public function manage_election()
    {
        if ($this->session->userdata("id_admin")) {
            $date_time = $this->get_current_date_time();
            $data["date_time"] = $date_time["dateTime"];
            $role = $this->session->userdata("role");
            $data['title'] = $this->title;
            $this->load_template_view('templates/election/manage_election', $data);
        } else {
            redirect(base_url('Login'));
        }
    }
    public function manage_position()
    {
        if ($this->session->userdata("id_admin")) {
            $date_time = $this->get_current_date_time();
            $data["date_time"] = $date_time["dateTime"];
            $role = $this->session->userdata("role");
            $data['title'] = $this->title;
            $this->load_template_view('templates/election/manage_position', $data);
        } else {
            redirect(base_url('Login'));
        }
    }
    public function get_positions()
    {
        $datatable = $this->input->post('datatable');
        $query['search']['append'] = "";
        $query['search']['total'] = "";
        // $status = $datatable['query']['status'];
        $where_name = "";
        $stat_where = "";
        $order = "position_name";
        // if (!empty($status) && trim($status) !== 'All') {
        //     $stat_where = " AND con.status_concern = '".$status."'";
        // }
        $query['query'] = "SELECT election_pos_id,position_name,position_description,position_status,datetime_added FROM tbl_election_position WHERE election_pos_id != 0";
        if ($datatable['query']['searchField'] != '') {
            $keyword = $datatable['query']['searchField'];
            $where = "(position_name LIKE '%" . $keyword . "%' OR position_description LIKE '%" . $keyword . "%')";
            $query['search']['append'] = " AND ($where)";
            $query['search']['total'] = " AND ($where)";
        }
        $page = $datatable['pagination']['page'];
        $pages = $datatable['pagination']['page'] * $datatable['pagination']['perpage'];
        $perpage = $datatable['pagination']['perpage'];
        $sort = (isset($datatable['sort']['sort'])) ? $datatable['sort']['sort'] : '';
        $field = (isset($datatable['sort']['field'])) ? $datatable['sort']['field'] : '';
        if (isset($query['search']['append'])) {
            $query['query'] .= $query['search']['append'];
            $search = $query['query'] . $query['search']['total'];
            $total = count($this->general_model->custom_query($search));
            $pages = ceil($total / $perpage);
            $page = ($page > $pages) ? 1 : $page;
        } else {
            $total = count($this->general_model->custom_query($query['query']));
        }
        if (isset($datatable['pagination'])) {
            $offset = $page * $perpage - $perpage;
            $limit = ' LIMIT ' . $offset . ' ,' . $perpage;
            // $order = $field ? " ORDER BY  " . $field : '';
            $order = $field ? " ORDER BY  " . $order : '';
            if ($perpage < 0) {
                $limit = ' LIMIT 0';
            }
            $query['query'] .= $order . ' ' . $sort . $limit;
        }
        $data = $this->general_model->custom_query($query['query']);
        $meta = [
            "page" => intval($page),
            "pages" => intval($pages),
            "perpage" => intval($perpage),
            "total" => $total,
            "sort" => $sort,
            "field" => $field,
        ];
        echo json_encode(['meta' => $meta, 'data' => $data]);
    }
    public function save_position()
    {
        $date_time = $this->get_current_date_time();
        $pos["datetime_added"] = $date_time["dateTime"];
        $pos['position_name']  = $this->input->post('title');
        $pos['position_description'] = $this->input->post('desc');
        $pos['position_status'] = "active";
        $pos['added_by'] = $this->session->userdata("id_admin");
        $this->general_model->insert_vals($pos, "tbl_election_position");
    }
    public function change_position_status()
    {
        $id = $this->input->post('id');
        $stat['position_status'] = $this->input->post('stat');
        $this->general_model->update_vals($stat, "election_pos_id = $id", "tbl_election_position");
    }
    public function get_position_details(){
        $id = $this->input->post('id');
        $position_info = $this->general_model->custom_query('SELECT election_pos_id,position_name,position_description,position_status,datetime_added FROM tbl_election_position WHERE election_pos_id = '. $id);
        echo json_encode($position_info);
    }
    public function update_position(){
        $id = $this->input->post('pos_id');
        $pos['position_name']  = $this->input->post('title');
        $pos['position_description'] = $this->input->post('desc');
        $this->general_model->update_vals($pos, "election_pos_id = $id", "tbl_election_position");
    }
}

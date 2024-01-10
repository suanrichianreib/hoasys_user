<?php

use Mpdf\Tag\P;

defined('BASEPATH') or exit('No direct script access allowed');
require_once(dirname(__FILE__) . "/General.php");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

class Concerns extends General
{
    protected $title = 'Concerns';
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
            $this->load_template_view('templates/concerns/concerns', $data);
        } else {
            redirect(base_url('Login'));
        }
    }
    public function get_concerns()
    {
        $datatable = $this->input->post('datatable');
        $query['search']['append'] = "";
        $query['search']['total'] = "";
		$status = $datatable['query']['status'];
        $where_name = "";
		$stat_where = "";
        $order = "con.desc_concern";

		if (!empty($status) && trim($status) !== 'All') {
            $stat_where = " AND con.status_concern = '".$status."'";
        }

        $query['query'] = "SELECT con.id_concern,con.title_concern,con.desc_concern, con.datesent_concern, con.status_concern,con.id_admin,con.id_ho,con.isReceivedEmail,ho.lname,ho.fname,ho.email_add FROM tbl_concern con, tbl_homeowner ho WHERE con.id_ho = ho.id_ho " . $where_name."".$stat_where;
        if ($datatable['query']['searchField'] != '') {
            $keyword = $datatable['query']['searchField'];
        $where = "(con.title_concern LIKE '%" . $keyword . "%' OR con.desc_concern LIKE '%" . $keyword . "%' OR ho.fname LIKE '%" . $keyword . "%' OR ho.lname LIKE '%" . $keyword . "%')";
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
    public function get_concern_details(){
        $id = $this->input->post('id');
        $ann_info = $this->general_model->custom_query('SELECT con.id_concern,con.title_concern,con.desc_concern, con.datesent_concern, con.status_concern,con.id_admin,con.id_ho,con.isReceivedEmail,ho.lname,ho.fname,ho.email_add FROM tbl_concern con, tbl_homeowner ho WHERE con.id_ho = ho.id_ho AND con.id_concern ='. $id);
        echo json_encode($ann_info);
    }
    public function send_concern_reply(){
        $email_to = $this->input->post('email_to');
        $subject = $this->input->post('subject');
        $email = $this->input->post('email_content');
        $concern_id = $this->input->post('concern_id');
        // Update concern 
        $em['isReceivedEmail'] = 1;
        $this->general_model->update_vals($em, "id_concern = $concern_id", "tbl_concern");
        // Send email
        $this->email_sending_concern_reply($email_to, $subject, $email);
    }
        // email sending back end
    public function email_sending_concern_reply($email_to, $subject, $email)
    {
            $this->load->library('email');
            $ser = 'http://' . $_SERVER['SERVER_NAME'];
            $config = array(
                'protocol' => 'smtp',
                'smtp_host' => 'ssl://smtp.gmail.com',
                'smtp_timeout' => 30,
                'smtp_port' => 465,
                'smtp_user' => 'ggn1cdo@gmail.com',
                'smtp_pass' => 'asklaymjpayxhkyi',
                'charset' => 'utf-8',
                'mailtype' => 'html',
                'newline' => '\r\n'
            );
            $message = $email;
            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->set_crlf("\r\n");
            $this->email->from("ggn1cdo@gmail.com");
            $this->email->to($email_to);
            $this->email->subject($subject);
            $this->email->message($message);
            if ($this->email->send()) {
                echo "Mail successful";
            } else {
                echo "Sorry";
                print_r($this->email->print_debugger());
            }
    }
    public function change_concern_status(){
        $id = $this->input->post('id');
        $em['status_concern'] = $this->input->post('status');
        $this->general_model->update_vals($em, "id_concern = $id", "tbl_concern");
    }
}

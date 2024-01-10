<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(dirname(__FILE__) . "/General.php");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");


class Dashboard extends General
{
    protected $title = 'Admin';

    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata("id_ho")) {
        } else {
            redirect(base_url('Login'));
        }
    }

    public function index()
    {
        $data['title'] = $this->title;
        // $role = $this->session->userdata("role");
        // if ($role == "Admin") {
        $this->load_template_view('templates/dashboard/index', $data);
        // } else {
            // redirect(base_url('participants'));
        //     redirect(base_url('homeowners'));
        // }
    }
    public function fetchData_sample() {
        $data['graph_data'] = $this->general_model->get_data();

        // Convert the data to JSON and echo it
        echo json_encode($data);
    }
	public function pie_chart() {
        // Fetch data from the model
        $chartData = $this->general_model->getChartData();

        // Prepare data for JSON response
        $data = [];
        foreach ($chartData as $row) {
            $data[] = [
                'name' => $row['category'],
                'y'    => (int)$row['value'],
            ];
        }

        // Send JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}

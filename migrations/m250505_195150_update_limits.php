<?php

use yii\db\Migration;

/**
 * Class m250505195150_update_limits
 */
class m250505_195150_update_limits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //storage_limits
        $this->update('storage_limits', ['storage' => 30, 'duration' => 31, 'number_of_projects' => 1], ['user_type' => 'bronze']);
        $this->update('storage_limits', ['storage' => 250,'duration' => 360, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('storage_limits', ['storage' => 1024,'duration' => 720, 'number_of_projects' => 20], ['user_type' => 'gold']);

        //storage_autoaccept
        $this->update('storage_autoaccept', ['storage' => 30,  'autoaccept_number' => 0], ['user_type' => 'bronze']);
        $this->update('storage_autoaccept', ['storage' => 250, 'autoaccept_number' => 1], ['user_type' => 'silver']);
        $this->update('storage_autoaccept', ['storage' => 1024, 'autoaccept_number' => 1], ['user_type' => 'gold']);

        //jupyter_limits
        $this->update('jupyter_limits', ['cores' => 2, 'ram' => 8, 'duration' => 31, 'number_of_projects' => 1], ['user_type' => 'bronze']);
        $this->update('jupyter_limits', ['cores' => 4, 'ram' => 16, 'duration' => 360, 'number_of_projects' => 4], ['user_type' => 'silver']);
        $this->update('jupyter_limits', ['cores' => 8, 'ram' => 32, 'duration' => 720, 'number_of_projects' => 999999], ['user_type' => 'gold']);

        //jupyter_autoaccept
        $this->update('jupyter_autoaccept', ['cores' => 2, 'ram' => 8,  'autoaccept_number' => 0], ['user_type' => 'bronze']);
        $this->update('jupyter_autoaccept', ['cores' => 4, 'ram' => 16,  'autoaccept_number' => 2], ['user_type' => 'silver']);
        $this->update('jupyter_autoaccept', ['cores' => 8, 'ram' => 32,  'autoaccept_number' => 3], ['user_type' => 'gold']);

        //ondemand_limits
        $this->update('ondemand_limits', ['num_of_jobs' => 500, 'cores' => 1, 'ram' => 16, 'duration' => 31, 'number_of_projects' => 1], ['user_type' => 'bronze']);
        $this->update('ondemand_limits', ['num_of_jobs' =>1000, 'cores' => 7, 'ram' => 60, 'duration' => 360, 'number_of_projects' => 999999], ['user_type' => 'silver']);
        $this->update('ondemand_limits', ['num_of_jobs' => 5000, 'cores' => 28, 'ram' => 240, 'duration' => 720, 'number_of_projects' => 999999], ['user_type' => 'gold']);

        //ondemand_autoaccept
        $this->update('ondemand_autoaccept', ['num_of_jobs' => 500, 'cores' => 1, 'ram' => 16, 'autoaccept_number' => 0], ['user_type' => 'bronze']);
        $this->update('ondemand_autoaccept', ['num_of_jobs' =>1000, 'cores' => 7, 'ram' => 60, 'autoaccept_number' => 3], ['user_type' => 'silver']);
        $this->update('ondemand_autoaccept', ['num_of_jobs' => 5000, 'cores' => 28, 'ram' => 240,  'autoaccept_number' => 3], ['user_type' => 'gold']);

        //service_limits
        $this->update('service_limits', ['vms' => 1, 'cores' => 4, 'ram' => 8, 'duration' => 31, 'number_of_projects' => 1], ['user_type' => 'bronze']);
        $this->update('service_limits', ['vms' => 1, 'cores' => 4, 'ram' => 16, 'duration' => 180, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('service_limits', ['vms' => 1, 'cores' => 8, 'ram' => 32, 'duration' => 360, 'number_of_projects' => 15], ['user_type' => 'gold']);

        //service_autoaccept
        $this->update('service_autoaccept', ['vms' => 1, 'cores' => 4, 'ram' => 8, 'autoaccept_number' => 0], ['user_type' => 'bronze']);
        $this->update('service_autoaccept', ['vms' => 1, 'cores' => 4, 'ram' => 16,  'autoaccept_number' => 1], ['user_type' => 'silver']);
        $this->update('service_autoaccept', ['vms' => 1, 'cores' => 8, 'ram' => 32,  'autoaccept_number' => 3], ['user_type' => 'gold']);

        //machine_compute_limits
        $this->update('machine_compute_limits', ['number_of_projects' => 0], ['user_type' => 'bronze']);
        $this->update('machine_compute_limits', ['number_of_projects' => 0], ['user_type' => 'silver']);
        $this->update('machine_compute_limits', ['number_of_projects' => 5], ['user_type' => 'gold']);


    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //storage_limits
        $this->update('storage_limits', ['storage' => 30, 'duration' => 180, 'number_of_projects' => 10], ['user_type' => 'bronze']);
        $this->update('storage_limits', ['storage' => 500,'duration' => 360, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('storage_limits', ['storage' => 10240,'duration' => 720, 'number_of_projects' => 10], ['user_type' => 'gold']);

        //jupyter_limits
        $this->update('jupyter_limits', ['cores' => 2, 'ram' => 16, 'duration' => 14, 'number_of_projects' => 10], ['user_type' => 'bronze']);
        $this->update('jupyter_limits', ['cores' => 32, 'ram' => 32, 'duration' => 30, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('jupyter_limits', ['cores' => 64, 'ram' => 64, 'duration' => 120, 'number_of_projects' => 10], ['user_type' => 'gold']);

        //ondemand_limits
        $this->update('ondemand_limits', ['num_of_jobs' => 500, 'cores' => 2, 'ram' => 16, 'duration' => 180, 'number_of_projects' => 10], ['user_type' => 'bronze']);
        $this->update('ondemand_limits', ['num_of_jobs' => 1000, 'cores' =>16, 'ram' => 64, 'duration' => 360, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('ondemand_limits', ['num_of_jobs' => 5000, 'cores' => 95, 'ram' => 980, 'duration' => 720, 'number_of_projects' => 10], ['user_type' => 'gold']);

        //service_limits
        $this->update('service_limits', ['vms' => 1, 'cores' => 4, 'ram' => 8, 'duration' => 180, 'number_of_projects' => 10], ['user_type' => 'bronze']);
        $this->update('service_limits', ['vms' => 1, 'cores' => 4, 'ram' => 16, 'duration' => 360, 'number_of_projects' => 10], ['user_type' => 'silver']);
        $this->update('service_limits', ['vms' => 1, 'cores' => 8, 'ram' => 32, 'duration' => 720, 'number_of_projects' => 10], ['user_type' => 'gold']);

        //machine_compute_limits
        $this->update('machine_compute_limits', ['number_of_projects' => 0], ['user_type' => 'bronze']);
        $this->update('machine_compute_limits', ['number_of_projects' => 0], ['user_type' => 'silver']);
        $this->update('machine_compute_limits', ['number_of_projects' => -1], ['user_type' => 'gold']);

    }


}
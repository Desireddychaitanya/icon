<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Playground extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('earning');
    $this->load->model('downline_model');
    $this->load->model('custom_income');
  }

  public function task1()
  {
    $e = $this->db->query("select signup_package from member where id='5737475' ")->result_array()[0];

    $ee = $this->db->query("select id,position,signup_package from member where id='5737475' ")->result_array()[0];

    $t = $this->db->query("select id,position,signup_package from member where id=" . $ee['position'])->result_array()[0];

    if ($e['signup_package'] == $ee['signup_package']) {
      $this->earning->pay_earning($ee['id'], 'admin', 'bonus amount', 'bonus amount ', 100, '', '');
    }
    if ($e['signup_package'] == $t['signup_package']) {
      $this->earning->pay_earning($t['id'], 'admin', 'bonus amount', 'bonus amount ', 50, '', '');
    }
  }

  public function task2()
  {
    $pd = $this->db_model->select_multi('*', 'plans', array('id' => 1));
//$md = $this->db_model->select_multi('*', 'member', array('id' =>$r->id ));
    $r = $this->db->query("select * from member")->result_array();
    foreach ($r as $value) {
   //screen_log($value);
      $d = $this->db_model->select_multi('*', 'member', array('id' => $value['id']));
      if (strlen($d->sponsor) > 1) {
        $re = $this->db->query("select count(*) as totalsponsor from member where sponsor=" . $d->sponsor)->result_array()[0];
        if ($re['totalsponsor'] >= 2) {
          $query = $this->db->query("select * from earning where type='Referral Income' and userid='$re->sponsor' and ref_id=" . $value['id']);
          if ($query->num_rows() == 0) {
            $this->earning->credit_direct_referral_income($d, $pd);
          }
        }
      }
    }
  }

  public function task3()
  {
    $plan_id > 0 ? $this->db->where(array('plan_id' => $plan_id)) : '';
    $level_income = $this->db->get('level_wise_income')->result();
    foreach ($level_income as $res) {
      $duration = $res->income_duration > 0 ? date('Y-m-d', strtotime('-' . $res->income_duration . ' days')) : date('Y-m-d', '-20 Years');

      $total_mem = $this->db_model->sum('total_member', 'level_wise_income', array('level_no <=' => $res->level_no, 'plan_id' => $res->plan_id));
      $level_total_direct_count = $this->db_model->sum('direct', 'level_wise_income', array('level_no <=' => $res->level_no, 'plan_id' => $res->plan_id));

      $join_condition = $level_total_direct_count > 0 ? 'INNER' : 'LEFT';

      $this->db->select(
        "t1.secret, t1.id, t1.name,t1.rank, t1.phone, t2.last_upgrade, t2.gift_level,SUBSTR(t2.level" . $res->level_no . ",2) as level_ids, IFNULL(t3.am,0) as amount, IFNULL(t4.dc,0) as total_direct_count"
      )
        ->from('member as t1')->where(array('join_time >=' => $duration, 't1.rank !=' => 'Member'))
        ->join(
          "(select userid,last_upgrade,gift_level,level" . $res->level_no . " from level_details where pid = " . $res->plan_id . " and total_active >= $total_mem and gift_level < " . $res->level_no . " and gift_level >= " . ($res->level_no - 1) . ") as t2",
          "t1.id = t2.userid",
          "INNER"
        )
        ->join(
          "(select sponsor, count(*) as dc from member where status = 'Active' group by 1 having dc >= " . $level_total_direct_count . ") as t4",
          "t1.id = t4.sponsor",
          "$join_condition"
        )
        ->join(
          "(select userid, sum(amount) as am from earning where secret =" . $res->id . " and type='" . $res->income_name . "' group by 1 ) as t3",
          "t1.id = t3.userid",
          "LEFT"
        )
        ->having(array('amount <=' => 0))
        ->group_by('1,2,3,4,5,6,7,8');
      $data = $this->db->get()->result();

            //->join("(select userid from level where pid = ".$res->plan_id." and level".$res->level_no.">=".$res->total_member.") as t5","t1.id = t5.userid","INNER")

      foreach ($data as $result) {
        $userid = $result->id;
        $secret = $result->secret;
        $username = $result->name;
        $phone = $result->phone;
        $total_direct_count = $result->total_direct_count;

        $condition_flag = False;
        if (config_item('level_income_sponsor_carry') == 'Yes') {
          if ($total_direct_count >= $level_total_direct_count) {
            $condition_flag = True;
          }
        } else {
          $level_direct_count = 0;
          if ($res->direct > 0) {
            $level_direct_count = $this->db_model->count_all(
              'member',
              array('sponsor' => $userid, 'status' => 'Active', 'activate_time >' => date('Y-m-d H:i:s', $result->last_upgrade))
            );
          }
          if (($level_direct_count >= $res->direct) && ($total_direct_count >= $level_total_direct_count)) {
            $condition_flag = True;
          }
        }

        if (config_item('level_by_count') == 'Yes') {
          $level_ids = rtrim($result->level_ids, ',');

          if (strlen($level_ids) > 0) {
            $level_active_count = $this->db->query(" SELECT count(*) as active FROM level_details WHERE secret IN (" . $level_ids . ") and e_status = 1 and pid = '" . $res->plan_id . "'" )->result_array()[0]['active'];
          } else {
            $level_active_count = 0;
          }

          $condition_flag = $level_active_count >= $res->total_member ? $condition_flag : False;
        }

        screen_log('Level Wise Income Details ' . $userid . ' , ' . $res->id . ' , ' . $res->income_name . ' , ' . $res->direct . ' , ' . $level_direct_count . ' , ' . $total_direct_count . ' , ' . $level_total_direct_count . ' ' . $level_active_count
        );

        if ($condition_flag) {
          $check_earnings = $this->db_model->sum('amount', 'earning', array('userid' => $userid, 'secret' => $res->id, 'type' => $res->income_name));

          if ($check_earnings <= 0) {
            if ($res->amount > 0) {
              $this->earning->pay_earning($userid, '', $res->income_name, 'Level ' . $res->level_no . ' Completion Income', $res->amount, '', $res->id);
            }

            if (($res->upgrade > 0) && ($res->auto_upgrade == 'Yes')) {
          $this->earning->pay_earning('admin',$userid,'Level Upgrade Fee','Level ' . $res->level_no . ' Upgrade Fee From - ' . $username, $res->upgrade,'', $res->id);

              $this->earning->add_deduction(
                $userid,
                'admin',
                $res->upgrade,
                'Level ' . $res->level_no . ' Upgrade Fee',
                'Level ' . $res->level_no . ' Upgrade Fee',
                $res->id,
                'account_transfer',
                'account_transfer'
              );

              $get_user_balance = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
              wallet_log('$get_user_balance ' . $get_user_balance);
              $arra = array('balance' => ($get_user_balance - $res->upgrade),);
              $this->db->where('userid', $userid);
              $this->db->update('wallet', $arra);
              wallet_log($this->db->last_query());
            }

            if ($result->gift_level < $res->level_no) {
              $arr = array(
                'gift_level' => $res->level_no,
                'last_upgrade' => time(),
              );
              $this->db->where('id', $userid);
              $this->db->update('member', $arr);

              $arr = array(
                'gift_level' => $res->level_no,
                'last_upgrade' => time(),
              );
              $this->db->where(array('userid' => $userid, 'pid' => $res->plan_id));
              $this->db->update('level_details', $arr);

              if (config_item('sms_on_join') == "Yes") {
                $sms = "Hello " . $username . ", Congratulations!!!\nYour ID " . $userid . " Has Successfully Completed Level " . $res->level_no . "\nRegards:\n" . config_item('company_name');
                $messvar = "Ok";
                $phone = "91" . $phone;
                $status = $this->common_model->sms($phone, urlencode($sms));
              }
            }

            screen_log(
              '$res->new_id ' . $res->new_id . ' $userid ' . $userid . ' $res->plan_new_id ' . $res->plan_new_id . ' $res->auto_upgrade ' . $res->auto_upgrade
            );

            if (($res->new_id == 'Yes') && ($userid != config_item('top_id')) && ($res->plan_new_id > 0) && ($res->auto_upgrade == 'Yes')) {
              $this->session->set_userdata('_id_upgrade_', 'Yes');
              if (config_item('same_tree') == 'Yes') {
                $md = $this->db_model->select_multi('*', 'member', array('id' => $userid));
                $pd = $this->db_model->select_multi('*', 'plans', array('id' => $res->plan_new_id));
                $data = array(
                  'status' => 'Active',
                  'activate_time' => date('Y-m-d H:i:s'),
                  'signup_package' => $res->plan_new_id,
                );
                $this->db->where('secret', $md->secret);
                $this->db->update('member', $data);

                $e_status = 1;
                $update_status = "UPDATE level_details SET e_status = " . $e_status . " WHERE userid = " . $md->id . " and pid  = " . $res->plan_new_id . " ";
                $this->db->query($update_status);

                $update_status = "UPDATE earning SET status = 'Pending' WHERE userid = " . $md->id . " and pid  = " . $res->plan_new_id . " and status = 'Hold'";
                $this->db->query($update_status);

                $this->earning->credit_joining_commission($pd, $md);
              } else {
                $this->registration_model->upgrade_member($userid, $res->plan_new_id, 'Auto');
              }
            }
          }
        }
      }
    }
  }

  public function task4()
  {
    $e = $this->db->query(
      "SELECT member.id, level.userid, level.level2,member.status
FROM member
INNER JOIN level
ON member.id=level.userid where level.pid=1 and member.status='Active' and level.level2 >= 2 "
    )->result_array();
    screen_log($e);
    foreach ($e as $value) {
      $array = array(
        'rank' => 'practice',
        ' rank_upgrade' => time(),
      );
      $this->db->where('id', $value['id']);
      $this->db->update('member', $array);
    }
  }

  public function task5()
  {
    $e = $this->db->query(
      "SELECT member.id, level.userid, level.level2,member.status
FROM member
INNER JOIN level
ON member.id=level.userid where level.pid=1 and member.status='Active' and level.level2 >= 2 "
    )->result_array();
    foreach ($e as $value) {
      $array = array(
        'reward_id' => 'bike',
        'userid'    => $value['id'],
        'date'      => date('Y-m-d H:i:s'),
      );
      $this->db->insert('rewards', $array);
    }
  }
  public function task6()
  {
    $last_date_month = date('Y-m-d', strtotime('last day of this month'));

    $first_date_month = date('Y-m-d', strtotime('first day of this month'));

    $monthcount = $this->db->query(
      "SELECT userid, count(type) as total, sum(amount) as sum FROM earning WHERE  date >= '$first_date_month' and date <= '$last_date_month' and (type ='First Pair Matching Comm' || type = 'Binary Commission') GROUP BY  userid HAVING total >=1"
    )->result_array();

    foreach ($monthcount as $value) {
      if ($value['total'] = 1) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', 'pairbonus', 1000, '');
      }
      if ($value['total'] = 2) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', 'pairbonus', 2000, '');
      }
      if ($value['total'] = 3) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', 'pairbonus', 5000, '');
      }
    }
  }

  public function task7()
  {
    $first_date_month = date('Y-m-d', strtotime('first day of this month'));

    $monthcount = $this->db->query(
      "SELECT userid, count(type) as total, sum(amount) as sum FROM earning WHERE  (type ='First Pair Matching Comm' || type = 'Binary Commission') GROUP BY  userid HAVING total >=1"
    )->result_array();

    foreach ($monthcount as $value) {
      if ($value['total'] = 1) {
        $array = array(
          'rank' => 'silver',
          ' rank_upgrade' => time(),
        );
        $this->db->where('id', $value['id']);
        $this->db->update('member', $array);
      }
      if ($value['total'] = 2) {
        $array = array(
          'rank' => 'gold',
          ' rank_upgrade' => time(),
        );
        $this->db->where('id', $value['id']);
        $this->db->update('member', $array);
      }
    }
  }
  public function task8()
  {
    $first_date_month = date('Y-m-d', strtotime('first day of this month'));
    debug_log('$first_date_month ' . $first_date_month);
    $monthcount = $this->db->query(
      "SELECT userid, count(type) as total, sum(amount) as sum FROM earning WHERE  (type ='First Pair Matching Comm' || type = 'Binary Commission') GROUP BY  userid HAVING total >=1"
    )->result_array();

    foreach ($monthcount as $value) {
      if ($value['total'] = 1) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', '10 pair fund', 1000, '');
      }
      if ($value['total'] = 2) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', '20 pair fund', 2000, '');
      }
      if ($value['total'] = 3) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus', '20 pair fund', 3000, '');
      }
    }
  }

  public function task10()
  {
    $first_date_week = date('Y-m-d', strtotime('monday this week'));

    $last_date_week = date('Y-m-d', strtotime('sunday this week'));

    $weekcount = $this->db->query(
      "SELECT userid, count(type) as total, sum(amount) as sum FROM earning WHERE  date >= '$first_date_week' and date <= '$last_date_week' and (type ='First Pair Matching Comm' || type = 'Binary Commission' ) GROUP BY  userid HAVING total >=1"
    )->result_array();

    foreach ($weekcount as $value) {
      screen_log($value);
      if ($value['total'] = 1) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus weekly', '10 pair fund', 1000, '');
      }
      if ($value['total'] = 2) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus weekly', '20 pair fund', 2000, '');
      }
      if ($value['total'] = 3) {
        $this->earning->pay_earning($value['userid'], 'admin', 'pair bonus weekly', '20 pair fund', 3000, '');
      }
    }
  }
}

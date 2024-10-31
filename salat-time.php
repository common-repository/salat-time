<?php
/**
 * @package Salat Time
 * @author Faaiq Ahmed
 * @version 1.0
 */
/*
Plugin Name: Salat Timing
Description: Show Salat Times, muslim prayer time
Author: Faaiq Ahmed, Technical Architect PHP, faaiqsj@gmail.com
Version: 1.0
*/

global $ugf_db_version;	
$ugf_db_version = "2.5";


if(!defined('ST_PATH')) {
	define( 'ST_PATH', plugin_dir_path(__FILE__) );	
}

include_once(ST_PATH . 'PrayTime.php');
include_once(ST_PATH . 'widget-salat-time.php');


class MuslimSalatTimes { 
    
	function __construct() {
        
        add_action('wp', array($this,'start_session'));

        add_action('admin_menu', array($this,'hst_menu'));
        
        add_action('wp_head', array($this,'front_head'));
        
        //add_action('init', array($this,'process_post'));
        
        add_action( 'admin_init', array($this,'register_mysettings' ));
        
        add_action('admin_head', array($this,'admin_head_load'));
        
        register_activation_hook(__FILE__, array($this,'install'));
		
        register_deactivation_hook(__FILE__, array($this,'uninstall'));
	}
    
    function start_session() {
        if ( !session_id() ) {
            session_start();
        }
    }

    function set_html_content_type() {
        return 'text/html';
    }
    
    function front_head() {
        $url = plugins_url() . '/salat-time';
        print '<link rel="stylesheet" type="text/css" href="'.$url.'/salat-time.css" />';
        
    }
	
	function admin_head_load() {
		$url = plugins_url();
        print '<script/>var admin_url = "' . home_url("/wp-admin/") . '"</script>';
	}
    
    function hst_menu() {
        global $current_user, $wpdb;
        $role = $wpdb->prefix . 'capabilities';
        $current_user->role = array_keys($current_user->$role);
        $current_role = $current_user->role[0];
        
        add_menu_page('Salat Time', 'Salat Time', 'administrator', 'hst', array($this,'hst_main'));
        
        

    }
    
    
    function hst_main() {
        global $wpdb;
        $sql ="select * from ". $wpdb->prefix . "ugf_forms order by form_name";
        $form_rows = $wpdb->get_results($sql);
        
        ?>
        <div class="wrap">
        <h2>Muslim Salat Time</h2>
        <form method="post" action="options.php">
        <?php settings_fields( 'hst-group' ); ?>
        <?php do_settings_sections( 'hst-group' ); ?>
        <table class="form-table">
        <?php
            $time_zones_arr = $this->time_zones_arr();
        ?>
        <tr valign="top">
        <th scope="row">Time Zone</th>
        <td><select name="mst_time_zone"><?php print $this->build_select($time_zones_arr,get_option('mst_time_zone'));?></select></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Latitude</th>
        <td><input type="text" name="mst_latitude" value="<?php echo get_option('mst_latitude'); ?>" /></td>
        </tr>
        
        
        <tr valign="top">
        <th scope="row">Longitue</th>
        <td><input type="text" name="mst_longitude" value="<?php echo get_option('mst_longitude'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Method</th>
        <td><select name="mst_method"><?php print $this->build_select($this->get_method(),get_option('mst_method'));?></select></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Total Days In Widget</th>
        <td><input type="text" name="mst_total_days" value="<?php echo get_option('mst_total_days'); ?>" />
        <em>Total number of days you want to show in the table.</em>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Tatal Days Before Current Days:</th>
        <td><input type="text" name="mst_back_days" value="<?php echo get_option('mst_back_days'); ?>" />
        <em>If you want to show salat timing from previous days from current day,then enter the number.</em>
        </td>
        </tr>                       
        
        </table>
        <a href="widgets.php">Go To Widget Section After Settings the parameter</a>
        
        <?php submit_button(); ?>
        </form>
        </div>
        <?php
    }
    
    function get_method() {
        $arr = array();
        $arr["0"] = "Shia Ithna-Ashari";
        $arr["1"] = "University of Islamic Sciences, Karachi";
        $arr["2"] = "Islamic Society of North America (ISNA)";
        $arr["3"] = "Muslim World League (MWL)";
        $arr["4"] = "Umm al-Qura, Makkah";
        $arr["5"] = "Egyptian General Authority of Survey";
        $arr["7"] = "Institute of Geophysics, University of Tehran";
        return $arr;
    }
    
    function build_select($arr,$sel) {
        $option .= '<option value=""></option>';
        foreach($arr as $k => $v) {
            if($sel == $k) {
                $option .= '<option value="'.$k.'" selected="selected">'.$v.'</option>';    
            }else {
                $option .= '<option value="'.$k.'">'.$v.'</option>';    
            }
            
        }
        return $option;
    }
    
    function register_mysettings() {
        register_setting( 'hst-group', 'mst_time_zone' );
        register_setting( 'hst-group', 'mst_latitude' );
        register_setting( 'hst-group', 'mst_longitude' );
        register_setting( 'hst-group', 'mst_method' );
        register_setting( 'hst-group', 'mst_back_days' );
        register_setting( 'hst-group', 'mst_total_days' );
    }
    
    function hst_settings() {
        ?>
         <div class="wrap">
         <h2>UGF Smtp Settings</h2>
         <div id="form_list_view"></div>
         <?php form_option( 'smtp' ) ?>
         <?php form_option( 'smtp' ) ?>
            
        </div>
        <?php
    }
    
    

    function install() {
		global $wpdb;
		global $hst_db_version;
		
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		add_option('ccpo_db_version', $hst_db_version);
		
    }	


	
    function uninstall() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
    }
    
    function time_zones_arr() {
        return $timezones = array(
            '-11.00'       => "(GMT-11:00) Midway Island",
            '-11.00'             => "(GMT-11:00) Samoa",
            '-10.00'            => "(GMT-10:00) Hawaii",
            '-9.00'            => "(GMT-09:00) Alaska",
            '-8.00'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
            '-8.00'      => "(GMT-08:00) Tijuana",
            '-7.00'           => "(GMT-07:00) Arizona",
            '-7.00'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
            '-7.00'    => "(GMT-07:00) Chihuahua",
            '-7.00'     => "(GMT-07:00) Mazatlan",
            '-6.00'  => "(GMT-06:00) Mexico City",
            '-6.00'    => "(GMT-06:00) Monterrey",
            '-6.00'  => "(GMT-06:00) Saskatchewan",
            '-6.00'           => "(GMT-06:00) Central Time (US &amp; Canada)",
            '-5.00'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
            '-5.00'      => "(GMT-05:00) Indiana (East)",
            '-5.00'       => "(GMT-05:00) Bogota",
            '-5.00'         => "(GMT-05:00) Lima",
            '-4.50'      => "(GMT-04:30) Caracas",
            '-4.00'      => "(GMT-04:00) Atlantic Time (Canada)",
            '-4.00'       => "(GMT-04:00) La Paz",
            '-4.00'     => "(GMT-04:00) Santiago",
            '-3.50'  => "(GMT-03:30) Newfoundland",
            '-3.00' => "(GMT-03:00) Buenos Aires",
            '-3.00'            => "(GMT-03:00) Greenland",
            '-2.00'     => "(GMT-02:00) Stanley",
            '-1.00'      => "(GMT-01:00) Azores",
            '-1.00'  => "(GMT-01:00) Cape Verde Is.",
            '0'    => "(GMT) Casablanca",
            '0'        => "(GMT) Dublin",
            '0'        => "(GMT) Lisbon",
            '0'        => "(GMT) London",
            '0'      => "(GMT) Monrovia",
            '1.00'     => "(GMT+01:00) Amsterdam",
            '1.00'      => "(GMT+01:00) Belgrade",
            '1.00'        => "(GMT+01:00) Berlin",
            '1.00'    => "(GMT+01:00) Bratislava",
            '1.00'      => "(GMT+01:00) Brussels",
            '1.00'      => "(GMT+01:00) Budapest",
            '1.00'    => "(GMT+01:00) Copenhagen",
            '1.00'     => "(GMT+01:00) Ljubljana",
            '1.00'        => "(GMT+01:00) Madrid",
            '1.00'         => "(GMT+01:00) Paris",
            '1.00'        => "(GMT+01:00) Prague",
            '1.00'          => "(GMT+01:00) Rome",
            '1.00'      => "(GMT+01:00) Sarajevo",
            '1.00'        => "(GMT+01:00) Skopje",
            '1.00'     => "(GMT+01:00) Stockholm",
            '1.00'        => "(GMT+01:00) Vienna",
            '1.00'        => "(GMT+01:00) Warsaw",
            '1.00'        => "(GMT+01:00) Zagreb",
            '2.00'        => "(GMT+02:00) Athens",
            '2.00'     => "(GMT+02:00) Bucharest",
            '2.00'         => "(GMT+02:00) Cairo",
            '2.00'        => "(GMT+02:00) Harare",
            '2.00'      => "(GMT+02:00) Helsinki",
            '2.00'      => "(GMT+02:00) Istanbul",
            '2.00'       => "(GMT+02:00) Jerusalem",
            '2.00'          => "(GMT+02:00) Kyiv",
            '2.00'         => "(GMT+02:00) Minsk",
            '2.00'          => "(GMT+02:00) Riga",
            '2.00'         => "(GMT+02:00) Sofia",
            '2.00'       => "(GMT+02:00) Tallinn",
            '2.00'       => "(GMT+02:00) Vilnius",
            '3.00'         => "(GMT+03:00) Baghdad",
            '3.00'          => "(GMT+03:00) Kuwait",
            '3.00'       => "(GMT+03:00) Nairobi",
            '3.00'          => "(GMT+03:00) Riyadh",
            '3.50'          => "(GMT+03:30) Tehran",
            '4.00'        => "(GMT+04:00) Moscow",
            '4.00'            => "(GMT+04:00) Baku",
            '4.00'     => "(GMT+04:00) Volgograd",
            '4.00'          => "(GMT+04:00) Muscat",
            '4.00'         => "(GMT+04:00) Tbilisi",
            '4.00'         => "(GMT+04:00) Yerevan",
            '4.50'           => "(GMT+04:30) Kabul",
            '5.00'         => "(GMT+05:00) Karachi",
            '5.00'        => "(GMT+05:00) Tashkent",
            '5.50'         => "(GMT+05:30) Kolkata",
            '5.75'       => "(GMT+05:45) Kathmandu",
            '6.00'   => "(GMT+06:00) Ekaterinburg",
            '6.00'          => "(GMT+06:00) Almaty",
            '6.00'           => "(GMT+06:00) Dhaka",
            '7.00'     => "(GMT+07:00) Novosibirsk",
            '7.00'         => "(GMT+07:00) Bangkok",
            '7.00'         => "(GMT+07:00) Jakarta",
            '8.00'     => "(GMT+08:00) Krasnoyarsk",
            '8.00'       => "(GMT+08:00) Chongqing",
            '8.00'       => "(GMT+08:00) Hong Kong",
            '8.00'    => "(GMT+08:00) Kuala Lumpur",
            '8.00'      => "(GMT+08:00) Perth",
            '8.00'       => "(GMT+08:00) Singapore",
            '8.00'          => "(GMT+08:00) Taipei",
            '8.00'     => "(GMT+08:00) Ulaan Bataar",
            '8.00'          => "(GMT+08:00) Urumqi",
            '9.00'         => "(GMT+09:00) Irkutsk",
            '9.00'           => "(GMT+09:00) Seoul",
            '9.00'           => "(GMT+09:00) Tokyo",
            '9.00'   => "(GMT+09:30) Adelaide",
            '9.50'     => "(GMT+09:30) Darwin",
            '10.00'         => "(GMT+10:00) Yakutsk",
            '10.00'   => "(GMT+10:00) Brisbane",
            '10.00'   => "(GMT+10:00) Canberra",
            '10.00'         => "(GMT+10:00) Guam",
            '10.00'     => "(GMT+10:00) Hobart",
            '10.00'  => "(GMT+10:00) Melbourne",
            '10.00' => "(GMT+10:00) Port Moresby",
            '10.00'     => "(GMT+10:00) Sydney",
            '11.00'     => "(GMT+11:00) Vladivostok",
            '12.00'         => "(GMT+12:00) Magadan",
            '12.00'     => "(GMT+12:00) Auckland",
            '12.00'         => "(GMT+12:00) Fiji",
        );
        
    }
    
    
    
    
}

new MuslimSalatTimes();


